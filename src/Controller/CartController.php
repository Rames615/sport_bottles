<?php

namespace App\Controller;

use App\Service\CartService;
use App\Repository\ProductRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/panier', name: 'app_cart')]
class CartController extends AbstractController
{
    // @phpstan-ignore property.onlyWritten
    public function __construct(private CartService $cartService, private EntityManagerInterface $em) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à votre panier.');
            return $this->redirectToRoute('app_login');
        }

        $cart = $this->cartService->getCartWithItems($user);
        $total = $this->cartService->getCartTotal($cart);

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    #[Route('/add/{id}', name: 'add', methods: ['POST'])]
    public function add(int $id, Request $request, ProductRepository $productRepository): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter des produits au panier.');
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('cart_add', $request->request->get('_token'))) {
            return $this->redirectToRoute('app_product');
        }

        $product = $productRepository->find($id);
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('app_product');
        }

        $added = $this->cartService->addProduct($user, $product);
        
        if (!$added) {
            $stock = $product->getStock();
            if ($stock !== null && $stock <= 0) {
                $this->addFlash('error', 'Ce produit n\'est plus en stock.');
            } else {
                $this->addFlash('error', 'Impossible d\'ajouter ce produit au panier. Stock insuffisant.');
            }
            return $this->redirectToRoute('app_product');
        }

        $this->addFlash('success', 'Produit ajouté au panier avec succès');
        return $this->redirectToRoute('app_cartindex');
    }

    #[Route('/add-ajax/{id}', name: 'add_ajax', methods: ['POST'])]
    public function addAjax(int $id, Request $request, ProductRepository $productRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json([
                'ok' => false,
                'message' => 'Vous devez être connecté pour ajouter des produits au panier.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->isCsrfTokenValid('cart_add', $request->request->get('_token'))) {
            return $this->json([
                'ok' => false,
                'message' => 'Token CSRF invalide',
            ], Response::HTTP_FORBIDDEN);
        }

        $product = $productRepository->find($id);
        if (!$product) {
            return $this->json([
                'ok' => false,
                'message' => 'Produit introuvable',
            ], Response::HTTP_NOT_FOUND);
        }

        $added = $this->cartService->addProduct($user, $product);
        
        if (!$added) {
            $stock = $product->getStock();
            if ($stock !== null && $stock <= 0) {
                return $this->json([
                    'ok' => false,
                    'message' => 'Ce produit n\'est plus en stock.',
                ], Response::HTTP_BAD_REQUEST);
            }

            return $this->json([
                'ok' => false,
                'message' => 'Stock insuffisant',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get the current cart item count
        $cart = $this->cartService->getCartWithItems($user);
        $itemCount = count($cart->getItems());

        return $this->json([
            'ok' => true,
            'message' => 'Produit ajouté au panier avec succès',
            'count' => $itemCount,
        ]);
    }

    #[Route('/update/{itemId}', name: 'update', methods: ['POST'])]
    public function update(int $itemId, Request $request): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('cart_update', $request->request->get('_token'))) {
            return $this->redirectToRoute('app_cartindex');
        }

        $quantity = (int) $request->request->get('quantity', 1);
        $this->cartService->updateItemQuantity($user, $itemId, $quantity);

        return $this->redirectToRoute('app_cartindex');
    }

    #[Route('/remove/{itemId}', name: 'remove', methods: ['POST'])]
    public function remove(int $itemId, Request $request): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('cart_remove', $request->request->get('_token'))) {
            return $this->redirectToRoute('app_cartindex');
        }

        $this->cartService->removeItemById($user, $itemId);

        return $this->redirectToRoute('app_cartindex');
    }

    #[Route('/checkout', name: 'checkout', methods: ['GET'])]
    public function checkout(): Response
    {
        return $this->redirectToRoute('checkout_shipping');
    }

    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clear(Request $request): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('cart_clear', $request->request->get('_token'))) {
            return $this->redirectToRoute('app_cartindex');
        }

        $this->cartService->clear($user);
        $this->addFlash('success', 'Votre panier a été vidé avec succès');

        return $this->redirectToRoute('app_cartindex');
    }

}
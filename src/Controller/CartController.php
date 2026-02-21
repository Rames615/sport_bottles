<?php

namespace App\Controller;

use App\Service\CartService;
use App\Repository\ProductRepository;
use App\Entity\User;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier', name: 'app_cart')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cartService) {}

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
            return new JsonResponse(['ok' => false, 'message' => 'Vous devez être connecté.'], 401);
        }

        // Expect CSRF token either as form field or header
        $token = $request->request->get('_token') ?? $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('cart_add', $token)) {
            return new JsonResponse(['ok' => false, 'message' => 'Token invalide'], 400);
        }

        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['ok' => false, 'message' => 'Produit introuvable'], 404);
        }

        $added = $this->cartService->addProduct($user, $product);
        if (!$added) {
            $stock = $product->getStock();
            if ($stock !== null && $stock <= 0) {
                return new JsonResponse(['ok' => false, 'message' => 'Ce produit n\'est plus en stock.'], 409);
            }
            return new JsonResponse(['ok' => false, 'message' => 'Impossible d\'ajouter le produit.'], 409);
        }

        // Return updated cart count so frontend can update badge
        $count = $this->cartService->getCartItemCount($user);

        return new JsonResponse(['ok' => true, 'message' => 'Produit ajouté', 'count' => $count]);
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
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->addFlash('error', 'Vous devez être connecté pour procéder au paiement.');
            return $this->redirectToRoute('app_login');
        }

        $result = $this->cartService->prepareCheckout($user);
        if (!$result['ok']) {
            $this->addFlash('error', $result['message'] ?? 'Problème lors de la préparation du paiement');
            return $this->redirectToRoute('app_cartindex');
        }

        // For now just render a summary - real payment flow would start here
        return $this->render('cart/checkout.html.twig', [
            'cart' => $result['cart'],
            'total' => $result['total'],
        ]);
    }

    #[Route('/confirm', name: 'confirm', methods: ['POST'])]
    public function confirm(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->addFlash('error', 'Vous devez être connecté pour finaliser la commande.');
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('cart_confirm', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_cartindex');
        }

        $result = $this->cartService->prepareCheckout($user);
        if (!$result['ok']) {
            $this->addFlash('error', $result['message'] ?? 'Problème lors de la validation du panier');
            return $this->redirectToRoute('app_cartindex');
        }

        // 1️Création de la commande
        $order = new Order();
        $order->setUser($user);
        // store amount in cents as integer
        $total = $result['total'] ?? 0.0;
        $order->setTotalAmount((int) round($total * 100));
        $order->setStatus('pending');
        $order->setCreatedAt(new \DateTimeImmutable());

        // Sauvegarde en base
        $entityManager->persist($order);
        $entityManager->flush();

        // Vider le panier
        $this->cartService->clear($user);

        // Ajouter le message
        $this->addFlash('success', 'Merci d’avoir passé commande sur le site Sports Bottles.');

        return $this->redirectToRoute('app_home');
    }
}
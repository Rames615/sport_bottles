<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/checkout', name: 'checkout_')]
final class CheckoutController extends AbstractController
{
    public function __construct(
        private \App\Service\CartService $cartService,
    ) {}

    /**
     * Displays the order confirmation page with cart summary before payment.
     */
    #[Route('/confirm', name: 'confirm', methods: ['GET'])]
    public function confirm(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $result = $this->cartService->prepareCheckout($user);

        if (!$result['ok']) {
            $this->addFlash('error', $result['message'] ?? 'Votre panier est vide.');
            return $this->redirectToRoute('app_cartindex');
        }

        return $this->render('checkout/confirm.html.twig', [
            'cart'  => $result['cart'],
            'total' => $result['total'],
        ]);
    }

    /**
     * Creates the Stripe Checkout session and redirects the user to Stripe.
     */
    #[Route('/pay', name: 'pay', methods: ['POST'])]
    public function pay(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('checkout_pay', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_cartindex');
        }

        $result = $this->cartService->prepareCheckout($user);

        if (!$result['ok']) {
            $this->addFlash('error', $result['message'] ?? 'Panier vide ou invalide.');
            return $this->redirectToRoute('app_cartindex');
        }

        $cart  = $result['cart'];
        $total = (int) round($result['total'] * 100);

        // Create the order in DB with status "pending"
        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($total);
        $order->setStatus('pending');
        $order->setCreatedAt(new \DateTimeImmutable());
        $em->persist($order);
        $em->flush();

        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        if (!$stripeSecret) {
            $this->addFlash('error', 'Configuration Stripe manquante. Contactez l\'administrateur.');
            return $this->redirectToRoute('app_cartindex');
        }

        Stripe::setApiKey($stripeSecret);

        $lineItems = [];
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            if ($product) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'eur',
                        'product_data' => ['name' => $product->getDesignation()],
                        'unit_amount'  => (int) round((float) $item->getUnitPrice() * 100),
                    ],
                    'quantity' => $item->getQuantity(),
                ];
            }
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $order->setStripeSessionId($session->id);
        $em->flush();

        return new RedirectResponse($session->url);
    }
}
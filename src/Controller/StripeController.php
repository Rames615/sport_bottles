<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\PaymentIntent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class StripeController extends AbstractController
{
    public function __construct(
        private \App\Service\CartService $cartService,
    ) {}

    #[Route('/stripe/checkout', name: 'stripe_checkout')]
    public function checkout(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('stripe_checkout', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_cartindex');
        }

        // Get actual cart total from CartService
        $checkoutResult = $this->cartService->prepareCheckout($user);
        if (!$checkoutResult['ok']) {
            $this->addFlash('error', $checkoutResult['message'] ?? 'Panier vide ou invalide');
            return $this->redirectToRoute('app_cartindex');
        }

        $cart = $checkoutResult['cart'];
        $totalAmount = (int) round($checkoutResult['total'] * 100); // Convert to cents

        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($totalAmount);
        $order->setStatus('pending');
        $order->setCreatedAt(new \DateTimeImmutable());

        $em->persist($order);
        $em->flush();

        // ✅ On utilise directement .env
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // Build line items from cart
        $lineItems = [];
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            if ($product) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $product->getDesignation(),
                        ],
                        'unit_amount' => (int) round((float) $item->getUnitPrice() * 100),
                    ],
                    'quantity' => $item->getQuantity(),
                ];
            }
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl(
                'stripe_success',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl(
                'stripe_cancel',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        $order->setStripeSessionId($session->id);
        $em->flush();

        return new RedirectResponse($session->url);
    }

    #[Route('/stripe/success', name: 'stripe_success')]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('app_home');
        }

        $order = $em->getRepository(Order::class)
            ->findOneBy(['stripeSessionId' => $sessionId]);

        if (!$order) {
            throw $this->createNotFoundException();
        }

        // If order is already paid (webhook processed quickly), redirect to completion
        if ($order->getStatus() === 'paid') {
            return $this->redirectToRoute('stripe_payment_complete', ['orderId' => $order->getId()]);
        }

        // Otherwise show polling page waiting for webhook
        return $this->render('stripe/success.html.twig', [
            'order' => $order,
            'sessionId' => $sessionId,
        ]);
    }

    #[Route('/stripe/payment-complete/{orderId}', name: 'stripe_payment_complete')]
    public function paymentComplete(int $orderId, EntityManagerInterface $em): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_home');
        }

        $order = $em->getRepository(Order::class)->find($orderId);
        if (!$order || $order->getUser()?->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_home');
        }

        // Only process if order is actually paid
        if ($order->getStatus() === 'paid') {
            // Clear the user's cart
            $this->cartService->clear($user);

            // Add success flash message
            $this->addFlash('success', 'Merci d\'avoir passé commande sur le site Sports Bottles. Votre paiement a été confirmé.');
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/stripe/order-status/{sessionId}', name: 'stripe_order_status', methods: ['GET'])]
    public function orderStatus(string $sessionId, EntityManagerInterface $em): Response
    {
        $order = $em->getRepository(Order::class)
            ->findOneBy(['stripeSessionId' => $sessionId]);

        if (!$order) {
            return $this->json(['ok' => false, 'message' => 'Order not found'], 404);
        }

        return $this->json(['ok' => true, 'status' => $order->getStatus()]);
    }

    #[Route('/stripe/cancel', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }

    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function webhook(Request $request, EntityManagerInterface $em, \Psr\Log\LoggerInterface $logger): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null;

        $logger->info('Stripe webhook received', ['hasSig' => $sigHeader !== null, 'endpointSecretSet' => (bool) $endpointSecret]);

        try {
            if ($endpointSecret) {
                $event = Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $endpointSecret
                );
            } else {
                // Fallback: try to decode payload without signature (useful for local testing only)
                $event = json_decode($payload);
                if (!$event) {
                    throw new \RuntimeException('Invalid payload');
                }
            }
        } catch (\Exception $e) {
            $logger->error('Invalid Stripe webhook: ' . $e->getMessage());
            return new Response('Invalid webhook', 400);
        }

        // Handle checkout.session.completed
        $type = is_object($event) && isset($event->type) ? $event->type : ($event['type'] ?? null);
        $logger->info('Stripe event type', ['type' => $type]);

        if ($type === 'checkout.session.completed' || ($event->type ?? null) === 'checkout.session.completed') {
            $session = is_object($event) ? $event->data->object : $event['data']['object'];

            $logger->info('checkout.session.completed received', ['session_id' => $session->id ?? null]);

            $order = $em->getRepository(Order::class)
                ->findOneBy(['stripeSessionId' => $session->id]);

            if (!$order) {
                $logger->warning('Order not found for session', ['session_id' => $session->id ?? null]);
            } else {
                if ($order->getStatus() !== 'paid') {
                    $order->setStatus('paid');
                    $em->flush();
                    $logger->info('Order status updated to paid', ['order_id' => $order->getId()]);
                } else {
                    $logger->info('Order already paid', ['order_id' => $order->getId()]);
                }
            }
        }

        return new Response('Webhook handled', 200);
    }
}

<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Service\CartService;
use App\Service\OrderService;
use App\Service\StripeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class StripeController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private StripeService $stripeService,
        private OrderService $orderService,
    ) {}

    #[Route('/stripe/checkout', name: 'stripe_checkout')]
    public function checkout(Request $request): RedirectResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('stripe_checkout', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_cartindex');
        }

        $checkoutResult = $this->cartService->prepareCheckout($user);
        if (!$checkoutResult['ok']) {
            $this->addFlash('error', $checkoutResult['message']);
            return $this->redirectToRoute('app_cartindex');
        }

        $cart  = $checkoutResult['cart'];
        $total = (int) round($checkoutResult['total'] * 100);

        $order = $this->orderService->createOrder($user, $total, 'pending', '');

        try {
            $successUrl = $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl  = $this->generateUrl('stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $session = $this->stripeService->createCheckoutSession($cart, $successUrl, $cancelUrl);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_cartindex');
        }

        $stripeUrl = $session->url;
        if (!$stripeUrl) {
            $this->addFlash('error', 'Impossible de créer la session de paiement Stripe.');
            return $this->redirectToRoute('app_cartindex');
        }

        $this->orderService->attachStripeSession($order, $session->id);

        return new RedirectResponse($stripeUrl);
    }

    #[Route('/stripe/success', name: 'stripe_success')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('app_home');
        }

        $order = $this->orderService->findByStripeSessionId($sessionId);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        // Sync status with Stripe right now (one-time, on page load)
        try {
            $wasPaid = $this->stripeService->syncPaymentStatus($sessionId, $order);

            if ($wasPaid) {
                $user = $this->getUser();
                if ($user instanceof User) {
                    $this->cartService->deductStockForUser($user);
                }

                $this->orderService->flush();
                $this->stripeService->sendOrderConfirmationEmail($order, $sessionId);
            }
        } catch (\Exception) {
            // Non-blocking: the JS polling will catch it
        }

        return $this->render('stripe/success.html.twig', [
            'order'     => $order,
            'sessionId' => $sessionId,
        ]);
    }

    #[Route('/stripe/payment-complete/{id}', name: 'stripe_payment_complete')]
    public function paymentComplete(Order $order): Response
    {
        $user = $this->getUser();

        $userFromOrder = ($user instanceof User) ? $user : $order->getUser();

        if ($userFromOrder instanceof User) {
            $this->cartService->clear($userFromOrder);
        }

        $this->addFlash('success', 'Merci pour votre commande ! Paiement confirmé.');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/stripe/order-status/{sessionId}', name: 'stripe_order_status', methods: ['GET'])]
    public function orderStatus(string $sessionId): Response
    {
        $order = $this->orderService->findByStripeSessionId($sessionId);

        if (!$order) {
            return $this->json(['ok' => false, 'message' => 'Commande introuvable'], 404);
        }

        return $this->json(['ok' => true, 'status' => $order->getStatus()]);
    }

    #[Route('/stripe/cancel', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }

    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST', 'GET'])]
    public function webhook(Request $request, \Psr\Log\LoggerInterface $logger): Response
    {
        // Health check for local tests (Stripe CLI, etc.)
        if ($request->getMethod() === 'GET') {
            $logger->info('Stripe webhook health check (GET)');
            return new Response('Webhook endpoint OK', 200);
        }

        $payload   = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        $logger->info('Stripe webhook reçu', ['signature' => $sigHeader]);
        $logger->debug('Payload brut webhook', ['payload' => $payload]);

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $sigHeader);
        } catch (\UnexpectedValueException $e) {
            $logger->error('Webhook Stripe invalide : ' . $e->getMessage());
            return new Response('Webhook invalide', 400);
        } catch (\Exception $e) {
            $logger->error('Erreur lors du traitement du webhook Stripe : ' . $e->getMessage());
            return new Response('Erreur serveur', 500);
        }

        $type = $this->stripeService->getEventType($event);
        $logger->info('Stripe event', ['type' => $type]);

        if ($type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($event, $logger);
        }

        if ($type === 'payment_intent.payment_failed') {
            $this->handlePaymentFailed($event, $logger);
        }

        return new Response('Webhook traité', 200);
    }

    /**
     * @param \Stripe\Event|array<string, mixed> $event
     */
    private function handleCheckoutCompleted(\Stripe\Event|array $event, \Psr\Log\LoggerInterface $logger): void
    {
        $sessionId = $this->stripeService->getSessionIdFromEvent($event);
        if (!$sessionId) {
            return;
        }

        $order = $this->orderService->findByStripeSessionId($sessionId);

        if ($order && $order->getStatus() !== 'paid') {
            $orderUser = $order->getUser();
            if ($orderUser instanceof User) {
                $this->cartService->deductStockForUser($orderUser);
            }

            $this->orderService->markAsPaid($order);
            $logger->info('Commande payée via webhook', ['order_id' => $order->getId()]);

            $this->stripeService->sendOrderConfirmationEmail($order, $sessionId);
        }
    }

    /**
     * @param \Stripe\Event|array<string, mixed> $event
     */
    private function handlePaymentFailed(\Stripe\Event|array $event, \Psr\Log\LoggerInterface $logger): void
    {
        $data = $this->stripeService->getFailedPaymentData($event);
        $logger->warning('Paiement échoué', ['payment_intent' => $data['id']]);

        if (!empty($data['order_id'])) {
            $order = $this->orderService->find((int) $data['order_id']);
            if ($order) {
                $this->orderService->markAsFailed($order);
                $logger->info('Commande marquée failed via webhook', ['order_id' => $order->getId()]);
            }
        }
    }
}
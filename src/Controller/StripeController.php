<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Service\MailerService;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
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
        private MailerService $mailerService,
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

        $checkoutResult = $this->cartService->prepareCheckout($user);
        if (!$checkoutResult['ok']) {
            $this->addFlash('error', $checkoutResult['message'] ?? 'Panier vide ou invalide');
            return $this->redirectToRoute('app_cartindex');
        }

        $cart  = $checkoutResult['cart'];
        $total = (int) round($checkoutResult['total'] * 100);

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
            'success_url'          => $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $this->generateUrl('stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
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
    
        $order = $em->getRepository(Order::class)->findOneBy(['stripeSessionId' => $sessionId]);
    
        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }
    
        // Sync status with Stripe right now (one-time, on page load)
        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        if ($stripeSecret) {
            Stripe::setApiKey($stripeSecret);
            try {
                $stripeSession = Session::retrieve($sessionId);
                if ($stripeSession->payment_status === 'paid' && $order->getStatus() !== 'paid') {
                    $order->setStatus('paid');
                    $em->flush();

                    // Send order confirmation email (fallback if webhook hasn't fired yet)
                    try {
                        $items = [];
                        $lineItems = Session::allLineItems($sessionId, ['limit' => 100]);
                        foreach ($lineItems->data as $li) {
                            $items[] = [
                                'name'      => $li->description,
                                'quantity'  => $li->quantity,
                                'unitPrice' => $li->price->unit_amount / 100,
                                'subtotal'  => $li->amount_total / 100,
                            ];
                        }
                        $this->mailerService->sendOrderConfirmation($order, $items);
                    } catch (\Exception $e) {
                        // Non-blocking: email failure shouldn't break the success page
                    }
                }
            } catch (\Exception $e) {
                // Non-blocking: the JS polling will catch it
            }
        }
    
        // Always render the template — JS polling handles the rest
        return $this->render('stripe/success.html.twig', [
            'order'     => $order,
            'sessionId' => $sessionId,
        ]);
    }
     
    // Endpoint pour le webhook Stripe (POST) et health check (GET)
    #[Route('/stripe/payment-complete/{id}', name: 'stripe_payment_complete')]
    public function paymentComplete(Order $order, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
    
        // Clear cart using user from order (session may be lost after Stripe redirect)
        $userFromOrder = ($user instanceof User) ? $user : $order->getUser();
    
        if ($userFromOrder instanceof User) {
            $this->cartService->clear($userFromOrder);
        }
    
        $this->addFlash('success', 'Merci pour votre commande ! Paiement confirmé.');
        return $this->redirectToRoute('app_home');
    }
    
    #[Route('/stripe/order-status/{sessionId}', name: 'stripe_order_status', methods: ['GET'])]
    public function orderStatus(string $sessionId, EntityManagerInterface $em): Response
    {
        $order = $em->getRepository(Order::class)->findOneBy(['stripeSessionId' => $sessionId]);

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
    public function webhook(Request $request, EntityManagerInterface $em, \Psr\Log\LoggerInterface $logger): Response
    {
        // Health check pour les tests locaux (Stripe CLI, etc.)
        if ($request->getMethod() === 'GET') {
            $logger->info('Stripe webhook health check (GET)');
            return new Response('Webhook endpoint OK', 200);
        }

        $payload        = $request->getContent();
        $sigHeader      = $request->headers->get('Stripe-Signature');
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null;

        $logger->info('Stripe webhook reçu', ['signature' => $sigHeader]);
        $logger->debug('Payload brut webhook', ['payload' => $payload]);

        try {
            if ($endpointSecret) {
                $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            } else {
                $logger->warning('STRIPE_WEBHOOK_SECRET absent — traitement sans vérification de signature');
                $event = json_decode($payload, true);
                if (!$event) {
                    throw new \RuntimeException('Payload invalide');
                }
            }
        } catch (\UnexpectedValueException | \DomainException $e) {
            $logger->error('Webhook Stripe invalide : ' . $e->getMessage());
            return new Response('Webhook invalide', 400);
        } catch (\Exception $e) {
            $logger->error('Erreur lors du traitement du webhook Stripe : ' . $e->getMessage());
            return new Response('Erreur serveur', 500);
        }

        $type = is_object($event) && isset($event->type) ? $event->type : ($event['type'] ?? null);
        $logger->info('Stripe event', ['type' => $type, 'id' => is_object($event) ? $event->id ?? null : ($event['id'] ?? null)]);

        if ($type === 'checkout.session.completed') {
            $session = is_object($event) ? $event->data->object : $event['data']['object'];
            $sessionId = is_object($session) ? $session->id : $session['id'];
            $order   = $em->getRepository(Order::class)->findOneBy(['stripeSessionId' => $sessionId]);

            if ($order && $order->getStatus() !== 'paid') {
                $order->setStatus('paid');
                $em->flush();
                $logger->info('Commande payée via webhook', ['order_id' => $order->getId()]);

                // Send order confirmation email with line items from Stripe
                try {
                    $items = [];
                    $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
                    if ($stripeSecret) {
                        Stripe::setApiKey($stripeSecret);
                        $lineItems = Session::allLineItems($sessionId, ['limit' => 100]);
                        foreach ($lineItems->data as $li) {
                            $items[] = [
                                'name'      => $li->description,
                                'quantity'  => $li->quantity,
                                'unitPrice' => $li->price->unit_amount / 100,
                                'subtotal'  => $li->amount_total / 100,
                            ];
                        }
                    }
                    $this->mailerService->sendOrderConfirmation($order, $items);
                } catch (\Exception $e) {
                    $logger->error('Failed to send order confirmation email from webhook', ['exception' => $e->getMessage()]);
                }
            }
        }

        if ($type === 'payment_intent.payment_failed') {
            $pi = is_object($event) ? $event->data->object : $event['data']['object'];
            $logger->warning('Paiement échoué', ['payment_intent' => $pi->id ?? ($pi['id'] ?? null)]);

            if (isset($pi->metadata) || isset($pi['metadata'])) {
                $metadata = is_object($pi) ? $pi->metadata : $pi['metadata'];
                if (!empty($metadata['order_id'])) {
                    $order = $em->getRepository(Order::class)->find($metadata['order_id']);
                    if ($order) {
                        $order->setStatus('failed');
                        $em->flush();
                        $logger->info('Commande marquée failed via webhook', ['order_id' => $order->getId()]);
                    }
                }
            }
        }

        return new Response('Webhook traité', 200);
    }
}
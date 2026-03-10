<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\MailerService;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/webhook', name: 'webhook_')]
final class WebhookController extends AbstractController
{
    public function __construct(
        private readonly MailerService $mailerService,
    ) {}
    /**
     * Health check (GET) + Stripe event handler (POST).
     */
    #[Route('/stripe', name: 'stripe', methods: ['GET', 'POST'])]
    public function stripe(Request $request, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        // Health check for local testing (Stripe CLI, etc.)
        if ($request->isMethod('GET')) {
            $logger->info('Stripe webhook health check (GET)');
            return new Response('Webhook endpoint OK', 200);
        }

        $payload        = $request->getContent();
        $sigHeader      = $request->headers->get('Stripe-Signature');
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null;

        $logger->info('Stripe webhook received', ['signature' => $sigHeader]);

        try {
            if ($endpointSecret) {
                $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            } else {
                $logger->warning('STRIPE_WEBHOOK_SECRET missing — processing without signature verification.');
                $event = json_decode($payload, true);
                if (!$event) {
                    throw new \RuntimeException('Invalid payload.');
                }
            }
        } catch (\UnexpectedValueException | \DomainException $e) {
            $logger->error('Invalid Stripe webhook: ' . $e->getMessage());
            return new Response('Invalid webhook', 400);
        } catch (\Exception $e) {
            $logger->error('Webhook processing error: ' . $e->getMessage());
            return new Response('Server error', 500);
        }

        $type = is_object($event) ? ($event->type ?? null) : ($event['type'] ?? null);
        $logger->info('Stripe event received', [
            'type' => $type,
            'id'   => is_object($event) ? ($event->id ?? null) : ($event['id'] ?? null),
        ]);

        match ($type) {
            'checkout.session.completed'   => $this->handleSessionCompleted($event, $em, $logger),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event, $em, $logger),
            default                        => $logger->info('Unhandled Stripe event type: ' . $type),
        };

        return new Response('Webhook processed', 200);
    }

    private function handleSessionCompleted(mixed $event, EntityManagerInterface $em, LoggerInterface $logger): void
    {
        $session = is_object($event) ? $event->data->object : $event['data']['object'];
        $sessionId = is_object($session) ? $session->id : $session['id'];

        $order = $em->getRepository(Order::class)->findOneBy(['stripeSessionId' => $sessionId]);

        if ($order && $order->getStatus() !== 'paid') {
            $order->setStatus('paid');
            $em->flush();
            $logger->info('Order marked as paid via webhook', ['order_id' => $order->getId()]);

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

    private function handlePaymentFailed(mixed $event, EntityManagerInterface $em, LoggerInterface $logger): void
    {
        $pi       = is_object($event) ? $event->data->object : $event['data']['object'];
        $piId     = is_object($pi) ? ($pi->id ?? null) : ($pi['id'] ?? null);
        $metadata = is_object($pi) ? ($pi->metadata ?? null) : ($pi['metadata'] ?? null);

        $logger->warning('Payment failed', ['payment_intent' => $piId]);

        $orderId = is_object($metadata) ? ($metadata->order_id ?? null) : ($metadata['order_id'] ?? null);

        if ($orderId) {
            $order = $em->getRepository(Order::class)->find($orderId);
            if ($order) {
                $order->setStatus('failed');
                $em->flush();
                $logger->info('Order marked as failed via webhook', ['order_id' => $order->getId()]);
            }
        }
    }
}
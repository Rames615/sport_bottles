<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Order;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

class StripeService
{
    public function __construct(
        private MailerService $mailerService,
    ) {}

    /**
     * Returns the Stripe secret key or null if not configured.
     */
    public function getApiKey(): ?string
    {
        return $_ENV['STRIPE_SECRET_KEY'] ?? null;
    }

    /**
     * Build Stripe line items array from a Cart entity.
     *
     * @return list<array{price_data: array{currency: string, product_data: array{name: string}, unit_amount: int}, quantity: int}>
     */
    public function buildLineItems(Cart $cart): array
    {
        $lineItems = [];

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            if ($product) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'eur',
                        'product_data' => ['name' => $product->getDesignation() ?? ''],
                        'unit_amount'  => (int) round((float) $item->getUnitPrice() * 100),
                    ],
                    'quantity' => $item->getQuantity() ?? 1,
                ];
            }
        }

        return $lineItems;
    }

    /**
     * Create a Stripe Checkout Session and return it.
     *
     * @throws \RuntimeException if the API key is missing
     */
    public function createCheckoutSession(Cart $cart, string $successUrl, string $cancelUrl): Session
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            throw new \RuntimeException('Configuration Stripe manquante. Contactez l\'administrateur.');
        }

        Stripe::setApiKey($apiKey);

        $lineItems = $this->buildLineItems($cart);

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $successUrl,
            'cancel_url'           => $cancelUrl,
        ]);
    }

    /**
     * Sync order status with Stripe. Returns true if the order was marked as paid.
     */
    public function syncPaymentStatus(string $sessionId, Order $order): bool
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return false;
        }

        Stripe::setApiKey($apiKey);

        $stripeSession = Session::retrieve($sessionId);

        if ($stripeSession->payment_status === 'paid' && $order->getStatus() !== 'paid') {
            $order->setStatus('paid');
            return true;
        }

        return false;
    }

    /**
     * Fetch formatted line items from a Stripe session.
     *
     * @return list<array{name: string, quantity: int, unitPrice: float, subtotal: float}>
     */
    public function fetchLineItems(string $sessionId): array
    {
        $apiKey = $this->getApiKey();
        if (!$apiKey) {
            return [];
        }

        Stripe::setApiKey($apiKey);

        $items = [];
        $lineItems = Session::allLineItems($sessionId, ['limit' => 100]);

        foreach ($lineItems->data as $li) {
            $items[] = [
                'name'      => $li->description ?? '',
                'quantity'  => $li->quantity ?? 0,
                'unitPrice' => ($li->price->unit_amount ?? 0) / 100,
                'subtotal'  => ($li->amount_total ?? 0) / 100,
            ];
        }

        return $items;
    }

    /**
     * Send order confirmation email with line items from Stripe.
     * Non-blocking: catches any exception.
     */
    public function sendOrderConfirmationEmail(Order $order, string $sessionId): void
    {
        try {
            $items = $this->fetchLineItems($sessionId);
            $this->mailerService->sendOrderConfirmation($order, $items);
        } catch (\Exception) {
            // Non-blocking: email failure shouldn't break the flow
        }
    }

    /**
     * Construct and validate a Stripe webhook event.
     *
     * @return \Stripe\Event|array<string, mixed>
     *
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     *
     * @return \Stripe\Event|array<string, mixed>
     */
    public function constructWebhookEvent(string $payload, ?string $sigHeader): \Stripe\Event|array
    {
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null;

        if ($endpointSecret) {
            return Webhook::constructEvent($payload, (string) $sigHeader, (string) $endpointSecret);
        }

        $event = json_decode($payload, true);
        if (!$event) {
            throw new \RuntimeException('Payload invalide');
        }

        return $event;
    }

    /**
     * Extract event type from a Stripe event (object or array).
     *
     * @param \Stripe\Event|array<string, mixed> $event
     */
    public function getEventType(\Stripe\Event|array $event): ?string
    {
        if (is_object($event) && isset($event->type)) {
            return $event->type;
        }

        return $event['type'] ?? null;
    }

    /**
     * Extract session ID from a checkout.session.completed event.
     *
     * @param \Stripe\Event|array<string, mixed> $event
     */
    public function getSessionIdFromEvent(\Stripe\Event|array $event): ?string
    {
        $session = is_object($event) ? $event->data->object : ($event['data']['object'] ?? null);
        if (!$session) {
            return null;
        }

        return is_object($session) ? $session->id : ($session['id'] ?? null);
    }

    /**
     * Extract payment intent data from a payment_intent.payment_failed event.
     *
     * @param \Stripe\Event|array<string, mixed> $event
     * @return array{id: string|null, order_id: string|null}
     */
    public function getFailedPaymentData(\Stripe\Event|array $event): array
    {
        $pi = is_object($event) ? $event->data->object : ($event['data']['object'] ?? null);

        $id = null;
        $orderId = null;

        if ($pi) {
            $id = is_object($pi) ? ($pi->id ?? null) : ($pi['id'] ?? null);
            $metadata = is_object($pi) ? ($pi->metadata ?? null) : ($pi['metadata'] ?? null);
            if ($metadata) {
                $orderId = is_object($metadata) ? ($metadata->order_id ?? null) : ($metadata['order_id'] ?? null);
            }
        }

        return ['id' => $id, 'order_id' => $orderId];
    }
}

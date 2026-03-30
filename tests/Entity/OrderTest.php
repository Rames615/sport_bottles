<?php

namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Order.
 * Vérifie la création, les valeurs par défaut, la référence auto-générée
 * et les transitions de statut.
 */
class OrderTest extends TestCase
{
    public function testOrderHasDefaultValues(): void
    {
        $order = new Order();

        $this->assertSame('pending', $order->getStatus());
        $this->assertNotNull($order->getReference());
        $this->assertNotNull($order->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $order->getCreatedAt());
    }

    public function testReferenceStartsWithORD(): void
    {
        $order = new Order();

        $this->assertStringStartsWith('ORD-', $order->getReference());
    }

    public function testReferenceHasCorrectLength(): void
    {
        $order = new Order();
        $reference = $order->getReference();

        // 'ORD-' (4 chars) + 10 hex chars = 14
        $this->assertSame(14, strlen($reference));
    }

    public function testReferenceIsUnique(): void
    {
        $order1 = new Order();
        $order2 = new Order();

        $this->assertNotSame($order1->getReference(), $order2->getReference());
    }

    public function testSetAndGetUser(): void
    {
        $order = new Order();
        $user = new User();
        $user->setEmail('client@example.com');

        $order->setUser($user);

        $this->assertSame($user, $order->getUser());
    }

    public function testSetAndGetTotalAmount(): void
    {
        $order = new Order();
        $order->setTotalAmount(4999); // 49.99 € en centimes

        $this->assertSame(4999, $order->getTotalAmount());
    }

    public function testTotalAmountInCentsConversion(): void
    {
        $order = new Order();
        $priceEuros = 29.99;
        $order->setTotalAmount((int) round($priceEuros * 100));

        $this->assertSame(2999, $order->getTotalAmount());
        $this->assertSame(29.99, $order->getTotalAmount() / 100);
    }

    public function testSetAndGetStatus(): void
    {
        $order = new Order();

        $order->setStatus('paid');
        $this->assertSame('paid', $order->getStatus());

        $order->setStatus('failed');
        $this->assertSame('failed', $order->getStatus());
    }

    public function testSetAndGetStripeSessionId(): void
    {
        $order = new Order();
        $sessionId = 'cs_test_abc123xyz';
        $order->setStripeSessionId($sessionId);

        $this->assertSame($sessionId, $order->getStripeSessionId());
    }

    public function testStripeSessionIdNullByDefault(): void
    {
        $order = new Order();

        $this->assertNull($order->getStripeSessionId());
    }

    public function testSetAndGetShippingAddress(): void
    {
        $order = new Order();
        $order->setShippingAddress('12 Rue du Sport, 75001 Paris');

        $this->assertSame('12 Rue du Sport, 75001 Paris', $order->getShippingAddress());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $order = new Order();
        $date = new \DateTimeImmutable('2026-01-15 10:30:00');
        $order->setCreatedAt($date);

        $this->assertSame($date, $order->getCreatedAt());
    }

    public function testSetAndGetReference(): void
    {
        $order = new Order();
        $order->setReference('ORD-CUSTOM12345');

        $this->assertSame('ORD-CUSTOM12345', $order->getReference());
    }
}

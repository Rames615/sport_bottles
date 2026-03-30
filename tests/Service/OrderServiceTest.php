<?php

namespace App\Tests\Service;

use App\Entity\Order;
use App\Entity\ShippingAddress;
use App\Entity\User;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour OrderService.
 * Vérifie la création de commande, les transitions de statut,
 * l'association Stripe et la validation d'adresse.
 */
class OrderServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private OrderService $orderService;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->orderService = new OrderService($this->em);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('client@sportsbottles.com');
        return $user;
    }

    // ── createOrder ────────────────────────────────────────────────────

    public function testCreateOrder(): void
    {
        $user = $this->createUser();

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $order = $this->orderService->createOrder(
            $user,
            4999, // 49.99 €
            'pending',
            '12 Rue du Sport, 75001 Paris'
        );

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame($user, $order->getUser());
        $this->assertSame(4999, $order->getTotalAmount());
        $this->assertSame('pending', $order->getStatus());
        $this->assertSame('12 Rue du Sport, 75001 Paris', $order->getShippingAddress());
        $this->assertNotNull($order->getReference());
        $this->assertStringStartsWith('ORD-', $order->getReference());
        $this->assertInstanceOf(\DateTimeImmutable::class, $order->getCreatedAt());
    }

    public function testCreateOrderWithPaidStatus(): void
    {
        $user = $this->createUser();

        $this->em->expects($this->once())->method('persist');

        $order = $this->orderService->createOrder($user, 9998, 'paid', 'Adresse test');

        $this->assertSame('paid', $order->getStatus());
        $this->assertSame(9998, $order->getTotalAmount());
    }

    // ── markAsPaid / markAsFailed ──────────────────────────────────────

    public function testMarkAsPaid(): void
    {
        $order = new Order();
        $this->assertSame('pending', $order->getStatus());

        $this->em->expects($this->once())->method('flush');

        $this->orderService->markAsPaid($order);

        $this->assertSame('paid', $order->getStatus());
    }

    public function testMarkAsFailed(): void
    {
        $order = new Order();

        $this->em->expects($this->once())->method('flush');

        $this->orderService->markAsFailed($order);

        $this->assertSame('failed', $order->getStatus());
    }

    // ── attachStripeSession ────────────────────────────────────────────

    public function testAttachStripeSession(): void
    {
        $order = new Order();
        $sessionId = 'cs_test_stripe123';

        $this->em->expects($this->once())->method('flush');

        $this->orderService->attachStripeSession($order, $sessionId);

        $this->assertSame($sessionId, $order->getStripeSessionId());
    }

    // ── findByStripeSessionId ──────────────────────────────────────────

    public function testFindByStripeSessionIdReturnsOrder(): void
    {
        $order = new Order();
        $order->setStripeSessionId('cs_test_abc');

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')
            ->with(['stripeSessionId' => 'cs_test_abc'])
            ->willReturn($order);

        $this->em->method('getRepository')
            ->with(Order::class)
            ->willReturn($repo);

        $result = $this->orderService->findByStripeSessionId('cs_test_abc');

        $this->assertSame($order, $result);
    }

    public function testFindByStripeSessionIdReturnsNull(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')
            ->with(['stripeSessionId' => 'not_found'])
            ->willReturn(null);

        $this->em->method('getRepository')
            ->with(Order::class)
            ->willReturn($repo);

        $result = $this->orderService->findByStripeSessionId('not_found');

        $this->assertNull($result);
    }

    // ── find ───────────────────────────────────────────────────────────

    public function testFindReturnsOrder(): void
    {
        $order = new Order();

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')
            ->with(42)
            ->willReturn($order);

        $this->em->method('getRepository')
            ->with(Order::class)
            ->willReturn($repo);

        $result = $this->orderService->find(42);

        $this->assertSame($order, $result);
    }

    // ── getValidatedShippingAddress ────────────────────────────────────

    public function testGetValidatedShippingAddressReturnsAddress(): void
    {
        $user = $this->createUser();

        $address = new ShippingAddress();
        $address->setUser($user);
        $address->setFullName('Jean Test');
        $address->setAddress('1 Rue Test');
        $address->setCity('Paris');
        $address->setPostalCode('75001');
        $address->setCountry('France');

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')
            ->with(1)
            ->willReturn($address);

        $this->em->method('getRepository')
            ->with(ShippingAddress::class)
            ->willReturn($repo);

        $result = $this->orderService->getValidatedShippingAddress(1, $user);

        $this->assertSame($address, $result);
    }

    public function testGetValidatedShippingAddressReturnsNullWhenNotOwned(): void
    {
        $user = $this->createUser();
        $otherUser = new User();
        $otherUser->setEmail('other@example.com');

        $address = new ShippingAddress();
        $address->setUser($otherUser);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')
            ->with(1)
            ->willReturn($address);

        $this->em->method('getRepository')
            ->with(ShippingAddress::class)
            ->willReturn($repo);

        $result = $this->orderService->getValidatedShippingAddress(1, $user);

        $this->assertNull($result);
    }

    public function testGetValidatedShippingAddressReturnsNullWhenNotFound(): void
    {
        $user = $this->createUser();

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')
            ->with(999)
            ->willReturn(null);

        $this->em->method('getRepository')
            ->with(ShippingAddress::class)
            ->willReturn($repo);

        $result = $this->orderService->getValidatedShippingAddress(999, $user);

        $this->assertNull($result);
    }

    // ── flush ──────────────────────────────────────────────────────────

    public function testFlush(): void
    {
        $this->em->expects($this->once())->method('flush');

        $this->orderService->flush();
    }

    // ── Calcul du total de commande ────────────────────────────────────

    public function testOrderTotalCalculation(): void
    {
        $order = new Order();

        // Simule un total de panier de 149.97 €
        $totalCents = (int) round(149.97 * 100);
        $order->setTotalAmount($totalCents);

        $this->assertSame(14997, $order->getTotalAmount());
        $this->assertEqualsWithDelta(149.97, $order->getTotalAmount() / 100, 0.001);
    }
}

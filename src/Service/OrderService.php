<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\ShippingAddress;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    /**
     * Create a new order with the given status.
     */
    public function createOrder(User $user, int $totalAmountCents, string $status, string $shippingAddress): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount($totalAmountCents);
        $order->setStatus($status);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setShippingAddress($shippingAddress);

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

    /**
     * Find an order by its Stripe session ID.
     */
    public function findByStripeSessionId(string $sessionId): ?Order
    {
        return $this->em->getRepository(Order::class)->findOneBy(['stripeSessionId' => $sessionId]);
    }

    /**
     * Find an order by its primary key.
     */
    public function find(int $id): ?Order
    {
        return $this->em->getRepository(Order::class)->find($id);
    }

    /**
     * Attach a Stripe session ID to an order and persist.
     */
    public function attachStripeSession(Order $order, string $stripeSessionId): void
    {
        $order->setStripeSessionId($stripeSessionId);
        $this->em->flush();
    }

    /**
     * Mark an order as paid and persist.
     */
    public function markAsPaid(Order $order): void
    {
        $order->setStatus('paid');
        $this->em->flush();
    }

    /**
     * Mark an order as failed and persist.
     */
    public function markAsFailed(Order $order): void
    {
        $order->setStatus('failed');
        $this->em->flush();
    }

    /**
     * Validate that a shipping address belongs to the given user.
     */
    public function getValidatedShippingAddress(int $addressId, User $user): ?ShippingAddress
    {
        $address = $this->em->getRepository(ShippingAddress::class)->find($addressId);

        if (!$address || $address->getUser() !== $user) {
            return null;
        }

        // Protection IDOR : on retourne l'adresse seulement si elle appartient
        // à l'utilisateur courant.
        return $address;
    }

    /**
     * Persist any pending changes (e.g. after external status update).
     */
    public function flush(): void
    {
        $this->em->flush();
    }
}

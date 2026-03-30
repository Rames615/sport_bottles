<?php

namespace App\Tests\Entity;

use App\Entity\ShippingAddress;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité ShippingAddress.
 */
class ShippingAddressTest extends TestCase
{
    public function testConstructorSetsCreatedAt(): void
    {
        $address = new ShippingAddress();
        $this->assertInstanceOf(\DateTimeImmutable::class, $address->getCreatedAt());
    }

    public function testGettersAndSetters(): void
    {
        $address = new ShippingAddress();
        $address->setFullName('Marie Martin');
        $address->setAddress('45 Avenue des Champions');
        $address->setCity('Lyon');
        $address->setPostalCode('69001');
        $address->setCountry('France');
        $address->setPhone('0612345678');

        $this->assertSame('Marie Martin', $address->getFullName());
        $this->assertSame('45 Avenue des Champions', $address->getAddress());
        $this->assertSame('Lyon', $address->getCity());
        $this->assertSame('69001', $address->getPostalCode());
        $this->assertSame('France', $address->getCountry());
        $this->assertSame('0612345678', $address->getPhone());
    }

    public function testSetAndGetUser(): void
    {
        $address = new ShippingAddress();
        $user = new User();
        $user->setEmail('user@example.com');

        $address->setUser($user);
        $this->assertSame($user, $address->getUser());
    }

    public function testToString(): void
    {
        $address = new ShippingAddress();
        $address->setFullName('Jean Dupont');
        $address->setAddress('12 Rue du Sport');
        $address->setCity('Paris');
        $address->setPostalCode('75001');
        $address->setCountry('France');

        $string = (string) $address;
        $this->assertStringContainsString('Jean Dupont', $string);
    }

    public function testSetUpdatedAt(): void
    {
        $address = new ShippingAddress();
        $date = new \DateTimeImmutable('2026-06-01');
        $address->setUpdatedAt($date);

        $this->assertSame($date, $address->getUpdatedAt());
    }
}

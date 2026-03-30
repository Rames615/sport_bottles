<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\ShippingAddress;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité User.
 * Vérifie les rôles, l'email, la vérification et le token de réinitialisation.
 */
class UserTest extends TestCase
{
    public function testDefaultRoleIsUser(): void
    {
        $user = new User();
        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
    }

    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $user->setEmail('sportif@example.com');

        $this->assertSame('sportif@example.com', $user->getEmail());
        $this->assertSame('sportif@example.com', $user->getUserIdentifier());
    }

    public function testSetAndGetPassword(): void
    {
        $user = new User();
        $user->setPassword('hashed_password_123');

        $this->assertSame('hashed_password_123', $user->getPassword());
    }

    public function testSetRolesAlwaysIncludesRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testIsVerifiedDefaultFalse(): void
    {
        $user = new User();
        $this->assertFalse($user->isVerified());
    }

    public function testSetIsVerified(): void
    {
        $user = new User();
        $user->setIsVerified(true);
        $this->assertTrue($user->isVerified());
    }

    public function testResetToken(): void
    {
        $user = new User();
        $token = bin2hex(random_bytes(32));
        $expiry = new \DateTimeImmutable('+1 hour');

        $user->setResetToken($token);
        $user->setResetTokenExpiresAt($expiry);

        $this->assertSame($token, $user->getResetToken());
        $this->assertSame($expiry, $user->getResetTokenExpiresAt());
    }

    public function testClearResetToken(): void
    {
        $user = new User();
        $user->setResetToken('some_token');
        $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));

        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        $this->assertNull($user->getResetToken());
        $this->assertNull($user->getResetTokenExpiresAt());
    }

    public function testAddAndRemoveProduct(): void
    {
        $user = new User();
        $product = new Product();
        $product->setDesignation('Gourde Test');
        $product->setDescription('Description test');
        $product->setPrice('29.99');
        $product->setStock(10);
        $product->setCapacity('500ml');

        $user->addProduct($product);
        $this->assertCount(1, $user->getProducts());

        $user->removeProduct($product);
        $this->assertCount(0, $user->getProducts());
    }

    public function testAddAndRemoveShippingAddress(): void
    {
        $user = new User();
        $address = new ShippingAddress();
        $address->setFullName('Jean Dupont');
        $address->setAddress('12 Rue du Sport');
        $address->setCity('Paris');
        $address->setPostalCode('75001');
        $address->setCountry('France');

        $user->addShippingAddress($address);
        $this->assertCount(1, $user->getShippingAddresses());
        $this->assertSame($user, $address->getUser());

        $user->removeShippingAddress($address);
        $this->assertCount(0, $user->getShippingAddresses());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');
        $this->assertNotNull($user->getPassword());
    }

    public function testSerialize(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashed');

        $serialized = $user->__serialize();
        $this->assertIsArray($serialized);
    }
}

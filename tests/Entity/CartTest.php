<?php

namespace App\Tests\Entity;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour les entités Cart et CartItem.
 * Vérifie la gestion des articles, le calcul du sous-total
 * et les relations entre panier, articles et produits.
 */
class CartTest extends TestCase
{
    private function createProduct(string $price = '25.00', int $stock = 10): Product
    {
        $product = new Product();
        $product->setDesignation('Gourde Inox');
        $product->setDescription('Gourde en acier inoxydable');
        $product->setPrice($price);
        $product->setStock($stock);
        $product->setCapacity('750ml');

        return $product;
    }

    private function createCartItem(Product $product, int $quantity, string $unitPrice): CartItem
    {
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity($quantity);
        $item->setUnitPrice($unitPrice);

        return $item;
    }

    // ── Cart ───────────────────────────────────────────────────────────

    public function testCartConstructorInitializesDefaults(): void
    {
        $cart = new Cart();

        $this->assertInstanceOf(\DateTimeImmutable::class, $cart->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $cart->getUpdatedAt());
        $this->assertCount(0, $cart->getItems());
    }

    public function testCartConstructorWithUser(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $cart = new Cart($user);

        $this->assertSame($user, $cart->getUser());
    }

    public function testAddItemToCart(): void
    {
        $cart = new Cart();
        $product = $this->createProduct();
        $item = $this->createCartItem($product, 2, '25.00');

        $cart->addItem($item);

        $this->assertCount(1, $cart->getItems());
        $this->assertSame($cart, $item->getCart());
    }

    public function testAddSameItemTwiceDoesNotDuplicate(): void
    {
        $cart = new Cart();
        $product = $this->createProduct();
        $item = $this->createCartItem($product, 1, '25.00');

        $cart->addItem($item);
        $cart->addItem($item); // Deuxième ajout du même objet

        $this->assertCount(1, $cart->getItems());
    }

    public function testRemoveItemFromCart(): void
    {
        $cart = new Cart();
        $product = $this->createProduct();
        $item = $this->createCartItem($product, 1, '25.00');

        $cart->addItem($item);
        $this->assertCount(1, $cart->getItems());

        $cart->removeItem($item);
        $this->assertCount(0, $cart->getItems());
        $this->assertNull($item->getCart());
    }

    public function testCartTotalWithMultipleItems(): void
    {
        $cart = new Cart();

        $product1 = $this->createProduct('25.00');
        $item1 = $this->createCartItem($product1, 2, '25.00');
        $cart->addItem($item1);

        $product2 = $this->createProduct('15.50');
        $item2 = $this->createCartItem($product2, 3, '15.50');
        $cart->addItem($item2);

        $total = 0.0;
        foreach ($cart->getItems() as $item) {
            $total += $item->getSubtotal();
        }

        // (25.00 * 2) + (15.50 * 3) = 50.00 + 46.50 = 96.50
        $this->assertEqualsWithDelta(96.50, $total, 0.001);
    }

    public function testSetAndGetUser(): void
    {
        $cart = new Cart();
        $user = new User();
        $user->setEmail('user@example.com');

        $cart->setUser($user);
        $this->assertSame($user, $cart->getUser());
    }

    public function testSetUpdatedAt(): void
    {
        $cart = new Cart();
        $newDate = new \DateTimeImmutable('2026-06-15');
        $cart->setUpdatedAt($newDate);

        $this->assertSame($newDate, $cart->getUpdatedAt());
    }

    // ── CartItem ───────────────────────────────────────────────────────

    public function testCartItemSubtotal(): void
    {
        $product = $this->createProduct('29.99');
        $item = $this->createCartItem($product, 3, '29.99');

        // 29.99 * 3 = 89.97
        $this->assertEqualsWithDelta(89.97, $item->getSubtotal(), 0.001);
    }

    public function testCartItemSubtotalWithZeroQuantity(): void
    {
        $product = $this->createProduct('29.99');
        $item = $this->createCartItem($product, 0, '29.99');

        $this->assertSame(0.0, $item->getSubtotal());
    }

    public function testCartItemSubtotalWithNullValues(): void
    {
        $item = new CartItem();
        // unitPrice et quantity sont null par défaut
        $this->assertSame(0.0, $item->getSubtotal());
    }

    public function testCartItemGettersAndSetters(): void
    {
        $product = $this->createProduct('19.99');
        $item = $this->createCartItem($product, 2, '19.99');

        $this->assertSame($product, $item->getProduct());
        $this->assertSame(2, $item->getQuantity());
        $this->assertSame('19.99', $item->getUnitPrice());
    }

    public function testCartItemCustomImagePath(): void
    {
        $product = $this->createProduct();
        $item = $this->createCartItem($product, 1, '25.00');
        $item->setCustomImagePath('promo/custom.jpg');

        $this->assertSame('promo/custom.jpg', $item->getCustomImagePath());
    }

    public function testCartItemCustomImagePathNullByDefault(): void
    {
        $item = new CartItem();
        $this->assertNull($item->getCustomImagePath());
    }

    public function testCartItemQuantityUpdate(): void
    {
        $product = $this->createProduct('10.00');
        $item = $this->createCartItem($product, 1, '10.00');

        $this->assertEqualsWithDelta(10.0, $item->getSubtotal(), 0.001);

        $item->setQuantity(5);
        $this->assertEqualsWithDelta(50.0, $item->getSubtotal(), 0.001);
    }
}

<?php

namespace App\Tests\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour CartService.
 * Utilise des mocks pour EntityManager et CartRepository
 * afin de tester la logique métier sans base de données.
 */
class CartServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private CartRepository&MockObject $cartRepository;
    private CartService $cartService;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->cartRepository = $this->createMock(CartRepository::class);
        $this->cartService = new CartService($this->em, $this->cartRepository);
    }

    // ── Helpers ────────────────────────────────────────────────────────

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@sportsbottles.com');
        return $user;
    }

    private function createProduct(string $price = '29.99', int $stock = 10): Product
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getPrice')->willReturn($price);
        $product->method('getStock')->willReturn($stock);
        $product->method('getDesignation')->willReturn('Gourde Sport');
        return $product;
    }

    private function createCartWithItems(User $user, array $items = []): Cart
    {
        $cart = new Cart($user);
        foreach ($items as $item) {
            $cart->addItem($item);
        }
        return $cart;
    }

    private function createCartItem(Product $product, int $quantity, string $unitPrice): CartItem
    {
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity($quantity);
        $item->setUnitPrice($unitPrice);
        return $item;
    }

    // ── getCart ─────────────────────────────────────────────────────────

    public function testGetCartCreatesNewCartWhenNoneExists(): void
    {
        $user = $this->createUser();

        $this->cartRepository
            ->method('findOneBy')
            ->with(['user' => $user])
            ->willReturn(null);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $cart = $this->cartService->getCart($user);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertSame($user, $cart->getUser());
    }

    public function testGetCartReturnsExistingCart(): void
    {
        $user = $this->createUser();
        $existingCart = new Cart($user);

        $this->cartRepository
            ->method('findOneBy')
            ->with(['user' => $user])
            ->willReturn($existingCart);

        $this->em->expects($this->never())->method('persist');

        $cart = $this->cartService->getCart($user);

        $this->assertSame($existingCart, $cart);
    }

    // ── addProduct ─────────────────────────────────────────────────────

    public function testAddProductToEmptyCart(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct('25.00', 5);
        $cart = new Cart($user);

        // findCartWithItems returns the cart (getCartWithItems uses this)
        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $this->cartRepository
            ->method('findOneBy')
            ->willReturn($cart);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->cartService->addProduct($user, $product);

        $this->assertTrue($result);
        $this->assertCount(1, $cart->getItems());
    }

    public function testAddProductReturnsFalseWhenNoStock(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct('25.00', 0);

        $result = $this->cartService->addProduct($user, $product);

        $this->assertFalse($result);
    }

    public function testAddProductIncrementsExistingItemQuantity(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct('25.00', 10);

        $existingItem = $this->createCartItem($product, 2, '25.00');
        $cart = $this->createCartWithItems($user, [$existingItem]);

        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $this->cartRepository
            ->method('findOneBy')
            ->willReturn($cart);

        $this->em->expects($this->once())->method('flush');

        $result = $this->cartService->addProduct($user, $product);

        $this->assertTrue($result);
        $this->assertSame(3, $existingItem->getQuantity());
    }

    public function testAddProductReturnsFalseWhenStockExceeded(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct('25.00', 2);

        $existingItem = $this->createCartItem($product, 2, '25.00');
        $cart = $this->createCartWithItems($user, [$existingItem]);

        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $this->cartRepository
            ->method('findOneBy')
            ->willReturn($cart);

        $result = $this->cartService->addProduct($user, $product);

        $this->assertFalse($result);
    }

    public function testAddProductWithCustomImagePath(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct('25.00', 5);
        $cart = new Cart($user);

        $this->cartRepository
            ->method('findCartWithItems')
            ->willReturn($cart);

        $this->cartRepository
            ->method('findOneBy')
            ->willReturn($cart);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->cartService->addProduct($user, $product, 'promo/image.jpg');

        $this->assertTrue($result);
        $items = $cart->getItems();
        $this->assertSame('promo/image.jpg', $items->first()->getCustomImagePath());
    }

    // ── getCartTotal ───────────────────────────────────────────────────

    public function testGetCartTotalWithMultipleItems(): void
    {
        $user = $this->createUser();

        $product1 = $this->createProduct('25.00');
        $item1 = $this->createCartItem($product1, 2, '25.00'); // 50.00

        $product2 = $this->createProduct('15.50');
        $item2 = $this->createCartItem($product2, 3, '15.50'); // 46.50

        $cart = $this->createCartWithItems($user, [$item1, $item2]);

        $total = $this->cartService->getCartTotal($cart);

        // 50.00 + 46.50 = 96.50
        $this->assertEqualsWithDelta(96.50, $total, 0.001);
    }

    public function testGetCartTotalWithEmptyCart(): void
    {
        $cart = new Cart();

        $total = $this->cartService->getCartTotal($cart);

        $this->assertSame(0.0, $total);
    }

    public function testGetCartTotalWithSingleItem(): void
    {
        $product = $this->createProduct('49.99');
        $item = $this->createCartItem($product, 1, '49.99');
        $cart = $this->createCartWithItems($this->createUser(), [$item]);

        $total = $this->cartService->getCartTotal($cart);

        $this->assertEqualsWithDelta(49.99, $total, 0.001);
    }

    // ── getCartTotal avec promotions ───────────────────────────────────

    public function testGetCartTotalWithPromotionPrice(): void
    {
        $user = $this->createUser();

        // Simule un produit avec prix promotionnel déjà stocké dans CartItem
        $product = $this->createProduct('100.00');
        $item = $this->createCartItem($product, 2, '80.00'); // Prix après -20% promo

        $cart = $this->createCartWithItems($user, [$item]);

        $total = $this->cartService->getCartTotal($cart);

        // 80.00 * 2 = 160.00
        $this->assertEqualsWithDelta(160.00, $total, 0.001);
    }

    // ── deductStockForUser ─────────────────────────────────────────────

    public function testDeductStockForUser(): void
    {
        $user = $this->createUser();

        $product = new Product();
        $product->setDesignation('Gourde');
        $product->setDescription('Description');
        $product->setPrice('25.00');
        $product->setStock(10);
        $product->setCapacity('500ml');

        $item = $this->createCartItem($product, 3, '25.00');
        $cart = $this->createCartWithItems($user, [$item]);

        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $this->em->expects($this->once())->method('flush');

        $this->cartService->deductStockForUser($user);

        $this->assertSame(7, $product->getStock());
    }

    public function testDeductStockDoesNotGoBelowZero(): void
    {
        $user = $this->createUser();

        $product = new Product();
        $product->setDesignation('Gourde');
        $product->setDescription('Description');
        $product->setPrice('25.00');
        $product->setStock(2);
        $product->setCapacity('500ml');

        $item = $this->createCartItem($product, 5, '25.00'); // Quantité > stock
        $cart = $this->createCartWithItems($user, [$item]);

        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $this->em->expects($this->once())->method('flush');

        $this->cartService->deductStockForUser($user);

        $this->assertSame(0, $product->getStock());
    }

    public function testDeductStockWithEmptyCart(): void
    {
        $user = $this->createUser();
        $cart = new Cart($user);

        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $this->em->expects($this->never())->method('flush');

        $this->cartService->deductStockForUser($user);
    }

    // ── clear ──────────────────────────────────────────────────────────

    public function testClearRemovesAllItems(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();
        $item1 = $this->createCartItem($product, 1, '25.00');
        $item2 = $this->createCartItem($product, 2, '15.00');
        $cart = $this->createCartWithItems($user, [$item1, $item2]);

        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $this->em->expects($this->once())->method('flush');

        $this->cartService->clear($user);

        $this->assertCount(0, $cart->getItems());
    }

    // ── confirmPayment ─────────────────────────────────────────────────

    public function testConfirmPaymentClearsCart(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();
        $item = $this->createCartItem($product, 1, '25.00');
        $cart = $this->createCartWithItems($user, [$item]);

        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $this->em->expects($this->once())->method('flush');

        $this->cartService->confirmPayment($user);

        $this->assertCount(0, $cart->getItems());
    }

    // ── getCartItemCount ───────────────────────────────────────────────

    public function testGetCartItemCount(): void
    {
        $user = $this->createUser();
        $product1 = $this->createProduct();
        $product2 = $this->createProduct('15.00');
        $item1 = $this->createCartItem($product1, 2, '25.00');
        $item2 = $this->createCartItem($product2, 3, '15.00');
        $cart = $this->createCartWithItems($user, [$item1, $item2]);

        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $count = $this->cartService->getCartItemCount($user);

        $this->assertSame(5, $count); // 2 + 3
    }

    public function testGetCartItemCountWithEmptyCart(): void
    {
        $user = $this->createUser();
        $cart = new Cart($user);

        $this->cartRepository
            ->method('findCartWithItems')
            ->with($user)
            ->willReturn($cart);

        $count = $this->cartService->getCartItemCount($user);

        $this->assertSame(0, $count);
    }
}

<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\Promotion;
use App\Entity\Category;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Product.
 * Vérifie les getters/setters, le calcul du prix final avec promotion,
 * la gestion du stock et les descriptions.
 */
class ProductTest extends TestCase
{
    private function createProduct(string $price = '29.99', int $stock = 10): Product
    {
        $product = new Product();
        $product->setDesignation('Gourde Sport 500ml');
        $product->setDescription('Une gourde de sport isotherme très performante pour les athlètes');
        $product->setPrice($price);
        $product->setStock($stock);
        $product->setCapacity('500ml');

        return $product;
    }

    private function createActivePromotion(string $type, float $value): Promotion
    {
        $promotion = new Promotion();
        $promotion->setTitle('Promo Test');
        $promotion->setDiscountType($type);
        $promotion->setDiscountValue($value);
        $promotion->setStartAt(new \DateTimeImmutable('-1 day'));
        $promotion->setEndAt(new \DateTimeImmutable('+1 day'));
        $promotion->setIsActive(true);

        return $promotion;
    }

    // ── Getters / Setters ──────────────────────────────────────────────

    public function testGettersAndSetters(): void
    {
        $product = $this->createProduct();

        $this->assertSame('Gourde Sport 500ml', $product->getDesignation());
        $this->assertSame('29.99', $product->getPrice());
        $this->assertSame(10, $product->getStock());
        $this->assertSame('500ml', $product->getCapacity());
    }

    public function testSetAndGetTemperature(): void
    {
        $product = $this->createProduct();
        $product->setTemperature('Chaud/Froid');

        $this->assertSame('Chaud/Froid', $product->getTemperature());
    }

    public function testSetAndGetImgPath(): void
    {
        $product = $this->createProduct();
        $product->setImgPath('images/gourde.jpg');

        $this->assertSame('images/gourde.jpg', $product->getImgPath());
    }

    public function testSetAndGetCategory(): void
    {
        $product = $this->createProduct();
        $category = new Category();
        $category->setName('Isotherme');
        $product->setCategory($category);

        $this->assertSame($category, $product->getCategory());
        $this->assertSame('Isotherme', $product->getCategory()->getName());
    }

    public function testToString(): void
    {
        $product = $this->createProduct();
        $this->assertSame('Gourde Sport 500ml', (string) $product);
    }

    public function testToStringReturnsEmptyWhenNoDesignation(): void
    {
        $product = new Product();
        $this->assertSame('', (string) $product);
    }

    // ── Card Description ───────────────────────────────────────────────

    public function testGetCardDescriptionReturnsShortDescriptionWhenSet(): void
    {
        $product = $this->createProduct();
        $product->setShortDescription('Description courte');

        $this->assertSame('Description courte', $product->getCardDescription());
    }

    public function testGetCardDescriptionFallsBackToFullDescription(): void
    {
        $product = $this->createProduct();

        $this->assertSame(
            'Une gourde de sport isotherme très performante pour les athlètes',
            $product->getCardDescription()
        );
    }

    public function testGetCardDescriptionReturnsEmptyStringWhenNoDescription(): void
    {
        $product = new Product();
        $product->setDesignation('Test');
        $product->setCapacity('1L');
        $product->setPrice('10.00');
        $product->setStock(5);

        $this->assertSame('', $product->getCardDescription());
    }

    // ── Stock ──────────────────────────────────────────────────────────

    public function testStockDecrement(): void
    {
        $product = $this->createProduct('29.99', 10);

        $newStock = $product->getStock() - 3;
        $product->setStock($newStock);

        $this->assertSame(7, $product->getStock());
    }

    public function testStockDecrementToZero(): void
    {
        $product = $this->createProduct('29.99', 5);

        $product->setStock(0);
        $this->assertSame(0, $product->getStock());
    }

    public function testStockCannotGoNegativeWithMaxProtection(): void
    {
        $product = $this->createProduct('29.99', 3);

        $newStock = max(0, $product->getStock() - 5);
        $product->setStock($newStock);

        $this->assertSame(0, $product->getStock());
    }

    // ── Promotions ─────────────────────────────────────────────────────

    public function testGetFinalPriceWithoutPromotion(): void
    {
        $product = $this->createProduct('29.99');

        $this->assertSame(29.99, $product->getFinalPrice());
    }

    public function testGetFinalPriceWithPercentagePromotion(): void
    {
        $product = $this->createProduct('100.00');
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 20.0);
        $product->addPromotion($promotion);

        $this->assertSame(80.0, $product->getFinalPrice());
    }

    public function testGetFinalPriceWithFixedPromotion(): void
    {
        $product = $this->createProduct('50.00');
        $promotion = $this->createActivePromotion(Promotion::TYPE_FIXED, 15.0);
        $product->addPromotion($promotion);

        $this->assertSame(35.0, $product->getFinalPrice());
    }

    public function testGetFinalPriceWithInactivePromotion(): void
    {
        $product = $this->createProduct('50.00');

        $promotion = new Promotion();
        $promotion->setTitle('Promo inactive');
        $promotion->setDiscountType(Promotion::TYPE_PERCENTAGE);
        $promotion->setDiscountValue(50.0);
        $promotion->setStartAt(new \DateTimeImmutable('-10 days'));
        $promotion->setEndAt(new \DateTimeImmutable('-1 day')); // Expirée
        $promotion->setIsActive(true);
        $product->addPromotion($promotion);

        $this->assertSame(50.0, $product->getFinalPrice());
    }

    public function testHasActivePromotion(): void
    {
        $product = $this->createProduct();
        $this->assertFalse($product->hasActivePromotion());

        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 10.0);
        $product->addPromotion($promotion);

        $this->assertTrue($product->hasActivePromotion());
    }

    public function testGetActivePromotionReturnsNullWithoutPromotion(): void
    {
        $product = $this->createProduct();
        $this->assertNull($product->getActivePromotion());
    }

    public function testAddAndRemovePromotion(): void
    {
        $product = $this->createProduct();
        $promotion = $this->createActivePromotion(Promotion::TYPE_FIXED, 5.0);

        $product->addPromotion($promotion);
        $this->assertCount(1, $product->getPromotions());
        $this->assertSame($product, $promotion->getProduct());

        $product->removePromotion($promotion);
        $this->assertCount(0, $product->getPromotions());
    }
}

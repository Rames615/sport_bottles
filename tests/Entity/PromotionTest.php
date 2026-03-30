<?php

namespace App\Tests\Entity;

use App\Entity\Promotion;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Promotion.
 * Vérifie le calcul de réduction (pourcentage et fixe), l'état actif/inactif
 * et les cas limites (prix négatif, promotion expirée, etc.).
 */
class PromotionTest extends TestCase
{
    private function createActivePromotion(string $type, float $value): Promotion
    {
        $promotion = new Promotion();
        $promotion->setTitle('Soldes été');
        $promotion->setDiscountType($type);
        $promotion->setDiscountValue($value);
        $promotion->setStartAt(new \DateTimeImmutable('-1 day'));
        $promotion->setEndAt(new \DateTimeImmutable('+1 day'));
        $promotion->setIsActive(true);

        return $promotion;
    }

    // ── isCurrentlyActive ──────────────────────────────────────────────

    public function testIsCurrentlyActiveWhenWithinDateRange(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 10.0);

        $this->assertTrue($promotion->isCurrentlyActive());
    }

    public function testIsNotActiveWhenExpired(): void
    {
        $promotion = new Promotion();
        $promotion->setTitle('Promo expirée');
        $promotion->setDiscountType(Promotion::TYPE_PERCENTAGE);
        $promotion->setDiscountValue(10.0);
        $promotion->setStartAt(new \DateTimeImmutable('-30 days'));
        $promotion->setEndAt(new \DateTimeImmutable('-1 day'));
        $promotion->setIsActive(true);

        $this->assertFalse($promotion->isCurrentlyActive());
    }

    public function testIsNotActiveWhenNotStartedYet(): void
    {
        $promotion = new Promotion();
        $promotion->setTitle('Promo future');
        $promotion->setDiscountType(Promotion::TYPE_FIXED);
        $promotion->setDiscountValue(5.0);
        $promotion->setStartAt(new \DateTimeImmutable('+1 day'));
        $promotion->setEndAt(new \DateTimeImmutable('+10 days'));
        $promotion->setIsActive(true);

        $this->assertFalse($promotion->isCurrentlyActive());
    }

    public function testIsNotActiveWhenFlagDisabled(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 10.0);
        $promotion->setIsActive(false);

        $this->assertFalse($promotion->isCurrentlyActive());
    }

    // ── calculateDiscountedPrice ───────────────────────────────────────

    public function testPercentageDiscount(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 20.0);

        $this->assertSame(80.0, $promotion->calculateDiscountedPrice(100.0));
    }

    public function testPercentageDiscount50Percent(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 50.0);

        $this->assertSame(25.0, $promotion->calculateDiscountedPrice(50.0));
    }

    public function testFixedDiscount(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_FIXED, 15.0);

        $this->assertSame(85.0, $promotion->calculateDiscountedPrice(100.0));
    }

    public function testFixedDiscountDoesNotGoBelowZero(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_FIXED, 200.0);

        $this->assertSame(0.0, $promotion->calculateDiscountedPrice(50.0));
    }

    public function testPercentageDiscountDoesNotGoBelowZero(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 150.0);

        $this->assertSame(0.0, $promotion->calculateDiscountedPrice(50.0));
    }

    public function testDiscountNotAppliedWhenInactive(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 50.0);
        $promotion->setIsActive(false);

        // Le prix original doit être retourné sans modification
        $this->assertSame(100.0, $promotion->calculateDiscountedPrice(100.0));
    }

    public function testDiscountNotAppliedWhenExpired(): void
    {
        $promotion = new Promotion();
        $promotion->setTitle('Expirée');
        $promotion->setDiscountType(Promotion::TYPE_FIXED);
        $promotion->setDiscountValue(10.0);
        $promotion->setStartAt(new \DateTimeImmutable('-10 days'));
        $promotion->setEndAt(new \DateTimeImmutable('-1 day'));
        $promotion->setIsActive(true);

        $this->assertSame(50.0, $promotion->calculateDiscountedPrice(50.0));
    }

    // ── Getters / Setters ──────────────────────────────────────────────

    public function testGettersAndSetters(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 25.0);
        $promotion->setDescription('Soldes de saison');

        $this->assertSame('Soldes été', $promotion->getTitle());
        $this->assertSame('Soldes de saison', $promotion->getDescription());
        $this->assertSame(Promotion::TYPE_PERCENTAGE, $promotion->getDiscountType());
        $this->assertSame(25.0, $promotion->getDiscountValue());
        $this->assertTrue($promotion->isActive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $promotion->getCreatedAt());
    }

    public function testSetAndGetProduct(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_FIXED, 10.0);
        $product = new Product();
        $product->setDesignation('Gourde Test');
        $product->setDescription('Description');
        $product->setPrice('49.99');
        $product->setStock(5);
        $product->setCapacity('750ml');

        $promotion->setProduct($product);

        $this->assertSame($product, $promotion->getProduct());
    }

    public function testSetAndGetImgPath(): void
    {
        $promotion = $this->createActivePromotion(Promotion::TYPE_FIXED, 5.0);
        $promotion->setImgPath('promo/summer.jpg');

        $this->assertSame('promo/summer.jpg', $promotion->getImgPath());
    }

    // ── Constants ──────────────────────────────────────────────────────

    public function testConstants(): void
    {
        $this->assertSame('percentage', Promotion::TYPE_PERCENTAGE);
        $this->assertSame('fixed', Promotion::TYPE_FIXED);
    }
}

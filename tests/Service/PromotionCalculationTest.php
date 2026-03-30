<?php

namespace App\Tests\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\Promotion;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour le calcul des promotions appliquées au panier.
 * Vérifie que les prix promotionnels sont correctement calculés
 * et répercutés sur le total du panier.
 */
class PromotionCalculationTest extends TestCase
{
    private function createProduct(string $price, int $stock = 10): Product
    {
        $product = new Product();
        $product->setDesignation('Gourde Test');
        $product->setDescription('Description test');
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

    // ── Prix final produit avec promotion ──────────────────────────────

    public function testProductFinalPriceWithPercentagePromotion(): void
    {
        $product = $this->createProduct('100.00');
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 25.0);
        $product->addPromotion($promotion);

        $this->assertSame(75.0, $product->getFinalPrice());
    }

    public function testProductFinalPriceWithFixedPromotion(): void
    {
        $product = $this->createProduct('80.00');
        $promotion = $this->createActivePromotion(Promotion::TYPE_FIXED, 20.0);
        $product->addPromotion($promotion);

        $this->assertSame(60.0, $product->getFinalPrice());
    }

    public function testProductFinalPriceWithoutPromotion(): void
    {
        $product = $this->createProduct('45.99');

        $this->assertEqualsWithDelta(45.99, $product->getFinalPrice(), 0.001);
    }

    // ── Total panier avec promotions ───────────────────────────────────

    public function testCartTotalWithPromotionPrices(): void
    {
        $cart = new Cart();

        // Produit 1: 100€ avec promo -20% = 80€, quantité 2
        $product1 = $this->createProduct('100.00');
        $promo1 = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 20.0);
        $product1->addPromotion($promo1);

        $item1 = new CartItem();
        $item1->setProduct($product1);
        $item1->setQuantity(2);
        $item1->setUnitPrice((string) $product1->getFinalPrice()); // 80.00
        $cart->addItem($item1);

        // Produit 2: 50€ sans promo, quantité 1
        $product2 = $this->createProduct('50.00');

        $item2 = new CartItem();
        $item2->setProduct($product2);
        $item2->setQuantity(1);
        $item2->setUnitPrice($product2->getPrice()); // 50.00
        $cart->addItem($item2);

        $total = 0.0;
        foreach ($cart->getItems() as $item) {
            $total += $item->getSubtotal();
        }

        // (80.00 * 2) + (50.00 * 1) = 160.00 + 50.00 = 210.00
        $this->assertEqualsWithDelta(210.00, $total, 0.001);
    }

    public function testCartTotalWithFixedPromotion(): void
    {
        $cart = new Cart();

        // Produit: 60€ avec promo fixe -10€ = 50€, quantité 3
        $product = $this->createProduct('60.00');
        $promo = $this->createActivePromotion(Promotion::TYPE_FIXED, 10.0);
        $product->addPromotion($promo);

        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(3);
        $item->setUnitPrice((string) $product->getFinalPrice()); // 50.00
        $cart->addItem($item);

        $total = 0.0;
        foreach ($cart->getItems() as $item) {
            $total += $item->getSubtotal();
        }

        // 50.00 * 3 = 150.00
        $this->assertEqualsWithDelta(150.00, $total, 0.001);
    }

    public function testCartTotalAllItemsWithPromotions(): void
    {
        $cart = new Cart();

        // Produit 1: 120€ -30% = 84€, quantité 1
        $product1 = $this->createProduct('120.00');
        $promo1 = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 30.0);
        $product1->addPromotion($promo1);

        $item1 = new CartItem();
        $item1->setProduct($product1);
        $item1->setQuantity(1);
        $item1->setUnitPrice((string) $product1->getFinalPrice()); // 84.00
        $cart->addItem($item1);

        // Produit 2: 40€ -5€ fixe = 35€, quantité 4
        $product2 = $this->createProduct('40.00');
        $promo2 = $this->createActivePromotion(Promotion::TYPE_FIXED, 5.0);
        $product2->addPromotion($promo2);

        $item2 = new CartItem();
        $item2->setProduct($product2);
        $item2->setQuantity(4);
        $item2->setUnitPrice((string) $product2->getFinalPrice()); // 35.00
        $cart->addItem($item2);

        $total = 0.0;
        foreach ($cart->getItems() as $item) {
            $total += $item->getSubtotal();
        }

        // 84.00 + (35.00 * 4) = 84.00 + 140.00 = 224.00
        $this->assertEqualsWithDelta(224.00, $total, 0.001);
    }

    // ── Économies calculées ────────────────────────────────────────────

    public function testSavingsCalculation(): void
    {
        $product = $this->createProduct('100.00');
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 15.0);
        $product->addPromotion($promotion);

        $originalPrice = (float) $product->getPrice();
        $finalPrice = $product->getFinalPrice();
        $savings = $originalPrice - $finalPrice;

        $this->assertEqualsWithDelta(15.0, $savings, 0.001);
        $this->assertEqualsWithDelta(85.0, $finalPrice, 0.001);
    }

    // ── Stock insuffisant avec promotion ───────────────────────────────

    public function testStockValidationWithPromotion(): void
    {
        $product = $this->createProduct('100.00', 0); // stock = 0
        $promotion = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 50.0);
        $product->addPromotion($promotion);

        // Le prix final reste calculé même si stock = 0
        $this->assertSame(50.0, $product->getFinalPrice());
        // Mais le stock est 0
        $this->assertSame(0, $product->getStock());
    }
}

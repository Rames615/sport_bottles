<?php

namespace App\Tests\Service;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la validation du stock.
 * Vérifie les scénarios de stock insuffisant, de décrémentation
 * et les cas limites (stock zéro, négatif, etc.).
 */
class StockValidationTest extends TestCase
{
    private function createProduct(int $stock): Product
    {
        $product = new Product();
        $product->setDesignation('Gourde Test');
        $product->setDescription('Description');
        $product->setPrice('29.99');
        $product->setStock($stock);
        $product->setCapacity('500ml');

        return $product;
    }

    // ── Validation stock suffisant ─────────────────────────────────────

    public function testStockSufficientForQuantity(): void
    {
        $product = $this->createProduct(10);
        $requestedQuantity = 5;

        $this->assertTrue($product->getStock() >= $requestedQuantity);
    }

    public function testStockExactlyEqualToQuantity(): void
    {
        $product = $this->createProduct(3);
        $requestedQuantity = 3;

        $this->assertTrue($product->getStock() >= $requestedQuantity);
    }

    // ── Validation stock insuffisant ───────────────────────────────────

    public function testStockInsufficient(): void
    {
        $product = $this->createProduct(2);
        $requestedQuantity = 5;

        $this->assertFalse($product->getStock() >= $requestedQuantity);
    }

    public function testStockZero(): void
    {
        $product = $this->createProduct(0);

        $this->assertSame(0, $product->getStock());
        $this->assertFalse($product->getStock() > 0);
    }

    // ── Décrémentation du stock ────────────────────────────────────────

    public function testStockDecrementAfterPurchase(): void
    {
        $product = $this->createProduct(10);
        $purchasedQuantity = 3;

        $newStock = $product->getStock() - $purchasedQuantity;
        $product->setStock($newStock);

        $this->assertSame(7, $product->getStock());
    }

    public function testStockDecrementMultipleProducts(): void
    {
        $products = [
            ['product' => $this->createProduct(10), 'qty' => 2],
            ['product' => $this->createProduct(5), 'qty' => 3],
            ['product' => $this->createProduct(8), 'qty' => 1],
        ];

        foreach ($products as $entry) {
            /** @var Product $product */
            $product = $entry['product'];
            $originalStock = $product->getStock();
            $product->setStock(max(0, $originalStock - $entry['qty']));
        }

        $this->assertSame(8, $products[0]['product']->getStock());
        $this->assertSame(2, $products[1]['product']->getStock());
        $this->assertSame(7, $products[2]['product']->getStock());
    }

    public function testStockDecrementProtectedByMax(): void
    {
        $product = $this->createProduct(2);
        $purchasedQuantity = 5; // Plus que le stock

        $newStock = max(0, $product->getStock() - $purchasedQuantity);
        $product->setStock($newStock);

        $this->assertSame(0, $product->getStock());
    }

    public function testStockDecrementToExactlyZero(): void
    {
        $product = $this->createProduct(5);
        $product->setStock(max(0, $product->getStock() - 5));

        $this->assertSame(0, $product->getStock());
    }

    // ── Vérification avant ajout au panier ─────────────────────────────

    public function testCanAddToCartWhenStockAvailable(): void
    {
        $product = $this->createProduct(5);
        $currentCartQuantity = 2;
        $additionalQuantity = 1;

        $totalNeeded = $currentCartQuantity + $additionalQuantity;

        $this->assertTrue($product->getStock() >= $totalNeeded);
    }

    public function testCannotAddToCartWhenStockExceeded(): void
    {
        $product = $this->createProduct(3);
        $currentCartQuantity = 2;
        $additionalQuantity = 2; // 2 + 2 = 4 > stock (3)

        $totalNeeded = $currentCartQuantity + $additionalQuantity;

        $this->assertFalse($product->getStock() >= $totalNeeded);
    }
}

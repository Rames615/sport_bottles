<?php

namespace App\Tests\Integration;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Promotion;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests d'intégration métier (sans base de données).
 * Simule le parcours complet d'un achat e-commerce :
 * ajout au panier → calcul total → vérification stock → création commande.
 */
class CheckoutFlowTest extends TestCase
{
    private function createProduct(string $designation, string $price, int $stock): Product
    {
        $product = new Product();
        $product->setDesignation($designation);
        $product->setDescription("Description de $designation");
        $product->setPrice($price);
        $product->setStock($stock);
        $product->setCapacity('500ml');

        return $product;
    }

    private function createActivePromotion(string $type, float $value): Promotion
    {
        $promotion = new Promotion();
        $promotion->setTitle('Promo');
        $promotion->setDiscountType($type);
        $promotion->setDiscountValue($value);
        $promotion->setStartAt(new \DateTimeImmutable('-1 day'));
        $promotion->setEndAt(new \DateTimeImmutable('+1 day'));
        $promotion->setIsActive(true);

        return $promotion;
    }

    // ── Scénario complet d'achat ───────────────────────────────────────

    public function testCompleteCheckoutFlow(): void
    {
        // 1. Création utilisateur
        $user = new User();
        $user->setEmail('acheteur@sportsbottles.com');

        // 2. Création produits
        $gourdeInox = $this->createProduct('Gourde Inox 750ml', '35.00', 10);
        $gourdeSport = $this->createProduct('Gourde Sport 500ml', '25.00', 5);

        // 3. Création panier
        $cart = new Cart($user);

        // 4. Ajout au panier
        $item1 = new CartItem();
        $item1->setProduct($gourdeInox);
        $item1->setQuantity(2);
        $item1->setUnitPrice($gourdeInox->getPrice());
        $cart->addItem($item1);

        $item2 = new CartItem();
        $item2->setProduct($gourdeSport);
        $item2->setQuantity(1);
        $item2->setUnitPrice($gourdeSport->getPrice());
        $cart->addItem($item2);

        // 5. Calcul total
        $total = 0.0;
        foreach ($cart->getItems() as $item) {
            $total += $item->getSubtotal();
        }

        // (35.00 * 2) + (25.00 * 1) = 70.00 + 25.00 = 95.00
        $this->assertEqualsWithDelta(95.00, $total, 0.001);

        // 6. Vérification stock
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $this->assertGreaterThanOrEqual(
                $item->getQuantity(),
                $product->getStock(),
                sprintf('Stock insuffisant pour %s', $product->getDesignation())
            );
        }

        // 7. Création commande
        $order = new Order();
        $order->setUser($user);
        $order->setTotalAmount((int) round($total * 100));
        $order->setShippingAddress('12 Rue du Sport, 75001 Paris');

        $this->assertSame('pending', $order->getStatus());
        $this->assertSame(9500, $order->getTotalAmount());
        $this->assertStringStartsWith('ORD-', $order->getReference());

        // 8. Simulation paiement réussi
        $order->setStatus('paid');
        $order->setStripeSessionId('cs_test_completed123');

        $this->assertSame('paid', $order->getStatus());

        // 9. Décrémentation stock
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $newStock = max(0, $product->getStock() - $item->getQuantity());
            $product->setStock($newStock);
        }

        $this->assertSame(8, $gourdeInox->getStock());  // 10 - 2
        $this->assertSame(4, $gourdeSport->getStock()); // 5 - 1
    }

    // ── Scénario avec promotions ───────────────────────────────────────

    public function testCheckoutFlowWithPromotions(): void
    {
        $user = new User();
        $user->setEmail('promo@sportsbottles.com');

        // Produit avec promotion -20%
        $product = $this->createProduct('Gourde Premium', '100.00', 5);
        $promo = $this->createActivePromotion(Promotion::TYPE_PERCENTAGE, 20.0);
        $product->addPromotion($promo);

        $this->assertTrue($product->hasActivePromotion());
        $this->assertSame(80.0, $product->getFinalPrice());

        // Panier avec prix promotionnel
        $cart = new Cart($user);
        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(2);
        $item->setUnitPrice((string) $product->getFinalPrice()); // 80.00
        $cart->addItem($item);

        $total = 0.0;
        foreach ($cart->getItems() as $cartItem) {
            $total += $cartItem->getSubtotal();
        }

        // 80.00 * 2 = 160.00 (au lieu de 200.00)
        $this->assertEqualsWithDelta(160.00, $total, 0.001);

        // Économie réalisée
        $originalTotal = (float) $product->getPrice() * 2; // 200.00
        $savings = $originalTotal - $total; // 40.00
        $this->assertEqualsWithDelta(40.00, $savings, 0.001);
    }

    // ── Scénario stock insuffisant ─────────────────────────────────────

    public function testCheckoutBlockedByInsufficientStock(): void
    {
        $product = $this->createProduct('Gourde Rare', '50.00', 1);

        // Tentative d'ajout de 3 unités alors que stock = 1
        $requestedQuantity = 3;
        $stockAvailable = $product->getStock();

        $this->assertFalse(
            $stockAvailable >= $requestedQuantity,
            'Le stock devrait être insuffisant'
        );
    }

    public function testCheckoutBlockedByZeroStock(): void
    {
        $product = $this->createProduct('Gourde Épuisée', '30.00', 0);

        $this->assertSame(0, $product->getStock());
        $this->assertFalse($product->getStock() > 0);
    }

    // ── Scénario paiement échoué ───────────────────────────────────────

    public function testPaymentFailureDoesNotDeductStock(): void
    {
        $product = $this->createProduct('Gourde Standard', '25.00', 10);
        $originalStock = $product->getStock();

        // Commande créée mais paiement échoué
        $order = new Order();
        $order->setUser(new User());
        $order->setTotalAmount(2500);
        $order->setShippingAddress('Test');
        $order->setStatus('failed');

        // Le stock ne doit pas être modifié
        $this->assertSame($originalStock, $product->getStock());
        $this->assertSame('failed', $order->getStatus());
    }

    // ── Scénario panier vide ───────────────────────────────────────────

    public function testEmptyCartTotal(): void
    {
        $cart = new Cart();

        $total = 0.0;
        foreach ($cart->getItems() as $item) {
            $total += $item->getSubtotal();
        }

        $this->assertSame(0.0, $total);
        $this->assertCount(0, $cart->getItems());
    }
}

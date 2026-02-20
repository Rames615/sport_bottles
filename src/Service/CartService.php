<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartRepository $cartRepository
    ) {}

    public function getCart(User $user): Cart
    {
        $cart = $this->cartRepository->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart($user);
            $this->em->persist($cart);
            $this->em->flush();
        }

        return $cart;
    }

    public function addProduct(User $user, Product $product): bool
    {
        $cart = $this->getCart($user);

        // Check stock - if stock is null, treat as unlimited; otherwise validate
        $available = $product->getStock();
        $available = $available === null ? PHP_INT_MAX : $available;
        if ($available <= 0) {
            // no stock available
            return false;
        }

        // Ensure items are loaded with proper joins
        $cart = $this->getCartWithItems($user);

        // Check if product already exists in cart
        foreach ($cart->getItems() as $cartItem) {
            $existingProduct = $cartItem->getProduct();
            if ($existingProduct && $existingProduct->getId() === $product->getId()) {
                $currentQty = $cartItem->getQuantity() ?? 0;
                // validate against stock
                if ($currentQty + 1 > $available) {
                    // cannot add more than stock
                    return false;
                }
                $cartItem->setQuantity($currentQty + 1);
                $cart->setUpdatedAt(new \DateTimeImmutable());
                $this->em->flush();
                return true;
            }
        }

        // create new cart item
        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setQuantity(1);
        // store the unit price at time of adding
        $cartItem->setUnitPrice((string) $product->getPrice());

        $cart->addItem($cartItem);
        $cart->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($cartItem);
        $this->em->flush();

        return true;
    }

    // Clear all items from the cart
    public function clear(User $user): void
    {
        $cart = $this->getCartWithItems($user);

        foreach ($cart->getItems() as $item) {
            $cart->removeItem($item);
        }

        $this->em->flush();
    }

    public function getCartWithItems(User $user): Cart
    {
        $cart = $this->cartRepository->findCartWithItems($user);

        if (!$cart) {
            // retourne ou crée un panier vide pour respecter le type de retour
            return $this->getCart($user);
        }

        return $cart;
    }

    public function removeItemById(User $user, int $itemId): void
    {
        $item = $this->em->getRepository(CartItem::class)->find($itemId);
        if (!$item) {
            return;
        }

        $cart = $item->getCart();
        if (!$cart || $cart->getUser()?->getId() !== $user->getId()) {
            // item does not belong to user's cart; ignore
            return;
        }

        $cart->removeItem($item);
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->em->remove($item);
        $this->em->flush();
    }

    public function updateItemQuantity(User $user, int $itemId, int $quantity): void
    {
        $item = $this->em->getRepository(CartItem::class)->find($itemId);
        if (!$item) {
            return;
        }

        $cart = $item->getCart();
        if (!$cart || $cart->getUser()?->getId() !== $user->getId()) {
            return;
        }

        if ($quantity <= 0) {
            $this->removeItemById($user, $itemId);
            return;
        }

        // validate against stock
        $product = $item->getProduct();
        $available = $product?->getStock();
        $available = $available === null ? PHP_INT_MAX : $available;
        if ($quantity > $available) {
            // cannot set quantity beyond stock
            return;
        }

        $item->setQuantity($quantity);
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    /**
     * Prepare cart for checkout: validate prices and non-empty
     * Returns array with status and message or prepared data
     */
    /**
     * Prepare cart for checkout: validate prices and non-empty
     * Returns array with status and message or prepared data
     *
     * @return array{ok: bool, total?: float, cart?: Cart, message?: string}
     */
    public function prepareCheckout(User $user): array
    {
        $cart = $this->getCartWithItems($user);
        $items = $cart->getItems();

        if ($items->isEmpty()) {
            return ['ok' => false, 'message' => 'Panier vide'];
        }

        $total = 0.0;
        foreach ($items as $item) {
            $product = $item->getProduct();
            if (!$product) {
                return ['ok' => false, 'message' => 'Produit introuvable dans le panier'];
            }

            // Validate unit price hasn't been tampered with
            $currentPrice = (float) $product->getPrice();
            $storedUnit = (float) $item->getUnitPrice();
            if (abs($currentPrice - $storedUnit) > 0.001) {
                // update stored price to current and continue
                $item->setUnitPrice((string) $product->getPrice());
            }

            $total += $item->getSubtotal();
        }

        // taxes/discounts can be applied here
        return ['ok' => true, 'total' => $total, 'cart' => $cart];
    }

    public function getCartTotal(Cart $cart): float
    {
        $total = 0.0;
        foreach ($cart->getItems() as $item) {
            $total += (float) $item->getSubtotal();
        }
        return $total;
    }

    public function getCartItemCount(User $user): int
    {
        $cart = $this->getCartWithItems($user);
        $count = 0;
        foreach ($cart->getItems() as $item) {
            $count += $item->getQuantity() ?? 0;
        }
        return $count;
    }
}

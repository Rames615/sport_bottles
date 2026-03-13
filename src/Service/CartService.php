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

    /**
     * Récupère le panier de l'utilisateur, ou en crée un nouveau s'il n'existe pas.
     */
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

    /**
     * Ajoute un produit au panier de l'utilisateur.
     * Retourne true en cas de succès, false si le stock est insuffisant.
     * 
     * @param User $user
     * @param Product $product
     * @param string|null $customImagePath Optional custom image path (e.g., promotion image)
     * @return bool
     */
    public function addProduct(User $user, Product $product, ?string $customImagePath = null): bool
    {
        $cart = $this->getCart($user);

        // Vérifie le stock — null signifie stock illimité
        $stockDisponible = $product->getStock();
        $stockDisponible = $stockDisponible === null ? PHP_INT_MAX : $stockDisponible;

        if ($stockDisponible <= 0) {
            // Aucun stock disponible
            return false;
        }

        // Charge les articles du panier avec les jointures appropriées
        $cart = $this->getCartWithItems($user);

        // Vérifie si le produit est déjà présent dans le panier
        foreach ($cart->getItems() as $cartItem) {
            $produitExistant = $cartItem->getProduct();
            if ($produitExistant && $produitExistant->getId() === $product->getId()) {
                $quantiteActuelle = $cartItem->getQuantity() ?? 0;

                // Vérifie que la nouvelle quantité ne dépasse pas le stock
                if ($quantiteActuelle + 1 > $stockDisponible) {
                    return false;
                }

                $cartItem->setQuantity($quantiteActuelle + 1);
                // Update custom image if provided (use the latest promotion image)
                if ($customImagePath !== null) {
                    $cartItem->setCustomImagePath($customImagePath);
                }
                $cart->setUpdatedAt(new \DateTimeImmutable());
                $this->em->flush();

                return true;
            }
        }

        // Crée un nouvel article dans le panier
        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setQuantity(1);
        // Stocke le prix unitaire au moment de l'ajout
        $cartItem->setUnitPrice((string) $product->getPrice());
        // Store custom image path if provided (e.g., promotion image)
        if ($customImagePath !== null) {
            $cartItem->setCustomImagePath($customImagePath);
        }

        $cart->addItem($cartItem);
        $cart->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($cartItem);
        $this->em->flush();

        return true;
    }

    /**
     * Supprime tous les articles du panier de l'utilisateur.
     */
    public function clear(User $user): void
    {
        $cart = $this->getCartWithItems($user);

        foreach ($cart->getItems() as $article) {
            $cart->removeItem($article);
        }

        $this->em->flush();
    }

    /**
     * Récupère le panier avec ses articles chargés via jointure.
     * Crée un panier vide si aucun n'existe.
     */
    public function getCartWithItems(User $user): Cart
    {
        $cart = $this->cartRepository->findCartWithItems($user);

        if (!$cart) {
            return $this->getCart($user);
        }

        return $cart;
    }

    /**
     * Supprime un article du panier par son identifiant.
     * Ignore l'opération si l'article est introuvable ou n'appartient pas à l'utilisateur.
     */
    public function removeItemById(User $user, int $itemId): void
    {
        $article = $this->em->getRepository(CartItem::class)->find($itemId);

        if (!$article) {
            return;
        }

        $cart = $article->getCart();

        // Vérifie que l'article appartient bien au panier de l'utilisateur
        if (!$cart || $cart->getUser()?->getId() !== $user->getId()) {
            return;
        }

        $cart->removeItem($article);
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->em->remove($article);
        $this->em->flush();
    }

    /**
     * Met à jour la quantité d'un article dans le panier.
     * Supprime l'article si la quantité est inférieure ou égale à zéro.
     * Ignore l'opération si la quantité dépasse le stock disponible.
     */
    public function updateItemQuantity(User $user, int $itemId, int $quantity): void
    {
        $article = $this->em->getRepository(CartItem::class)->find($itemId);

        if (!$article) {
            return;
        }

        $cart = $article->getCart();

        // Vérifie que l'article appartient bien au panier de l'utilisateur
        if (!$cart || $cart->getUser()?->getId() !== $user->getId()) {
            return;
        }

        // Supprime l'article si la quantité demandée est nulle ou négative
        if ($quantity <= 0) {
            $this->removeItemById($user, $itemId);
            return;
        }

        // Vérifie que la quantité ne dépasse pas le stock disponible
        $produit = $article->getProduct();
        $stockDisponible = $produit?->getStock();
        $stockDisponible = $stockDisponible === null ? PHP_INT_MAX : $stockDisponible;

        if ($quantity > $stockDisponible) {
            return;
        }

        $article->setQuantity($quantity);
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    /**
     * Prépare le panier pour le passage en caisse :
     * vérifie que le panier n'est pas vide et recalcule les prix si nécessaire.
     *
     * @return array{ok: bool, total?: float, cart?: Cart, message?: string}
     */
    public function prepareCheckout(User $user): array
    {
        $cart = $this->getCartWithItems($user);
        $articles = $cart->getItems();

        if ($articles->isEmpty()) {
            return ['ok' => false, 'message' => 'Panier vide'];
        }

        $total = 0.0;

        foreach ($articles as $article) {
            $produit = $article->getProduct();

            if (!$produit) {
                return ['ok' => false, 'message' => 'Produit introuvable dans le panier'];
            }

            // Met à jour le prix unitaire stocké si le prix du produit a changé
            $prixActuel = (float) $produit->getPrice();
            $prixStocke = (float) $article->getUnitPrice();

            if (abs($prixActuel - $prixStocke) > 0.001) {
                $article->setUnitPrice((string) $produit->getPrice());
            }

            $total += $article->getSubtotal();
        }

        // Les taxes et remises peuvent être appliquées ici
        return ['ok' => true, 'total' => $total, 'cart' => $cart];
    }

    /**
     * Confirme le paiement d'une commande et vide automatiquement le panier.
     * Doit être appelée une fois le paiement validé avec succès.
     */
    public function confirmPayment(User $user): void
    {
        $this->clear($user);
    }

    /**
     * Déduit le stock des produits en fonction des articles du panier de l'utilisateur.
     * Si le panier est vide (déjà vidé), aucune déduction n'est effectuée.
     */
    public function deductStockForUser(User $user): void
    {
        $cart = $this->getCartWithItems($user);
        $items = $cart->getItems();

        if ($items->isEmpty()) {
            return;
        }

        foreach ($items as $item) {
            $product = $item->getProduct();
            if ($product && $product->getStock() !== null) {
                $newStock = max(0, $product->getStock() - ($item->getQuantity() ?? 0));
                $product->setStock($newStock);
            }
        }

        $this->em->flush();
    }

    /**
     * Calcule le montant total du panier.
     */
    public function getCartTotal(Cart $cart): float
    {
        $total = 0.0;

        foreach ($cart->getItems() as $article) {
            $total += (float) $article->getSubtotal();
        }

        return $total;
    }

    /**
     * Retourne le nombre total d'articles dans le panier (toutes quantités confondues).
     */
    public function getCartItemCount(User $user): int
    {
        $cart = $this->getCartWithItems($user);
        $count = 0;

        foreach ($cart->getItems() as $article) {
            $count += $article->getQuantity() ?? 0;
        }

        return $count;
    }
}
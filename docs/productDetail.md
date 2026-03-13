# Fiche produit – Approche route-based

## Contexte

Le système de modal Bootstrap (`#productModal`) a été remplacé par une page dédiée côté serveur.
La raison principale : des conflits d'exécution JS (double insertion du script, état Bootstrap corrompu)
gelaient la page dès l'ouverture du modal.

---

## Nouvelles routes

| Nom            | URL               | Contrôleur                        | Description             |
|----------------|-------------------|-----------------------------------|-------------------------|
| `app_product`  | `/product`        | `ProductController::index()`      | Grille de produits      |
| `app_product_show` | `/product/{id}` | `ProductController::show()`   | Détail d'un produit     |

### Paramètre de route

`{id}` est contraint à `\d+` (entiers uniquement) pour éviter les routes ambiguës.
Symfony résout automatiquement le `Product` par ParamConverter (injection directe dans `show()`).

---

## Fichiers modifiés

### `src/Controller/ProductController.php`
- Correction PHPStan : suppression de `use App\Repository\Category;` (Category est une **Entity**, pas un Repository).
- Ajout de `use App\Entity\Product;`.
- Nouvelle méthode `show(Product $product)` avec route `app_product_show`.

### `templates/product/product_description.html.twig` *(nouveau)*
- Étend `base.html.twig`.
- Affiche : image, nom, catégorie (badge), prix final (avec prix barré si promotion active), description, caractéristiques (capacité, température).
- Formulaire « Ajouter au panier » avec CSRF, intercepté en AJAX par `cart.js`.
- Lien de retour vers `app_product`.
- Charge `styles/product-detail.css` (bloc `stylesheets`).

### `templates/product/_card.html.twig`
- Le `<button data-bs-toggle="modal">` a été remplacé par un `<a href="{{ path('app_product_show', {id: product.id}) }}">`.
- Suppression de tous les `data-*` du modal (nom, prix, image, CSRF, etc.).

### `templates/home/index.html.twig`
- Le bouton `btn-view-more` dans la section Promotions remplacé par un `<a>` vers `app_product_show`.
- Suppression de `{% include 'product/_modal.html.twig' %}`.
- Suppression des commentaires JS liés au modal.

### `templates/product/index.html.twig`
- Suppression de `{% include 'product/_modal.html.twig' %}`.

### `templates/base.html.twig`
- Suppression de `<script src="scripts/product-modal.js">`.

### `public/scripts/cart.js`
- Suppression du bloc `modalAddToCartForm` (formulaire du modal, désormais inexistant).
- Ajout du bloc `add-to-cart-form-detail` : AJAX interception du formulaire de la page détail produit.

---

## Fichiers supprimés

| Fichier                                      | Raison                                   |
|----------------------------------------------|------------------------------------------|
| `templates/product/_modal.html.twig`         | Modal supprimé, page dédiée utilisée     |
| `public/scripts/product-modal.js`            | Logique modal supprimée                  |

---

## Flux utilisateur

```
Page produits (/product)
  └── clic "Voir plus" → /product/{id}   (navigation standard, pas de JS requis)
        └── formulaire "Ajouter au panier"
              └── AJAX POST /panier/add-ajax/{id}   (cart.js)
                    └── badge panier mis à jour + toast de confirmation
```

---

## Notes CSS

- Le style de la page détail est dans `assets/styles/product-detail.css` (à créer si non existant).
- Les variables CSS (`--primary-color`, `--secondary-color`) définies dans `variables.css` sont utilisées.
- `modal.css` est conservé dans `base.html.twig` car il sert au `#cookieConsentModal`.

---

## PHPStan

Avant cette modification, `ProductController.php` importait `use App\Repository\Category;` qui n'existe pas
(la classe s'appelle `CategoryRepository` et `Category` est une entité). Ce import erroné est corrigé.

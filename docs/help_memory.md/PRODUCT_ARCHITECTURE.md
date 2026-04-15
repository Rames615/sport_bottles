# Architecture du module produit

## Objectif

Ce document décrit l'architecture du catalogue et des pages produit, sans reprendre tout le contexte global déjà couvert dans `docs/ARCHITECTURE.md`.

## Composants principaux

- `ProductController` pour la liste et le détail ;
- `Product` et `Category` pour le modèle de données ;
- `templates/product/index.html.twig` pour le catalogue ;
- `templates/product/_card.html.twig` pour les cartes produit ;
- `templates/product/product_description.html.twig` pour la page détail ;
- `assets/styles/product.css` pour le style du catalogue ;
- styles dédiés pour la page détail lorsque nécessaire.

## Flux catalogue

`ProductController::index()` :

- charge toutes les catégories ;
- charge tous les produits ;
- construit un regroupement par catégorie ;
- transmet `categories`, `productsByCategory` et `allProducts` au template.

## Flux détail produit

`ProductController::show(Product $product)` :

- charge le produit ciblé ;
- affiche une page dédiée avec description, caractéristiques, promotion active éventuelle et formulaire d'ajout au panier.

## Principes de conception

- le catalogue est structuré par catégories ;
- la page détail côté serveur remplace avantageusement des modales complexes lorsque le besoin métier devient plus riche ;
- la logique de prix final est portée par l'entité `Product` via les promotions actives ;
- l'ajout au panier s'intègre au service panier déjà centralisé.

## Documents liés

- `docs/PRODUCT_IMPLEMENTATION_SUMMARY.md` ;
- `docs/productDetail.md` ;
- `docs/PRODUCT_VISUAL_DESIGN.md`.
# Résumé technique des modifications

## Objectif

Fournir une synthèse technique lisible des grands chantiers mis en place dans le projet.

## Axes principaux

### 1. Structuration métier

- centralisation de la logique panier dans `CartService` ;
- amélioration du découpage des flux entre panier, checkout, paiement et webhook ;
- enrichissement du modèle avec `ShippingAddress` et les objets liés au catalogue.

### 2. Flux de paiement

- création de commande avant redirection Stripe ;
- association entre commande et session Stripe ;
- confirmation serveur via webhook ;
- mise à jour du stock après paiement.

### 3. Interface et catalogue

- montée en qualité des cartes produit ;
- clarification du détail produit via une route dédiée ;
- meilleure cohérence entre catalogue, promotions et ajout au panier.

### 4. Maintenance

- documentation rationalisée ;
- analyse statique avec PHPStan ;
- commandes console utiles à l'exploitation ;
- back-office EasyAdmin pour l'administration quotidienne.

## Documents liés

- `docs/RESUME_MODIFICATIONS.md` ;
- `docs/ARCHITECTURE.md` ;
- `docs/FILES_AUDIT.md`.
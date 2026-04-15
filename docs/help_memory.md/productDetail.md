# Fiche produit - approche route dédiée

## Objectif

Ce document décrit le choix d'une page détail produit dédiée côté serveur, plus stable et plus simple à maintenir qu'une modale riche pilotée en JavaScript.

## Route active

- `app_product` : `/product`
- `app_product_show` : `/product/{id}`

Le paramètre `{id}` est contraint aux identifiants numériques et Symfony injecte directement l'entité `Product` dans l'action `show()`.

## Pourquoi cette approche

- meilleure robustesse ;
- moins de dépendance au JavaScript pour un contenu métier important ;
- meilleure lisibilité du code ;
- navigation plus claire pour l'utilisateur ;
- page directement partageable.

## Contenu attendu de la page

- image produit ;
- nom du produit ;
- prix final et éventuel prix promotionnel ;
- description ;
- caractéristiques ;
- bouton d'ajout au panier ;
- lien de retour vers le catalogue.

## Conséquence documentaire

Les documents anciens basés sur une modale produit doivent être lus comme des traces historiques, pas comme la référence de fonctionnement actuelle.
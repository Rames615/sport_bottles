# Architecture technique

## Objectif

Ce document synthétise l'architecture réelle de Sports Bottles. Il complète la documentation générale sans répéter les éléments métier déjà couverts dans `docs/readme.md` et `docs/rapport_rncp_sports_bottles.md`.

## Vue d'ensemble

L'application repose sur Symfony 7.4, Doctrine ORM, Twig et EasyAdmin. L'architecture est structurée en couches lisibles : contrôleurs, services, entités, formulaires, repositories et templates.

## Couches applicatives

### Présentation

La couche présentation s'appuie sur Twig. Les vues sont regroupées par domaine :

- `home` ;
- `product` ;
- `cart` ;
- `checkout` ;
- `payment` et `stripe` ;
- `account` ;
- `admin` ;
- `legal` ;
- `contact`.

### Contrôleurs

Les principaux contrôleurs du projet sont :

- `HomeController` pour l'accueil et la mise en avant des promotions ;
- `ProductController` pour le catalogue et la page de détail produit ;
- `CartController` pour les actions sur le panier ;
- `CheckoutController` pour l'adresse de livraison, la confirmation et la préparation du paiement ;
- `PaymentController` pour la page de succès, le polling d'état et la confirmation finale ;
- `WebhookController` pour les événements Stripe ;
- `AccountController` pour le profil et l'historique des commandes.

### Services métier

Le coeur de la logique transactionnelle est regroupé dans `CartService`. Ce service gère :

- la récupération ou la création du panier ;
- l'ajout de produits ;
- la mise à jour des quantités ;
- la suppression et le vidage du panier ;
- la préparation du checkout ;
- le calcul du total ;
- la déduction du stock après paiement.

### Persistance

Doctrine ORM assure la persistance des données. Les changements de schéma sont tracés par migrations et les données de démonstration sont fournies via les fixtures.

### Ressources statiques et JavaScript

Le dossier `public/scripts/` contient les scripts JavaScript non bundlés utilisés en complément d'AssetMapper. Le fichier principal est `payment.js`, qui porte la logique du formulaire de paiement Stripe : création du PaymentIntent, confirmation de la carte via l'API Stripe.js et gestion des erreurs avec retry.

Pour éviter d'inliner des valeurs dynamiques (clé publique Stripe, URLs d'API) dans un fichier statique, un objet `window.PaymentConfig` est défini par un `<script>` inline dans `payment.html.twig`. Ce pont est lu par `payment.js` au chargement, ce qui conserve la séparation entre logique comportementale et configuration serveur.

Les templates Stripe (`cancel.html.twig`, `complete.html.twig`, `payment_complete.html.twig`) utilisent exclusivement des classes utilitaires Tailwind pour leurs animations (`animate-bounce`, `animate-ping`, `animate-spin`), sans CSS supplémentaire ni `@keyframes` personnalisés.

## Entités principales

Le modèle métier s'appuie sur les entités suivantes :

- `User` ;
- `Category` ;
- `Product` ;
- `Promotion` ;
- `Cart` ;
- `CartItem` ;
- `Order` ;
- `ShippingAddress`.

Relations structurantes :

- un utilisateur possède un panier actif ;
- un panier contient plusieurs lignes ;
- une ligne de panier pointe vers un produit ;
- un produit appartient à une catégorie ;
- un produit peut recevoir une promotion active ;
- un utilisateur possède plusieurs commandes et plusieurs adresses de livraison.

## Flux principaux

### Catalogue

`ProductController` charge les catégories et les produits puis transmet au template :

- la liste complète des produits ;
- l'organisation par catégorie ;
- les données nécessaires à l'affichage des cartes et de la page détail.

### Panier et checkout

Le flux standard est le suivant :

1. ajout d'un produit au panier ;
2. contrôle du stock ;
3. consultation du panier ;
4. saisie d'une adresse de livraison ;
5. récapitulatif ;
6. création d'une commande `pending` ;
7. redirection vers Stripe.

### Paiement

Le retour de paiement s'appuie sur trois éléments complémentaires :

- la session Stripe liée à la commande ;
- le webhook Stripe comme source de vérité ;
- une page de succès capable de vérifier puis d'afficher l'état de la commande.

## Administration

EasyAdmin gère le back-office sur les objets métier suivants :

- utilisateurs ;
- produits ;
- catégories ;
- promotions ;
- paniers ;
- commandes.

## Points de conception importants

- le panier est réservé à l'utilisateur authentifié ;
- le prix est figé au niveau de `CartItem` ;
- le montant de la commande est stocké en centimes ;
- le stock n'est décrémenté qu'après paiement confirmé ;
- les routes liées au paiement sont volontairement séparées entre préparation, confirmation utilisateur et confirmation serveur.

## Références complémentaires

- `docs/STRIPE.md` pour le flux de paiement ;
- `docs/PRODUCT_ARCHITECTURE.md` pour la partie catalogue ;
- `docs/FILES_AUDIT.md` pour la cartographie du corpus documentaire.
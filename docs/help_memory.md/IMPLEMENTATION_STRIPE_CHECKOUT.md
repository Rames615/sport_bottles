# Implémentation actuelle du checkout Stripe

## Objectif

Ce document décrit, de manière opérationnelle, comment le checkout Stripe est implémenté aujourd'hui dans l'application.

## Étapes techniques

### 1. Préparation du panier

`CartService::prepareCheckout()` :

- vérifie que le panier n'est pas vide ;
- recharge les produits ;
- met à jour si nécessaire les prix unitaires stockés dans les lignes ;
- calcule le total.

### 2. Collecte de l'adresse

`CheckoutController::shipping()` :

- affiche le formulaire `ShippingAddressType` ;
- valide les champs ;
- persiste une adresse liée à l'utilisateur ;
- place l'identifiant de l'adresse en session pour la suite du tunnel.

### 3. Création de la commande

`CheckoutController::pay()` :

- contrôle le CSRF ;
- recharge le panier et l'adresse ;
- crée une commande avec statut `pending` ;
- convertit le total en centimes ;
- construit les `line_items` attendus par Stripe ;
- crée la session Checkout ;
- enregistre `stripeSessionId` ;
- redirige vers l'URL Stripe.

### 4. Retour utilisateur

`PaymentController::success()` :

- retrouve la commande via `session_id` ;
- tente une synchronisation ponctuelle avec Stripe ;
- affiche la page de succès même si le webhook n'a pas encore terminé son traitement.

### 5. Confirmation serveur

`WebhookController::handleSessionCompleted()` :

- retrouve la commande par `stripeSessionId` ;
- évite un retraitement si elle est déjà `paid` ;
- déduit le stock ;
- passe la commande à `paid` ;
- peut envoyer l'e-mail de confirmation.

### 6. Finalisation

`PaymentController::complete()` :

- vide le panier restant ;
- affiche la page finale de confirmation.

## Résumé

Le flux s'appuie sur une combinaison classique et fiable : session Stripe pour l'expérience de paiement, webhook Stripe pour la vérité métier, contrôleurs Symfony distincts pour la préparation, la confirmation et l'affichage.
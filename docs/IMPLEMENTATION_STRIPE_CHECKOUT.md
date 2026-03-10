# Système de Panier et Paiement Stripe - Documentation

## Vue d'ensemble

Ce système implémente un processus de paiement e-commerce complet avec :
- Gestion du panier (ajout, suppression, mise à jour de quantité)
- Bouton "Vider le panier"
- Formulaire de saisie d'adresse de livraison
- Intégration Stripe Checkout
- Gestion des webhooks Stripe
- Confirmation de paiement via webhooks

## Flux d'utilisation

### 1. Panier (Cart)
- **Route** : `/panier` (GET) - Affiche le panier
- **Route** : `/panier/clear` (POST) - Vide le panier
- **Route** : `/panier/add/{id}` (POST) - Ajoute un produit
- **Route** : `/panier/update/{itemId}` (POST) - Met à jour la quantité
- **Route** : `/panier/remove/{itemId}` (POST) - Supprime un article

### 2. Adresse de Livraison (Shipping)
- **Route** : `/checkout/shipping` (GET/POST)
- Formulaire avec validation des champs :
  - Nom complet (3-255 caractères)
  - Adresse (5-255 caractères)
  - Ville (2-100 caractères)
  - Code postal (5 chiffres)
  - Pays (2-100 caractères)
  - Téléphone (format valide)
- Sauvegarde en base de données dans la table `shipping_address`
- Redirection vers `/checkout/confirm`

### 3. Confirmation de Commande
- **Route** : `/checkout/confirm` (GET)
- Affiche un résumé du panier
- Bouton "Payer"

### 4. Paiement Stripe
- **Route** : `/checkout/pay` (POST)
- Crée une session Stripe Checkout
- Redirige l'utilisateur vers Stripe
- Crée une commande en base avec le statut `pending`

### 5. Confirmation de Paiement
- **Route** : `/payment/success` (GET) - Page d'attente
- **Route** : `/payment/order-status/{sessionId}` (GET) - Polling API
- **Route** : `/payment/complete/{id}` (GET) - Page finale

### 6. Webhooks
- **Route** : `/webhook/stripe` (POST/GET)
- Gère `checkout.session.completed` : Marque la commande comme payée
- Gère `payment_intent.payment_failed` : Marque la commande comme échouée

## Configuration .env

```
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

## Structure des Entités

### ShippingAddress
```php
- id (int)
- user_id (int) - Référence User
- fullName (string, 255)
- address (string, 255)
- city (string, 100)
- postalCode (string, 10)
- country (string, 100)
- phone (string, 20)
- createdAt (datetime_immutable)
- updatedAt (datetime_immutable nullable)
```

### Order
```php
- id (int)
- user_id (int)
- totalAmount (int) - En centimes
- status (string) - pending, paid, failed
- stripeSessionId (string) nullable
- shippingAddress (string) - Adresse formatée
- reference (string) - Référence unique de commande
- createdAt (datetime_immutable)
```

## Sécurité

### 1. Vérification des montants côté serveur
- La `CheckoutController` recalcule le montant total
- Stripe reçoit le montant exact en centimes

### 2. Webhooks Stripe
- Signature vérifiée avec `STRIPE_WEBHOOK_SECRET`
- Marque la commande comme payée seulement via webhook
- Impossible de bypasser côté client

### 3. CSRF Protection
- Tous les formulaires POST ont un token CSRF
- Vérification avec `$this->isCsrfTokenValid()`

### 4. Authentification
- Toutes les routes nécessitent un utilisateur connecté
- Vérification avec `$this->getUser() instanceof User`

### 5. Session Security
- L'ID de l'adresse de livraison est stocké en session
- Vérification que l'utilisateur possède cette adresse

## Test Local avec Stripe CLI

```bash
# 1. Installer Stripe CLI
# https://stripe.com/docs/stripe-cli

# 2. Authentifier
stripe login

# 3. Écouter les webhooks
stripe listen --forward-to http://localhost:8000/webhook/stripe

# 4. Envoyer un test
stripe trigger checkout.session.completed
```

## Points clés de l'implémentation

### Vider le panier
- Endpoint POST `/panier/clear`
- Appelle `CartService::clear()`
- Supprime tous les articles de la base de données
- Redirige vers la page du panier avec message de succès

### Adresse de livraison
- Formulaire avec validation Symfony
- Stockage structuré en base (table `shipping_address`)
- Lien avec l'utilisateur via Many-to-One
- Session utilisée pour passer l'ID entre pages

### Paiement Stripe
- La commande est créée avec statut `pending` AVANT redirection à Stripe
- Le montant est recalculé côté serveur (pas de confiance au client)
- La session Stripe stocke l'ID pour récupération future

### Confirmation via Webhooks
- Le webhook Stripe marque la commande comme `paid`
- C'est la source de vérité (pas de confiance au client)
- Polling JS pour UX (affichage du spinner)

### Redirection finale
- Une fois payée, le panier est vidé
- Redirection vers la page de confirmation avec le numéro de commande
- Lien vers l'accueil

## Templates

### `/checkout/shipping.html.twig`
- Formulaire d'adresse de livraison
- Résumé du panier à droite (sticky)

### `/checkout/confirm.html.twig`
- Résumé de la commande
- Détails de livraison
- Bouton "Payer" qui envoie POST à `/checkout/pay`

### `/stripe/success.html.twig`
- Spinner durant la vérification
- Polling JS toutes les 3s
- Redirection automatique en cas de succès

### `/stripe/complete.html.twig`
- Message "Commande confirmée"
- Numéro de commande et montant
- Lien vers accueil

### `/stripe/cancel.html.twig`
- Message d'annulation
- Lien pour retourner au panier

## Notes de sécurité critiques

1. **Ne jamais faire confiance aux montants du client**
   - Toujours recalculer côté serveur

2. **Webhooks comme source de vérité**
   - Seul le webhook Stripe marque une commande comme payée
   - Le formulaire POST initial crée juste la commande en `pending`

3. **CSRF tokens obligatoires**
   - Tous les formulaires POST doivent vérifier le token

4. **Authentification requise**
   - Vérifier `$this->getUser()` sur toutes les actions

5. **Isolation utilisateur**
   - Vérifier que l'adresse appartient à l'utilisateur connecté
   - Vérifier que la commande appartient à l'utilisateur

## Prochaines améliorations possibles

- [ ] Confirmation d'email avec récapitulatif
- [ ] Page d'historique des commandes
- [ ] Remboursement via l'interface admin
- [ ] Factures PDF
- [ ] Suivi du statut de livraison
- [ ] Paiement par d'autres méthodes (PayPal, etc.)

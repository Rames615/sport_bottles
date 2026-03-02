# Architecture Technique - Système Panier et Paiement

## Diagramme du Flux

```
┌─────────────────────────────────────────────────────────────────────┐
│                        UTILISATEUR AUTHENTIFIÉ                      │
└─────────────────────────────────────────────────────────────────────┘
                                  ↓
                            ┌──────────────┐
                            │   /panier    │
                            │   (INDEX)    │
                            └──────────────┘
                                  ↓
                    ┌─────────────────────────┐
                    │ Options Panier :        │
                    │ - Ajouter produit       │
                    │ - Modifier quantité     │
                    │ - Supprimer article     │
                    │ - VIDER LE PANIER ✓     │
                    │ - Procéder au paiement  │
                    └─────────────────────────┘
                                  ↓
                      ┌───────────────────────────┐
                      │ /checkout/shipping        │
                      │ (Formulaire Adresse) ✓    │
                      └───────────────────────────┘
                                  ↓
                    ┌───────────────────────────┐
                    │ Validation Adresse        │
                    │ - Client-side (HTML5)     │
                    │ - Server-side (Symfony)   │
                    │ - Regex (5 digits, etc)   │
                    └───────────────────────────┘
                                  ↓
                          ┌─────────────────┐
                          │ Sauvegarde DB   │
                          │ shipping_addr.. │
                          │ + Session ID    │
                          └─────────────────┘
                                  ↓
                    ┌──────────────────────────┐
                    │ /checkout/confirm        │
                    │ (Résumé Commande)        │
                    └──────────────────────────┘
                                  ↓
                    ┌──────────────────────────┐
                    │ POST /checkout/pay       │
                    │ - Créer Order (pending)  │
                    │ - Créer Stripe Session   │
                    │ - Rediriger vers Stripe  │
                    └──────────────────────────┘
                                  ↓
                    ╔══════════════════════════╗
                    ║   STRIPE CHECKOUT       ║
                    ║   (Extérieur)           ║
                    ║                          ║
                    ║ Utilisateur paie         ║
                    ╚══════════════════════════╝
                                  ↓
                    ┌──────────────────────────┐
                    │ /payment/success         │
                    │ (Page Attente Spinner)   │
                    │ - Polling chaque 3s      │
                    │ - Attendre webhook       │
                    └──────────────────────────┘
                                  ↓
            ┌─────────────────────────────────────┐
            │  WEBHOOK STRIPE REÇU                │
            │  checkout.session.completed         │
            │  ↓                                  │
            │  Order.status = 'pending' → 'paid' │
            │  ↓                                  │
            │  JS détecte et redirige             │
            └─────────────────────────────────────┘
                                  ↓
                    ┌──────────────────────────┐
                    │ /payment/complete/{id}   │
                    │ (Confirmation Finale)    │
                    │ - Vider le panier        │
                    │ - Afficher numéro        │
                    │ - Lien vers accueil      │
                    └──────────────────────────┘
                                  ↓
                           ┌──────────────┐
                           │ App Accueil  │
                           └──────────────┘
```

## Structure des Données

### Table `shipping_address`
```
┌──────────────────────────────────────────────────────┐
│ shipping_address                                     │
├─────────────────────────────────────────────────────┤
│ id (PK)        │ INT           │ AUTO INCREMENT      │
│ user_id (FK)   │ INT           │ NOT NULL            │
│ fullName       │ VARCHAR(255)  │ NOT NULL            │
│ address        │ VARCHAR(255)  │ NOT NULL            │
│ city           │ VARCHAR(100)  │ NOT NULL            │
│ postalCode     │ VARCHAR(10)   │ NOT NULL (5 digits) │
│ country        │ VARCHAR(100)  │ NOT NULL            │
│ phone          │ VARCHAR(20)   │ NOT NULL            │
│ createdAt      │ DATETIME      │ NOT NULL            │
│ updatedAt      │ DATETIME      │ NULLABLE            │
└──────────────────────────────────────────────────────┘
```

### Table `order` (enrichie)
```
Status Timeline:
  'pending'  → Créée en POST /checkout/pay
  'paid'     → Webhook checkout.session.completed
  'failed'   → Webhook payment_intent.payment_failed

shippingAddress contient la donnée texte formatée
(ne pas utiliser pour les mises à jour futures)
```

## Architecture Couches

```
┌──────────────────────────────────────────────────┐
│           PRESENTATION (Templates)               │
├──────────────────────────────────────────────────┤
│ - cart/index.html.twig                           │
│ - checkout/shipping.html.twig ✓ NOUVEAU         │
│ - checkout/confirm.html.twig                     │
│ - stripe/success.html.twig                       │
│ - stripe/complete.html.twig                      │
└──────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────┐
│        CONTROLLERS (Logique Métier)              │
├──────────────────────────────────────────────────┤
│ CartController                                   │
│  - index()             → Affiche panier          │
│  - add()              → Ajoute produit           │
│  - update()           → Modifie quantité         │
│  - remove()           → Supprime article         │
│  - clear() ✓ NOUVEAU  → Vide panier              │
├──────────────────────────────────────────────────┤
│ CheckoutController ✓ REFONDU                     │
│  - shipping() ✓       → Formulaire adresse       │
│  - confirm()          → Confirmation             │
│  - pay()              → Crée session Stripe      │
├──────────────────────────────────────────────────┤
│ PaymentController                                │
│  - success()          → Page attente             │
│  - orderStatus()      → API polling              │
│  - complete()         → Confirmation finale      │
│  - cancel()           → Annulation               │
├──────────────────────────────────────────────────┤
│ WebhookController                                │
│  - stripe() POST      → Traite webhooks ✓       │
│  - stripe() GET       → Health check ✓          │
│  - handleSessionCompleted()  → Marque payée     │
│  - handlePaymentFailed()     → Marque échouée   │
└──────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────┐
│          FORMS (Validation)                      │
├──────────────────────────────────────────────────┤
│ ShippingAddressType ✓ NOUVEAU                    │
│  - fullName (3-255 chars)                        │
│  - address (5-255 chars)                          │
│  - city (2-100 chars)                             │
│  - postalCode (regex: ^\d{5}$)                    │
│  - country (2-100 chars)                          │
│  - phone (regex: telefone valide)              │
└──────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────┐
│         SERVICES (Logique Métier)                │
├──────────────────────────────────────────────────┤
│ CartService                                      │
│  - getCart()               → Ajoute ou crée      │
│  - addProduct()            → Stock check         │
│  - clear() ✓               → Vide articles       │
│  - removeItemById()        → Supprime            │
│  - updateItemQuantity()    → Modifie quantité    │
│  - prepareCheckout()       → Récalc montant      │
│  - getCartTotal()          → Total               │
│  - getCartItemCount()      → Nombre articles     │
└──────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────┐
│           ENTITIES (Modèle de Données)           │
├──────────────────────────────────────────────────┤
│ User                                             │
│  - id, email, roles, password                    │
│  - products (Many-to-Many)                       │
│  - totalAmount (One-to-Many: Order)             │
│  - shippingAddresses ✓ NOUVEAU                   │
├──────────────────────────────────────────────────┤
│ Cart                                             │
│  - id, user, items, createdAt, updatedAt         │
├──────────────────────────────────────────────────┤
│ CartItem                                         │
│  - id, cart, product, quantity, unitPrice        │
├──────────────────────────────────────────────────┤
│ Order                                            │
│  - id, user, totalAmount, status, reference      │
│  - stripeSessionId, shippingAddress              │
│  - createdAt, updatedAt                          │
├──────────────────────────────────────────────────┤
│ ShippingAddress ✓ NOUVEAU                        │
│  - id, user, fullName, address, city             │
│  - postalCode, country, phone                    │
│  - createdAt, updatedAt                          │
├──────────────────────────────────────────────────┤
│ Product, Category, Promotion                     │
│  - (Inchangés)                                   │
└──────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────┐
│        REPOSITORIES (Accès Données)              │
├──────────────────────────────────────────────────┤
│ CartRepository                                   │
│  - findCartWithItems()                           │
├──────────────────────────────────────────────────┤
│ ShippingAddressRepository ✓ NOUVEAU              │
│  - findByUser()                                  │
├──────────────────────────────────────────────────┤
│ OrderRepository, UserRepository, etc             │
└──────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────┐
│      DATABASE (Doctrine/MySQL)                   │
├──────────────────────────────────────────────────┤
│ Tables : user, cart, cart_item, order            │
│ Tables ✓ : shipping_address, product, category  │
└──────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────┐
│          STRIPE API (Externe)                    │
├──────────────────────────────────────────────────┤
│ POST /v1/checkout/sessions         → Session    │
│ GET  /v1/checkout/sessions/{id}    → Status     │
│ POST webhooks (checkout.session..)  → Confirm   │
└──────────────────────────────────────────────────┘
```

## Appels API Clés

### 1. Créer Session Stripe
```php
// CheckoutController::pay()
$session = Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [
        [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => 'Produit'],
                'unit_amount' => 9999, // Centimes
            ],
            'quantity' => 1,
        ]
    ],
    'mode' => 'payment',
    'success_url' => 'https://monsite.com/payment/success?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'https://monsite.com/payment/cancel',
]);
```

### 2. Récupérer Session Stripe
```php
// PaymentController::success()
$stripeSession = Session::retrieve($sessionId);
if ($stripeSession->payment_status === 'paid') {
    $order->setStatus('paid');
}
```

### 3. Vérifier Webhook Signature
```php
// WebhookController::stripe()
$event = Webhook::constructEvent(
    $payload,
    $sigHeader,
    $endpointSecret  // STRIPE_WEBHOOK_SECRET
);
```

## Sécurité - Points Critiques

### 1. Validation Montant
```
CLIENT: Envoie /checkout/pay
  └─> SERVEUR: Recalcule le montant total
       └─> Compares avec la valeur reçue
            └─> Si mismatch → Refuse
             └─> Si match → Crée Stripe Session
```

### 2. Vérification Utilisateur
```
Chaque action doit vérifier:
  ✓ $this->getUser() instanceof User
  ✓ L'adresse appartient à cet utilisateur
  ✓ La commande appartient à cet utilisateur
```

### 3. Tokens CSRF
```
Tous les POST :
  ✗ $_POST['_token'] sans vérification
  ✓ $this->isCsrfTokenValid('checkout_pay', token)
```

### 4. Webhooks Signature
```
Stripe → Application :
  ✓ Signature vérifiée avec STRIPE_WEBHOOK_SECRET
  ✗ Sans secret → Warning, pas refusé (fallback JSON)
```

## Sessions et Cookies

### Utilisation Session

```php
// Stocker ID adresse en session
$request->getSession()->set('shipping_address_id', $id);

// Récupérer depuis CheckoutController
$id = $request->getSession()->get('shipping_address_id');

// Supprimer après utilisation
$request->getSession()->remove('shipping_address_id');
```

### Cookies Dépendances

- PHPSESSID : Session utilisateur Symfony
- Nécessaire pour :
  - Authentification (`$this->getUser()`)
  - Session data (`$request->getSession()`)
  - Tokens CSRF

## Performance

### Optimisations Implémentées

```
✓ CartRepository::findCartWithItems()
  └─> Utilise LEFT JOIN pour charger articles

✓ Polling limité à 20 tentatives
  └─> Max 1 minute d'attente

✓ Webhooks non-bloquants
  └─> Retour 200 immédiatement

✓ Index recommandé
  └─> shipping_address.user_id
  └─> order.stripeSessionId
```

## Améliorations Futures

### Court Terme
- [ ] Email de confirmation
- [ ] Factures PDF
- [ ] Historique des commandes

### Moyen Terme
- [ ] Multiple adresses par utilisateur
- [ ] Mise en cache des prix
- [ ] Remises/codes promo avancés

### Long Terme
- [ ] Paiement par abonnement
- [ ] Intégrations logistiques
- [ ] Dashboard admin avancé
- [ ] Analyse prédictive

## Documentation À Maintenir

- [ ] Docs API interne
- [ ] Database diagram (erdplus, etc)
- [ ] Runbook dépannage
- [ ] Guide déploiement

## Contacts et Support

- Stripe Documentation : https://stripe.com/docs
- Stripe Dashboard : https://dashboard.stripe.com
- Stripe Support : https://support.stripe.com
- PHP Symfony : https://symfony.com/doc

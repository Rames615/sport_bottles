# Intégration Stripe - Guide Complet

## Vue d'ensemble du flux de paiement

Le processus de paiement dans Sports Bottles utilise **Stripe Checkout** pour traiter les moyens de paiement de manière sécurisée.

### Flux de paiement (DÉTAILLÉ) :

```
┌─────────────────┐
│  Panier Rempli  │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────┐
│ Clique "Validation Commande"│
│  (/panier/checkout - GET)   │
└────────┬────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ Affiche Récapitulatif        │
│ (checkout.html.twig)         │
│ - Articles du panier         │
│ - Total à payer              │
│ - Bouton "Payer"             │
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ Clique "Payer"               │
│ POST → /stripe/checkout      │
│ Validation CSRF              │
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ StripeController::checkout() │
│ 1. Valide panier             │
│ 2. Crée Order (status=pending)
│ 3. Envoie articles à Stripe  │
│ 4. Crée session Stripe       │
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ Redirection vers Stripe       │
│ (Page de paiement)           │
│ - Rentre coordonnées carte   │
│ - Confirme paiement          │
└────────┬─────────────────────┘
         │
         ▼ (Paiement réussi)
┌──────────────────────────────┐
│ Stripe redirige vers /success │
│ + session_id={CHECKOUT_ID}   │
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ StripeController::success()       │
│ 1. Récupère Order via sessionId   │
│ 2. Si status='paid' → redirige    │
│ 3. Sinon → affiche page polling   │
└────────┬──────────────────────────┘
         │
    ┌────┴────┐
    │          │
    ▼ (Déjà payé) ▼ (En attente)
┌─────────────┐  ┌──────────────────┐
│ Redirige    │  │ Page success.html │
│immédiatement│  │ - Affiche détails │
└──────┬──────┘  │ - Poll toutes 3s  │
       │         │ - Attend webhook  │
       │         └────────┬──────────┘
       │                  │
       │                  ▼
       │         ┌──────────────────────┐
       │    PARALLÈLEMENT             │
       │         │ Webhook Stripe       │
       │         │ checkout.session.    │
       │         │ completed            │
       │         │                      │
       │         ▼                      │
       │    ┌──────────────────────┐   │
       │    │ StripeController::   │   │
       │    │ webhook()            │   │
       │    │ 1. Valide signature  │   │
       │    │ 2. Trouve Order      │   │
       │    │ 3. Status → 'paid'   │   │
       │    └─────────────────────────┘
       │ │
       │ ▼
       │ (Poll détecte status='paid')
       └→┐
         │
         ▼
┌──────────────────────────────────┐
│ JS redirection auto              │
│ /stripe/payment-complete/{id}    │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ StripeController::paymentComplete│
│ 1. Vérifie user owns order       │
│ 2. Vérifie status='paid'         │
│ 3. Vide panier (clear())         │
│ 4. Ajoute flash success          │
│ 5. Redirection app_home          │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ Page d'accueil (app_home)        │
│ - Affiche flash message ✅       │
│ - "Merci d'avoir passé commande" │
│ - Panier est maintenant vide     │
│ - Order sauvegardée en base      │
└──────────────────────────────────┘
```

---

## Problèmes résolus

### 1. **Message de succès n'apparaissait pas**
**Problème** : Les messages flash Symfony ne fonctionnent qu'avec les redirects. La page success affichait directement le template sans redirect.

**Solution** :
- Créé une nouvelle route `/stripe/payment-complete/{orderId}`
- Cette route ajoute le flash message PUIS redirige vers home
- Le message flash persiste et s'affiche sur la page d'accueil

### 2. **Pas de redirection automatique vers l'accueil**
**Problème** : L'utilisateur restait coincé sur la page success en attente de polling.

**Solution** :
- Ajouté `setTimeout()` dans le JavaScript polling
- Quand webhook détecte status='paid', page redirige automatiquement vers `/stripe/payment-complete`
- Ensuite vers `/panier` (home) avec le message flash

### 3. **Panier n'était pas vidé après paiement**
**Problème** : Le panier restait rempli après la commande.

**Solution** :
- Appelé `$this->cartService->clear($user)` dans la route `paymentComplete()`
- Le panier est vidé juste avant la redirection vers home

---

## Les fichiers clés

### 1. **CartController.php** - Gestion du panier

- **`/panier`** (GET) - Affiche le panier avec les articles
- **`/panier/checkout`** (GET) - Affiche le récapitulatif avant paiement
- Plus d'autres routes pour ajouter/retirer des articles

> **Note** : Le endpoint `/panier/confirm` (POST) a été supprimé car il n'était pas utilisé dans le flux Stripe.

### 2. **StripeController.php** - Intégration Stripe

#### `/stripe/checkout` (POST)
**Rôle** : Crée la session Stripe et redirige vers le paiement

**Étapes** :
1. Valide le token CSRF
2. Récupère le panier réel via `CartService::prepareCheckout()`
3. Crée une commande en base avec `status='pending'`
4. Construit les articles Stripe à partir du panier (avec prix/quantité réels)
5. Appelle l'API Stripe pour créer une session checkout
6. Enregistre l'ID Stripe dans la commande
7. Redirige vers `session->url` (page de paiement Stripe)

```php
$checkoutResult = $this->cartService->prepareCheckout($user);
$totalAmount = (int) round($checkoutResult['total'] * 100); // en centimes

// Construit les articles Stripe
foreach ($cart->getItems() as $item) {
    $lineItems[] = [
        'price_data' => [
            'currency' => 'eur',
            'product_data' => ['name' => $product->getDesignation()],
            'unit_amount' => (int) round((float) $item->getUnitPrice() * 100),
        ],
        'quantity' => $item->getQuantity(),
    ];
}

$session = Session::create([
    'payment_method_types' => ['card'],
    'line_items' => $lineItems,
    'mode' => 'payment',
    'success_url' => ..., // Redirection après paiement
    'cancel_url' => ...,  // Redirection si annulation
]);
```

#### `/stripe/success` (GET)
**Rôle** : Affiche la confirmation de paiement avec polling

**Étapes** :
1. Récupère la commande via l'ID session Stripe
2. Si commande est déjà payée (webhook rapide) → redirige vers `/stripe/payment-complete`
3. Sinon → affiche page polling

**Polling JavaScript** :
- Scrute `/stripe/order-status/{sessionId}` toutes les 3 secondes
- Attends le statut "paid" (mis à jour par le webhook)
- Quand statut devient "paid" → redirige vers `/stripe/payment-complete`

#### `/stripe/payment-complete/{orderId}` (GET) - ⭐ **NOUVEAU**
**Rôle** : Finalise la commande et affiche le message de succès

**Étapes** :
1. Vérifie que l'utilisateur est connecté
2. Vérifie que la commande existe et appartient à l'utilisateur
3. Vérifie que la commande est en status 'paid'
4. **Vide le panier** de l'utilisateur (clear())
5. **Ajoute le message flash** : "Merci d'avoir passé commande sur le site Sports Bottles. Votre paiement a été confirmé."
6. Redirige vers `app_home`

> Cette route gère l'apparition du message flash !


#### `/stripe/webhook` (POST)
**Rôle** : Reçoit et traite les événements Stripe

**Événement traité** : `checkout.session.completed`
- Valide la signature webhook
- Récupère l'ID session Stripe
- Trouve la commande correspondante
- Change le statut de `pending` à `paid`

```php
if ($type === 'checkout.session.completed') {
    $order = $em->getRepository(Order::class)
        ->findOneBy(['stripeSessionId' => $session->id]);

    if ($order) {
        $order->setStatus('paid');
        $em->flush();
    }
}
```

---

## Les templates

### `cart/checkout.html.twig`
- Affiche le récapitulatif du panier
- Formulaire de paiement avec token CSRF
- POST vers `/stripe/checkout`

### `stripe/success.html.twig`
- Affiche les détails de la commande
- Statut initial : "pending" (badge jaune)
- JavaScript polle le statut et affiche le message de succès quand `status='paid'`
- Lien pour retourner à l'accueil

---

## La classe Order

```php
class Order {
    private ?int $id;                    // ID de la commande
    private ?User $user;                // Client associé
    private ?int $totalAmount;          // Montant en centimes
    private ?string $status;            // pending, paid
    private ?string $stripeSessionId;   // ID session Stripe
    private ?\DateTimeImmutable $createdAt;
}
```

**Statuts possibles** :
- `pending` - Commande créée, en attente de paiement
- `paid` - Paiement reçu via webhook

---

## Configuration Stripe

### Variables d'environnement (.env)

```env
STRIPE_SECRET_KEY=sk_test_... (ou sk_live_... en production)
STRIPE_WEBHOOK_SECRET=whsec_...
```

### services.yaml
```yaml
parameters:
    stripe.secret_key: '%env(STRIPE_SECRET)%'
    stripe.webhook_secret: '%env(STRIPE_WEBHOOK_SECRET)%'
```

---

## Montants et devises

- **Devise** : EUR (euros)
- **Unité** : Les montants sont stockés en **centimes** (cents) dans la base de données et envoyés à Stripe
- **Format de stockage** : Entier (ex: 5000 = 50€)

**Conversion** :
```php
// De euros à centimes
$cents = (int) round($euros * 100);

// De centimes à euros
$euros = $cents / 100;
```

---

## Flux du panier avant le paiement

Le service `CartService` gère tous les articles du panier :

1. **Récupération du panier** : `getCartWithItems($user)`
2. **Préparation** : `prepareCheckout($user)` valide et calcule le total
3. **Clearing** : `clear($user)` vide le panier après la commande

> **Note** : Le panier est maintenant **vidé après la confirmation du paiement** dans le route `/stripe/payment-complete`. Cela garantit que le panier n'est vidé que si le paiement est vraiment confirmé (status='paid').

---

## Points clés à retenir

✅ **Correct** :
- Montants en centimes
- Token CSRF validé
- Articles du panier envoyés à Stripe
- Webhook valide les paiements
- Polling pour statut réel

⚠️ **À éviter** :
- Montants en euros sans conversion
- Pas de validation CSRF
- Montants hardcodés (à supprimer)
- Confiance aveugle au client (server-side validation)

---

## Testing local (sandbox)

1. Utilisez des clés Stripe `sk_test_`
2. Cartes de test Stripe :
   - `4242 4242 4242 4242` - Succès
   - `4000 0000 0000 0002` - Échec

3. Webhook local : Utilisez Stripe CLI pour forwarding
   ```bash
   stripe listen --forward-to localhost:8000/stripe/webhook
   ```

---

## Checklist de test complet

- [ ] Ajouter un produit au panier
- [ ] Aller à "Validation de la commande"
- [ ] Vérifier les détails du panier
- [ ] Cliquer sur "Payer"
- [ ] Remplir les informations de paiement (carte de test)
- [ ] Valider le paiement
- [ ] Vérifier qu'on est redirigé vers `/stripe/success`
- [ ] Vérifier que la page affiche les détails de la commande
- [ ] Vérifier que le statut initial est "pending" (badge jaune)
- [ ] Attendre le webhook (ou vérifier les logs)
- [ ] Vérifier que la page redirige vers l'accueil
- [ ] Vérifier que le message flash "Merci d'avoir passé commande..." est affiché
- [ ] Vérifier que le panier est vide après le paiement
- [ ] Vérifier que la commande existe en base avec status='paid'

---

## Dépannage

| Problème | Cause | Solution |
|----------|-------|----------|
| Redirection échouée après "Payer" | Token CSRF invalide | Vérifier le token dans la form |
| Montant incorrect | Montant hardcodé ou non converti en centimes | Utiliser CartService et convertir en cents |
| Webhook non reçu | STRIPE_WEBHOOK_SECRET absent | Configurer dans .env |
| Statut reste "pending" | Webhook ne s'active pas | Vérifier le logging et les logs Stripe |


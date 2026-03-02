# Checklist de Vérification - Système Stripe Checkout

## Routes à Vérifier

### CartController
```
GET    /panier                      = app_cartindex
GET    /panier/add-ajax/{id}        = app_cartadd_ajax
POST   /panier/add/{id}             = app_cartadd
POST   /panier/update/{itemId}      = app_cartupdate
POST   /panier/remove/{itemId}      = app_cartremove
POST   /panier/clear                = app_cartclear          ✅ NOUVELLE ROUTE
GET    /panier/checkout             = app_cartcheckout      (OPTIONNEL)
```

### CheckoutController
```
GET/POST /checkout/shipping         = checkout_shipping     ✅ NOUVELLE ROUTE PRINCIPALE
GET      /checkout/confirm          = checkout_confirm      ✅ MODIFIÉE
POST     /checkout/pay              = checkout_pay          ✅ MODIFIÉE
```

### PaymentController
```
GET  /payment/success               = payment_success
GET  /payment/order-status/{sessionId} = payment_order_status
GET  /payment/complete/{id}         = payment_complete
GET  /payment/cancel                = payment_cancel
```

### WebhookController
```
GET/POST /webhook/stripe            = webhook_stripe        ✅ WEBHOOKS
```

## Vérifications à Faire Avant Production

### Formulaire d'Adresse
- [ ] Tous les champs obligatoires marqués avec *
- [ ] Messages d'erreur en français
- [ ] Validation regex du code postal (5 chiffres)
- [ ] Validation du téléphone (regex correct)

### Templates
- [ ] `cart/index.html.twig` - Route checkout_shipping au lieu de app_cartcheckout
- [ ] `checkout/shipping.html.twig` - Formulaire POST vers checkout_shipping
- [ ] `checkout/confirm.html.twig` - Bouton payer vers checkout_pay
- [ ] `stripe/success.html.twig` - Polling vers payment_order_status
- [ ] `stripe/cancel.html.twig` - Lien vers app_cartindex

### Base de Données
- [ ] Table `shipping_address` créée
- [ ] Colonnes correctes (fullName, address, city, postalCode, country, phone)
- [ ] Foreign key vers `user`

### Variables d'Environnement
- [ ] STRIPE_SECRET_KEY configurée
- [ ] STRIPE_PUBLIC_KEY configurée
- [ ] STRIPE_WEBHOOK_SECRET configurée

### Sécurité
- [ ] CSRF tokens sur tous les POST
- [ ] Vérification utilisateur connecté
- [ ] Validation montants côté serveur
- [ ] Webhooks signature vérifiée

## Points d'Entrée Utilisateur

### 1. Depuis la Page Panier
```
/panier (index)
  └─> Fait l'ajout/suppression/mise à jour d'articles
  └─> Boutton "Procéder au paiement" → /checkout/shipping
  └─> Bouton "Vider le panier" → /panier/clear (POST)
```

### 2. Depuis le Formulaire d'Adresse
```
/checkout/shipping
  └─> POST avec formulaire
  └─> Sauvegarde adresse en DB
  └─> Stocke ID en session
  └─> Redirection → /checkout/confirm
```

### 3. Depuis la Confirmation
```
/checkout/confirm
  └─> Affichage résumé
  └─> Bouton "Payer" → POST /checkout/pay
```

### 4. Création Commande et Redirection Stripe
```
POST /checkout/pay
  └─> Crée commande status=pending
  └─> Crée session Stripe
  └─> Redirige vers Stripe Checkout URL
```

### 5. Après Paiement Stripe
```
Utilisateur ramené à /payment/success?session_id={id}
  └─> Affiche spinner
  └─> Poll /payment/order-status/{sessionId} chaque 3s
  └─> Quand status=paid, redirection vers /payment/complete/{id}
```

### 6. Confirmation Finale
```
/payment/complete/{id}
  └─> Affichage message confirmé et numéro commande
  └─> Panier vidé
  └─> Lien vers accueil
```

## Scénarios à Tester

### Scénario 1 : Succès Complet
```
1. Ajouter produit au panier
2. Cliquer "Procéder au paiement"
3. Remplir adresse et valider
4. Vérifier résumé et payer
5. Faire le paiement sur Stripe
6. Voir la confirmation
7. Vérifier panier vide
```

### Scénario 2 : Annulation
```
1. Procéder jusqu'à Stripe
2. Cliquer "Annuler" sur Stripe
3. Être redirigé à /payment/cancel
4. Vérifier panier toujours là
5. Pouvoir recommencer l'achat
```

### Scénario 3 : Validation Adresse
```
1. Laisser champs vides
2. Voir les erreurs de validation
3. Remplir avec données invalides
4. Voir les erreurs spécifiques
5. Remplir correctement
6. Valider avec succès
```

### Scénario 4 : Vider le Panier
```
1. Ajouter plusieurs produits
2. Voir le résumé correct
3. Cliquer "Vider le panier"
4. Confirmer l'alerte
5. Voir le panier vide
6. Message de succès affiché
```

### Scénario 5 : Webhook Stripe
```
1. Intercepter l'appel au webhook avec Stripe CLI
2. Envoyer checkout.session.completed
3. Vérifier que la commande passe à status=paid
4. Vérifier le logging
```

## Dépannage Courant

### Problème : Erreur "Adresse introuvable"
- Vérifier que session contient shipping_address_id
- Vérifier que l'adresse appartient à l'utilisateur

### Problème : Stripe Checkout ne fonctionne pas
- Vérifier STRIPE_SECRET_KEY et STRIPE_PUBLIC_KEY
- Vérifier clés en env.local (local) ou env (serveur)

### Problème : Webhook ne déclenche pas
- Vérifier STRIPE_WEBHOOK_SECRET
- Vérifier route /webhook/stripe accessible
- Vérifier logs pour erreurs

### Problème : Panier ne se vide pas après paiement
- Vérifier que PaymentController::complete() appelle clear()
- Vérifier que complete() est déclenché

## Logs à Surveiller

```
# WebhookController
Stripe webhook received
Stripe event received [type, id]
Order marked as paid via webhook
Unhandled Stripe event type

# CheckoutController
Order created (montant, user_id)
Stripe session created (session_id)

# PaymentController
Order status polling
Order marked as complete
Cart cleared
```

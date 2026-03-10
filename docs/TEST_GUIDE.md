# Exemples d'utilisation et Tests - Système Stripe

## Test API avec cURL

### 1. Test de Panier - Ajouter un Produit

```bash
# Ajouter un produit au panier (authentifié)
curl -X POST http://localhost:8000/panier/add/1 \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d "_token=your_csrf_token"
```

### 2. Test de Panier - Vider

```bash
# Vider le panier complet
curl -X POST http://localhost:8000/panier/clear \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d "_token=your_csrf_token"
```

### 3. Test Formulaire d'Adresse

```bash
# Soumettre le formulaire d'adresse
curl -X POST http://localhost:8000/checkout/shipping \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d "shipping_address[fullName]=Jean Dupont" \
  -d "shipping_address[address]=123 Rue de la Paix" \
  -d "shipping_address[city]=Paris" \
  -d "shipping_address[postalCode]=75001" \
  -d "shipping_address[country]=France" \
  -d "shipping_address[phone]=+33612345678" \
  -d "_token=your_csrf_token"
```

### 4. Test de Paiement

```bash
# Soumettre le paiement
curl -X POST http://localhost:8000/checkout/pay \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d "_token=your_csrf_token"
```

### 5. Test Webhook Stripe

```bash
# Test Health Check (GET)
curl -X GET http://localhost:8000/webhook/stripe

# Avec Stripe CLI (recommended)
stripe trigger checkout.session.completed
```

## Test avec Stripe CLI

### Installation et Configuration

```bash
# 1. Installer Stripe CLI
# macOS
brew install stripe/stripe-cli/stripe

# Linux
curl https://files.stripe.com/stripe-cli/install.sh -O
bash install.sh

# Windows
choco install stripe

# 2. Authentifier
stripe login

# 3. Écouter les webhooks
stripe listen --forward-to http://localhost:8000/webhook/stripe

# Cela donne une API key (whsec_...) à mettre dans .env
# STRIPE_WEBHOOK_SECRET=whsec_...

# 4. Envoyer un événement de test
stripe trigger checkout.session.completed
```

## Cartes de Test Stripe

### Paiement Réussi
```
Numéro : 4242 4242 4242 4242
Expiration : 12/25
CVC : 123
Nom : N'importe quel nom
```

### Paiement Échoué (Risque)
```
Numéro : 4000 0000 0000 0002
Expiration : 12/25
CVC : 123
```

### Paiement Refusé
```
Numéro : 4000 0000 0000 0069
Expiration : 12/25
CVC : 123
```

## Flux de Test Complet

### Étape 1 : Préparer l'Utilisateur
```bash
# 1. S'authentifier sur l'application
# 2. Aller à /panier

# Ou directement via terminal de logs (voir routes)
```

### Étape 2 : Ajouter et Vérifier les Produits
```
1. Cliquer sur un produit pour l'ajouter
2. Vérifier le panier affiche les articles
3. Vérifier le montant total
4. Essayer de modifier les quantités
```

### Étape 3 : Test du Bouton "Vider"
```
1. Cliquer "Vider le panier"
2. Confirmer l'alerte
3. Vérifier le panier est vide
4. Vérifier le message "Votre panier a été vidé"
```

### Étape 4 : Ajouter à Nouveau et Procéder
```
1. Ajouter des produits au panier
2. Cliquer "Procéder au paiement"
3. Devrait aller à /checkout/shipping
```

### Étape 5 : Test Formulaire d'Adresse
```
1. Laisser champs vides
   → Voir les erreurs de validation ✓

2. Remplir avec données invalides
   - Code postal : ABC (devrait refuser) ✓
   - Téléphone : 12 (trop court) ✓

3. Remplir correctement
   - Jean Dupont
   - 123 Rue de la Paix
   - Paris
   - 75001
   - France
   - +33612345678
   
4. Cliquer "Continuer vers le paiement"
5. Devrait aller à /checkout/confirm
```

### Étape 6 : Confirmation et Paiement
```
1. Vérifier le résumé du panier
2. Vérifier l'adresse de livraison affichée
3. Cliquer "Payer {montant} €"
4. Devrait rediriger vers Stripe Checkout
```

### Étape 7 : Simulation Stripe
```
1. Sur Stripe Checkout, utiliser carte 4242...
2. Remplir date : 12/25, CVC : 123
3. Cliquer "Payer"
4. Devrait revenir à /payment/success?session_id=...
5. Voir spinner de vérification
```

### Étape 8 : Webhook et Confirmation
```
# Option A : Avec Stripe CLI
stripe trigger checkout.session.completed

# Option B : Attendre le webhook réel (quelques secondes)

1. Voir le spinner changé en check ✓
2. Message "Paiement confirmé" ✓
3. Redirection à /payment/complete/{id} ✓
4. Affichage du numéro de commande ✓
5. Montant payé affiché ✓

```

### Étape 9 : Vérification Base de Données
```bash
# Vérifier la commande créée
mysql> SELECT * FROM `order` WHERE user_id = {id};
# Devrait voir status = 'paid'

# Vérifier l'adresse sauvegardée
mysql> SELECT * FROM shipping_address WHERE user_id = {id};
# Devrait voir les infos saisies

# Vérifier le panier vidé
mysql> SELECT * FROM cart_item WHERE cart_id = {cart_id};
# Devrait être vide
```

## Scénarios de Teste Avancés

### Test Webhook Signature (Sécurité)

```php
// En production, le webhook DOIT vérifier la signature
// Tester que sans STRIPE_WEBHOOK_SECRET, une erreur 400 est retournée

// Envoyer un webhook avec mauvaise signature
curl -X POST http://localhost:8000/webhook/stripe \
  -H "Stripe-Signature: bad_signature" \
  -d '{...payload...}'
# Devrait retourner 400 "Invalid webhook"
```

### Test Montant Manipulé

```php
// Tester que le serveur recalcule le montant
// Envoyer un montant différent du client ne devrait pas changer l'ordre
// La CheckoutController recalcule toujours le montant
```

### Test CSRF Token

```bash
# Envoyer POST sans CSRF token
curl -X POST http://localhost:8000/panier/clear

# Devrait refuser et rediriger
```

## Logs à Vérifier

### Fichier de Log
```
tail -f var/log/dev.log | grep -i stripe
```

### Événements Attendus
```
[2026-02-03 14:32:10] app.INFO: Stripe webhook received
[2026-02-03 14:32:10] app.INFO: Stripe event received {"type":"checkout.session.completed","id":"evt_..."}
[2026-02-03 14:32:10] app.INFO: Order marked as paid via webhook {"order_id":123}
```

### Debug SQL
```
# Dans .env.local
DATABASE_URL="mysql://user:pass@localhost/sports_bottles?serverVersion=mysql8.0"
QUERY_DEBUG=1
```

## Dépannage en Cas d'Erreur

### Erreur : "Invalid webhook"
```
Cause : Mauvaise signature Stripe
Fix   : 
  1. Vérifier STRIPE_WEBHOOK_SECRET
  2. Vérifier que Stripe CLI forward-to est correct
  3. Vérifier que webhooks sont activés dans dashboard Stripe
```

### Erreur : "Order marked as failed"
```
Cause : Utilisateur a annulé ou erreur de paiement
Fix   : 
  1. Essayer à nouveau avec bonnes coordonnées
  2. Vérifier les logs pour erreur spécifique
  3. Vérifier le statut de la commande en DB
```

### Erreur : "Adresse introuvable"
```
Cause : Session perdue ou ID invalide
Fix   : 
  1. Vérifier que cookies de session sont activés
  2. Vérifier que l'adresse est bien sauvegardée
  3. Vérifier que l'utilisateur est connecté tout du long
```

### Erreur : Panier ne se vide pas
```
Cause : `CartService::clear()` pas appelée après paiement
Fix   : 
  1. Vérifier que `payment_complete` route est atteinte
  2. Vérifier que `clear()` est appelée
  3. Vérifier les logs para erreurs
```

## Performance et Scalabilité

### Points de Pot

1. **Webhook Traitement** - Asynchrone (non-bloquant) ✓
2. **Polling Frontend** - Limité à 20 tentatives (1 min) ✓
3. **Session Storage** - Court terme, OK pour cette utilisation ✓
4. **Base de Données** - Index sur `stripeSessionId` recommandé

### Index à Ajouter

```sql
ALTER TABLE `order` ADD INDEX idx_stripe_session (stripeSessionId);
ALTER TABLE shipping_address ADD INDEX idx_user (user_id);
ALTER TABLE cart ADD INDEX idx_user (user_id);
```

## Monitoring et Alertes

### À Configurer
```
1. Alertes Stripe pour paiements échoués
2. Logs centralisés pour webhooks
3. Monitoring montant total vs Stripe
4. Alertes si webhook timeout > 30s
```

## Support
- Documentation Stripe : https://stripe.com/docs
- Dashboard Stripe : https://dashboard.stripe.com
- Logs applicatif : `/var/log/sports_bottles.log`

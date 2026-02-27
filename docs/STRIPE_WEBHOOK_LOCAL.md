# Configuration des Webhooks Stripe en Local

## Vue d'ensemble

Ce document explique comment configurer et tester les webhooks Stripe localement avec votre application Symfony.

---

## 1. Variables d'environnement requises

Créez ou mettez à jour le fichier `.env.local` à la racine du projet:

```bash
# Clés API Stripe (disponibles sur https://dashboard.stripe.com/apikeys)
# Utilisez les clés "Test" pour développement
STRIPE_PUBLIC_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxx
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxx

# Clé secrète du webhook (générée par Stripe CLI)
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx
```

⚠️ **IMPORTANT**: Ne commitez JAMAIS `.env.local` ou les clés API dans votre dépôt. Ajoutez-le à `.gitignore`.

---

## 2. Installation et configuration de Stripe CLI

### Installation

**Windows** (via Chocolatey ou téléchargement):
```bash
choco install stripe-cli
```

Ou téléchargez depuis: https://github.com/stripe/stripe-cli/releases

**Mac**:
```bash
brew install stripe/stripe-cli/stripe
```

**Linux**:
```bash
# Téléchargez la version appropriée et installez
```

### Authentification avec Stripe CLI

```bash
# Connectez-vous à votre compte Stripe
stripe login

# Suivez les instructions pour confirmer l'accès
```

---

## 3. Lancer le webhook forward en développement

Une fois Stripe CLI installé et authentifié, lancez le forwarding des webhooks vers votre application locale:

```bash
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

**Sortie attendue**:
```
> Ready! Your webhook signing secret is: whsec_xxxxxxxxxxxxxxxxxxxxx
> Ready to accept events...
```

💡 Copiez la clé `whsec_xxx` et mettez-la dans votre `.env.local` sous `STRIPE_WEBHOOK_SECRET`.

---

## 4. Tester les webhooks en local

### Option A: Via les events de test Stripe CLI

Avec `stripe listen` actif dans un terminal, vous pouvez déclencher des événements de test:

```bash
# Dans un autre terminal
stripe trigger payment_intent.succeeded --cvc 242
stripe trigger checkout.session.completed
stripe trigger payment_intent.payment_failed --cvc 402
```

### Option B: Via votre application en local

1. Démarrez votre serveur Symfony:
   ```bash
   symfony serve
   ```
   ou
   ```bash
   php -S localhost:8000 -t public
   ```

2. Dans un autre terminal, lancez `stripe listen`:
   ```bash
   stripe listen --forward-to http://localhost:8000/stripe/webhook
   ```

3. Effectuez un paiement complet:
   - Accédez à votre page panier
   - Lancez le checkout Stripe
   - Utilisez une **carte de test**: `4242 4242 4242 4242` (mm/aa quelconque, CVC 123)

4. Vérifiez les logs:
   ```bash
   tail -f var/log/dev.log | grep -i stripe
   ```

---

## 5. État des webhooks supportés

| Événement | Statut | Description |
|-----------|--------|-------------|
| `checkout.session.completed` | ✅ Implémenté | Paiement confirmé → Marque la commande `paid` |
| `payment_intent.payment_failed` | ✅ Implémenté | Paiement échoué → Marque la commande `failed` |
| `charge.refunded` | ❌ Non implémenté | Remboursement |
| `invoice.payment_failed` | ❌ Non implémenté | Facture non payée |

---

## 6. Dépannage courant

### Erreur: "No route found for GET /stripe/webhook"

**Cause**: Le webhook reçoit une requête GET (test de santé).

**Solution**: ✅ **Déjà corrigé** — la route accepte maintenant `GET` et `POST`.

```php
#[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST', 'GET'])]
```

### Erreur: "Webhook Stripe invalide"

**Cause**: `STRIPE_WEBHOOK_SECRET` manquant ou incorrecte.

**Vérification**:
```bash
# Vérifiez que la clé est bien en .env.local
grep STRIPE_WEBHOOK_SECRET .env.local

# Vérifiez que Stripe CLI utilise la même URL
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

### Erreur CSRF: "Invalid CSRF token"

**Cause**: La protection CSRF rejette les webhooks Stripe.

**Solution**: ✅ **Déjà corrigé** — la route est exclue via `config/packages/csrf.yaml`:

```yaml
csrf_protection:
    skip_path: ^/stripe/webhook$
```

### Les webhooks ne sont pas reçus

1. Vérifiez que `stripe listen` est actif
2. Vérifiez que Stripe CLI est authentifié: `stripe status`
3. Vérifiez les logs: `stripe trigger checkout.session.completed --help`
4. Vérifiez que votre serveur tourne sur `http://localhost:8000`

---

## 7. Logging et débogage

Tous les événements webhook sont loggés dans `var/log/dev.log`:

```bash
# Affichage des logs Stripe
tail -f var/log/dev.log | grep -i stripe

# Affichage détaillé (inclut payload brut)
tail -f var/log/dev.log | grep -i "stripe webhook reçu" -A 5
```

**Niveaux de log**:
- `info`: Webhook reçu et traité avec succès
- `warning`: Événement non critque (ex: secret webhook absent en dev)
- `error`: Erreur lors du traitement

---

## 8. Webhook en production

Une fois en production, vous devez configurer les webhooks directement dans le dashboard Stripe:

1. Allez sur: https://dashboard.stripe.com/webhooks
2. Cliquez "Add Endpoint"
3. Entrez votre URL publique: `https://votresite.com/stripe/webhook`
4. Sélectionnez les événements:
   - `checkout.session.completed`
   - `payment_intent.payment_failed`
5. Copiez le **Signing Secret** et placez-le dans votre `.env` ou variable de serveur

---

## 9. Checklist de sécurité

- [ ] Les clés API sont dans `.env.local` (jamais commitées)
- [ ] `STRIPE_WEBHOOK_SECRET` est configuré
- [ ] La signature du webhook est vérifiée (implémenté)
- [ ] Le webhook est exclu de CSRF (implémenté)
- [ ] Les statuts de commande sont correctement mis à jour
- [ ] Les logs sont actifs pour audit
- [ ] En production, utilisez HTTPS uniquement
- [ ] Changez les clés après tout incident de sécurité

---

## Résumé des commandes utiles

```bash
# Démarrer le serveur
symfony serve

# Forwarder les webhooks Stripe en local
stripe listen --forward-to http://localhost:8000/stripe/webhook

# Tester un événement
stripe trigger checkout.session.completed

# Vérifier que Stripe CLI est connecté
stripe status

# Voir l'aide Stripe CLI
stripe help
```

---

## Liens utiles

- [Stripe API Keys](https://dashboard.stripe.com/apikeys)
- [Stripe Webhooks](https://dashboard.stripe.com/webhooks)
- [Stripe CLI Documentation](https://stripe.com/docs/stripe-cli)
- [Stripe Test Cards](https://stripe.com/docs/testing)
- [Symfony CSRF Protection](https://symfony.com/doc/current/security/csrf_protection.html)


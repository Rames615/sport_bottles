# Intégration Stripe — Résumé des modifications et guide de test (FR)

Ce document décrit les changements réalisés pour rendre le paiement Stripe fonctionnel, la validation côté serveur et les étapes pour tester localement.

## Fichiers modifiés/créés
- `config/packages/security.yaml` : correction d'un problème d'indentation du firewall `webhook` (évite l'erreur YAML duplicate key).
- `config/services.yaml` : ajout des paramètres :
  - `stripe.secret_key` -> lit `STRIPE_SECRET`
  - `stripe.webhook_secret` -> lit `STRIPE_WEBHOOK_SECRET`
- `src/Controller/StripeController.php` :
  - Vérification de la session Stripe sur la route `stripe_success` (récupère la session Stripe et/ou PaymentIntent et marque la commande `paid` si le paiement est confirmé).
  - Ajout d'un point d'entrée webhook `POST /webhook` (`stripe_webhook`) qui valide la signature et met à jour la commande quand l'évènement `checkout.session.completed` est reçu.
- `STRIPE_SETUP_FR.md` : ce fichier (vous êtes en train de le lire).

## Pourquoi ces changements ?
- La page de succès ne devait pas se contenter d'afficher une page : il faut vérifier l'état réel du paiement côté Stripe (sécurité et fiabilité).
- Les webhooks sont recommandés pour recevoir la confirmation asynchrone que le paiement est bien finalisé (utile surtout quand la confirmation arrive après la redirection navigateur).

## Variables d'environnement (à définir localement)
Ajoutez dans `/.env.local` (ou via votre gestion d'environnement) :

```
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

- `STRIPE_SECRET` : clé secrète Stripe (test ou live selon l'environnement).
- `STRIPE_WEBHOOK_SECRET` : secret du webhook (fourni par Stripe quand vous créez un webhook endpoint). Utilisé pour valider la signature.

Ne mettez JAMAIS ces clés dans le repo public.

## Comment tester localement
1. Assurez-vous que les variables d'environnement sont en place (`.env.local` préférence pour dev local).
2. Redémarrez le serveur Symfony si nécessaire :

```bash
symfony server:stop
symfony server:start -d
```

3. Créez un panier et lancez le flux de paiement (vous devez être connecté si l'action nécessite un utilisateur). Le contrôleur `checkout` crée une `Order`, demande une session Stripe et redirige vers `session->url`.

4. Après paiement (mode test) vous êtes redirigé vers `/success?session_id={CHECKOUT_SESSION_ID}`. La route `stripe_success` récupère la session depuis Stripe et vérifie si le paiement est `paid` (ou vérifie le `PaymentIntent`). Si oui, la commande est marquée `paid` en base.

5. Pour tester les webhooks localement (fortement recommandé) :
   - Installez l'outil CLI Stripe (optionnel si déjà installé) et lancez :

```bash
stripe listen --forward-to https://127.0.0.1:8000/webhook
```

   - Cette commande affichera un `whsec_...` que vous devez copier dans `STRIPE_WEBHOOK_SECRET`.
   - Effectuez un paiement test ; Stripe enverra l'évènement `checkout.session.completed` au endpoint local. Le webhook authentifiera l'évènement et mettra à jour la commande.

## Points d'attention
- `security.yaml` contient désormais un firewall `webhook` marqué `stateless: true` — cela permet aux requêtes webhook d'arriver sans session/CSRF.
- Les webhooks doivent être validés par signature (`Stripe-Signature`) pour éviter les faux positifs.
- En production, utilisez `live` keys et `https`.

## Commandes utiles
- Vider le cache Symfony :

```bash
php bin/console cache:clear
```

- Vérifier l'installation Stripe PHP (depuis le projet) :

```bash
php -r "require 'vendor/autoload.php'; echo class_exists('Stripe\\Checkout\\Session') ? 'OK' : 'MISSING';"
```

## Fichiers principaux à vérifier
- `src/Controller/StripeController.php` — logique checkout / success / webhook
- `config/services.yaml` — variables d'environnement Stripe
- `config/packages/security.yaml` — firewall webhook

---
Si vous voulez, je peux :
- ajouter des tests automatisés pour le controller (PHPUnit),
- fournir un petit script Postman / HTTPie pour simuler le webhook,
- ou déployer un exemple de webhook endpoint côté Stripe Dashboard et lier automatiquement la clé.

Souhaitez-vous que j'ajoute un petit test fonctionnel qui simule une session Stripe (mock) pour valider la logique `success` ?

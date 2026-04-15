# Configuration Stripe en local

## Objectif

Ce document décrit la configuration minimale nécessaire pour exécuter Stripe en environnement de développement.

## Variables d'environnement

À définir dans `.env.local` :

```env
STRIPE_PUBLIC_KEY=pk_test_xxx
STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

## Bonnes pratiques

- utiliser uniquement des clés de test en local ;
- ne jamais versionner les secrets ;
- redémarrer le serveur Symfony après modification des variables si nécessaire.

## Vérification rapide

- le panier doit mener jusqu'à la page de paiement ;
- l'appel à `/checkout/pay` doit créer une session Stripe ;
- la redirection vers Stripe Checkout doit fonctionner.

## Outils utiles

- Dashboard Stripe pour récupérer les clés ;
- Stripe CLI pour exposer les webhooks localement.

## Suite logique

- `docs/STRIPE_WEBHOOK_LOCAL.md` pour la configuration du webhook ;
- `docs/TEST_GUIDE.md` pour la recette du paiement.
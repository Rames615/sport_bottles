# Webhooks Stripe en local

## Objectif

Ce document décrit la mise en place des webhooks Stripe en environnement local afin de valider le passage des commandes à l'état `paid`.

## Préparation

1. installer Stripe CLI ;
2. lancer le serveur Symfony ;
3. vérifier que `WebhookController` expose bien `/webhook/stripe`.

## Lancer Stripe CLI

```bash
stripe login
stripe listen --forward-to http://localhost:8000/webhook/stripe
```

Stripe CLI affiche alors un secret de signature `whsec_...` à reporter dans `STRIPE_WEBHOOK_SECRET`.

## Tester un événement

```bash
stripe trigger checkout.session.completed
```

Ou effectuer un paiement de test réel avec la carte `4242 4242 4242 4242`.

## Résultat attendu

- le webhook est reçu sans erreur ;
- la commande liée à la session Stripe passe à `paid` ;
- le stock est décrémenté ;
- le panier est vidé lors de la confirmation finale.

## Points d'attention

- si le webhook n'arrive pas, la page de succès peut rester dans un état d'attente ;
- si `STRIPE_WEBHOOK_SECRET` est faux, la signature échoue ;
- sans tunnel ou sans Stripe CLI, la confirmation serveur n'est pas testable correctement.
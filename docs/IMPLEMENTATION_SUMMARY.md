# Synthèse du tunnel de commande

## Objectif

Fournir une vue courte du tunnel panier, checkout et paiement, sans reproduire tout le détail technique du document Stripe principal.

## Ce qui est en place

- panier persistant lié à l'utilisateur ;
- formulaire d'adresse de livraison ;
- récapitulatif de commande ;
- commande créée avant paiement ;
- redirection Stripe Checkout ;
- webhook de confirmation ;
- déduction du stock ;
- confirmation finale et vidage du panier.

## Flux simplifié

`Panier` → `Adresse de livraison` → `Récapitulatif` → `Stripe Checkout` → `Webhook` → `Confirmation`

## Référence détaillée

Pour le détail complet, consulter `docs/IMPLEMENTATION_STRIPE_CHECKOUT.md` et `docs/STRIPE.md`.
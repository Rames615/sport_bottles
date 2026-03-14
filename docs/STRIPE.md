# Paiement Stripe

## Objectif

Ce document décrit le fonctionnement actuel du paiement Stripe dans Sports Bottles. Il sert de référence principale pour le sujet paiement.

## Composants impliqués

- `CartController` pour l'accès au panier ;
- `CheckoutController` pour l'adresse, le récapitulatif et la création de la commande `pending` ;
- `PaymentController` pour la page de succès, le polling d'état et la confirmation finale ;
- `WebhookController` pour les événements Stripe ;
- `CartService` pour le calcul du panier et la gestion du stock.

## Flux de paiement

1. l'utilisateur constitue son panier ;
2. il renseigne son adresse de livraison ;
3. le serveur recalcule le panier avant paiement ;
4. une commande interne est créée avec le statut `pending` ;
5. une session Stripe Checkout est créée ;
6. l'utilisateur est redirigé vers Stripe ;
7. Stripe redirige l'utilisateur vers la page de succès ;
8. le webhook confirme le paiement ;
9. la commande passe à `paid`, le stock est déduit et le panier est vidé.

## Principes de sécurité

- les montants sont recalculés côté serveur ;
- les actions sensibles sont protégées par CSRF ;
- l'utilisateur doit être authentifié ;
- le webhook constitue la source de vérité pour la confirmation de paiement ;
- la signature Stripe est prise en charge lorsque le secret webhook est configuré.

## État actuel

Le flux actif du projet repose sur `CheckoutController`, `PaymentController` et `WebhookController`. Le contrôleur `StripeController` présent dans le dépôt correspond à un historique de mise en oeuvre et ne doit pas être considéré comme la référence principale du flux actuel.

## Documents liés

- `docs/STRIPE_SETUP_FR.md` pour la configuration locale ;
- `docs/STRIPE_WEBHOOK_LOCAL.md` pour les tests webhook ;
- `docs/GUIDE_STRIPE_OPTIMISATIONS.md` pour les améliorations recommandées ;
- `docs/IMPLEMENTATION_STRIPE_CHECKOUT.md` pour le détail d'implémentation.
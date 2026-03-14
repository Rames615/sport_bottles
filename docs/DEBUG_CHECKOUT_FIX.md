# Note de débogage - checkout Stripe

## Objet

Ce document conserve la trace des principaux problèmes déjà rencontrés sur le flux de paiement et de leur résolution, afin d'éviter de requalifier plusieurs fois les mêmes incidents.

## Problèmes historiquement rencontrés

- routes Stripe incohérentes ou héritées d'une ancienne implémentation ;
- webhook non déclenché en local ;
- commande restant bloquée à `pending` ;
- panier non vidé à la fin du parcours ;
- manque de clarté entre confirmation utilisateur et confirmation serveur.

## Résolution retenue

Le flux actuel sépare clairement :

- la préparation de commande dans `CheckoutController` ;
- la page de succès et la finalisation dans `PaymentController` ;
- la confirmation Stripe dans `WebhookController`.

## Leçon principale

En environnement Stripe, il faut documenter et tester séparément :

- la redirection navigateur ;
- la confirmation webhook ;
- l'effet métier final sur la commande, le stock et le panier.
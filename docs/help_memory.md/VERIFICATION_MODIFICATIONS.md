# Vérification des modifications récentes

## Objectif

Ce document formalise les contrôles à effectuer après une série de corrections ou d'évolutions, sans reprendre toute la recette globale.

## Vérifications ciblées

### Documentation

- [ ] les documents réécrits utilisent un ton homogène ;
- [ ] les doublons ont été supprimés ou réduits ;
- [ ] chaque fichier reste centré sur un seul sujet.

### Catalogue

- [ ] les pages produit décrivent bien la route dédiée `/product/{id}` ;
- [ ] les documents produit ne font plus référence à d'anciens comportements obsolètes comme les modales si elles ne sont plus actives.

### Panier et checkout

- [ ] le flux décrit dans la documentation correspond à `CartController`, `CheckoutController`, `PaymentController` et `WebhookController` ;
- [ ] le rôle de Stripe est correctement expliqué sans doublon excessif.

### Compte utilisateur

- [ ] les documents sur le profil et le mot de passe reflètent les écrans réellement présents ;
- [ ] les parcours décrits sont compatibles avec les routes du projet.

## Critère de validation

La vérification est satisfaisante lorsque le lecteur peut :

- identifier rapidement le bon document ;
- comprendre la fonctionnalité concernée sans relire plusieurs fichiers redondants ;
- distinguer ce qui relève de l'état actuel, de l'historique et des pistes d'amélioration.
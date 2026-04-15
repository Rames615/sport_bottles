# Édition du profil utilisateur

## Objectif

Décrire le fonctionnement de l'édition du profil utilisateur sans répéter l'ensemble de la documentation compte et sécurité.

## Périmètre actuel

La fonctionnalité est portée par `AccountController` et `ProfileFormType`. Elle permet à un utilisateur authentifié de consulter puis de modifier certaines informations de son compte, principalement son adresse e-mail.

## Flux fonctionnel

1. l'utilisateur accède à `/mon-profil` ;
2. il ouvre l'écran d'édition `/mon-profil/editer` ;
3. le formulaire est prérempli avec ses données ;
4. après validation, les modifications sont persistées ;
5. un message de succès est affiché au retour sur le profil.

## Points importants

- l'accès est réservé aux utilisateurs connectés ;
- la validation Symfony s'applique au formulaire ;
- la mise à jour reste volontairement simple pour conserver un parcours lisible.

## Documents liés

- `docs/password.md` pour la gestion du mot de passe ;
- `docs/readme.md` pour la vue d'ensemble de l'espace utilisateur.
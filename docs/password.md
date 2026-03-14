# Réinitialisation du mot de passe

## Objectif

Présenter le fonctionnement actuel de la réinitialisation du mot de passe dans Sports Bottles.

## Flux fonctionnel

1. l'utilisateur ouvre la page « mot de passe oublié » ;
2. il saisit son e-mail ;
3. un token temporaire est généré pour le compte correspondant ;
4. un e-mail de réinitialisation est envoyé ;
5. le lien reçu mène vers un formulaire de nouveau mot de passe ;
6. le mot de passe est haché puis enregistré ;
7. le token est invalidé.

## Principes de sécurité

- le token de réinitialisation est temporaire ;
- le mot de passe est haché via les mécanismes Symfony ;
- le message affiché lors de la demande de réinitialisation reste neutre afin de ne pas divulguer l'existence d'un compte.

## Périmètre documentaire

Ce fichier décrit le flux fonctionnel. Les détails de configuration de sécurité globale relèvent de la documentation applicative générale.
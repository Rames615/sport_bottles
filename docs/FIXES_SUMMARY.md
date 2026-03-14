# Résumé des corrections checkout

## Objectif

Ce document résume en quelques lignes les corrections structurantes déjà apportées au tunnel de commande.

## Corrections majeures

- clarification du flux checkout autour de `CheckoutController` ;
- distinction explicite entre page de succès et confirmation webhook ;
- gestion cohérente des statuts `pending`, `paid` et `failed` ;
- décrémentation du stock à la confirmation ;
- vidage final du panier ;
- documentation recentrée sur le flux actuel.

## À retenir

La plupart des anomalies rencontrées dans le passé provenaient d'un mélange entre anciennes routes, documentation historique et comportement réel du code. Cette réécriture vise précisément à éviter ce problème.
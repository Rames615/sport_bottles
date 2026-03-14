# Optimisations et durcissement Stripe

## Objectif

Ce document recense les améliorations recommandées autour du paiement Stripe, en complément de l'implémentation actuelle.

## Ce qui est déjà bien en place

- recalcul serveur du montant ;
- création de commande avant redirection ;
- webhook comme source de vérité ;
- gestion du statut `failed` sur échec de paiement ;
- déduction du stock après confirmation.

## Optimisations prioritaires

### 1. Historiser les lignes de commande

Le projet stocke le montant global et l'adresse formatée, mais pas encore des lignes de commande détaillées. Pour une exploitation plus robuste, il serait pertinent d'ajouter une entité de type `OrderItem`.

### 2. Mieux tracer les événements de paiement

Prévoir un niveau de log homogène pour :

- création de commande ;
- création de session Stripe ;
- réception webhook ;
- passage en `paid` ou `failed`.

### 3. Durcir l'idempotence

Le code vérifie déjà l'état de la commande avant de repasser en `paid`, ce qui limite les doubles traitements. Cette logique peut être renforcée en journalisant davantage les cas déjà traités.

### 4. Prévoir les remboursements

Le projet ne couvre pas encore le cycle de remboursement. Une évolution naturelle serait d'ajouter :

- une gestion métier du remboursement ;
- un suivi d'état complémentaire ;
- une mise à jour cohérente côté administration.

### 5. Tester davantage

Le flux Stripe devrait idéalement être couvert par :

- des tests fonctionnels sur le checkout ;
- des tests unitaires ou d'intégration autour de la transition d'état ;
- des tests manuels documentés sur les webhooks.

## Conclusion

Le socle actuel est bon pour un projet RNCP et une démonstration complète. Les recommandations ci-dessus visent surtout une montée en robustesse vers un usage de production plus strict.
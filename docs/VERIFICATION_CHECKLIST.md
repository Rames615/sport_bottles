# Checklist de vérification avant livraison

## Objectif

Cette checklist sert de contrôle final avant démonstration, soutenance ou mise en ligne d'une version de travail.

## Configuration

- [ ] Les variables d'environnement essentielles sont définies.
- [ ] La base de données est accessible.
- [ ] Les migrations ont été exécutées.
- [ ] Les fixtures ont été rechargées si un jeu de données de démo est requis.

## Front-office

- [ ] La page d'accueil s'affiche correctement.
- [ ] Le catalogue produit charge sans erreur.
- [ ] Les promotions actives sont visibles quand elles existent.
- [ ] Une fiche produit est accessible depuis le catalogue.
- [ ] Le panier s'actualise correctement.

## Tunnel de commande

- [ ] L'ajout au panier fonctionne.
- [ ] La mise à jour de quantité respecte le stock.
- [ ] Le formulaire d'adresse de livraison valide correctement les champs.
- [ ] Le récapitulatif de commande est cohérent.
- [ ] Le bouton de paiement lance bien le flux Stripe.

## Paiement et webhooks

- [ ] Une commande `pending` est créée avant redirection vers Stripe.
- [ ] Le webhook Stripe peut joindre l'application.
- [ ] Une commande payée passe au statut `paid`.
- [ ] Le stock est déduit après confirmation.
- [ ] Le panier est vidé après paiement validé.

## Compte utilisateur

- [ ] L'inscription fonctionne.
- [ ] La connexion fonctionne.
- [ ] L'édition du profil fonctionne.
- [ ] L'historique des commandes est accessible.
- [ ] Le parcours de réinitialisation du mot de passe est exploitable.

## Administration

- [ ] EasyAdmin est accessible au compte administrateur.
- [ ] Les produits sont visibles et modifiables.
- [ ] Les commandes sont visibles.
- [ ] Les promotions sont visibles.

## Qualité et documentation

- [ ] PHPStan ne signale pas de régression évidente liée au périmètre modifié.
- [ ] Les documents clés du dossier `docs` sont cohérents entre eux.
- [ ] Les captures, exemples et consignes de démonstration sont à jour si nécessaires.
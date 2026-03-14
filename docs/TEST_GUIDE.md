# Guide de test

## Objectif

Ce document centralise la stratégie de recette fonctionnelle du projet. Il remplace les anciens guides redondants et renvoie aux documents spécialisés uniquement lorsqu'un sujet nécessite un détail supplémentaire.

## Préparation de l'environnement

Avant toute recette :

- vérifier que la base de données est à jour ;
- disposer d'un compte utilisateur standard ;
- disposer d'un compte administrateur ;
- configurer Stripe en mode test si le tunnel de paiement doit être validé.

## Parcours à tester en priorité

### 1. Navigation et catalogue

- affichage de la page d'accueil ;
- affichage des promotions actives ;
- accès au catalogue ;
- affichage des catégories ;
- affichage d'une page détail produit ;
- cohérence des prix, promotions et stocks visibles.

### 2. Panier

- ajout d'un produit au panier ;
- ajout AJAX depuis les zones qui le supportent ;
- mise à jour des quantités ;
- refus en cas de stock insuffisant ;
- suppression d'une ligne ;
- vidage complet du panier.

### 3. Compte utilisateur

- inscription ;
- connexion ;
- édition du profil ;
- consultation des commandes ;
- réinitialisation du mot de passe.

### 4. Tunnel de commande

- saisie d'une adresse de livraison ;
- validation des erreurs formulaire ;
- affichage du récapitulatif ;
- création d'une commande `pending` ;
- redirection vers Stripe ;
- retour vers la page de succès ;
- confirmation finale après paiement.

### 5. Webhooks et confirmations

- réception d'un `checkout.session.completed` ;
- passage du statut de commande à `paid` ;
- décrémentation du stock ;
- vidage du panier ;
- envoi éventuel de l'e-mail de confirmation.

### 6. Administration

- accès à EasyAdmin avec un compte autorisé ;
- consultation et modification des produits ;
- consultation des commandes ;
- visibilité des promotions et des utilisateurs.

## Jeux de test recommandés

- produit en stock ;
- produit proche de la rupture ;
- panier avec plusieurs lignes ;
- paiement validé ;
- paiement échoué ;
- utilisateur non connecté ;
- formulaire volontairement invalide.

## Cartes Stripe de test

Paiement accepté : `4242 4242 4242 4242`  
Paiement refusé : `4000 0000 0000 0002`

## Résultats attendus

- aucune erreur serveur visible sur le parcours nominal ;
- messages d'erreur compréhensibles en cas d'entrée invalide ;
- cohérence entre le panier, la commande et le stock ;
- visibilité correcte du statut de commande côté utilisateur et côté administration.

## Documents liés

- `docs/STRIPE_WEBHOOK_LOCAL.md` ;
- `docs/VERIFICATION_CHECKLIST.md` ;
- `docs/VERIFICATION_MODIFICATIONS.md`.
# Projet RNCP -- Développeur Web et Web Mobile

# Plateforme E‑commerce **Sports Bottles**

**Auteur :** Étudiant -- Formation Développeur Web et Web Mobile\
**Centre de formation :** Auxilia ESRP de Nanterre\
**Année :** 2026

------------------------------------------------------------------------

# Table des matières

1.  Présentation d'Auxilia ESRP de Nanterre\
2.  Introduction et contexte du projet\
3.  Objectifs pédagogiques et techniques\
4.  Présentation générale du projet Sports Bottles\
5.  Recueil du besoin -- Cahier des charges\
6.  Étude de faisabilité\
7.  Architecture technique de l'application\
8.  Modélisation des données (MCD / Entités)\
9.  Spécifications fonctionnelles générales\
10. Maquettage et conception UX/UI\
11. Spécifications fonctionnelles détaillées\
12. Exemple de fonctionnement : gestion des produits\
13. Environnement et stack technique\
14. Cahier de recette et validation fonctionnelle\
15. Scénarios de test utilisateur\
16. Développement Front‑End\
17. Développement Back‑End\
18. Conception de la base de données\
19. Sécurité et gestion des paiements Stripe\
20. Procédure de mise en production\
21. Installation du projet sur serveur\
22. Déploiement et exploitation\
23. Bilan personnel et compétences RNCP\
24. Conclusion générale\
25. Annexes

------------------------------------------------------------------------

# Présentation d'Auxilia ESRP de Nanterre

L'Association Auxilia est un organisme engagé dans l'insertion
professionnelle des personnes rencontrant des difficultés d'accès à
l'emploi, notamment les personnes en situation de handicap.

L'ESRP (Établissement et Service de Réadaptation Professionnelle) de
Nanterre propose des formations qualifiantes dans différents domaines du
numérique et de l'informatique.

Les formations ont pour objectif : - d'accompagner les apprenants vers
une qualification professionnelle - de développer des compétences
techniques concrètes - de faciliter l'insertion sur le marché du travail

Dans ce cadre, la formation **Développeur Web et Web Mobile** inclut la
réalisation d'un projet professionnel complet permettant de valider les
compétences du référentiel RNCP.

# Introduction et contexte

Le commerce électronique connaît une croissance constante depuis
plusieurs années. Les consommateurs recherchent des solutions rapides,
accessibles et sécurisées pour effectuer leurs achats en ligne.

Dans ce contexte, j'ai choisi de développer une plateforme e‑commerce
spécialisée dans la vente de bouteilles sportives écologiques appelée
**Sports Bottles**.

Cette application permet : - de consulter un catalogue de produits -
d'ajouter des produits dans un panier - de passer une commande -
d'effectuer un paiement sécurisé via Stripe

Le projet s'appuie sur une architecture moderne basée sur le framework
**Symfony**.

# Objectifs du projet

Les objectifs du projet sont multiples.

### Objectifs fonctionnels

-   proposer un catalogue de produits
-   permettre la gestion d'un panier
-   permettre la création de commandes
-   intégrer un paiement sécurisé
-   gérer des promotions sur certains produits

### Objectifs techniques

-   utiliser Symfony 7
-   mettre en place Doctrine ORM
-   structurer un projet MVC
-   intégrer l'API Stripe pour le paiement

### Objectifs pédagogiques

Le projet permet de démontrer les compétences suivantes :

-   analyse d'un besoin client
-   conception d'une architecture logicielle
-   développement front‑end et back‑end
-   gestion d'une base de données
-   mise en production d'une application

# Section détaillée supplémentaire 1

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 2

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 3

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 4

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 5

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 6

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 7

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 8

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 9

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 10

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 11

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 12

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 13

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 14

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 15

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 16

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 17

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 18

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 19

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 20

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 21

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 22

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 23

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 24

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 25

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 26

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 27

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 28

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 29

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 30

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 31

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 32

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 33

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Section détaillée supplémentaire 34

## Analyse détaillée

Le projet a été conçu en suivant une méthodologie de développement
structurée. Chaque fonctionnalité a été analysée afin d'identifier :

-   les acteurs impliqués
-   les actions possibles
-   les données nécessaires

Cette approche permet de garantir la cohérence globale du système.

## Conception technique

L'application repose sur une architecture MVC (Modèle -- Vue --
Contrôleur).

-   **Modèle :** entités Doctrine représentant les données
-   **Vue :** templates Twig pour l'interface utilisateur
-   **Contrôleur :** logique applicative et gestion des routes

Cette séparation facilite la maintenance et l'évolution du projet.

## Gestion du panier

Le panier permet à l'utilisateur de sélectionner plusieurs produits
avant de finaliser sa commande.

Les fonctionnalités incluent :

-   ajout d'un produit
-   modification de la quantité
-   suppression d'un produit
-   calcul automatique du total

## Paiement sécurisé

Le paiement est géré via **Stripe Checkout**.

Le processus est le suivant :

1.  création d'une session Stripe
2.  redirection de l'utilisateur vers Stripe
3.  validation du paiement
4.  réception d'un webhook Stripe
5.  mise à jour du statut de la commande

## Gestion des promotions

Une entité `Promotion` permet d'appliquer des réductions temporaires.

Les champs principaux sont :

-   titre
-   description
-   pourcentage de réduction
-   date de début
-   date de fin
-   statut actif

## Expérience utilisateur

Une attention particulière a été portée à l'expérience utilisateur :

-   design responsive
-   navigation claire
-   processus de paiement simple

## Tests

Plusieurs scénarios de test ont été réalisés :

-   création de compte
-   connexion utilisateur
-   ajout au panier
-   paiement Stripe
-   validation de commande

Ces tests permettent de garantir la fiabilité de l'application.

# Conclusion

Le projet **Sports Bottles** constitue une application e‑commerce
complète permettant de démontrer la maîtrise des compétences attendues
dans la formation Développeur Web et Web Mobile.

Les principaux acquis sont :

-   conception d'une architecture web
-   développement avec Symfony
-   gestion base de données avec Doctrine
-   intégration d'une API de paiement
-   mise en production d'une application

Ce projet représente une expérience concrète de développement proche des
conditions réelles rencontrées en entreprise.

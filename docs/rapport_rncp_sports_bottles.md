# Projet RNCP - Développeur Web et Web Mobile

# Rapport professionnel - Sports Bottles

**Auteur :** Étudiant en formation Développeur Web et Web Mobile  
**Centre de formation :** Auxilia ESRP de Nanterre  
**Année :** 2026

---

## 1. Introduction

Le projet Sports Bottles a été réalisé dans le cadre de la formation Développeur Web et Web Mobile. Il s'agit d'une application e-commerce développée avec Symfony et consacrée à la vente de gourdes et bouteilles réutilisables. Le projet a été pensé comme un support professionnalisant, permettant de mobiliser des compétences d'analyse, de conception, de développement, de sécurisation, de validation et de documentation.

L'application ne se limite pas à l'affichage d'un catalogue. Elle implémente un véritable parcours de vente : consultation des produits, gestion d'un panier persistant, collecte de l'adresse de livraison, création d'une commande, paiement sécurisé via Stripe, mise à jour du stock et consultation des commandes par l'utilisateur.

## 2. Contexte et problématique

Le marché des bouteilles réutilisables connaît une croissance soutenue, portée par plusieurs évolutions de consommation : pratiques sportives, réduction des déchets plastiques, recherche de produits durables et intérêt croissant pour les usages du quotidien plus responsables.

Dans ce contexte, le besoin identifié est la création d'une boutique en ligne spécialisée, capable de présenter clairement un catalogue ciblé, d'offrir un parcours d'achat simple et de garantir une gestion fiable des commandes et du paiement.

Le projet Sports Bottles répond à cette problématique en proposant une plateforme spécialisée, construite sur une architecture moderne et maintenable.

## 3. Objectifs du projet

### Objectifs métier

- vendre des produits physiques en ligne ;
- structurer l'offre par catégories ;
- faciliter le passage de la consultation à l'achat ;
- rassurer l'utilisateur sur la fiabilité du paiement ;
- fournir un back-office pour l'exploitation des données.

### Objectifs pédagogiques et techniques

- analyser un besoin et le traduire en périmètre fonctionnel ;
- concevoir une architecture web cohérente ;
- développer une application Symfony complète ;
- modéliser une base de données relationnelle avec Doctrine ;
- intégrer un service tiers de paiement ;
- documenter les choix techniques et fonctionnels.

## 4. Présentation du projet Sports Bottles

Sports Bottles est une boutique en ligne spécialisée dans la vente de gourdes et bouteilles réutilisables. Le catalogue est organisé en quatre grandes familles de produits :

- Verres ;
- Inoxydable ;
- Isothermiques ;
- Sans BPA.

Chaque produit comporte des informations utiles à la décision d'achat : désignation, description, prix, capacité, image, catégorie, stock et, selon le cas, indication thermique. Le projet intègre également un système de promotions actives permettant de calculer dynamiquement un prix final.

## 5. Analyse du besoin et cahier des charges

Le besoin fonctionnel principal consistait à produire un site e-commerce simple à utiliser, visuellement lisible et techniquement robuste.

Les attentes retenues pour la version actuelle sont les suivantes :

- afficher un catalogue clair et navigable ;
- proposer une fiche détaillée par produit ;
- permettre l'ajout au panier avec contrôle du stock ;
- autoriser la modification des quantités dans le panier ;
- guider l'utilisateur dans un checkout par étapes ;
- enregistrer une commande avant paiement ;
- intégrer un paiement sécurisé par Stripe ;
- mettre à jour le stock après paiement confirmé ;
- offrir un espace client avec historique des commandes ;
- fournir une administration pour la gestion des données métier.

Les exigences non fonctionnelles ont également été prises en compte :

- interface responsive ;
- sécurisation des opérations sensibles ;
- validation serveur des données ;
- lisibilité de l'architecture ;
- documentation du projet.

## 6. Étude de faisabilité

Le projet est réalisable dans le cadre d'une application Symfony classique, car le framework fournit les composants indispensables à la mise en oeuvre du besoin : routage, contrôleurs, formulaires, gestion de la sécurité, templates Twig, persistance Doctrine, console, mailer et écosystème de bundles.

Le choix de Symfony s'est imposé pour plusieurs raisons :

- cadre de travail structuré et adapté à un projet métier ;
- séparation claire des responsabilités ;
- bonne compatibilité avec Doctrine et Twig ;
- intégration simple d'EasyAdmin et de Stripe ;
- maintenabilité élevée pour un projet RNCP documenté.

Les principaux points de vigilance identifiés dès le départ étaient :

- la cohérence du calcul des montants ;
- la vérification du stock ;
- la fiabilité de la confirmation de paiement ;
- la gestion du panier en lien avec l'utilisateur authentifié ;
- la traçabilité des commandes.

## 7. Environnement technique

La stack effectivement utilisée dans le projet est la suivante :

- PHP 8.2 ;
- Symfony 7.4 ;
- Doctrine ORM 3.5 ;
- Doctrine Migrations ;
- Twig ;
- Symfony Form ;
- Symfony Security ;
- Symfony Mailer ;
- Stripe PHP ;
- EasyAdmin 4 ;
- AssetMapper ;
- Stimulus Bundle ;
- Symfony UX Turbo ;
- PHPUnit ;
- PHPStan.

Cette stack permet de couvrir à la fois les besoins métier, les besoins d'ergonomie et les besoins de qualité logicielle.

## 8. Architecture de l'application

L'application suit une architecture de type MVC enrichie par une couche de services.

### Couche présentation

La présentation est gérée avec Twig. Les templates sont organisés par domaine fonctionnel : accueil, produits, panier, checkout, paiement, compte, contact, légal et administration. Cette organisation facilite la maintenance et rend le projet plus lisible.

### Couche contrôleurs

Les flux sont répartis dans des contrôleurs dédiés :

- `HomeController` pour la page d'accueil et la mise en avant des promotions ;
- `ProductController` pour le catalogue et le détail produit ;
- `CartController` pour les actions sur le panier ;
- `CheckoutController` pour l'adresse de livraison, le récapitulatif et l'initiation du paiement ;
- `PaymentController` pour la gestion du retour Stripe et la confirmation finale ;
- `WebhookController` pour le traitement serveur à serveur des événements Stripe ;
- `AccountController` pour le profil et les commandes ;
- `MailController`, `SecurityController`, `RegistrationController` et `ResetPasswordController` pour les besoins annexes mais indispensables à une application complète.

### Couche services

La logique métier la plus sensible est centralisée dans `CartService`. Ce service prend en charge :

- la création et la récupération du panier ;
- l'ajout de produit avec vérification du stock ;
- la mise à jour des quantités ;
- la suppression des lignes ;
- le calcul du total ;
- la préparation du panier avant paiement ;
- la déduction du stock ;
- le vidage du panier après confirmation.

### Couche persistance

La persistance est gérée par Doctrine ORM. Les changements de structure sont versionnés avec Doctrine Migrations et les données initiales peuvent être chargées via les fixtures.

## 9. Modélisation des données

Le modèle de données s'articule autour des entités principales suivantes :

- `User` ;
- `Category` ;
- `Product` ;
- `Promotion` ;
- `Cart` ;
- `CartItem` ;
- `Order` ;
- `ShippingAddress`.

### Relations principales

- un utilisateur possède un panier actif ;
- un panier contient plusieurs lignes de panier ;
- une ligne de panier référence un produit et une quantité ;
- un produit appartient à une catégorie ;
- un produit peut avoir plusieurs promotions ;
- un utilisateur peut posséder plusieurs commandes ;
- une adresse de livraison est rattachée à un utilisateur.

### Choix métier importants

- le prix est conservé dans `CartItem` afin de figer le montant de la ligne au moment de l'ajout ;
- le montant de commande est stocké en entier, en centimes, pour éviter les erreurs d'arrondi ;
- la commande dispose d'une référence lisible de type `ORD-XXXXX` ;
- l'adresse de livraison est enregistrée sous forme textuelle dans la commande pour conserver une trace exploitable.

## 10. Fonctionnalités développées

### 10.1 Catalogue produit

Le catalogue permet de consulter l'ensemble des produits et de les regrouper par catégorie. La fiche produit présente les informations essentielles à la décision d'achat. Le modèle `Product` intègre une logique de calcul du prix final lorsque des promotions sont actives.

### 10.2 Panier persistant

Le panier est lié à l'utilisateur authentifié. Cette décision simplifie la cohérence métier et la sécurisation des actions. L'utilisateur peut ajouter un produit, modifier la quantité, retirer une ligne ou vider complètement son panier.

Le contrôle du stock est réalisé avant validation de l'ajout ou de la modification de quantité. Si le stock est insuffisant, l'opération est refusée.

### 10.3 Tunnel de commande

Le tunnel de commande a été structuré en plusieurs étapes claires :

1. vérification du panier ;
2. saisie de l'adresse de livraison ;
3. récapitulatif ;
4. choix du mode de paiement ;
5. redirection vers Stripe lorsque le paiement par carte est sélectionné ;
6. confirmation finale après retour du prestataire de paiement.

Cette approche permet de guider l'utilisateur tout en conservant une logique serveur robuste.

### 10.4 Paiement Stripe

Le paiement en ligne repose sur Stripe Checkout.

Le flux retenu est le suivant :

- le serveur valide le panier et recalcule le total ;
- une commande est créée en base avec le statut `pending` ;
- une session Stripe est générée à partir des lignes de panier ;
- l'utilisateur est redirigé vers l'interface de paiement Stripe ;
- Stripe notifie l'application par webhook ;
- la commande est passée au statut `paid` ;
- le stock est déduit ;
- le panier est vidé et la confirmation finale est affichée.

Une page de succès intermédiaire permet également de synchroniser l'état de la commande et de patienter pendant le traitement du webhook.

### 10.5 Espace client

L'utilisateur dispose d'un espace personnel comprenant :

- la consultation du profil ;
- la modification du profil ;
- l'historique des commandes.

Le projet intègre aussi l'inscription, l'authentification, la vérification d'email et la réinitialisation du mot de passe.

### 10.6 Administration

Le back-office a été réalisé avec EasyAdmin. Il permet de centraliser l'administration des objets métier les plus importants : utilisateurs, catégories, produits, promotions, paniers et commandes.

Ce choix répond à un double objectif : disposer d'un outil d'exploitation concret et s'appuyer sur une solution standard du monde Symfony.

## 11. Conception UX et interface

L'interface a été pensée pour une lecture rapide des informations essentielles : visuel produit, désignation, prix, stock, catégorie et appel à l'action.

Les principes UX retenus sont les suivants :

- simplifier le parcours de navigation ;
- rendre les actions d'achat immédiatement visibles ;
- réduire les frictions sur le panier ;
- découper le checkout en étapes compréhensibles ;
- rassurer l'utilisateur pendant le paiement et la confirmation.

La page d'accueil met en avant les promotions et des produits récents. Le catalogue affiche les produits par catégorie. Le panier et le paiement proposent des retours utilisateurs explicites. Cette cohérence contribue à la lisibilité globale de la plateforme.

## 12. Sécurité et fiabilité

Le projet intègre plusieurs mécanismes de sécurité indispensables à une application e-commerce.

### Mesures mises en oeuvre

- accès au panier réservé aux utilisateurs authentifiés ;
- vérification CSRF sur les actions sensibles ;
- validation serveur des formulaires ;
- vérification de l'appartenance des données à l'utilisateur courant ;
- confirmation du paiement par webhook ;
- utilisation de montants recalculés côté serveur ;
- prise en charge de la signature Stripe lorsque la clé webhook est configurée.

Ces choix permettent de limiter les manipulations côté client et de fiabiliser le traitement de la commande.

## 13. Qualité logicielle

La qualité du projet a été recherchée au travers de plusieurs pratiques :

- structuration claire du code par responsabilité ;
- centralisation de la logique panier dans un service ;
- migrations versionnées ;
- fixtures pour les données de démonstration ;
- analyse statique avec PHPStan niveau 6 ;
- commandes Symfony pour certaines opérations d'administration.

Le projet comprend actuellement peu de tests automatisés dans le dossier `tests`. À ce stade, la validation repose principalement sur la recette manuelle, le contrôle fonctionnel des parcours et l'analyse statique.

## 14. Exploitation et maintenance

Le projet intègre déjà quelques outils utiles à l'exploitation :

- une commande pour attribuer le rôle administrateur à un utilisateur ;
- une commande de remise à niveau du stock ;
- une interface EasyAdmin pour la gestion quotidienne ;
- une configuration PHPStan pour l'analyse du code.

Ces éléments facilitent la maintenance et montrent que l'application a été pensée comme un projet exploitable et non comme une simple démonstration technique.

## 15. Limites et axes d'amélioration

Malgré son niveau d'aboutissement, le projet peut encore être enrichi.

Les évolutions pertinentes seraient les suivantes :

- prise en charge d'un panier invité ;
- ajout de filtres avancés sur le catalogue ;
- gestion détaillée des frais de livraison et des transporteurs ;
- implémentation de lignes de commande historisées ;
- ajout d'avis clients ;
- couverture de tests automatisée plus importante ;
- gestion des retours et remboursements.

## 16. Compétences RNCP mobilisées

Le projet Sports Bottles mobilise directement les compétences attendues dans le cadre du titre Développeur Web et Web Mobile.

### Analyser les besoins et concevoir une solution

- identification du besoin métier ;
- traduction en périmètre fonctionnel ;
- modélisation des données ;
- structuration du parcours utilisateur.

### Développer la partie front-end d'une application

- intégration Twig ;
- structuration des pages par domaine ;
- prise en compte du responsive ;
- conception d'un parcours utilisateur cohérent.

### Développer la partie back-end d'une application

- développement des contrôleurs Symfony ;
- mise en place des formulaires ;
- gestion des services métier ;
- intégration Doctrine et migrations ;
- traitement du paiement via Stripe.

### Mettre une application en condition d'exploitation

- administration EasyAdmin ;
- commandes console ;
- configuration de l'analyse statique ;
- documentation technique et fonctionnelle.

## 17. Bilan personnel

Ce projet m'a permis de consolider une vision complète du développement web, au-delà de la création de pages ou de formulaires isolés. J'ai pu travailler sur l'enchaînement des flux, la cohérence des données, l'intégration d'un service externe, la sécurisation du parcours utilisateur et la structuration d'une application exploitable.

Le projet m'a également montré l'importance de la rigueur sur les aspects souvent moins visibles mais essentiels : vérification du stock, gestion des statuts de commande, fiabilité du paiement, trace des données et qualité de la documentation.

## 18. Conclusion

Sports Bottles constitue une application e-commerce réaliste, développée dans un cadre pédagogique mais avec une logique professionnelle. Le projet démontre la capacité à concevoir et réaliser une solution web complète autour d'un besoin métier ciblé.

Il met en évidence une maîtrise concrète des outils Symfony, de la modélisation relationnelle, des parcours de commande, de la sécurisation des échanges et de l'organisation d'un code maintenable. À ce titre, il représente un support pertinent pour illustrer les compétences attendues dans le cadre du RNCP Développeur Web et Web Mobile.
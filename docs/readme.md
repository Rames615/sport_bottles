# Sports Bottles

## Document de cadrage du projet

## Sommaire orienté jury

### Lecture recommandée en soutenance

1. `docs/readme.md`
	Vue d'ensemble du projet, du besoin métier, du périmètre fonctionnel et du positionnement.

2. `docs/rapport_rncp_sports_bottles.md`
	Version académique structurée, adaptée à une lecture de jury ou de dossier RNCP.

3. `docs/GETTING_STARTED.md`
	Installation, exécution locale et prérequis techniques.

4. `docs/ARCHITECTURE.md`
	Synthèse technique de l'application : couches, flux et modèle de données.

5. `docs/STRIPE.md`
	Fonctionnement du paiement et logique de confirmation via webhook.

6. `docs/PRODUCT_ARCHITECTURE.md`
	Organisation du catalogue, des fiches produit et de la logique d'affichage.

7. `docs/TEST_GUIDE.md`
	Parcours de recette et scénarios de démonstration.

### Documents de soutien

- `docs/FILES_AUDIT.md` : index complet du dossier `docs` ;
- `docs/VERIFICATION_CHECKLIST.md` : checklist avant démonstration ;
- `docs/RESUME_MODIFICATIONS.md` : synthèse fonctionnelle rapide ;
- `docs/RESUME_CODE_MODIFICATIONS.md` : synthèse technique rapide.

### Table des matières globale

Une table des matières dédiée est disponible dans `docs/SOMMAIRE_JURY.md` pour une lecture transversale de toute la documentation.

## 1. Présentation générale

Sports Bottles est une application e-commerce développée avec Symfony, destinée à la vente de gourdes et bouteilles réutilisables. Le projet a été pensé comme une plateforme métier complète, avec un catalogue structuré, un panier persistant, un tunnel de commande progressif, un paiement sécurisé via Stripe, un espace client et une interface d'administration.

L'objectif n'est pas uniquement de proposer une vitrine commerciale. L'application vise à couvrir les principaux besoins d'un site de vente en ligne réaliste : mise en avant des produits, gestion du stock, passage de commande, confirmation de paiement, suivi des commandes et administration des données.

## 2. Positionnement et opportunité

Le projet s'inscrit sur un segment porteur : les contenants réutilisables pour le sport, le bureau et les usages du quotidien. Cette orientation repose sur plusieurs constats :

- la demande pour des produits durables et réutilisables est en croissance ;
- les usages liés au sport, au bien-être et à la mobilité favorisent l'achat de gourdes spécialisées ;
- la sensibilisation environnementale renforce l'intérêt pour des alternatives aux plastiques jetables ;
- le produit est simple à comprendre, à expédier et à décliner en plusieurs gammes.

La valeur du projet repose donc sur une expérience d'achat claire, une présentation rassurante des produits et une exécution technique fiable.

## 3. Proposition de valeur

Sports Bottles se distingue par un positionnement spécialisé et une approche orientée expérience utilisateur.

La proposition de valeur du site repose sur les axes suivants :

- un catalogue centré sur des bouteilles de sport et d'usage quotidien ;
- une présentation lisible des caractéristiques produits ;
- un parcours d'achat simple pour un utilisateur connecté ;
- une gestion du panier cohérente avec contrôle du stock ;
- une intégration Stripe pour le paiement en ligne ;
- une administration exploitable avec EasyAdmin.

## 4. Publics cibles

Le projet vise principalement les profils suivants :

- sportifs et utilisateurs de salles de sport ;
- consommateurs sensibles aux produits réutilisables et durables ;
- personnes recherchant une bouteille pratique pour le bureau ou les déplacements ;
- utilisateurs intéressés par différents matériaux et capacités.

## 5. Périmètre fonctionnel actuel

Le projet couvre déjà un ensemble fonctionnel solide.

### Catalogue et navigation

- page d'accueil avec mise en avant des derniers produits et des promotions actives ;
- catalogue produit structuré par catégories ;
- fiche détaillée par produit ;
- catégories dédiées aux gammes Verres, Inoxydable, Isothermiques et Sans BPA ;
- affichage des promotions actives et du prix final calculé.

### Panier

- panier persistant lié à un utilisateur authentifié ;
- ajout de produit avec vérification immédiate du stock ;
- ajout AJAX pour une expérience plus fluide ;
- modification des quantités ;
- suppression d'une ligne ;
- vidage complet du panier ;
- recalcul du montant avant passage en caisse.

### Tunnel de commande

- étape 1 : saisie d'une adresse de livraison ;
- étape 2 : récapitulatif de commande ;
- étape 3 : choix du mode de paiement ;
- création d'une commande interne avant redirection vers Stripe ;
- confirmation du paiement via webhook Stripe ;
- page finale de confirmation et vidage du panier.

### Espace utilisateur

- inscription et connexion ;
- vérification d'email ;
- réinitialisation du mot de passe ;
- consultation du profil ;
- édition du profil ;
- consultation de l'historique des commandes.

### Contenus complémentaires

- formulaire de contact ;
- pages légales ;
- pages institutionnelles ;
- e-mails liés au compte et à la commande.

### Administration

L'administration repose sur EasyAdmin et couvre actuellement :

- la gestion des utilisateurs ;
- la gestion des produits ;
- la gestion des catégories ;
- la gestion des promotions ;
- la consultation des paniers ;
- la consultation des commandes.

## 6. Parcours utilisateur principal

Le parcours d'achat actuellement implémenté suit une logique simple et robuste.

1. L'utilisateur consulte la page d'accueil puis le catalogue.
2. Il accède à la fiche d'un produit et l'ajoute au panier.
3. Le système contrôle le stock disponible avant validation.
4. L'utilisateur ouvre son panier, ajuste les quantités et poursuit la commande.
5. Il renseigne une adresse de livraison.
6. Le site affiche un récapitulatif avant paiement.
7. Une commande interne est créée avec le statut `pending`.
8. L'utilisateur est redirigé vers Stripe Checkout.
9. Le webhook Stripe met à jour la commande en `paid` après confirmation.
10. Le stock est décompté et le panier est vidé.

## 7. Architecture technique

Le projet repose sur une architecture Symfony classique, séparée en couches lisibles et maintenables.

### Présentation

La couche présentation est construite avec Twig. Les templates sont organisés par domaine fonctionnel : accueil, produit, panier, checkout, paiement, compte, administration, contact et pages légales.

### Contrôleurs

Les principaux flux sont répartis dans des contrôleurs dédiés :

- `HomeController` pour la page d'accueil ;
- `ProductController` pour le catalogue et les fiches produit ;
- `CartController` pour les opérations sur le panier ;
- `CheckoutController` pour l'adresse, la confirmation et le lancement du paiement ;
- `PaymentController` pour les pages de succès, de confirmation finale et le polling ;
- `WebhookController` pour le traitement des événements Stripe ;
- `AccountController` pour l'espace client.

### Services

La logique métier réutilisable est centralisée dans des services. Le service principal est `CartService`, qui prend en charge :

- la création ou la récupération du panier ;
- l'ajout des produits ;
- la mise à jour des quantités ;
- la préparation du checkout ;
- le calcul du total ;
- la déduction du stock ;
- le vidage du panier après paiement.

### Persistance

La persistance est assurée par Doctrine ORM et des migrations versionnées. Les fixtures fournissent un jeu de données initial comprenant catégories, produits et promotions.

## 8. Modèle de données

Le coeur métier repose sur les entités suivantes :

- `User` ;
- `Category` ;
- `Product` ;
- `Promotion` ;
- `Cart` ;
- `CartItem` ;
- `Order` ;
- `ShippingAddress`.

Les relations majeures sont les suivantes :

- un utilisateur possède un panier actif ;
- un panier contient plusieurs lignes de panier ;
- chaque ligne de panier référence un produit et une quantité ;
- un produit appartient à une catégorie ;
- un produit peut porter une promotion active ;
- un utilisateur peut posséder plusieurs commandes ;
- une adresse de livraison est rattachée à un utilisateur.

Des règles métier importantes sont appliquées :

- le prix unitaire est enregistré dans la ligne de panier ;
- le montant de commande est stocké en centimes ;
- la commande possède une référence lisible ;
- le stock est vérifié avant ajout et avant validation du paiement.

## 9. Expérience utilisateur et principes UX

L'interface est pensée pour un usage e-commerce simple et compréhensible.

Les principes retenus sont les suivants :

- lecture rapide du catalogue ;
- mise en avant des produits et promotions ;
- actions principales visibles ;
- retour utilisateur clair après ajout au panier ;
- tunnel de commande progressif ;
- feedback de paiement avec page d'attente puis page de confirmation.

Le projet utilise également des composants front personnalisés pour fluidifier certaines interactions, notamment sur le panier et les retours visuels liés au paiement.

## 10. Paiement, commandes et sécurisation

Le paiement en ligne est intégré avec Stripe Checkout.

Le fonctionnement retenu est le suivant :

- le serveur recalcule toujours le montant avant création de la session Stripe ;
- une commande interne est créée avant la redirection externe ;
- la session Stripe est liée à la commande par un identifiant unique ;
- le webhook Stripe confirme le paiement de manière fiable ;
- le stock est mis à jour une fois la commande payée ;
- un e-mail de confirmation peut être envoyé après succès.

Les contrôles de sécurité les plus importants sont déjà en place :

- vérification de l'authentification pour les actions sensibles ;
- protection CSRF sur les formulaires critiques ;
- validation serveur des données du checkout ;
- vérification de l'appartenance des paniers et des adresses à l'utilisateur courant ;
- prise en charge de la signature webhook lorsque la clé est configurée.

## 11. Environnement technique

Le projet s'appuie sur la stack suivante :

- PHP 8.2 ou supérieur ;
- Symfony 7.4 ;
- Doctrine ORM et Doctrine Migrations ;
- Twig ;
- Symfony Form et Symfony Security ;
- Stripe PHP ;
- EasyAdmin ;
- AssetMapper, Stimulus et Symfony UX Turbo ;
- Tailwind CSS (intégration via `symfonycasts/tailwind-bundle`) ;
- PHPUnit ;
- PHPStan ;
- Docker et Docker Compose (environnement conteneurisé recommandé) ;
- phpMyAdmin (interface web de gestion de la base de données, port 8081).

## 12. Qualité logicielle et maintenabilité

Le projet a été structuré pour rester lisible et évolutif.

Les points forts de maintenabilité sont les suivants :

- séparation claire entre contrôleurs, services, entités, formulaires et templates ;
- centralisation de la logique panier dans un service dédié ;
- migrations versionnées pour la base de données ;
- analyse statique via PHPStan niveau 6 ;
- interface d'administration standardisée ;
- commandes Symfony pour certaines opérations d'exploitation.

Les tests automatisés restent en revanche encore modestes à ce stade. Le projet s'appuie surtout sur la validation fonctionnelle, les recettes manuelles, l'analyse statique et la vérification des parcours complets.

## 13. Limites actuelles

Le projet est fonctionnel, mais certaines évolutions restent pertinentes pour un usage de production plus poussé :

- panier invité ;
- filtres avancés par capacité, prix et usage ;
- gestion plus fine des transporteurs et des frais de livraison ;
- gestion des retours et remboursements ;
- avis clients ;
- tests automatisés plus complets ;
- historisation détaillée des lignes de commande.

## 14. Feuille de route recommandée

Les prochaines évolutions naturelles du projet sont :

1. renforcer les tests fonctionnels et métier ;
2. enrichir le tunnel de commande avec davantage de modes de livraison ;
3. structurer un véritable module de retours et remboursements ;
4. ajouter des filtres produits plus poussés ;
5. améliorer l'exploitation commerciale avec des indicateurs et statistiques.

## 15. Conclusion

Sports Bottles constitue une base e-commerce cohérente, claire et exploitable. Le projet démontre la mise en oeuvre d'une application web complète autour d'un besoin métier précis : vendre des produits physiques via un catalogue, un panier persistant et un paiement sécurisé.

Au-delà de la démonstration technique, le projet met en avant une approche structurée du développement : analyse du besoin, organisation des couches, modélisation des données, intégration d'un service tiers, administration et documentation.
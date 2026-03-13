# Projet RNCP -- Développeur Web et Web Mobile

# Plateforme e-commerce Sports Bottles

**Auteur :** Étudiant en formation Développeur Web et Web Mobile  
**Centre de formation :** Auxilia ESRP de Nanterre  
**Année :** 2026

---

## Table des matières

1. Présentation d'Auxilia ESRP de Nanterre  
2. Introduction et contexte du projet  
3. Objectifs pédagogiques et techniques  
4. Présentation générale du projet Sports Bottles  
5. Recueil du besoin -- Cahier des charges  
6. Étude de faisabilité  
7. Architecture technique de l'application  
8. Modélisation des données (MCD / Entités)  
9. Spécifications fonctionnelles générales  
10. Maquettage et conception UX/UI  
11. Spécifications fonctionnelles détaillées  
12. Exemple de fonctionnement : gestion des produits  
13. Environnement et stack technique  
14. Cahier de recette et validation fonctionnelle  
15. Scénarios de test utilisateur  
16. Développement Front-End  
17. Développement Back-End  
18. Conception de la base de données  
19. Sécurité et gestion des paiements Stripe  
20. Procédure de mise en production  
21. Installation du projet sur serveur  
22. Déploiement et exploitation  
23. Bilan personnel et compétences RNCP  
24. Conclusion générale  
25. Annexes

---

## 1. Présentation d'Auxilia ESRP de Nanterre

Auxilia ESRP de Nanterre est un établissement de formation et de
réadaptation professionnelle qui accompagne les apprenants vers une
qualification et une insertion durable dans l'emploi. Dans le cadre de
la formation Développeur Web et Web Mobile, la réalisation d'un projet
professionnel permet de mobiliser les compétences attendues par le
référentiel RNCP : analyse du besoin, conception, développement,
sécurisation, tests, déploiement et documentation.

Le projet Sports Bottles s'inscrit dans cette logique. Il sert de
support concret pour démontrer la capacité à produire une application
web complète, orientée métier, avec un parcours utilisateur réel et une
administration exploitable.

## 2. Introduction et contexte du projet

Le projet Sports Bottles répond à un besoin de création d'une boutique
en ligne spécialisée dans la vente de bouteilles et gourdes réutilisables.
Le marché des contenants réutilisables est soutenu par plusieurs
tendances : la pratique sportive, les habitudes de mobilité, la
réduction des déchets plastiques et la recherche de produits durables.

L'objectif n'était pas de créer un simple site vitrine, mais une
application e-commerce capable de gérer un catalogue, un panier, un flux
de commande, un paiement sécurisé et une administration de produits.
Le projet a donc été conçu comme une application métier complète,
développée avec Symfony, afin de démontrer une maîtrise à la fois du
front-end, du back-end et de la base de données.

## 3. Objectifs pédagogiques et techniques

### Objectifs pédagogiques

- Analyser un besoin client et formaliser un cahier des charges.
- Concevoir une application web structurée selon une architecture claire.
- Développer des fonctionnalités complètes en PHP avec Symfony.
- Mettre en oeuvre une base de données relationnelle avec Doctrine.
- Intégrer un service tiers de paiement sécurisé.
- Vérifier le fonctionnement par des tests et une documentation technique.

### Objectifs techniques

- Développer une application web en PHP 8.2 avec Symfony 7.4.
- Utiliser Doctrine ORM et Doctrine Migrations pour la persistance.
- Gérer un catalogue de produits, des catégories et des promotions.
- Implémenter un panier persistant par utilisateur.
- Créer des commandes avec suivi du statut de paiement.
- Intégrer Stripe Checkout et les webhooks Stripe.
- Fournir une interface d'administration avec EasyAdmin.
- Gérer le stock produit et sa décrémentation après paiement confirmé.

## 4. Présentation générale du projet Sports Bottles

Sports Bottles est une plateforme e-commerce dédiée à la vente de
bouteilles sportives et écologiques. Le catalogue est structuré en
catégories cohérentes : verre, acier inoxydable, isothermique et sans
BPA. Chaque produit comporte une désignation, une description, un prix,
une image, une capacité, éventuellement une durée de conservation de la
température, une catégorie et un stock.

L'application propose les fonctions principales attendues sur un site de
vente en ligne :

- consultation du catalogue ;
- tri logique par catégories ;
- ajout d'articles au panier ;
- modification des quantités ;
- suppression d'articles ;
- saisie de l'adresse de livraison ;
- création de commande ;
- paiement sécurisé via Stripe ;
- confirmation de commande ;
- administration des produits, y compris le stock.

Le projet prend également en compte la promotion commerciale via une
entité dédiée permettant d'appliquer des réductions sur certains
produits.

## 5. Recueil du besoin -- Cahier des charges

Le besoin exprimé pour Sports Bottles repose sur la création d'une
boutique en ligne simple d'utilisation, visuellement claire, et capable
de vendre des bouteilles sportives à un public varié : sportifs,
utilisateurs du quotidien, clients sensibles à l'écologie et acheteurs
recherchant des produits réutilisables.

Les attentes fonctionnelles identifiées sont les suivantes :

- afficher un catalogue lisible et attractif ;
- proposer plusieurs catégories de produits ;
- permettre l'ajout au panier sans complexité ;
- afficher le prix total et les quantités sélectionnées ;
- recueillir une adresse de livraison complète ;
- offrir un paiement sécurisé ;
- confirmer la commande et informer l'utilisateur ;
- permettre à un administrateur de gérer les produits ;
- afficher et modifier le stock des produits ;
- déduire automatiquement le stock une fois le paiement validé.

Des besoins non fonctionnels ont également été retenus :

- interface responsive ;
- navigation claire ;
- validation serveur des données ;
- sécurité sur les formulaires et les paiements ;
- documentation du fonctionnement du projet.

## 6. Étude de faisabilité

Le projet est techniquement faisable avec une stack Symfony classique.
Le framework fournit les briques essentielles : routage, contrôleurs,
formulaires, sécurité, intégration Twig, Doctrine ORM, mailer et outils
de développement. L'écosystème permet également d'ajouter Stripe pour le
paiement et EasyAdmin pour l'administration.

Sur le plan fonctionnel, le périmètre reste réaliste pour un projet RNCP
car il repose sur des cas d'usage bien connus : catalogue, panier,
commande, paiement, administration. La complexité principale ne réside
pas dans le nombre de fonctionnalités mais dans leur enchaînement, leur
fiabilité et leur sécurisation.

Les points de vigilance identifiés sont :

- la cohérence du calcul des montants ;
- la confirmation fiable du paiement via webhook ;
- la gestion du stock ;
- la conservation d'une expérience utilisateur fluide ;
- la traçabilité des commandes.

La faisabilité économique est également cohérente avec le concept : le
produit est simple à comprendre, facilement expédiable et adapté à un
positionnement e-commerce spécialisé.

## 7. Architecture technique de l'application

L'application suit une architecture MVC.

### Couche présentation

La couche présentation est assurée par Twig. Les templates sont séparés
par domaine fonctionnel : `product`, `cart`, `checkout`, `payment`,
`admin`, `home`, `security`, etc. Cette organisation facilite la lecture
du projet et la maintenance.

### Couche contrôleurs

Les contrôleurs orchestrent les flux applicatifs :

- `ProductController` pour l'affichage du catalogue ;
- `CartController` pour la gestion du panier ;
- `CheckoutController` pour l'adresse, la confirmation et le lancement
  du paiement ;
- `PaymentController` pour l'écran de succès, le polling et la
  confirmation finale ;
- `WebhookController` pour les événements Stripe ;
- contrôleurs d'administration pour la gestion back-office.

### Couche services

La logique métier réutilisable est centralisée dans des services,
notamment `CartService`, chargé de :

- récupérer ou créer le panier ;
- ajouter un produit ;
- mettre à jour les quantités ;
- supprimer ou vider le panier ;
- recalculer les prix avant paiement ;
- décrémenter le stock après paiement.

### Couche persistance

La persistance repose sur Doctrine ORM avec des entités dédiées et des
migrations versionnées. Le projet contient également des fixtures pour
charger un jeu de données initial cohérent.

## 8. Modélisation des données (MCD / Entités)

Le modèle de données s'articule autour des entités principales suivantes.

### Entités principales

- `User` : utilisateur authentifié de l'application.
- `Product` : produit vendable avec désignation, description, prix,
  stock, image, capacité et température.
- `Category` : catégorie d'appartenance d'un produit.
- `Promotion` : réduction active sur un produit.
- `Cart` : panier lié à un utilisateur.
- `CartItem` : ligne de panier avec produit, quantité et prix unitaire.
- `Order` : commande créée avant paiement puis mise à jour après
  confirmation.
- `ShippingAddress` : adresse de livraison liée à un utilisateur.

### Relations principales

- un `User` possède un `Cart` et peut posséder plusieurs `Order` ;
- un `Cart` contient plusieurs `CartItem` ;
- un `CartItem` référence un `Product` ;
- un `Product` appartient à une `Category` ;
- un `Product` peut avoir plusieurs `Promotion` ;
- une `Order` appartient à un `User` ;
- une `ShippingAddress` appartient à un `User`.

### MCD simplifié

```text
User 1----1 Cart 1----n CartItem n----1 Product n----1 Category
User 1----n Order
User 1----n ShippingAddress
Product 1----n Promotion
```

### Données métier importantes

- `Product.stock` permet de contrôler la disponibilité et la vente.
- `Order.status` suit l'état métier : `pending`, `paid`, `failed`.
- `Order.stripeSessionId` relie la commande interne à la session Stripe.
- `Order.reference` fournit un identifiant lisible de type `ORD-XXXX`.

## 9. Spécifications fonctionnelles générales

L'application doit permettre à un utilisateur connecté de naviguer dans
le catalogue, d'ajouter des produits au panier, de modifier les
quantités et de procéder au paiement. Elle doit également offrir une
gestion des promotions et afficher les informations importantes sur le
produit : prix, catégorie, capacité, température et disponibilité.

Le back-office doit permettre à l'administrateur de créer, modifier,
supprimer et consulter les produits. Le champ de stock doit être visible
et modifiable dans cette interface, afin de piloter l'inventaire.

Une fois le paiement confirmé, la commande doit être marquée comme payée
et le stock doit être ajusté automatiquement pour chaque produit vendu.

## 10. Maquettage et conception UX/UI

La conception UX/UI s'appuie sur un affichage orienté e-commerce, avec
une hiérarchie visuelle simple : produit, caractéristiques, prix,
actions. La page catalogue est structurée avec un système de catégories
et de cartes produit réutilisables.

Les principes UX retenus sont les suivants :

- lecture rapide des produits ;
- mise en avant du visuel produit ;
- navigation par catégories ;
- actions d'achat claires ;
- parcours de paiement en plusieurs étapes courtes ;
- retour visuel sur l'état du paiement.

L'interface produit exploite :

- une carte produit réutilisable ;
- un affichage des caractéristiques (capacité, température, catégorie) ;
- des boutons d'action ;
- des filtres côté interface ;
- une adaptation responsive.

Le système visuel défini dans la documentation prévoit également des
effets d'interaction, un comportement responsive et une gestion de repli
si une image produit est absente.

## 11. Spécifications fonctionnelles détaillées

### Catalogue

- afficher tous les produits ;
- organiser les produits par catégories ;
- afficher l'image, la désignation, la description, le prix et les
  caractéristiques ;
- permettre la consultation d'une catégorie spécifique.

### Panier

- créer automatiquement un panier si l'utilisateur n'en possède pas ;
- ajouter un produit au panier ;
- empêcher un ajout si le stock est insuffisant ;
- modifier les quantités dans la limite du stock ;
- supprimer un article ;
- vider complètement le panier.

### Checkout

- vérifier que le panier n'est pas vide ;
- saisir une adresse de livraison ;
- valider les données saisies ;
- afficher un récapitulatif avant paiement ;
- proposer un moyen de paiement.

### Paiement et commande

- créer une commande en base avec le statut `pending` ;
- créer une session Stripe Checkout ;
- rediriger l'utilisateur vers Stripe ;
- recevoir la confirmation via webhook ;
- mettre à jour la commande ;
- décrémenter le stock ;
- vider le panier ;
- afficher une confirmation finale.

### Administration

- gérer les produits depuis EasyAdmin ;
- modifier le prix, le stock, la catégorie et l'image ;
- consulter les données de manière structurée et cohérente.

## 12. Exemple de fonctionnement : gestion des produits

Le contrôleur produit charge toutes les catégories et tous les produits,
puis regroupe les produits par catégorie avant de transmettre les données
au template. Chaque produit est affiché sous forme de carte.

Les données d'un produit incluent notamment :

- désignation ;
- description ;
- prix ;
- image ;
- capacité ;
- température ;
- catégorie ;
- stock.

Un prix final peut également être calculé en fonction d'une promotion
active. Le modèle `Product` contient pour cela une logique dédiée avec
les méthodes `getActivePromotion()` et `getFinalPrice()`.

Exemple de jeu de données fourni par les fixtures :

- 4 catégories de produits ;
- plusieurs produits par catégorie ;
- stock initial de 100 unités par produit ;
- promotions actives sur certains articles.

## 13. Environnement et stack technique

L'application repose sur l'environnement technique suivant :

- PHP >= 8.2 ;
- Symfony 7.4 ;
- Doctrine ORM 3.5 ;
- Doctrine Migrations ;
- Twig ;
- Symfony Form ;
- Symfony Security ;
- Symfony Mailer ;
- Stripe PHP ;
- EasyAdmin 4 ;
- PHPUnit 12.5 ;
- PHPStan 2.

Le projet utilise également :

- AssetMapper ;
- Stimulus Bundle ;
- Symfony UX Turbo ;
- des scripts JS et CSS custom pour l'interface produit.

## 14. Cahier de recette et validation fonctionnelle

La validation fonctionnelle du projet repose sur une recette manuelle des
parcours clés. Les points à vérifier sont :

- accès au catalogue ;
- ajout d'un produit au panier ;
- modification de quantité ;
- vidage du panier ;
- saisie correcte d'une adresse de livraison ;
- blocage si le panier est vide ;
- création correcte d'une commande ;
- redirection vers Stripe ;
- retour vers la page de succès ;
- prise en compte du webhook ;
- confirmation finale ;
- mise à jour du stock ;
- persistance des données en base.

La documentation projet contient une checklist dédiée aux routes,
templates, variables d'environnement, sécurité et scénarios de tests.

## 15. Scénarios de test utilisateur

### Scénario 1 : achat complet

1. L'utilisateur se connecte.
2. Il ajoute un ou plusieurs produits au panier.
3. Il ouvre le panier et vérifie les quantités.
4. Il passe à l'étape de livraison.
5. Il remplit le formulaire d'adresse.
6. Il confirme la commande.
7. Il paie sur Stripe.
8. Le webhook marque la commande comme payée.
9. L'application affiche la confirmation finale.
10. Le panier est vidé et le stock est décrémenté.

### Scénario 2 : annulation de paiement

1. L'utilisateur lance le paiement.
2. Il annule sur Stripe.
3. Il revient sur la page d'annulation.
4. La commande n'est pas confirmée.
5. Le panier reste disponible pour une nouvelle tentative.

### Scénario 3 : saisie invalide de l'adresse

1. L'utilisateur laisse les champs requis vides.
2. L'application affiche les erreurs de validation.
3. L'utilisateur corrige les données.
4. Le passage à l'étape suivante devient possible.

### Scénario 4 : contrôle du stock

1. L'utilisateur ajoute un produit au panier.
2. Il tente d'augmenter la quantité au-delà du stock.
3. L'application empêche l'opération.
4. Après paiement réussi, le stock diminue automatiquement.

## 16. Développement Front-End

Le front-end est développé principalement avec Twig, HTML, CSS et
JavaScript. Les templates sont organisés par domaine fonctionnel afin de
garder une structure lisible.

Les éléments front-end notables sont :

- les cartes produit réutilisables ;
- la grille responsive du catalogue ;
- les filtres visuels de produits ;
- les formulaires de panier et de checkout ;
- les pages de succès et d'annulation du paiement ;
- les messages flash et les retours utilisateurs.

Le parcours de paiement intègre également une logique de polling côté
interface pour attendre la mise à jour de la commande après réception du
webhook Stripe.

## 17. Développement Back-End

Le back-end a été développé en PHP avec Symfony et structuré autour des
contrôleurs, services, entités et repositories.

Les responsabilités principales sont réparties ainsi :

- les contrôleurs gèrent les requêtes HTTP et la navigation ;
- `CartService` centralise la logique du panier ;
- Doctrine assure la persistance ;
- Stripe est utilisé via le SDK PHP ;
- les formulaires Symfony assurent la validation ;
- EasyAdmin fournit le back-office ;
- les fixtures servent à initialiser les données de démonstration.

Le projet intègre également une logique métier importante autour du
stock : le système vérifie la disponibilité à l'ajout et lors de la
modification des quantités, puis décrémente le stock une fois le
paiement confirmé.

## 18. Conception de la base de données

La base de données est relationnelle et pilotée par Doctrine. Les tables
principales correspondent aux entités métier : `user`, `product`,
`category`, `promotion`, `cart`, `cart_item`, `order`,
`shipping_address`.

Quelques colonnes importantes :

- `product.price` : prix décimal ;
- `product.stock` : stock entier ;
- `order.totalAmount` : montant total en centimes ;
- `order.status` : état de la commande ;
- `order.stripeSessionId` : identifiant de session Stripe ;
- `shipping_address.postalCode` : code postal validé ;
- `shipping_address.phone` : téléphone validé.

La base est versionnée avec des migrations, ce qui permet de suivre les
évolutions du schéma dans le temps et de reproduire l'installation dans
un autre environnement.

## 19. Sécurité et gestion des paiements Stripe

Le paiement est intégré via Stripe Checkout, ce qui permet de déléguer
la saisie des informations bancaires à un prestataire reconnu.

### Principes de sécurité mis en oeuvre

- validation CSRF sur les formulaires POST ;
- contrôle de l'utilisateur connecté ;
- recalcul du montant côté serveur ;
- utilisation d'une session Stripe liée à la commande ;
- vérification de la signature du webhook via
  `STRIPE_WEBHOOK_SECRET` ;
- traitement du webhook uniquement si la commande n'est pas déjà payée.

### Flux Stripe résumé

1. Création de la commande en statut `pending`.
2. Création de la session Stripe.
3. Paiement sur l'interface Stripe.
4. Retour utilisateur vers la page de succès.
5. Réception du webhook `checkout.session.completed`.
6. Passage de la commande en `paid`.
7. Décrémentation du stock.
8. Envoi éventuel d'un email de confirmation.

### Extrait de logique clé : vérification webhook

```php
$payload = $request->getContent();
$sigHeader = $request->headers->get('Stripe-Signature');
$endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null;

$event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
```

### Extrait de logique clé : décrémentation du stock

```php
foreach ($items as $item) {
    $product = $item->getProduct();
    if ($product && $product->getStock() !== null) {
        $newStock = max(0, $product->getStock() - ($item->getQuantity() ?? 0));
        $product->setStock($newStock);
    }
}
$this->em->flush();
```

## 20. Procédure de mise en production

La mise en production doit suivre un ordre logique afin d'assurer une
application fonctionnelle et sécurisée.

Étapes recommandées :

1. préparer l'environnement serveur ;
2. installer PHP, la base de données et le serveur web ;
3. cloner le dépôt ;
4. installer les dépendances Composer ;
5. configurer les variables d'environnement ;
6. créer la base et exécuter les migrations ;
7. charger éventuellement les fixtures ;
8. configurer Stripe côté production ;
9. vérifier HTTPS et l'exposition du webhook ;
10. effectuer une recette complète avant ouverture.

Les variables critiques à configurer sont notamment :

- `DATABASE_URL` ;
- `APP_ENV` ;
- `APP_SECRET` ;
- `STRIPE_SECRET_KEY` ;
- `STRIPE_PUBLIC_KEY` ;
- `STRIPE_WEBHOOK_SECRET`.

## 21. Installation du projet sur serveur

L'installation du projet suit une procédure standard Symfony.

### Étapes principales

1. récupérer le code source ;
2. lancer `composer install` ;
3. configurer le fichier d'environnement ;
4. lancer les migrations Doctrine ;
5. donner accès au répertoire `public/` via le serveur web ;
6. vérifier les permissions d'écriture pour `var/` ;
7. tester les routes principales ;
8. configurer les webhooks Stripe vers la bonne URL.

### Points techniques à surveiller

- compatibilité PHP 8.2 minimum ;
- disponibilité des extensions PHP requises ;
- connexion correcte à la base de données ;
- présence des clés Stripe ;
- configuration correcte des images produits et des assets.

## 22. Déploiement et exploitation

Une fois déployée, l'application doit être exploitée avec des contrôles
réguliers sur :

- le bon fonctionnement du paiement ;
- l'arrivée des webhooks Stripe ;
- la cohérence des commandes ;
- l'évolution du stock ;
- les journaux applicatifs ;
- les sauvegardes de la base de données.

L'exploitation comprend également la mise à jour du catalogue, la
gestion des promotions et la surveillance des éventuelles erreurs sur le
flux de commande. Une amélioration continue peut ensuite être menée avec
ajout de tests automatisés, d'un historique des commandes ou d'une
gestion logistique plus complète.

## 23. Bilan personnel et compétences RNCP

La réalisation de Sports Bottles m'a permis de mettre en pratique les
compétences attendues dans le cadre du titre Développeur Web et Web
Mobile.

Compétences mobilisées :

- analyse du besoin utilisateur ;
- rédaction d'une documentation projet ;
- conception d'une architecture applicative ;
- création d'une base de données relationnelle ;
- développement d'interfaces web ;
- développement de logique métier serveur ;
- gestion de formulaires et validation ;
- intégration d'un service tiers sécurisé ;
- mise en place d'une administration ;
- gestion des tests et de la recette.

Ce projet a également renforcé ma compréhension des enjeux réels d'un
site e-commerce : cohérence des données, sécurité du paiement,
fiabilité du traitement métier et importance de l'expérience utilisateur.

## 24. Conclusion générale

Sports Bottles constitue un projet e-commerce complet et cohérent pour
un dossier RNCP. Il démontre la capacité à concevoir, développer et
documenter une application web moderne s'appuyant sur Symfony et des
outils professionnels.

Le projet couvre tout le cycle principal d'un achat en ligne :

- présentation du catalogue ;
- sélection des produits ;
- gestion du panier ;
- saisie de l'adresse ;
- paiement ;
- confirmation de commande ;
- mise à jour du stock ;
- administration du catalogue.

Il s'agit donc d'un support pertinent pour illustrer à la fois les
compétences techniques, l'organisation du développement et la logique
métier attendues dans un projet professionnel.

## 25. Annexes

### Annexe A -- Documents du projet

- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md#L1)
- [docs/GETTING_STARTED.md](docs/GETTING_STARTED.md#L1)
- [docs/STRIPE.md](docs/STRIPE.md#L1)
- [docs/TEST_GUIDE.md](docs/TEST_GUIDE.md#L1)
- [docs/VERIFICATION_CHECKLIST.md](docs/VERIFICATION_CHECKLIST.md#L1)
- [docs/PRODUCT_ARCHITECTURE.md](docs/PRODUCT_ARCHITECTURE.md#L1)
- [docs/PRODUCT_VISUAL_DESIGN.md](docs/PRODUCT_VISUAL_DESIGN.md#L1)

### Annexe B -- Extrait de l'entité Product

```php
#[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
private ?string $price = null;

#[ORM\Column]
private ?int $stock = null;

#[ORM\Column(length: 50)]
private ?string $capacity = null;

#[ORM\Column(length: 50, nullable: true)]
private ?string $temperature = null;
```

### Annexe C -- Extrait d'administration du stock

```php
NumberField::new('stock')->setLabel('Stock')
```

### Annexe D -- Données d'initialisation

Les fixtures du projet créent :

- 4 catégories ;
- plusieurs produits répartis par catégorie ;
- un stock initial de 100 unités par produit ;
- plusieurs promotions actives.

### Annexe E -- Commandes utiles

```bash
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console cache:clear
php bin/console debug:router
```

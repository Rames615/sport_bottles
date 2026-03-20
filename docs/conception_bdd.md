# 18. Conception de la base de données

## 18.1 Objectif de la conception

La base de données de Sports Bottles est une base relationnelle pilotée par Doctrine ORM. Le modèle repose sur les entités métier du projet Symfony et couvre les besoins principaux d'une boutique en ligne : gestion des utilisateurs, catalogue produit, promotions, panier, commandes et adresses de livraison.

Cette conception poursuit quatre objectifs :

- structurer les données métier de manière lisible et maintenable ;
- garantir l'intégrité des relations entre les objets du domaine ;
- faciliter les traitements applicatifs du panier et du paiement ;
- permettre l'évolution du schéma via Doctrine Migrations.

Le coeur du schéma s'appuie sur les tables suivantes : `user`, `category`, `product`, `promotion`, `cart`, `cart_item`, `order`, `shipping_address`.

Une table technique supplémentaire, `user_product`, est également générée par Doctrine pour une relation many-to-many présente dans le modèle. Elle n'est pas au coeur du tunnel de commande, mais elle fait partie du schéma réel.

## 18.2 Vue d'ensemble du modèle relationnel

Le modèle de données peut être lu en trois blocs fonctionnels :

### Bloc 1 - Gestion des comptes

- `user` stocke les informations d'authentification et de sécurité ;
- `shipping_address` stocke les adresses de livraison rattachées à un utilisateur.

### Bloc 2 - Catalogue

- `category` organise les produits par famille ;
- `product` porte les données commerciales du catalogue ;
- `promotion` permet d'appliquer des réductions datées sur un produit.

### Bloc 3 - Achat et paiement

- `cart` représente le panier actif d'un utilisateur ;
- `cart_item` représente chaque ligne du panier ;
- `order` conserve la trace d'une commande et de son état de paiement.

## 18.3 Tables principales et rôle de chacune

### Table `user`

Cette table représente les comptes applicatifs. Elle porte les informations indispensables à l'authentification, aux autorisations et à certains mécanismes de sécurité.

Colonnes importantes :

- `id` : clé primaire entière auto-incrémentée ;
- `email` : email utilisateur, obligatoire et unique ;
- `roles` : rôles de sécurité stockés au format JSON ;
- `password` : mot de passe haché ;
- `is_verified` : indicateur de vérification du compte ;
- `reset_token` : jeton de réinitialisation du mot de passe ;
- `reset_token_expires_at` : date d'expiration du jeton.

Contraintes et remarques :

- unicité de l'email via l'index `UNIQ_IDENTIFIER_EMAIL` ;
- un utilisateur peut posséder plusieurs commandes ;
- un utilisateur peut posséder plusieurs adresses de livraison ;
- un utilisateur possède au maximum un panier actif dans le schéma actuel.

### Table `category`

Cette table structure le catalogue en familles de produits.

Colonnes importantes :

- `id` : clé primaire ;
- `name` : nom de la catégorie ;
- `slug` : identifiant textuel pour les URLs ou traitements de présentation ;
- `description` : description longue ;
- `updated_at` : date de mise à jour.

Relation principale :

- une catégorie peut contenir plusieurs produits.

### Table `product`

Cette table centralise les informations commerciales du catalogue.

Colonnes importantes :

- `id` : clé primaire ;
- `designation` : nom commercial du produit ;
- `description` : description fonctionnelle ou marketing ;
- `price` : prix décimal stocké sur `DECIMAL(10,2)` ;
- `stock` : quantité disponible en entier ;
- `img_path` : chemin de l'image du produit ;
- `capacity` : contenance ;
- `temperature` : information thermique, nullable ;
- `category_id` : clé étrangère vers la catégorie.

Choix de modélisation :

- le prix produit représente le prix catalogue ;
- le stock est porté directement sur le produit, puis décrémenté après paiement confirmé ;
- le produit peut recevoir plusieurs promotions dans le temps, mais seule une promotion actuellement active est appliquée dans la logique métier.

### Table `promotion`

Cette table gère les campagnes promotionnelles rattachées à un produit.

Colonnes importantes :

- `id` : clé primaire ;
- `title` : titre de la promotion ;
- `description` : texte descriptif ;
- `discount_type` : type de remise (`percentage` ou `fixed`) ;
- `discount_value` : valeur numérique de la remise ;
- `start_at` : date de début ;
- `end_at` : date de fin ;
- `is_active` : activation logique ;
- `created_at` : date de création ;
- `img_path` : image éventuelle de la promotion ;
- `product_id` : clé étrangère vers le produit cible.

Règles métier associées :

- une promotion doit être rattachée à un produit ;
- la promotion n'est considérée comme applicable que si elle est active et que la date courante est comprise entre `start_at` et `end_at` ;
- la remise peut être un pourcentage ou un montant fixe.

### Table `cart`

Cette table représente le panier courant d'un utilisateur connecté.

Colonnes importantes :

- `id` : clé primaire ;
- `created_at` : date de création du panier ;
- `updated_at` : date de mise à jour ;
- `user_id` : clé étrangère vers l'utilisateur.

Choix de conception :

- la relation entre `cart` et `user` est de type un-à-un dans le schéma actuel ;
- un index unique sur `user_id` garantit qu'un utilisateur ne possède qu'un seul panier actif ;
- le panier est réservé au contexte authentifié, ce qui simplifie la cohérence métier et la sécurisation des actions.

### Table `cart_item`

Cette table correspond aux lignes du panier.

Colonnes importantes :

- `id` : clé primaire ;
- `cart_id` : clé étrangère vers le panier ;
- `product_id` : clé étrangère vers le produit ;
- `quantity` : quantité demandée ;
- `unit_price` : prix unitaire figé au moment de l'ajout ;
- `custom_image_path` : image personnalisée ou promotionnelle éventuelle.

Choix de modélisation essentiels :

- le prix de la ligne est dupliqué dans `cart_item.unit_price` pour figer le montant au moment de l'ajout au panier ;
- cette duplication évite qu'une modification ultérieure du prix catalogue ne change rétroactivement le panier déjà constitué ;
- `cart_item` est l'élément central des calculs de sous-total et du total de checkout.

### Table `order`

Cette table conserve la trace de la commande et de son statut de paiement.

Colonnes importantes :

- `id` : clé primaire ;
- `user_id` : clé étrangère vers l'utilisateur ;
- `total_amount` : montant total stocké en entier, en centimes ;
- `status` : état de la commande ;
- `stripe_session_id` : identifiant de session Stripe ;
- `created_at` : date de création ;
- `reference` : référence lisible de type `ORD-XXXXXXXXXX` ;
- `shipping_address` : adresse de livraison sérialisée sous forme textuelle.

Choix de conception importants :

- le montant est stocké en centimes pour éviter les erreurs d'arrondi lors des traitements de paiement ;
- le statut suit une logique simple de type `pending`, `paid` ou `failed` selon le cycle de paiement ;
- l'identifiant Stripe permet de relier la commande locale à la session de paiement externe ;
- l'adresse de livraison est enregistrée dans la commande sous forme de texte et non comme clé étrangère, afin de conserver un instantané lisible de l'adresse utilisée au moment de l'achat.

### Table `shipping_address`

Cette table stocke les adresses de livraison saisies pendant le checkout.

Colonnes importantes :

- `id` : clé primaire ;
- `user_id` : clé étrangère vers l'utilisateur ;
- `full_name` : nom complet du destinataire ;
- `address` : adresse postale ;
- `city` : ville ;
- `postal_code` : code postal ;
- `country` : pays ;
- `phone` : téléphone ;
- `created_at` : date de création ;
- `updated_at` : date de mise à jour.

Règles de validation associées :

- le schéma impose les tailles maximales et le caractère obligatoire de la plupart des champs ;
- la validation fonctionnelle du formulaire impose en plus un code postal sur 5 chiffres et un format de téléphone valide ;
- ces contrôles sont portés par `ShippingAddressType`, ce qui signifie que la règle est appliquée au niveau applicatif lors de la saisie.

### Table technique `user_product`

Cette table de jointure est générée par Doctrine pour la relation many-to-many entre `user` et `product`.

Colonnes :

- `user_id` ;
- `product_id`.

Remarque :

- elle dispose d'une clé primaire composite (`user_id`, `product_id`) ;
- elle n'est pas la table centrale du parcours e-commerce actuel, mais fait partie du schéma réel et doit apparaître dans un diagramme physique complet.

## 18.4 Relations et cardinalités

Les cardinalités principales sont les suivantes :

- `user` 1 - 1 `cart` : un utilisateur possède au plus un panier actif ;
- `cart` 1 - n `cart_item` : un panier contient plusieurs lignes ;
- `product` 1 - n `cart_item` : un produit peut apparaître dans plusieurs lignes de panier ;
- `category` 1 - n `product` : une catégorie regroupe plusieurs produits ;
- `product` 1 - n `promotion` : un produit peut porter plusieurs promotions dans le temps ;
- `user` 1 - n `order` : un utilisateur peut passer plusieurs commandes ;
- `user` 1 - n `shipping_address` : un utilisateur peut enregistrer plusieurs adresses ;
- `user` n - n `product` via `user_product` : relation technique secondaire présente dans le modèle.

Un point important de conception est à souligner : la table `order` ne possède pas de clé étrangère vers `shipping_address`. L'adresse est recopiée en texte dans la commande. Cette décision préserve l'historique exact de la commande même si l'utilisateur modifie ou supprime ensuite ses adresses sauvegardées.

## 18.5 Règles d'intégrité et choix techniques

Plusieurs choix structurants rendent le modèle cohérent avec les besoins d'une boutique en ligne :

- l'email utilisateur est unique ;
- le panier est rattaché à un seul utilisateur ;
- le stock est porté par le produit, puis diminue seulement quand le paiement est confirmé ;
- le prix catalogue est stocké en décimal, alors que le montant de commande est stocké en entier en centimes ;
- les promotions sont bornées dans le temps et actives seulement si leur fenêtre de validité est ouverte ;
- les lignes du panier mémorisent le prix unitaire au moment de l'ajout ;
- les adresses sont historisées dans la commande sous forme d'instantané textuel.

## 18.6 Logique applicative liée à la base de données

Le schéma relationnel soutient directement les principaux flux du projet.

### Catalogue

Le catalogue interroge `category`, `product` et `promotion` pour afficher les produits, les classer par catégorie et calculer le prix final lorsqu'une promotion est active.

### Panier

Lorsqu'un utilisateur ajoute un produit au panier :

1. l'application retrouve ou crée l'entrée `cart` rattachée à l'utilisateur ;
2. elle crée ou met à jour une ligne `cart_item` ;
3. elle copie le prix unitaire courant dans `unit_price` ;
4. elle contrôle la disponibilité à partir de `product.stock`.

### Checkout et paiement

Lors du passage en caisse :

1. une adresse est enregistrée dans `shipping_address` ;
2. une commande `order` est créée avec un `status` initial à `pending` ou `paid` selon le mode de paiement ;
3. `total_amount` est converti en centimes ;
4. `stripe_session_id` est renseigné pour relier la commande à Stripe en cas de paiement par carte ;
5. le webhook Stripe ou la page de succès font évoluer `status` vers `paid` ou `failed` ;
6. le stock produit est décrémenté après confirmation du paiement ;
7. le panier est ensuite vidé.

## 18.7 Migrations et évolution du schéma

Le schéma n'est pas figé dans du code SQL manuel. Il est versionné avec Doctrine Migrations, ce qui apporte plusieurs avantages :

- tracer les évolutions du schéma dans le temps ;
- reproduire l'installation sur un autre environnement ;
- sécuriser les changements de structure ;
- garder un historique des choix de modélisation.

Dans ce projet, le dossier `migrations/` montre une progression concrète du schéma :

- création initiale des tables du catalogue ;
- ajout des utilisateurs ;
- ajout du panier et des lignes de panier ;
- ajout des commandes ;
- ajout des promotions ;
- ajout de l'adresse de livraison et de champs complémentaires comme `custom_image_path`.

Cette approche est particulièrement adaptée à un projet Symfony évolutif, car elle aligne le modèle objet, le schéma SQL et l'historique des changements.

## 18.8 Forces et limites de la conception actuelle

### Forces

- schéma simple à comprendre ;
- bonne correspondance entre entités Doctrine et tables SQL ;
- relations principales claires pour un projet e-commerce ;
- gestion fiable des montants grâce au stockage en centimes pour les commandes ;
- conservation de l'historique d'adresse dans la commande ;
- évolution du schéma encadrée par migrations.

### Limites actuelles

- la commande ne possède pas encore de table dédiée de type `order_item` pour historiser localement chaque article commandé ;
- les validations de code postal et de téléphone sont principalement appliquées au niveau formulaire, pas directement au niveau base de données ;
- la relation `user_product` existe physiquement mais n'est pas centrale dans le parcours d'achat et peut demander une clarification métier ultérieure.

Ces limites n'empêchent pas le fonctionnement du projet, mais elles constituent de bons points d'évolution pour une version plus avancée.

## 18.9 Emplacement recommandé pour les captures du diagramme de base de données

Pour que la partie soit claire dans un rapport ou un dossier de conception, le meilleur emplacement pour coller les captures est le suivant :

### Capture 1 - Vue d'ensemble du schéma

Placer une première capture juste après le paragraphe d'introduction de la section `18.2 Vue d'ensemble du modèle relationnel`.

Pourquoi cet emplacement est pertinent :

- le lecteur voit d'abord la finalité du schéma ;
- il dispose ensuite immédiatement d'une vue globale avant de lire le détail des tables ;
- cela évite d'arriver aux captures trop tard, après plusieurs pages de texte.

Légende conseillée :

`Figure 18.1 - Vue d'ensemble du schéma relationnel de Sports Bottles`

### Capture 2 - Zoom sur le bloc transactionnel

Si tu veux ajouter une deuxième capture, place-la après la sous-partie `18.4 Relations et cardinalités` ou juste avant `18.6 Logique applicative liée à la base de données`.

Cette capture doit idéalement mettre en avant :

- `cart` ;
- `cart_item` ;
- `order` ;
- `shipping_address` ;
- `user`.

Légende conseillée :

`Figure 18.2 - Zoom sur le flux panier, adresse et commande`

### Capture 3 - Zoom sur le catalogue (optionnel)

Si le rapport accepte plusieurs illustrations, une troisième capture peut être placée après la description de `product` et `promotion` dans `18.3 Tables principales et rôle de chacune`.

Cette capture peut montrer :

- `category` ;
- `product` ;
- `promotion`.

Légende conseillée :

`Figure 18.3 - Relations entre catégories, produits et promotions`

## 18.10 Formulation de synthèse pour le rapport

Si tu veux une version plus courte à réintégrer dans un rapport principal, tu peux reprendre l'idée suivante :

La base de données de Sports Bottles est relationnelle et pilotée par Doctrine ORM. Elle s'organise autour des entités `user`, `category`, `product`, `promotion`, `cart`, `cart_item`, `order` et `shipping_address`. Le schéma sépare clairement le bloc catalogue, le bloc utilisateur et le bloc transactionnel. Les choix structurants les plus importants sont le stockage du prix produit en décimal, le stockage du montant de commande en centimes, le gel du prix unitaire dans `cart_item`, l'unicité de l'email utilisateur, le panier unique par utilisateur et la conservation d'un instantané textuel de l'adresse de livraison dans la commande. L'évolution du schéma est suivie par Doctrine Migrations, ce qui permet de reproduire l'installation et de tracer les changements de structure dans le temps.
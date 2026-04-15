# Questions Jury RNCP — Sports Bottles

---

## Questions générales

Q: Présentez votre projet en 3 phrases.
R: Sports Bottles est une boutique e-commerce Symfony 7.4 de vente de gourdes réutilisables. Elle couvre le catalogue, le panier persistant, le paiement Stripe et un back-office EasyAdmin. Le projet démontre un tunnel de commande complet avec confirmation serveur via webhook.

Q: Quel est le public cible de l'application ?
R: Sportifs, consommateurs sensibles à l'écologie, utilisateurs cherchant des contenants réutilisables pour le bureau ou les déplacements.

Q: Quelles fonctionnalités sont disponibles côté utilisateur ?
R: Inscription, connexion, catalogue par catégories, panier persistant, tunnel de commande, paiement Stripe, historique de commandes, édition de profil, réinitialisation de mot de passe.

Q: Quelles fonctionnalités sont disponibles côté administrateur ?
R: Back-office EasyAdmin pour gérer utilisateurs, produits, catégories, promotions, paniers et commandes.

Q: Pourquoi avoir choisi un projet e-commerce pour le RNCP ?
R: Il couvre un maximum de compétences : modélisation BDD, services métier, intégration tier (Stripe), sécurité, frontend responsive et administration.

Q: Quelle version de Symfony utilisez-vous et pourquoi ?
R: Symfony 7.4, version LTS stable offrant les dernières fonctionnalités tout en garantissant le support long terme.

Q: Quelle est la différence entre votre projet et un simple site vitrine ?
R: Il y a un tunnel de commande complet, un stock géré, un paiement external confirmé côté serveur, et une administration CRUD opérationnelle.

---

## Questions techniques

Q: Quelle est la structure des couches applicatives du projet ?
R: Présentation (Twig), Contrôleurs, Services métier (CartService, StripeService), Entités/Repositories (Doctrine ORM), Migrations.

Q: Qu'est-ce qu'un service Symfony et pourquoi avoir créé `CartService` ?
R: Un service est une classe injectable via le conteneur IoC. `CartService` centralise toute la logique panier pour éviter la duplication dans les contrôleurs.

Q: Comment fonctionne l'injection de dépendances dans Symfony ?
R: Le conteneur de services lit `services.yaml`, instancie les classes avec autowiring et les injecte automatiquement dans les constructeurs.

Q: Expliquez le rôle de `CartService::prepareCheckout()`.
R: Vérifie que le panier n'est pas vide, resynchronise les prix unitaires des lignes avec les prix produits actuels et retourne le total calculé.

Q: Pourquoi le prix unitaire est-il gelé dans `CartItem` et non lu depuis `Product` ?
R: Pour éviter qu'une modification du prix catalogue après ajout au panier ne change rétroactivement le montant dû par l'utilisateur.

Q: Qu'est-ce que Doctrine ORM et quel avantage apporte-t-il ?
R: Un ORM PHP qui mappe les objets PHP aux tables SQL. Il évite le SQL brut, gère les relations et facilite les migrations de schéma.

Q: Comment est géré le cycle de vie d'une commande ?
R: `pending` à la création → `paid` après confirmation webhook Stripe → `failed` si le paiement échoue.

Q: Qu'est-ce qu'AssetMapper et pourquoi l'avoir préféré à Webpack Encore ?
R: AssetMapper est l'outil natif Symfony 7 pour les assets sans étape de build Node.js ; plus simple à déployer pour un projet sans SPA complexe.

Q: Comment fonctionnent les migrations Doctrine ?
R: Chaque migration est une classe PHP versionnée avec `up()` et `down()`. `doctrine:migrations:migrate` les applique séquentiellement à la base.

Q: Pourquoi avoir 17 fichiers de migration dans le projet ?
R: Chaque évolution de schéma (ajout de colonne, nouvelle table) génère une migration distincte, traçant l'historique des changements de structure.

Q: Qu'est-ce qu'un repository Doctrine et quand l'utiliser ?
R: Une classe dédiée aux requêtes de récupération des entités. On l'utilise dès qu'une requête dépasse `find()` ou `findBy()` standard.

Q: Qu'est-ce que `window.PaymentConfig` dans `payment.js` ?
R: Un objet inliné dans le template Twig qui expose la clé publique Stripe et les URLs au script JS statique, sans inliner des secrets.

Q: Qu'est-ce que Supervisord et pourquoi est-il dans le conteneur Docker ?
R: Supervisord gère les processus `php-fpm` et `nginx` dans le même conteneur Alpine, les redémarre s'ils tombent.

Q: Qu'est-ce que PHPStan et à quel niveau est-il configuré dans ce projet ?
R: PHPStan est un analyseur statique PHP qui détecte les erreurs sans exécuter le code. Le niveau est défini dans `phpstan.neon`.

Q: Comment est implémentée la promotion sur un produit ?
R: L'entité `Promotion` est liée à un `Product`, avec `start_at`, `end_at`, `is_active`, `discount_type` (`percentage` ou `fixed`) et `discount_value`.

Q: Quelle différence entre `getCart()` et `getCartWithItems()` dans `CartService` ?
R: `getCartWithItems()` utilise une jointure Doctrine pour charger les lignes en une requête ; `getCart()` ne charge pas les items — préférer la première dès qu'on accède aux lignes.

Q: Qu'est-ce qu'un slug et à quoi sert-il dans l'entité `Category` ?
R: Identifiant textuel URL-friendly dérivé du nom, utilisé pour les URLs lisibles et les filtres côté présentation.

---

## Questions architecture

Q: Pourquoi séparer `CheckoutController`, `PaymentController` et `WebhookController` ?
R: Séparation des responsabilités : préparation de commande, retour utilisateur et confirmation serveur Stripe sont trois flux distincts et indépendants.

Q: Pourquoi la logique panier n'est-elle pas dans le contrôleur ?
R: Un service est testable, réutilisable et injectable. Mettre la logique dans le contrôleur violerait le principe de responsabilité unique.

Q: Quel pattern architectural utilisez-vous ?
R: MVC enrichi par une couche service (`CartService`, `StripeService`) qui isole la logique métier des contrôleurs.

Q: Pourquoi avoir créé une entité `ShippingAddress` séparée de `User` ?
R: Un utilisateur peut avoir plusieurs adresses différentes et les conserver d'une commande à l'autre. La séparation modélise cette cardinalité.

Q: Où est stockée l'adresse de livraison dans la commande elle-même ?
R: Un instantané textuel de l'adresse est stocké dans l'entité `Order` pour conserver l'adresse telle qu'elle était au moment de la commande.

Q: Pourquoi avoir choisi une page détail produit dédiée plutôt qu'une modale ?
R: Plus robuste, URL partageable, moins de dépendance JS, meilleure accessibilité et SEO, code plus lisible.

Q: Comment EasyAdmin est-il intégré sans casser l'architecture ?
R: EasyAdmin est configuré via ses propres `CrudController` sous `src/Controller/Admin/`, sans modifier les services ou entités métier.

Q: Pourquoi le montant de commande est-il stocké en centimes ?
R: Évite les erreurs d'arrondi des nombres flottants ; Stripe attend et manipule les montants en entiers de centimes.

Q: Quelle est la relation entre `Cart` et `User` ?
R: One-to-One : un utilisateur possède au maximum un panier actif, garanti par un index unique sur `user_id` dans la table `cart`.

Q: Pourquoi `user_product` existe-t-il dans le schéma ?
R: Table générée par Doctrine pour une relation many-to-many entre `User` et `Product`; elle n'est pas au cœur du tunnel de commande.

Q: Comment les données de démo sont-elles chargées ?
R: Via `doctrine:fixtures:load` qui exécute `AppFixtures.php` — purge la BDD puis insère les jeux de test.

---

## Questions sécurité

Q: Comment protégez-vous les formulaires contre les attaques CSRF ?
R: Symfony génère et valide automatiquement un token CSRF dans chaque formulaire; `CheckoutController::pay()` le vérifie explicitement avant de créer la session Stripe.

Q: Comment les mots de passe sont-ils stockés ?
R: Hachés via l'algorithme `auto` (bcrypt) de Symfony Security, jamais en clair.

Q: Comment protégez-vous contre l'IDOR dans le panier ?
R: `removeItemById()` et `updateItemQuantity()` vérifient que l'article appartient bien au panier de l'utilisateur avant toute modification.

Q: Pourquoi le webhook est-il la source de vérité et non le retour navigateur ?
R: Le navigateur peut être manipulé ou la session interrompue. Le webhook Stripe est authentifié par signature, indépendant du comportement client.

Q: Comment vérifiez-vous l'authenticité d'un webhook Stripe ?
R: Via la signature HMAC incluse dans l'en-tête `Stripe-Signature`, vérifiée avec `STRIPE_WEBHOOK_SECRET` — si la signature échoue, la requête est rejetée.

Q: Les secrets Stripe sont-ils versionnés dans le dépôt ?
R: Non. Les clés sont dans `.env.local` (non versionné). Les fichiers `.env` ne contiennent que des valeurs de placeholder.

Q: Comment l'accès à `/admin` est-il sécurisé ?
R: Par `access_control` dans `security.yaml` : `{ path: ^/admin, roles: ROLE_ADMIN }`. Tout utilisateur sans `ROLE_ADMIN` est redirigé.

Q: Qu'est-ce que `is_verified` sur l'entité `User` ?
R: Indicateur de validation de l'adresse e-mail. Un compte non vérifié peut être bloqué des actions sensibles.

Q: Comment sécurisez-vous le token de réinitialisation de mot de passe ?
R: Token temporaire avec date d'expiration (`reset_token_expires_at`); invalidé après usage; message neutre affiché pour ne pas divulguer l'existence d'un compte.

Q: Pourquoi le panier est-il réservé aux utilisateurs authentifiés ?
R: Simplifie la cohérence métier, la protection contre les abus et l'association fiable entre panier, commandes et utilisateur.

Q: Comment évitez-vous la double confirmation de paiement par le webhook ?
R: `WebhookController` vérifie l'état de la commande avant de la passer à `paid`; si elle est déjà `paid`, le traitement est ignoré (idempotence).

Q: Comment se comporte l'application si quelqu'un accède à `/checkout/pay` sans panier ?
R: `prepareCheckout()` retourne `['ok' => false]`, le contrôleur redirige avec un message d'erreur sans créer de commande ni de session Stripe.

---

## Questions performance

Q: Pourquoi utiliser des volumes nommés Docker (`app_var`, `app_vendor`) ?
R: Isoler `var/` et `vendor/` du bind-mount Windows→Linux évite les erreurs de cache et améliore drastiquement les performances I/O sur Windows.

Q: Qu'est-ce que le mode JIT de Tailwind et quel impact sur les performances ?
R: Le compilateur JIT génère uniquement les classes utilisées dans les templates, réduisant la taille du CSS final à quelques kB au lieu de plusieurs Mo.

Q: Comment limitez-vous les requêtes N+1 dans le catalogue ?
R: `getCartWithItems()` utilise une jointure `JOIN` Doctrine pour charger le panier et ses lignes en une seule requête.

Q: Pourquoi utiliser PHP 8.3 dans Docker ?
R: Performance JIT améliorée, typage renforcé, support des fibres — PHP 8.3 est la dernière version stable à la date du projet.

Q: Comment est géré le cache Symfony en production ?
R: Via `cache:warmup` et les volumes Docker qui persistent `var/cache/`; en dev, le cache se rebuild automatiquement à chaque requête.

Q: Quelle est l'importance du healthcheck MySQL dans `docker-compose.yml` ?
R: La dépendance `depends_on: condition: service_healthy` garantit que l'app ne démarke pas avant que MySQL soit prêt, évitant des erreurs de connexion au boot.

Q: Tailwind génère-t-il du CSS pour les classes ajoutées dynamiquement en JavaScript ?
R: Non. Seules les classes présentes statiquement dans les fichiers scannés sont générées. Les classes dynamiques JS doivent être safelist-ées dans `tailwind.config.js`.

---

## Questions pièges du jury

Q: Si le webhook ne se déclenche pas, que se passe-t-il pour l'utilisateur ?
R: La page de succès (`PaymentController::success()`) tente une synchronisation ponctuelle avec Stripe. L'utilisateur voit la page de succès même si le webhook est en retard.

Q: Le stock pourrait-il passer en négatif dans votre implémentation ?
R: Non. `deductStockForUser()` utilise `max(0, stock - quantité)` pour ramener le stock à 0 au minimum.

Q: Un utilisateur pourrait-il payer deux fois pour la même commande ?
R: Non. Le webhook vérifie le statut avant de passer en `paid`; une commande déjà `paid` ne sera pas retraitée.

Q: Que se passe-t-il si l'utilisateur quitte Stripe avant de payer ?
R: Stripe redirige vers l'URL `cancelUrl`; la commande reste à `pending` côté serveur; le panier est intact.

Q: Votre prix en `CartItem` peut-il être différent du prix `Product` actuel ?
R: Oui, intentionnellement. Le prix est gelé à l'ajout. C'est `prepareCheckout()` qui propose une resynchronisation optionnelle avant paiement.

Q: Un utilisateur non connecté peut-il accéder au panier d'un autre utilisateur ?
R: Non. Le panier est chargé depuis `CartService` à partir de l'objet `User` courant authentifié, pas depuis un ID passé en paramètre.

Q: Pourquoi avoir `StripeController` dans le code si le flux actif est dans `CheckoutController` ?
R: `StripeController` est un vestige d'une ancienne implémentation, documenté comme tel. Il ne constitue pas la référence du flux actuel.

Q: Que se passe-t-il si deux utilisateurs achètent le dernier article en stock simultanément ?
R: Le stock n'est décrémenté qu'après confirmation webhook. Sans verrouillage pessimiste ou transaction isolée, une condition de course reste théoriquement possible.

Q: Votre application fonctionne-t-elle sans variable `STRIPE_WEBHOOK_SECRET` définie ?
R: La vérification de signature est conditionnée à la présence du secret. Sans lui, la signature n'est pas vérifiée — ce qui est acceptable en dev mais inacceptable en production.

Q: Quel est le risque si `STRIPE_SECRET_KEY` est exposée dans le dépôt Git ?
R: Un attaquant pourrait créer des sessions Stripe, des remboursements ou accéder à toutes les données de paiement du compte Stripe.

Q: Pourquoi les rôles utilisateurs sont-ils stockés en JSON dans la BDD ?
R: Symfony Security gère les rôles comme un tableau PHP; Doctrine les sérialise en JSON pour persister une structure variable sans colonnes supplémentaires.

Q: L'administrateur par défaut (`sports@bottles` / `123456`) est-il sécurisé ?
R: Non en production. Ces credentials de démo sont créés par les fixtures destinées aux environnements de test uniquement, à supprimer/modifier avant mise en ligne.

Q: Que retourne `addProduct()` si le produit n'a plus de stock ?
R: `false`. Le contrôleur doit tester ce retour pour afficher un message d'erreur à l'utilisateur.

Q: Pourquoi ne pas utiliser les sessions PHP pour le panier plutôt que la BDD ?
R: Un panier en session est perdu à la fermeture du navigateur ou si la session expire. La persistance BDD garantit la durabilité et la cohérence multi-appareils.

---

## Questions sur les difficultés rencontrées

Q: Quelle a été la principale difficulté sur le flux Stripe ?
R: La synchronisation entre le retour navigateur (immédiat) et le webhook (asynchrone) — la page de succès devait s'afficher sans attendre la confirmation finale.

Q: Pourquoi la commande restait-elle bloquée à `pending` ?
R: Le webhook ne parvenait pas à joindre l'application locale. Solution : utiliser `stripe listen --forward-to` de Stripe CLI pour exposer le endpoint en dev.

Q: Comment avez-vous résolu l'incohérence entre routes Stripe héritées ?
R: Suppression des anciens contrôleurs redondants, centralisation dans `CheckoutController`, `PaymentController` et `WebhookController` avec des responsabilités claires.

Q: Pourquoi avez-vous rencontré des problèmes de performance Docker sur Windows ?
R: Le bind-mount Windows→Linux pour `vendor/` et `var/` est extrêmement lent. La solution a été d'utiliser des volumes nommés pour ces dossiers.

Q: Qu'est-ce qui était difficile dans la gestion du stock ?
R: Déterminer le bon moment pour décrémenter le stock — uniquement après confirmation serveur (webhook), pas après la redirection navigateur.

---

## Questions amélioration du projet

Q: Quelle serait la prochaine amélioration prioritaire du projet ?
R: Ajouter une entité `OrderItem` pour historiser les lignes de commande détaillées par produit, prix et quantité.

Q: Comment amélioreriez-vous la gestion des remboursements ?
R: Ajouter un flux de remboursement via l'API Stripe Refunds, un statut `refunded` sur `Order` et une interface admin dédiée.

Q: Comment passeriez-vous ce projet en production réelle ?
R: Configurer HTTPS, remplacer les clés test Stripe par les clés live, sécuriser les credentials, activer OPcache, configurer les logs et alertes.

Q: Comment amélioreriez-vous la testabilité du code ?
R: Ajouter des tests fonctionnels Symfony sur le tunnel de paiement, des tests unitaires sur `CartService` et mocker `StripeService` dans les tests.

Q: Comment amélioreriez-vous la scalabilité de l'application ?
R: Ajouter un cache Redis pour les sessions et le catalogue, une queue (Messenger) pour le traitement asynchrone des webhooks, et un CDN pour les assets.

Q: Quelles lignes de commande sont utiles à l'exploitation courante ?
R: `app:user:promote-admin` pour promouvoir un admin, `app:update-product-stock` pour remettre du stock, `doctrine:migrations:migrate` pour les déploiements.

Q: Comment géreriez-vous les e-mails transactionnels en production ?
R: Configurer `MAILER_DSN` avec un service SMTP fiable (Brevo, Mailjet, SES) et tester l'envoi de confirmation de commande après paiement.

Q: Comment amélioreriez-vous le SEO de l'application ?
R: Ajouter des balises meta dynamiques dans les templates Twig, des URLs slugifiées pour les produits et catégories, et un sitemap XML.

---

## Questions de justification des choix

Q: Pourquoi MySQL et non PostgreSQL ?
R: MySQL est le SGBD le plus répandu en hébergement mutualisé PHP/Symfony, compatible avec le stack Laragon et Docker du projet.

Q: Pourquoi Tailwind CSS et non Bootstrap ?
R: Tailwind offre un contrôle granulaire via des classes utilitaires et génère uniquement le CSS utilisé (JIT), contrairement à Bootstrap qui inclut tout son CSS.

Q: Pourquoi EasyAdmin et non Sonata Admin ?
R: EasyAdmin est plus léger, plus rapide à configurer et couvre les besoins CRUD sans la complexité de Sonata, adapté à un projet de taille RNCP.

Q: Pourquoi Stripe Checkout et non Stripe Elements (formulaire personnalisé) ?
R: Stripe Checkout délègue la gestion des données bancaires à Stripe, réduisant drastiquement la responsabilité PCI-DSS côté serveur.

Q: Pourquoi Docker Compose avec 3 conteneurs ?
R: Reproduire l'environnement de prod (app séparée de la BDD), faciliter l'onboarding de nouveaux développeurs et éviter les conflits de version locale.

Q: Pourquoi stocker le mot de passe avec `auto` (bcrypt) et non `argon2id` ?
R: `auto` choisit l'algorithme le plus sécurisé disponible sur la plateforme. Sur les serveurs sans l'extension Sodium, bcrypt reste le fallback robuste.

Q: Pourquoi utiliser AssetMapper et non npm/Webpack ?
R: AssetMapper ne nécessite pas Node.js, simplifie le déploiement et suffit pour un projet qui n'a pas de build JavaScript complexe côté front.

Q: Pourquoi créer la commande avant la redirection Stripe et non après ?
R: Pour avoir une trace métier avant le paiement, pouvoir rapper la session Stripe à la commande dès le début et ne jamais perdre une tentative de paiement.

Q: Pourquoi Docker expose-t-il MySQL sur le port 3307 et non 3306 ?
R: Évite les conflits avec une instance MySQL locale déjà en cours (Laragon) qui occupe le port 3306 standard.

Q: Pourquoi Twig côté serveur plutôt qu'un framework SPA React/Vue ?
R: Symfony est un projet educatif RNCP PHP ; Twig est natif, sans couche API supplémentaire, et suffit pour les besoins d'interactivité du projet.

Q: Pourquoi ne pas avoir utilisé Stripe PaymentIntent directement ?
R: Stripe Checkout gère l'UX de paiement côté Stripe (3D Secure, gestion d'erreurs, accessibilité). PaymentIntent nécessite plus de code custom pour le même résultat.

Q: Pourquoi les fixtures purgent-elles la BDD avant de recharger les données ?
R: Pour garantir un état déterministe et reproductible à chaque rechargement, indispensable pour les démos et les tests.

---

## Questions de mise en situation

Q: En démonstration, Stripe n'envoie pas le webhook. Que faites-vous ?
R: Lancer `stripe listen --forward-to http://localhost:8000/webhook/stripe` avec Stripe CLI pour exposer le endpoint localement.

Q: Un jury vous demande de montrer une commande payée en BDD. Comment procédez-vous ?
R: Accéder à phpMyAdmin sur `http://localhost:8081` ou à EasyAdmin `/admin` → section Commandes, filtrer par statut `paid`.

Q: La BDD est vide après un `fixtures:load`. Comment expliquez-vous cela au jury ?
R: C'est le comportement normal : les fixtures purgent la BDD avant d'insérer les données de démo. Les comptes admin et utilisateurs de test sont recréés.

Q: Comment démontrez-vous qu'un paiement échoué ne décrémente pas le stock ?
R: Payer avec la carte `4000 0000 0000 0002`, vérifier que la commande reste `pending` ou passe à `failed` et que le stock produit est inchangé.

Q: Comment prouvez-vous que le prix en panier reste gelé si le prix produit change ?
R: Modifier le prix d'un produit en admin, consulter le panier — la ligne `CartItem` conserve l'ancien `unit_price` figé à l'ajout.

Q: Que se passe-t-il si on tente d'accéder directement à `/admin` sans être admin ?
R: Symfony lit `access_control` dans `security.yaml` et redirige vers la page de connexion avec un message d'accès refusé.

Q: Comment ajouter un nouveau champ produit (ex. `weight`) sans perdre les données existantes ?
R: Ajouter la propriété dans l'entité `Product`, générer une migration avec `make:migration`, l'exécuter avec `doctrine:migrations:migrate`.

# 15. Backend — Architecture, services et gestion du paiement

---

## Sommaire du chapitre

- [15.1 Vue d'ensemble de l'architecture](#151-vue-densemble-de-larchitecture)
- [15.2 Couche Contrôleur](#152-couche-contrôleur)
  - [15.2.1 Contrôleurs métier](#1521-contrôleurs-métier)
  - [15.2.2 Back-office EasyAdmin](#1522-back-office-easyadmin)
  - [15.2.3 Tunnel de commande — `CheckoutController`](#1523-tunnel-de-commande--checkoutcontroller)
    - ► **[CAPTURE DE CODE 1 — `CheckoutController::pay()`]**
- [15.3 Couche Service](#153-couche-service)
  - [15.3.1 `CartService` — Gestion du panier](#1531-cartservice--gestion-du-panier)
    - ► **[CAPTURE DE CODE 2 — `CartService::addProduct()` / `deductStockForUser()`]**
  - [15.3.2 `OrderService` — Gestion des commandes](#1532-orderservice--gestion-des-commandes)
  - [15.3.3 `StripeService` — Intégration paiement](#1533-stripeservice--intégration-paiement)
  - [15.3.4 `MailerService` — Envoi d'e-mails transactionnels](#1534-mailerservice--envoi-de-mails-transactionnels)
  - [15.3.5 `PasswordService` — Réinitialisation de mot de passe](#1535-passwordservice--réinitialisation-de-mot-de-passe)
- [15.4 Couche Entité et modèle de données](#154-couche-entité-et-modèle-de-données)
- [15.5 Intégration Stripe — Flux complet](#155-intégration-stripe--flux-complet)
  - [15.5.1 Création de la session de paiement](#1551-création-de-la-session-de-paiement)
  - [15.5.2 Webhooks Stripe](#1552-webhooks-stripe)
- [15.6 Sécurité](#156-sécurité)
- [15.7 Extension Twig et composants transverses](#157-extension-twig-et-composants-transverses)
- [15.8 Commandes CLI Symfony](#158-commandes-cli-symfony)

---

## 15.1 Vue d'ensemble de l'architecture

Le backend de l'application **Sports Bottles** est développé avec **Symfony 7.4** et suit l'architecture **MVC** (Modèle — Vue — Contrôleur) enrichie d'une couche de **services** dédiés à la logique métier. Cette séparation des responsabilités garantit la **maintenabilité**, la **testabilité** et l'**évolutivité** du code.

```
src/
├── Command/          ← Commandes CLI Symfony
├── Controller/       ← Réception des requêtes HTTP, orchestration
│   └── Admin/        ← CRUD EasyAdmin (back-office)
├── DataFixtures/     ← Données de démonstration
├── Entity/           ← Modèle de données (Doctrine ORM)
├── Form/             ← Formulaires Symfony
├── Repository/       ← Requêtes Doctrine personnalisées
├── Security/         ← Vérification d'e-mail (SymfonyCasts)
├── Service/          ← Logique métier isolée et réutilisable
└── Twig/             ← Extensions Twig globales
```

**Principe d'organisation :** les contrôleurs sont volontairement allégés (thin controllers). Toute logique complexe — calcul de paniers, création de commandes, appels Stripe, envoi d'e-mails — est déléguée aux services injectés via l'**injection de dépendances** du conteneur Symfony.

---

## 15.2 Couche Contrôleur

### 15.2.1 Contrôleurs métier

L'application compte **14 contrôleurs** couvrant l'ensemble du parcours utilisateur :

| Contrôleur | Préfixe de route | Rôle principal |
|---|---|---|
| `HomeController` | `/` | Page d'accueil, mise en avant des promotions actives |
| `ProductController` | `/product` | Catalogue produits et fiche détail |
| `CategoryController` | `/category` | Gestion et affichage des catégories |
| `CartController` | `/panier` | Panier (affichage, ajout, mise à jour, suppression, vider) |
| `CheckoutController` | `/checkout` | Tunnel de commande en 4 étapes |
| `PaymentController` | `/payment` | Retour Stripe, polling d'état, confirmation finale |
| `StripeController` | `/stripe` | Endpoint Stripe alternatif, webhook |
| `WebhookController` | `/webhook` | Réception et traitement des webhooks Stripe |
| `AccountController` | `/mon-profil` | Profil utilisateur et historique des commandes |
| `SecurityController` | `/login` | Connexion / déconnexion |
| `RegistrationController` | `/register` | Inscription et vérification d'e-mail |
| `ResetPasswordController` | `/mot-de-passe-oublie` | Réinitialisation de mot de passe |
| `MailController` | `/contact` | Formulaire de contact |
| `LegalController` | — | Pages légales (CGU, RGPD, mentions légales) |

### 15.2.2 Back-office EasyAdmin

L'interface d'administration est construite avec **EasyAdmin 4** (`/admin`, accès `ROLE_ADMIN`). Elle expose sept contrôleurs CRUD :

| Contrôleur CRUD | Entité gérée | Fonctionnalités notables |
|---|---|---|
| `DashboardController` | — | Compteurs en temps réel (utilisateurs, produits, commandes…) |
| `UserCrudController` | `User` | Gestion des rôles, statut de vérification |
| `ProductCrudController` | `Product` | Upload d'image, prix en EUR, gestion du stock |
| `CategoryCrudController` | `Category` | Slug automatique |
| `OrderCrudController` | `Order` | Statut (`pending`/`paid`/`failed`), montant en centimes, ID session Stripe |
| `PromotionCrudController` | `Promotion` | Type de remise (pourcentage / fixe), dates de validité, upload image |
| `CartCrudController` | `Cart` | Lecture seule, vue des articles en détail |

### 15.2.3 Tunnel de commande — `CheckoutController`

Le `CheckoutController` orchestre un tunnel de commande en **4 étapes** :

| Étape | Route | Action |
|---|---|---|
| 1 | `GET/POST /checkout/shipping` | Saisie de l'adresse de livraison (stockée en session) |
| 2 | `GET /checkout/confirm` | Récapitulatif de la commande |
| 3 | `GET /checkout/method` | Sélection du mode de paiement |
| 4 | `POST /checkout/pay` | Création de la commande `pending` + session Stripe + redirection |

---

### ► CAPTURE DE CODE 1 — `CheckoutController::pay()`

> **Emplacement dans le dossier RNCP :** insérer ici une capture d'écran (ou bloc de code annoté) de la méthode `pay()` dans `src/Controller/CheckoutController.php` (lignes 185–260 environ).  
> **Ce qu'elle illustre :** la validation CSRF, l'appel à `CartService::prepareCheckout()`, la création de la commande via `OrderService::createOrder()` avec le statut `pending`, l'appel à `StripeService::createCheckoutSession()`, l'attachement du `session_id` Stripe et la redirection vers l'interface Stripe hébergée.

```php
// src/Controller/CheckoutController.php — méthode pay()
// [INSÉRER ICI LA CAPTURE D'ÉCRAN DE LA MÉTHODE pay()]

// Points clés à annoter sur la capture :
// 1. Vérification du token CSRF ('checkout_pay')
// 2. $this->cartService->prepareCheckout($user) — validation du panier
// 3. $this->orderService->createOrder($user, $total, 'pending', ...) — création Order
// 4. $this->stripeService->createCheckoutSession($cart, $successUrl, $cancelUrl)
// 5. $this->orderService->attachStripeSession($order, $session->id)
// 6. return new RedirectResponse($stripeUrl) — redirection Stripe
```

---

## 15.3 Couche Service

La couche service concentre **toute la logique métier** de l'application. Chaque service est un composant autonome, injecté par le conteneur Symfony grâce à l'**autowiring**.

### 15.3.1 `CartService` — Gestion du panier

**Fichier :** `src/Service/CartService.php`  
**Dépendances injectées :** `EntityManagerInterface`, `CartRepository`

Ce service est le cœur transactionnel de l'application. Il encapsule l'intégralité du cycle de vie du panier :

| Méthode | Description |
|---|---|
| `getCart(User)` | Récupère ou crée le panier de l'utilisateur (lazy creation) |
| `getCartWithItems(User)` | Chargement du panier avec jointure SQL (eager loading des articles) |
| `addProduct(User, Product, ?string)` | Ajoute un produit en vérifiant le stock ; incrémente la quantité si déjà présent ; capture le prix unitaire au moment de l'ajout |
| `updateItemQuantity(User, int, int)` | Mise à jour de quantité avec validation du stock ; supprime si quantité ≤ 0 |
| `removeItemById(User, int)` | Suppression sécurisée d'un article (vérification d'appartenance à l'utilisateur) |
| `clear(User)` | Vide entièrement le panier |
| `prepareCheckout(User)` | Valide le panier non vide, resynchronise les prix unitaires si le prix produit a changé |
| `deductStockForUser(User)` | Déduit le stock de chaque produit selon les quantités du panier |
| `confirmPayment(User)` | Déclenche le vidage du panier après paiement validé |
| `getCartTotal(Cart)` | Calcule le total (somme des sous-totaux par article) |
| `getCartItemCount(User)` | Nombre total d'articles (somme des quantités, pour le badge de navigation) |

**Mécanisme de protection du stock :**  
- `null` sur le champ `stock` d'un produit est interprété comme **stock illimité** (`PHP_INT_MAX`).  
- Toute tentative d'ajout dépassant le stock disponible retourne `false` sans modification du panier.  
- La déduction effective du stock intervient uniquement après confirmation du paiement (`deductStockForUser`), jamais à l'ajout au panier.

---

### ► CAPTURE DE CODE 2 — `CartService::addProduct()` et `CartService::deductStockForUser()`

> **Emplacement dans le dossier RNCP :** insérer ici une capture d'écran (ou bloc de code annoté) des méthodes `addProduct()` et `deductStockForUser()` dans `src/Service/CartService.php`.  
> **Ce qu'elles illustrent :** la vérification du stock avant ajout, la capture du prix unitaire au moment de l'ajout (snapshot de prix), la gestion de la mise à jour d'une ligne existante, et la déduction du stock uniquement après validation du paiement (séparation des responsabilités).

```php
// src/Service/CartService.php — méthodes addProduct() et deductStockForUser()
// [INSÉRER ICI LA CAPTURE D'ÉCRAN DES DEUX MÉTHODES]

// Points clés à annoter sur la capture :
// 1. $stockDisponible = null → PHP_INT_MAX (stock illimité)
// 2. Capture du prix : $cartItem->setUnitPrice((string) $product->getPrice())
// 3. Incrémentation de quantité si produit déjà dans le panier
// 4. Vérification $quantiteActuelle + 1 > $stockDisponible avant toute modification
// 5. Dans deductStockForUser() : max(0, $stock - $quantité) — protection contre stock négatif
// 6. $this->em->flush() unique en fin de méthode (transaction atomique)
```

---

### 15.3.2 `OrderService` — Gestion des commandes

**Fichier :** `src/Service/OrderService.php`  
**Dépendances injectées :** `EntityManagerInterface`

| Méthode | Description |
|---|---|
| `createOrder(User, int, string, string)` | Crée et persiste une commande ; le montant est stocké **en centimes** (entier) pour éviter les erreurs d'arrondi |
| `attachStripeSession(Order, string)` | Associe l'identifiant de session Stripe à la commande |
| `markAsPaid(Order)` | Passe le statut à `paid` |
| `markAsFailed(Order)` | Passe le statut à `failed` |
| `findByStripeSessionId(string)` | Retrouve une commande par son `stripeSessionId` (utilisé par les webhooks) |
| `getValidatedShippingAddress(int, User)` | Récupère une adresse en vérifiant qu'elle appartient à l'utilisateur courant (contrôle d'accès au niveau service) |
| `flush()` | Flush explicite de l'EntityManager (utilisé après des opérations en lot) |

**Note de conception :** le montant total est systématiquement exprimé en **centimes d'euro** (type `int`) dans l'entité `Order` et lors des échanges avec l'API Stripe, éliminant tout risque d'erreur lié aux flottants.

### 15.3.3 `StripeService` — Intégration paiement

**Fichier :** `src/Service/StripeService.php`  
**Dépendances injectées :** `MailerService`

Ce service encapsule tous les appels au **SDK Stripe PHP** :

| Méthode | Description |
|---|---|
| `buildLineItems(Cart)` | Transforme les `CartItem` en tableau de line items Stripe (prix en centimes, devise EUR) |
| `createCheckoutSession(Cart, string, string)` | Crée une Stripe Checkout Session (mode `payment`, méthode `card`) |
| `constructWebhookEvent(string, ?string)` | Valide la **signature HMAC** du payload avec `STRIPE_WEBHOOK_SECRET` ; fallback JSON si secret absent (développement) |
| `getEventType(Event\|array)` | Extrait le type d'événement Stripe |
| `getSessionIdFromEvent(Event\|array)` | Extrait le `session_id` d'un événement `checkout.session.completed` |
| `getFailedPaymentData(Event\|array)` | Extrait l'ID du PaymentIntent et l'`order_id` des métadonnées sur un échec |
| `syncPaymentStatus(string, Order)` | Récupère le statut réel depuis l'API Stripe et met à jour la commande |
| `fetchLineItems(string)` | Récupère les articles achetés depuis une session Stripe (pour l'e-mail de confirmation) |
| `sendOrderConfirmationEmail(Order, string)` | Orchestre : fetch des line items + envoi e-mail ; non-bloquant en cas d'erreur |

### 15.3.4 `MailerService` — Envoi d'e-mails transactionnels

**Fichier :** `src/Service/MailerService.php`  
**Dépendances injectées :** `MailerInterface`, `LoggerInterface`

| Méthode | Description |
|---|---|
| `sendContactEmail(string, string, array)` | Envoie une notification à l'administrateur et un accusé de réception au visiteur via des templates Twig dédiés |
| `sendOrderConfirmation(Order, array)` | Envoie l'e-mail de confirmation de commande avec le détail des articles |

Tous les e-mails sont envoyés depuis `no-reply@sportsbottles.fr`. Les templates sont localisés dans `templates/emails/`. Les erreurs d'envoi sont loggées sans bloquer le flux applicatif.

### 15.3.5 `PasswordService` — Réinitialisation de mot de passe

**Fichier :** `src/Service/PasswordService.php`  
**Dépendances :** `EntityManagerInterface`, `UserRepository`, `MailerInterface`, `LoggerInterface`, `UrlGeneratorInterface`

| Méthode | Description |
|---|---|
| `generateResetToken(User)` | Génère un token cryptographiquement sûr : `bin2hex(random_bytes(32))` — 64 caractères hexadécimaux |
| `validateToken(string)` | Vérifie l'existence du token et sa non-expiration (durée de vie : **1 heure**) ; efface automatiquement un token expiré |
| `clearToken(User)` | Réinitialise `resetToken` et `resetTokenExpiresAt` à `null` après utilisation |
| `sendResetEmail(User, string)` | Génère l'URL absolue de réinitialisation et envoie l'e-mail via template Twig |

**Sécurité :** le contrôleur `ResetPasswordController` retourne toujours une réponse positive à la demande de réinitialisation, quelle que soit l'existence de l'e-mail saisi, afin de prévenir l'**énumération des comptes utilisateurs**.

---

## 15.4 Couche Entité et modèle de données

L'application repose sur **8 entités** gérées par **Doctrine ORM** :

| Entité | Rôle | Relations clés |
|---|---|---|
| `User` | Compte utilisateur | OneToMany → `Order`, `ShippingAddress` ; OneToOne → `Cart` ; ManyToMany → `Product` (favoris) |
| `Product` | Fiche produit | ManyToOne → `Category` ; OneToMany → `Promotion`, `CartItem` |
| `Category` | Catégorie de produits | OneToMany → `Product` |
| `Promotion` | Offre promotionnelle | ManyToOne → `Product` ; type `percentage` ou `fixed` |
| `Cart` | Panier actif | OneToOne → `User` ; OneToMany → `CartItem` (cascade persist/remove, orphanRemoval) |
| `CartItem` | Ligne de panier | ManyToOne → `Cart`, `Product` ; stocke `unitPrice` (snapshot) et `customImagePath` |
| `Order` | Commande validée | ManyToOne → `User` ; statuts : `pending` / `paid` / `failed` / `cancelled` |
| `ShippingAddress` | Adresse de livraison | ManyToOne → `User` (cascade remove à la suppression de l'utilisateur) |

**Conception notable :**
- `CartItem.unitPrice` capture le prix au moment de l'ajout : une variation de tarif ultérieure n'affecte pas les paniers existants (resynchronisation uniquement dans `prepareCheckout`).
- `Order.totalAmount` est un `int` (centimes), jamais un flottant.
- `Order.reference` est auto-généré : `ORD-` + 10 caractères hexadécimaux aléatoires.

---

## 15.5 Intégration Stripe — Flux complet

### 15.5.1 Création de la session de paiement

Le flux de paiement suit les étapes suivantes :

```
[Utilisateur] → POST /checkout/pay
    │
    ├─ Validation CSRF (token 'checkout_pay')
    ├─ CartService::prepareCheckout() — validation du panier et des prix
    ├─ OrderService::createOrder() — création commande statut 'pending'
    ├─ StripeService::buildLineItems() — transformation du panier en line items Stripe
    ├─ StripeService::createCheckoutSession() — appel API Stripe
    ├─ OrderService::attachStripeSession() — liaison commande ↔ session Stripe
    └─ RedirectResponse($stripeUrl) → Interface Stripe hébergée

[Après paiement Stripe]
    │
    ├─ Succès → GET /payment/success?session_id=...
    │      StripeService::syncPaymentStatus() → markAsPaid()
    │      CartService::deductStockForUser() → décrémentation stock
    │      StripeService::sendOrderConfirmationEmail()
    │      CartService::clear()
    │
    └─ Annulation → GET /payment/cancel
```

### 15.5.2 Webhooks Stripe

Le `WebhookController` (route `POST /webhook/stripe`) et le `StripeController` (route `POST /stripe/webhook`) assurent la réception des événements asynchrones Stripe. La vérification de la **signature HMAC** du payload via `STRIPE_WEBHOOK_SECRET` garantit l'authenticité de chaque événement.

**Événements traités :**

| Événement Stripe | Action déclenchée |
|---|---|
| `checkout.session.completed` | Commande passée à `paid`, stock décrémenté, panier vidé, e-mail de confirmation envoyé |
| `payment_intent.payment_failed` | Commande passée à `failed` |

> Ce mécanisme webhook est le **seul moyen fiable** de valider une commande côté serveur, indépendamment du retour navigateur de l'utilisateur. Il garantit la cohérence même en cas de coupure réseau ou de fermeture prématurée de l'onglet.

---

## 15.6 Sécurité

La sécurité du backend repose sur plusieurs mécanismes complémentaires :

| Mécanisme | Implémentation | Protège contre |
|---|---|---|
| **Authentification** | Symfony Security — firewall `main`, formulaire de login | Accès non autorisé aux ressources protégées |
| **Hachage des mots de passe** | `UserPasswordHasherInterface` — algoritme **bcrypt/Argon2** | Vol de mots de passe depuis la base de données |
| **Vérification d'e-mail** | `SymfonyCasts/VerifyEmailBundle` — lien signé et expirant | Création de comptes avec des adresses inexistantes |
| **Protection CSRF** | Token Symfony sur chaque formulaire et action sensible | Attaques Cross-Site Request Forgery |
| **Contrôle d'accès par rôle** | `#[IsGranted('ROLE_ADMIN')]` / `#[IsGranted('ROLE_USER')]` — firewall EasyAdmin | Élévation de privilèges |
| **Validation serveur** | Contraintes Symfony sur tous les formulaires (`FormType`) | Injection de données malformées |
| **Vérification d'appartenance** | `OrderService::getValidatedShippingAddress()`, `CartService::removeItemById()` | Accès aux ressources d'un autre utilisateur (IDOR) |
| **Signature des webhooks** | `STRIPE_WEBHOOK_SECRET` — HMAC SHA-256 via Stripe SDK | Faux événements Stripe frauduleux |
| **Token de réinitialisation** | `bin2hex(random_bytes(32))` — 64 hex, expiration 1 heure | Brute force des liens de réinitialisation |
| **Anti-énumération** | Réponse toujours positive sur `/mot-de-passe-oublie` | Énumération des comptes utilisateurs |

---

## 15.7 Extension Twig et composants transverses

### `CartExtension`

**Fichier :** `src/Twig/CartExtension.php`  
**Dépendance :** `CartService`

Enregistre la fonction Twig **`cart_item_count(user)`**, appelée dans la barre de navigation pour afficher le badge indiquant le nombre d'articles dans le panier. La fonction retourne `0` si l'argument n'est pas une instance de `User`, évitant toute erreur sur les pages publiques.

```twig
{# Dans nav.html.twig #}
{{ cart_item_count(app.user) }}
```

### `EmailVerifier`

**Fichier :** `src/Security/EmailVerifier.php`  
**Dépendances :** `VerifyEmailHelperInterface`, `MailerInterface`, `EntityManagerInterface`

Gère la vérification d'e-mail en deux temps :
1. `sendEmailConfirmation()` — génère une URL signée et expirante puis envoie l'e-mail de confirmation.
2. `handleEmailConfirmation()` — valide le lien signé à l'ouverture, lève `VerifyEmailExceptionInterface` si invalide, marque `isVerified = true` sur l'utilisateur.

---

## 15.8 Commandes CLI Symfony

Deux commandes personnalisées facilitent la maintenance de l'application :

| Commande | Classe | Usage |
|---|---|---|
| `app:user:promote-admin` | `PromoteUserAdminCommand` | Attribue `ROLE_ADMIN` à un utilisateur par son adresse e-mail : `php bin/console app:user:promote-admin email@exemple.com` |
| `app:update-product-stock` | `UpdateProductStockCommand` | Remet à 100 le stock de tous les produits ayant un stock nul ou négatif (commande de maintenance) |
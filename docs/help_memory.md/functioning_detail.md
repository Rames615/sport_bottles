# 12. Spécifications fonctionnelles détaillées

## 12.1 CartService — Gestion du panier

`CartService` encapsule l'intégralité de la logique métier du panier. Chaque utilisateur connecté dispose d'un panier unique, persisté en base de données et composé de lignes `CartItem`.

### Méthodes disponibles

#### `getCart(User $user): Cart`
Récupère le panier de l'utilisateur. Si aucun panier n'existe, en crée un nouveau et le persiste en base de données.

#### `getCartWithItems(User $user): Cart`
Récupère le panier avec ses articles chargés via jointure (`JOIN` Doctrine). Crée un panier vide si aucun n'existe. Préférer cette méthode à `getCart` dès qu'on accède aux articles.

#### `addProduct(User $user, Product $product, ?string $customImagePath = null): bool`
Ajoute un produit au panier de l'utilisateur.
- Vérifie que le stock est disponible (`null` = stock illimité)
- Si l'article est déjà présent, incrémente la quantité de 1
- Vérifie que la nouvelle quantité ne dépasse pas le stock
- Si l'article est nouveau, crée une ligne `CartItem` et y duplique le prix unitaire courant
- Accepte un chemin d'image personnalisé (ex. image de promotion)
- Retourne `true` en cas de succès, `false` si le stock est insuffisant

#### `removeItemById(User $user, int $itemId): void`
Supprime un article du panier par son identifiant. Ignore l'opération si l'article est introuvable ou n'appartient pas au panier de l'utilisateur (sécurité IDOR).

#### `updateItemQuantity(User $user, int $itemId, int $quantity): void`
Met à jour la quantité d'un article dans le panier.
- Supprime l'article si la quantité est ≤ 0
- Ignore l'opération si la quantité dépasse le stock disponible
- Contrôle que l'article appartient bien au panier de l'utilisateur

#### `clear(User $user): void`
Supprime tous les articles du panier. Appelée automatiquement après confirmation du paiement.

#### `prepareCheckout(User $user): array`
Prépare le panier pour le passage en caisse.
- Vérifie que le panier n'est pas vide
- Recalcule les prix unitaires stockés si les prix produits ont changé
- Retourne `['ok' => true, 'total' => float, 'cart' => Cart]` ou `['ok' => false, 'message' => string]`

#### `confirmPayment(User $user): void`
Confirme le paiement et vide le panier via `clear()`. Doit être appelée uniquement après validation réussie du paiement.

#### `deductStockForUser(User $user): void`
Décrémente le stock de chaque produit en fonction des quantités du panier. Sans effet si le panier est déjà vide. `stock` est ramené à 0 au minimum (`max(0, stock - quantité)`).

#### `getCartTotal(Cart $cart): float`
Calcule et retourne le montant total du panier (somme des `prix unitaire × quantité` pour chaque ligne).

#### `getCartItemCount(User $user): int`
Retourne le nombre total d'articles dans le panier (toutes quantités cumulées).

---

## 12.2 StripeService — Intégration Stripe Checkout

`StripeService` encapsule toutes les interactions avec l'API Stripe. Le paiement est externalisé via **Stripe Checkout** afin de déléguer la gestion des informations bancaires à un tiers de confiance.

### Processus de paiement (6 étapes)

1. Le backend appelle `createCheckoutSession()` qui transmet à Stripe : les articles, le montant et les URLs de retour.
2. L'utilisateur est redirigé vers l'interface Stripe Checkout hébergée.
3. Stripe gère la saisie des données bancaires, la validation et la sécurisation de la transaction.
4. L'utilisateur est redirigé vers la page de succès ou d'échec de l'application.
5. Stripe envoie l'événement `checkout.session.completed` au `WebhookController`.
6. Le serveur vérifie la signature, met à jour la commande (`status → paid`), décrémente le stock et vide le panier.

> ⚠️ La validation côté serveur via webhook est un point critique de sécurité.  
> Elle garantit que le statut de la commande ne peut être mis à jour que par un événement authentifié en provenance de Stripe, indépendamment du comportement du navigateur.

### Méthodes disponibles

#### `getApiKey(): ?string`
Retourne la clé secrète Stripe depuis `$_ENV['STRIPE_SECRET_KEY']`, ou `null` si non configurée.

#### `buildLineItems(Cart $cart): array`
Construit le tableau `line_items` attendu par Stripe à partir d'un panier. Le montant unitaire est converti en centimes (`prix × 100`).

#### `createCheckoutSession(Cart $cart, string $successUrl, string $cancelUrl): Session`
Crée et retourne une `Stripe\Checkout\Session`. Lance une `\RuntimeException` si la clé API est absente. Passe les articles, les URLs de retour et le mode `payment`.

#### `syncPaymentStatus(string $sessionId, Order $order): bool`
Interroge l'API Stripe pour vérifier le statut de paiement d'une session. Met la commande à `paid` si `payment_status === 'paid'` et que la commande n'est pas déjà payée. Retourne `true` si la commande a été mise à jour.

#### `fetchLineItems(string $sessionId): array`
Récupère les lignes d'articles depuis une session Stripe (jusqu'à 100 articles). Retourne un tableau de `[name, quantity, unitPrice, subtotal]`.

#### `sendOrderConfirmationEmail(Order $order, string $sessionId): void`
Récupère les articles via `fetchLineItems()` puis délègue l'envoi de l'e-mail de confirmation à `MailerService::sendOrderConfirmation()`. Non bloquant : toute exception est silencieusement ignorée.

#### `constructWebhookEvent(string $payload, ?string $sigHeader): \Stripe\Event|array`
Valide et reconstruit un événement webhook Stripe. Si `STRIPE_WEBHOOK_SECRET` est défini, vérifie la signature via `Webhook::constructEvent()`. Sinon, décode le JSON brut (mode développement). Lance une exception si le payload est invalide.

#### `getEventType(\Stripe\Event|array $event): ?string`
Extrait le type d'événement (`checkout.session.completed`, `payment_intent.payment_failed`, etc.) depuis un objet ou un tableau Stripe.

#### `getSessionIdFromEvent(\Stripe\Event|array $event): ?string`
Extrait l'identifiant de session Stripe depuis un événement `checkout.session.completed`.

#### `getFailedPaymentData(\Stripe\Event|array $event): array`
Extrait l'identifiant du `PaymentIntent` et le `order_id` depuis les métadonnées d'un événement `payment_intent.payment_failed`. Retourne `['id' => string|null, 'order_id' => string|null]`.

---

## 12.3 OrderService — Gestion des commandes

`OrderService` encapsule les opérations CRUD sur l'entité `Order` et la validation des adresses de livraison.

### Méthodes disponibles

#### `createOrder(User $user, int $totalAmountCents, string $status, string $shippingAddress): Order`
Crée et persiste une nouvelle commande avec le statut initial donné (ex. `pending`). Le montant est en centimes.

#### `findByStripeSessionId(string $sessionId): ?Order`
Retrouve une commande par son identifiant de session Stripe (`stripeSessionId`). Retourne `null` si introuvable.

#### `find(int $id): ?Order`
Retrouve une commande par sa clé primaire.

#### `attachStripeSession(Order $order, string $stripeSessionId): void`
Associe un identifiant de session Stripe à une commande et persiste la modification.

#### `markAsPaid(Order $order): void`
Passe le statut de la commande à `paid` et persiste.

#### `markAsFailed(Order $order): void`
Passe le statut de la commande à `failed` et persiste.

#### `getValidatedShippingAddress(int $addressId, User $user): ?ShippingAddress`
Retrouve une adresse de livraison et vérifie qu'elle appartient à l'utilisateur courant (sécurité IDOR). Retourne `null` si l'adresse est introuvable ou n'appartient pas à l'utilisateur.

#### `flush(): void`
Persiste les changements en attente dans l'EntityManager. Utile après des modifications externes à ce service.

---

## 12.4 MailerService — Envoi d'e-mails

`MailerService` gère l'envoi de tous les e-mails transactionnels de l'application via le composant Symfony Mailer avec des templates Twig.

### Méthodes disponibles

#### `sendContactEmail(string $to, string $subject, array $context): void`
Envoie deux e-mails depuis le formulaire de contact :
1. **Notification admin** — envoyée à l'adresse `$to`, avec `Reply-To` sur l'expéditeur. En cas d'échec, lance une `\RuntimeException` (bloquant).
2. **Confirmation visiteur** — renvoyée à l'expéditeur du formulaire. En cas d'échec, journalise un warning sans bloquer. Un délai de 2 s est appliqué entre les deux envois (limite du plan Mailtrap gratuit).

Templates utilisés : `emails/contact/admin_notification.html.twig` / `.txt.twig` et `emails/contact/user_confirmation.html.twig` / `.txt.twig`.

#### `sendOrderConfirmation(Order $order, array $items = []): void`
Envoie un e-mail de confirmation de commande au client avec le détail des articles. Sans effet (avec log warning) si la commande n'a pas d'utilisateur ou si l'utilisateur n'a pas d'adresse e-mail. Les erreurs d'envoi sont journalisées mais non remontées.

Template utilisé : `emails/order/confirmation.html.twig` / `.txt.twig`.

---

## 12.5 PasswordService — Réinitialisation du mot de passe

`PasswordService` gère le cycle complet de réinitialisation de mot de passe par e-mail : génération du token, validation, expiration et envoi.

### Constante

- `TOKEN_LIFETIME_SECONDS = 3600` — durée de validité du token (1 heure).

### Méthodes disponibles

#### `generateResetToken(User $user): string`
Génère un token cryptographiquement sûr (`bin2hex(random_bytes(32))`) de 64 caractères hexadécimaux, le stocke sur l'utilisateur avec une date d'expiration à +1 heure, persiste et retourne le token.

#### `validateToken(string $token): ?User`
Recherche un utilisateur par son token de réinitialisation. Retourne `null` si le token est inconnu ou expiré. Efface automatiquement le token expiré via `clearToken()`.

#### `clearToken(User $user): void`
Supprime le token et la date d'expiration de l'utilisateur et persiste. Appelée après utilisation réussie du token ou après expiration.

#### `sendResetEmail(User $user, string $token): void`
Génère l'URL absolue de réinitialisation (route `app_reset_password`) et envoie l'e-mail avec le lien et la date d'expiration. Lance une `\RuntimeException` en cas d'échec d'envoi.

Template utilisé : `emails/reset_password/reset.html.twig` / `.txt.twig`.

---

## 12.6 Gestion des promotions

Une promotion est applicable à un produit si et seulement si les deux conditions suivantes sont réunies simultanément :

- `is_active = true` — la promotion est explicitement activée
- La date courante est comprise dans l'intervalle `start_at / end_at`

Le prix final est calculé dynamiquement : `prix_final = prix_original × (1 - taux_remise / 100)`

Les produits en promotion sont mis en avant sur la page d'accueil dans une section dédiée.

---

## 12.7 Gestion du stock

Chaque produit dispose d'un champ `stock` représentant la quantité disponible à la vente. `stock = null` signifie stock illimité.

### Lors de l'ajout au panier (`CartService::addProduct`)
- Vérifie que la quantité demandée ne dépasse pas le stock disponible
- En cas de stock insuffisant : l'action est bloquée et `false` est retourné

### Lors de la confirmation du paiement (webhook Stripe)
- Le stock est décrémenté via `CartService::deductStockForUser()` uniquement après réception et validation du webhook Stripe
- Cela prévient toute incohérence en cas d'échec de paiement ou de double paiement  
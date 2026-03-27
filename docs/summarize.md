# RNCP Presentation Summary

---

## 1. Project Overview (Slides 1–2)

- **Project name** : Sports Bottles
- **Context / problem** : The market for reusable containers is growing. No simple, focused platform existed for sport-oriented bottles.
- **Objectives** :
  - Build a complete, production-grade e-commerce application
  - Cover the full purchase cycle (catalog → cart → checkout → Stripe payment → confirmation)
  - Provide a usable admin back-office
- **Target users** :
  - Athletes and gym users
  - Eco-conscious consumers (anti single-use plastics)
  - People looking for everyday bottles (office, commute)
- **Value proposition** :
  - Specialized catalog (Glass, Stainless Steel, Isothermal, BPA-Free)
  - Simple, reassuring purchase experience
  - Secure payment via Stripe
  - Reliable stock management
  - Exploitable admin interface (EasyAdmin)

---

## 2. Functional Scope (Slides 3–4)

### Main Features

**Catalog & Navigation**
- Homepage with latest products and active promotions
- Category-based catalog (Glass / Stainless / Isothermal / BPA-Free)
- Dedicated product detail page (with price, description, characteristics, add-to-cart)
- Real-time promotion display with calculated final price

**Cart**
- Persistent cart linked to the authenticated user
- AJAX add-to-cart for fluid UX
- Stock check on add and on quantity update
- Line quantity update and deletion
- Full cart wipe

**Order Tunnel (multi-step)**
1. Shipping address form
2. Order summary
3. Payment (Stripe Checkout redirect)
4. Confirmation page (post-webhook)

**User Account**
- Registration + email verification
- Login / logout
- Profile editing (email)
- Password reset (tokenized, time-limited)
- Order history

**Administration (EasyAdmin)**
- Manage users, products, categories, promotions, carts, orders
- Dashboard with Chart.js counters (users, products, orders, conversion funnel)

### User Flow (nominal path)

```
Homepage → Catalog → Product Detail → Add to Cart
→ Cart → Shipping Address → Order Summary
→ Stripe Checkout → Success Page → Confirmation
```

### Core Capabilities
- Authenticated-only cart and checkout (no guest checkout)
- Server-side price recalculation before payment
- Stripe webhook as the single source of truth for payment confirmation
- Stock decremented **only** after confirmed payment
- Frozen unit price in `CartItem` (price changes don't retroactively alter open carts)

---

## 3. Technical Architecture (Slides 5–6)

### Stack

| Layer | Technology |
|---|---|
| Backend framework | Symfony 7.4 (LTS) |
| Language | PHP 8.4 |
| ORM | Doctrine ORM |
| Templating | Twig |
| CSS framework | Tailwind CSS (utility-first) |
| Asset pipeline | AssetMapper (no Node.js build) |
| Admin interface | EasyAdmin Bundle |
| Payment provider | Stripe Checkout API |
| Charts | Chart.js |
| Database | MySQL 8.0 |
| Containerization | Docker (PHP 8.4-fpm-alpine + Nginx + MySQL + phpMyAdmin) |
| Process manager | Supervisord |
| Static analysis | PHPStan |
| Email | Symfony Mailer |

### Architecture Layers

```
┌─────────────────────────────────────────────────────────┐
│              Presentation (Twig + Tailwind CSS)          │
│    home / product / cart / checkout / payment / account  │
├─────────────────────────────────────────────────────────┤
│              Controllers (Symfony HTTP layer)            │
│  HomeController · ProductController · CartController     │
│  CheckoutController · PaymentController · WebhookCtrl   │
│  AccountController · Admin/DashboardController           │
├─────────────────────────────────────────────────────────┤
│              Services (Business Logic)                   │
│        CartService           StripeService               │
│        MailerService                                     │
├─────────────────────────────────────────────────────────┤
│          Entities / Repositories (Doctrine ORM)          │
│  User · Category · Product · Promotion · Cart            │
│  CartItem · Order · ShippingAddress                      │
├─────────────────────────────────────────────────────────┤
│                  MySQL 8 (via Docker)                    │
└─────────────────────────────────────────────────────────┘
```

### Docker Topology

```
┌──────────────────────────────────────────────────────────────┐
│                     docker-compose.yml                        │
├──────────────────────┬─────────────────────┬─────────────────┤
│  sports_bottles_app  │  sports_bottles_db  │  sports_bottles │
│  PHP 8.4 + Nginx     │  MySQL 8.0.30       │       _pma      │
│  Port: 8080          │  Port: 3307         │  phpMyAdmin     │
│                      │                     │  Port: 8081     │
└──────────────────────┴─────────────────────┴─────────────────┘
```

### Key Technical Choices & Justification

| Choice | Justification |
|---|---|
| Symfony 7.4 LTS | Long-term support, mature ecosystem, strong DI container |
| AssetMapper over Webpack Encore | No Node.js build step — simpler deployment, sufficient for a non-SPA project |
| Stripe Checkout (hosted page) | Delegates card data handling to Stripe — PCI DSS scope reduction |
| Webhook as source of truth | Browser redirects are unreliable; server events are authoritative |
| Price frozen in `CartItem` | Prevents retroactive price changes from corrupting open carts |
| Amounts stored in centimes | Avoids floating-point rounding errors in payment calculations |
| EasyAdmin Bundle | Full CRUD admin with minimal code for standard back-office needs |

---

## 4. Implementation Highlights (Slides 7–8)

### CartService — Central Business Logic

`CartService` is the single entry point for all cart operations:

- `getCartWithItems()` — loads cart with a Doctrine JOIN (single query)
- `addProduct()` — stock check, price snapshot, duplicate detection
- `updateItemQuantity()` — stock-aware quantity update, owns IDOR protection
- `prepareCheckout()` — resynchronises unit prices before payment
- `deductStockForUser()` — stock decrement, called only after webhook confirms payment
- `clear()` — wipes cart after confirmed payment

### StripeService — Payment Integration

- `createCheckoutSession()` — builds Stripe `line_items` in centimes, returns hosted Checkout URL
- `constructWebhookEvent()` — verifies Stripe signature (`STRIPE_WEBHOOK_SECRET`) or falls back to raw JSON (dev mode)
- `syncPaymentStatus()` — polling fallback if webhook arrives late
- `sendOrderConfirmationEmail()` — non-blocking email triggered post-webhook

### Order Lifecycle

```
pending  →  paid   (webhook checkout.session.completed)
         →  failed (webhook payment_intent.payment_failed)
```

### window.PaymentConfig Bridge

To avoid inlining dynamic values (Stripe public key, API URLs) in a static `.js` file, the `payment.html.twig` template writes a `window.PaymentConfig` object via an inline `<script>`. The static `payment.js` reads this config at load time. This cleanly separates server configuration from client-side behaviour.

### Security Considerations

- CSRF protection on all state-changing forms
- Authentication required for cart, checkout, account, and admin
- Role-based access: `ROLE_USER` (profile/cart) — `ROLE_ADMIN` (EasyAdmin `/admin/*`)
- Stripe amounts recalculated server-side — client input is never trusted
- Webhook signature verified using `STRIPE_WEBHOOK_SECRET`
- Password reset tokens are time-limited and invalidated after use
- Cookie consent module (GDPR-aligned)
- Neutral message on password reset request (no account enumeration)
- IDOR protection in `CartService.removeItemById()` and `updateItemQuantity()`

### Notable Technical Components

| Component | File | Role |
|---|---|---|
| CartService | `src/Service/CartService.php` | All cart business logic |
| StripeService | `src/Service/StripeService.php` | Stripe API wrapper |
| WebhookController | `src/Controller/WebhookController.php` | Server payment confirmation |
| PaymentController | `src/Controller/PaymentController.php` | Success page + polling |
| DashboardController | `src/Controller/Admin/DashboardController.php` | Admin metrics |
| PromoteUserAdmin | `src/Command/PromoteUserAdminCommand.php` | CLI user promotion |
| UpdateProductStock | `src/Command/UpdateProductStockCommand.php` | CLI stock reset |

---

## 5. Methodology & Workflow (Slide 9)

- **Version control** : Git with incremental commits per feature/fix
- **Database migrations** : 17 versioned Doctrine migrations — full schema history traceable
- **Fixtures** : `AppFixtures` seeds categories, products, promotions, and a default admin account
- **Static analysis** : PHPStan (level defined in `phpstan.neon`) — runs without executing code
- **Testing** : PHPUnit (`phpunit.dist.xml`) — unit and functional test infrastructure in place
- **Environment management** : `.env` + `.env.local` pattern (secrets never committed)
- **Documentation** : Structured `docs/` corpus (35+ files) covering architecture, flows, setup, and demo
- **Deployment readiness** : Docker Compose stack with named volumes for Windows I/O performance

---

## 6. Challenges & Solutions (Slide 10)

### Challenge 1 — Reliable Payment Confirmation

- **Problem** : Browser redirects after Stripe Checkout are unreliable (tab close, network error)
- **Solution** : `WebhookController` is the authoritative source for `paid` status; `PaymentController` adds a polling fallback via `StripeService::syncPaymentStatus()`
- **Trade-off** : Slightly more complex controller split, but robust against all failure modes

### Challenge 2 — Cart Price Integrity

- **Problem** : A price change on a product could silently modify the amount owed on a cart already in progress
- **Solution** : `CartItem.unit_price` is frozen at the moment of add. `prepareCheckout()` resynchronises prices just before payment as a final safeguard

### Challenge 3 — Dynamic Config in Static JS

- **Problem** : Stripe public key and API URLs need to reach `payment.js` without being hardcoded
- **Solution** : `window.PaymentConfig` object written by an inline Twig `<script>` — no secrets exposed, clean separation of concerns

### Challenge 4 — Docker on Windows

- **Problem** : Bind-mount from Windows to Linux in Docker caused `rmdir: Directory not empty` cache errors and slow I/O
- **Solution** : Named volumes `app_var` and `app_vendor` isolate `var/` and `vendor/` from the bind-mount entirely

### Challenge 5 — Legacy Stripe Controller

- **Problem** : An older `StripeController` coexisted with the current flow, creating confusion
- **Solution** : Flow recentred on `CheckoutController` + `PaymentController` + `WebhookController`; old controller documented as historical and excluded from reference docs

---

## 7. Results & Demonstration (Slide 11)

### What Works

- Full purchase cycle from catalog to confirmed order
- Stock correctly decremented after Stripe webhook confirmation
- Admin dashboard with live counters and Chart.js visualisations
- User account: registration, email verification, profile edit, password reset, order history
- Promotions: active promotions displayed with discounted final price
- Cookie consent module
- Responsive layout (mobile + tablet + desktop)

### Recommended Demo Flow

1. **Homepage** — hero section, active promotions, last products
2. **Catalog** — categories, product cards
3. **Product detail** — description, price with promotion, add-to-cart
4. **Cart** — AJAX add, quantity update, stock enforcement
5. **Checkout** — shipping address form, order summary
6. **Stripe Checkout** — test card `4242 4242 4242 4242`
7. **Success page** — order confirmed, cart cleared
8. **Admin dashboard** — EasyAdmin, Chart.js, order list
9. **User account** — order history

### Test Stripe Cards

| Scenario | Card number |
|---|---|
| Accepted payment | `4242 4242 4242 4242` |
| Declined payment | `4000 0000 0000 0002` |

---

## 8. Skills Demonstrated (RNCP Mapping) (Slide 12)

### Technical Skills

- PHP 8.4 / Symfony 7.4 — controllers, services, forms, security, events
- Doctrine ORM — entity design, relationships, migrations, repositories
- API integration — Stripe Checkout, webhook handling, signature verification
- Frontend — Twig templating, Tailwind CSS (utility-first, responsive), Chart.js, AssetMapper
- JavaScript — DOM manipulation, Stripe.js, `window.PaymentConfig` pattern
- Email — Symfony Mailer, transactional order confirmation

### Architecture Skills

- Layered architecture: presentation / controller / service / persistence
- Service encapsulation: single-responsibility `CartService` and `StripeService`
- Design decisions documented and justified (frozen prices, centimes, webhook source-of-truth)
- 8 core entities with well-defined relations and integrity constraints
- Docker multi-container stack with named volumes

### Project Management

- 17 tracked database schema changes (Doctrine migrations)
- Structured documentation corpus (35+ Markdown files, indexed by audience and topic)
- Separation of current state from historical/debug notes
- Checklist-driven pre-delivery verification
- CLI commands for operational tasks (stock reset, user promotion)

### Quality & Testing

- PHPStan static analysis configured
- PHPUnit test infrastructure in place
- Functional test scenarios documented (`docs/TEST_GUIDE.md`)
- Pre-delivery checklist (`docs/VERIFICATION_CHECKLIST.md`)
- OWASP-aligned security practices (CSRF, IDOR, server-side amount validation, webhook verification)

---

## 9. Conclusion (Final Slide)

### Achievements

- Complete, functional e-commerce application demonstrated end-to-end
- Secure payment integration with Stripe (hosted Checkout + webhook confirmation)
- Solid, layered Symfony architecture with clear separation of concerns
- Production-ready Docker stack
- Comprehensive documentation suitable for jury, onboarding, and maintenance

### Possible Improvements

- Add `OrderItem` entity to store per-line order history
- Implement refund handling (Stripe refunds + admin UI)
- Add guest checkout (session-based cart)
- Expand test coverage (functional tests on checkout, webhook integration tests)
- Log payment lifecycle events homogeneously (creation, webhook reception, status change)
- Add front-end form validation (real-time feedback)

### Future Work

- Multi-vendor or multi-brand catalog
- Enhanced promotion engine (voucher codes, tiered discounts)
- Customer review system per product
- Progressive Web App (PWA) for mobile-first UX

---

## Slide-by-Slide Image Recommendations

---

**Slide 1 – Project Identity**
- Suggested image: Collage of product photos from the catalog
- File path: `public/products_images/rusty-termos.png`, `public/products_images/isothermique_green.png`, `public/products_images/glass_green.png`
- Placement: Full width background (hero-style)
- Reason: Immediately establishes the product identity and visual brand

---

**Slide 2 – Value Proposition & Positioning**
- Suggested image: A screenshot of the homepage hero section (Sports Bottles running locally)
- File path: *(to be captured from running application at `http://localhost:8080`)*
- Placement: Side (right half of slide)
- Reason: Shows the real application, not a wireframe; anchors the pitch in something concrete

---

**Slide 3 – Functional Scope Overview**
- Suggested image: Screenshot of the product catalog page
- File path: *(to be captured from running application — `/product`)*
- Placement: Side (right half)
- Reason: Illustrates the main user-facing feature set at a glance

---

**Slide 4 – User Flow**
- Suggested image: Flowchart diagram of the purchase journey
- File path: *(to be created — suggested tool: draw.io or Mermaid)*
- Placement: Full width
- Reason: A visual flow beats a bulleted list for demonstrating UX thinking to the jury
- Suggested diagram content:
  ```
  Homepage → Catalog → Product Detail → Cart → Shipping Address
  → Order Summary → Stripe Checkout → Success → Confirmation
  ```

---

**Slide 5 – Technical Architecture**
- Suggested image: Layered architecture diagram (presentation / controllers / services / ORM / DB)
- File path: *(to be created — the ASCII diagram in `docs/ARCHITECTURE.md` can be converted to vector)*
- Placement: Full width
- Reason: Demonstrates architectural thinking — essential for RNCP jury evaluation

---

**Slide 6 – Docker Stack & Deployment**
- Suggested image: Docker topology diagram (3-container stack)
- File path: *(reference ASCII in `docs/docker.md` — convert to visual)*
- Placement: Full width
- Reason: Shows deployment maturity and understanding of containerised environments

---

**Slide 7 – Database Model (ERD)**
- Suggested image: Entity-Relationship Diagram of the 8 core tables
- File path: *(to be generated from phpMyAdmin at `http://localhost:8081` or via a schema tool)*
- Placement: Full width
- Reason: Mandatory for demonstrating database design skills; jury will ask about relations

---

**Slide 8 – Stripe Payment Flow**
- Suggested image: Sequence diagram showing: Browser → CheckoutController → Stripe → WebhookController → Order `paid`
- File path: *(to be created)*
- Placement: Full width
- Reason: The Stripe webhook flow is technically complex and visually justifies the multi-controller design

---

**Slide 9 – Methodology & Tools**
- Suggested image: Screenshot of the `docs/` folder structure or a Git log showing migration commits
- File path: *(to be captured from terminal or IDE)*
- Placement: Side (right half)
- Reason: Demonstrates project discipline and documentation culture

---

**Slide 10 – Challenges & Solutions**
- Suggested image: Side-by-side before/after of a key decision (e.g., old Stripe flow vs. current Webhook flow)
- File path: *(to be created — simple 2-column diagram)*
- Placement: Full width
- Reason: Illustrates problem-solving and technical maturity

---

**Slide 11 – Demonstration**
- Suggested image: Screenshot of the EasyAdmin dashboard with Chart.js charts OR the Stripe success page
- File path: *(to be captured from running application at `/admin`)*
- Placement: Full width
- Reason: Most compelling live proof — shows both front-office and back-office delivery

---

**Slide 12 – RNCP Skills Mapping**
- Suggested image: Icons or badge grid for each skill domain (Symfony, Doctrine, Stripe, Docker, Twig, PHPStan)
- File path: *(to be assembled from vendor/technology logos — check licensing)*
- Placement: Grid layout (2×3 or 3×2 cells)
- Reason: Visual summary of the technology breadth covered by the project

---

**Final Slide – Conclusion**
- Suggested image: Hero product photo or final product screenshot
- File path: `public/products_images/rusty.png` or any visually strong product image
- Placement: Full width background, text overlay
- Reason: Closing on a product visual reinforces brand coherence and leaves a strong final impression

---

*Generated from analysis of 35+ Markdown documents in `docs/`. All technical information is sourced exclusively from project documentation.*

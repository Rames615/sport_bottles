# Tableau de bord Admin

## Accès

URL : `/admin`  
Rôle requis : `ROLE_ADMIN`

---

## Structure

### Contrôleur principal
`src/Controller/Admin/DashboardController.php`

- Route : `#[AdminDashboard(routePath: '/admin')]`
- Récupère les compteurs depuis la base via `EntityManagerInterface`
- Passe les variables au template `admin/dashboard.html.twig`

### Variables transmises au template

| Variable          | Source entity | Description              |
|-------------------|---------------|--------------------------|
| `userCount`       | `User`        | Nombre d'utilisateurs    |
| `productCount`    | `Product`     | Nombre de produits       |
| `categoryCount`   | `Category`    | Nombre de catégories     |
| `cartCount`       | `Cart`        | Nombre de paniers        |
| `orderCount`      | `Order`       | Nombre de commandes      |
| `promotionCount`  | `Promotion`   | Nombre de promotions     |

### Template
`templates/admin/dashboard.html.twig`

Les données sont lues depuis le `data-*` de `#adminDashboardData` par le script JS.

**Sections affichées :**
1. **Cartes de compteurs** — 6 tuiles cliquables (lien vers le CRUD correspondant)
2. **Graphique de répartition** — camembert généré par Chart.js (`canvas#overviewChart`)
3. **Statistiques détaillées** — 4 indicateurs calculés en Twig :
   - Total entités
   - Ratio commandes / paniers (taux de conversion)
   - Produits par catégorie
   - *(4ème indicateur custom)*
4. **Graphique en entonnoir** — `canvas#funnelChart`

### Script JS
`public/scripts/dashboard.js`

Lit les `data-*` du DOM et initialise les deux canvas Chart.js.

---

## CRUD disponibles (menu admin)

| Label          | Entité      | Contrôleur                        |
|----------------|-------------|-----------------------------------|
| Utilisateurs   | `User`      | `UserCrudController`              |
| Produits       | `Product`   | `ProductCrudController`           |
| Catégories     | `Category`  | `CategoryCrudController`          |
| Commandes      | `Order`     | `OrderCrudController`             |
| Promotions     | `Promotion` | `PromotionCrudController`         |

> Les paniers (`Cart`) sont accessibles via `/admin/cart` mais ne figurent pas dans le menu latéral EasyAdmin.

---

## Dépendances

- [EasyAdmin Bundle](https://symfony.com/bundles/EasyAdminBundle/current/index.html) — UI CRUD
- [Chart.js CDN](https://cdn.jsdelivr.net/npm/chart.js) — graphiques

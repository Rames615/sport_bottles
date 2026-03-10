# Product Component Architecture Guide

## File Structure

```
templates/product/
├── index.html.twig          # Main product listing page
└── _card.html.twig          # Reusable product card component

assets/styles/
└── product.css              # All product styling

public/
└── images_products/         # Product image storage
    ├── acier_inoxydable.png
    ├── glass_green.png
    ├── isothermique_blue.png
    ├── sky_blue_sans_bpa.png
    └── ... (9 total images)

src/
├── Controller/
│   └── ProductController.php # Data preparation & routing
├── Entity/
│   ├── Product.php          # Product model
│   └── Category.php         # Category model
└── Repository/
    └── ProductRepository.php # Database queries
```

## Data Flow

```
ProductController
    ↓
    ├── Loads all categories
    ├── Loads all products
    └── Organizes products by category
         ↓
         Passes to template:
         - allProducts
         - productsByCategory
         - categories
         ↓
         templates/product/index.html.twig
         ├── Renders category tabs
         ├── Renders "all products" section
         │   └── Loops through allProducts
         │       └── Includes _card.html.twig for each
         └── Renders per-category sections
             └── Loops through productsByCategory
                 └── Includes _card.html.twig for each
                    ↓
                    _card.html.twig
                    ├── Displays product image
                    ├── Shows designation & description
                    ├── Lists specs (capacity, temperature, category)
                    ├── Displays formatted price
                    └── Renders action buttons
```

## Product Card Structure (HTML)

```html
<div class="product-card" 
     data-category="..."
     data-price="..."
     data-capacity="..."
     data-temperature="...">
  
  <div class="product-image-wrapper">
    <!-- Product image with fallback -->
  </div>

  <div class="product-info">
    <h3 class="product-name">{{ product.designation }}</h3>
    
    <p class="product-description">{{ truncated description }}</p>
    
    <div class="product-specs">
      <div class="product-spec-item">
        <strong>Capacité:</strong> {{ product.capacity }}
      </div>
      <div class="product-spec-item">
        <strong>Température:</strong> {{ product.temperature }}
      </div>
      <div class="product-spec-item">
        <strong>Catégorie:</strong> {{ product.category.name }}
      </div>
    </div>

    <p class="product-price">{{ formatted price }}</p>
    
    <div class="product-actions">
      <button class="btn-add-cart">Ajouter au panier</button>
      <button class="btn-details">Voir détails</button>
    </div>
  </div>
</div>
```

## CSS Layout Hierarchy

```
.products-section
└── .products-container (grid: sidebar + main)
    ├── .filters-sidebar
    │   ├── .filters-title
    │   ├── .filter-group
    │   │   ├── .filter-group-title
    │   │   └── .filter-option (multiple)
    │   └── .filter-button-group
    │
    └── .products-main
        ├── .category-tabs
        │   └── .category-tab (multiple)
        │
        ├── .category-section (active: all)
        │   ├── h3 (section title)
        │   └── .products-grid
        │       └── .product-card (multiple)
        │
        └── .category-section (multiple)
            ├── h3 (section title)
            └── .products-grid
                └── .product-card (multiple)
```

## Entity Relationships

```
Category
├── id: int
├── name: string
├── slug: string
├── description: text
├── updatedAt: datetime
└── products: Collection<Product>  (One-to-Many)

Product
├── id: int
├── designation: string
├── description: text
├── price: decimal(10,2)
├── imgPath: string (image filename)
├── capacity: string
├── temperature: string|null
├── category: Category  (Many-to-One)
└── users: Collection<User>  (Many-to-Many)
```

## CSS Styling Approach

### Color Variables Used
```css
--primary-color: #1f7a63 (teal green)
--accent-color: (depends on variables.css)
--text-primary: (main text color)
--text-secondary: (secondary text color)
```

### Responsive Breakpoints

| Breakpoint | Grid Columns | Use Case |
|-----------|------------|----------|
| 1400px+ | 5-6 cards per row | Desktop |
| 1024px | 4-5 cards per row | Large tablet |
| 768px | 2 cards per row | Mobile landscape |
| < 768px | 2 cards per row | Mobile |

### Hover Effects Chain

1. **Card Elevation**
   - `transform: translateY(-8px)`
   - `box-shadow` increases

2. **Image Zoom**
   - `transform: scale(1.08)` on hover

3. **Border Highlight**
   - Border color changes to primary

4. **Button States**
   - "Add to Cart": Darkens on hover
   - "Details": Inverts colors on hover

## Image Loading Strategy

### Image Path Flow
```
Database (Product.imgPath)
  ↓
  "verre.png"
  ↓
Twig template processes:
  {{ asset('images_products/' ~ product.imgPath) }}
  ↓
Generated URL:
  /images_products/verre.png
  ↓
Browser requests from public folder:
  public/images_products/verre.png
  ↓
Display in product card
```

### Fallback Mechanism
```javascript
// In template:
<img 
  src="{{ asset('images_products/' ~ product.imgPath) }}" 
  onerror="this.src='{{ asset('images/placeholder.png') }}'">

// If imgPath image missing:
1. Browser tries to load image
2. If 404 error occurs
3. onerror event fires
4. Image source switches to placeholder
5. Placeholder displays instead
```

## Filtering System

### Filter Scope
- **Capacity Filter**: capacity-filter class
  - Values: 500ml, 750ml, 1L

- **Temperature Filter**: temperature-filter class
  - Values: 12h, 24h, 48h

- **Usage Filter**: usage-filter class (commented out in template)
  - Values: sport, hiking, office, kids

### Filter Application
```javascript
// 1. Get checked filters
const capacities = getCheckedValues('.capacity-filter')
const temperatures = getCheckedValues('.temperature-filter')

// 2. For each product in active section
// 3. Check if product matches ALL selected filters
// 4. Show matching products, hide non-matching
// 5. Show empty-state if no matches
```

### Data Attributes for Filtering
```html
<!-- Used for comparison in JavaScript -->
<div class="product-card" 
     data-capacity="500ml"
     data-temperature="12h"
     data-price="29.99">
```

## JavaScript Integration Points

### Event Listeners
```javascript
// Category tab switching
.category-tab -> addEventListener('click', switchCategory)

// Filter application
#applyFiltersBtn -> addEventListener('click', applyFilters)
#resetFiltersBtn -> addEventListener('click', resetFilters)

// Product actions (ready for your implementation)
.btn-add-cart -> data-product-id for cart functionality
.btn-details -> data-product-id for detail page
```

### Functions Available (public/scripts/product.js)
- `applyFilters()` - Apply selected filters to active category
- `resetFilters()` - Clear all filters and show all products
- `getCheckedValues(selector)` - Get array of checked filter values

## Performance Optimizations

1. **CSS Transforms**: Hardware accelerated
   - Use `transform` instead of `top/left`
   - Use `opacity` instead of `display` when possible

2. **Image Optimization**
   - `object-fit: contain` prevents image distortion
   - White padding area ensures proper product visibility
   - Fallback placeholder prevents broken image icons

3. **Grid Layout**
   - CSS Grid is more efficient than flexbox for complex layouts
   - Auto-fill with minmax allows responsive without media queries

4. **Minimal Repaints**
   - Hover effects use transforms (no layout changes)
   - Filtering uses `display: none` (single paint)

## Accessibility Considerations

1. **Image Alt Text**
   ```twig
   alt="{{ product.designation }}"
   ```

2. **Button Labels**
   - Clear, descriptive text
   - Data attributes don't rely on JavaScript for identification

3. **Color Contrast**
   - Primary color (#1f7a63) has sufficient contrast
   - Text colors meet WCAG standards

4. **Keyboard Navigation**
   - Form inputs are accessible
   - Buttons are focusable and activatable

## Testing Checklist

- [ ] All products display with correct images
- [ ] Category switching works without page reload
- [ ] Filters apply correctly to products
- [ ] Reset button clears all selections
- [ ] Product cards are responsive on mobile
- [ ] Hover effects work smoothly
- [ ] Missing images show placeholder
- [ ] Price formatting shows currency symbol
- [ ] Product specs display correctly
- [ ] Action buttons are properly aligned
- [ ] Empty category states display message

# Product Component - Quick Reference

## What Was Changed

### 1. Product Card Template (`templates/product/_card.html.twig`)
**Before:** Basic card with just name and price
**After:** Complete card with all product attributes

#### New Features:
✅ Image loading from `public/images_products/` folder
✅ Display of all product attributes:
   - Designation (name)
   - Description (truncated)
   - Capacity (500ml, 750ml, 1L)
   - Temperature maintenance (12h, 24h, 48h)
   - Category name
   - Formatted price
✅ Fallback placeholder for missing images
✅ Interactive buttons with data attributes

### 2. Product Card Styles (`assets/styles/product.css`)
**Enhanced:** Professional appearance with:

✅ **Image Container**
   - Height: 280px
   - Gradient background
   - Proper image scaling with `object-fit: contain`

✅ **Hover Animations**
   - Card lifts up: `translateY(-8px)`
   - Enhanced shadow
   - Image zoom: `scale(1.08)`
   - Border color change

✅ **Product Information**
   - Product specs in highlighted box
   - Clear typography hierarchy
   - Better spacing and padding

✅ **Responsive Design**
   - Adapts to mobile, tablet, desktop
   - Maintains usability on all devices

## File Changes Summary

### Modified Files:
1. **`templates/product/_card.html.twig`** - Complete rewrite
   - Added proper image path mapping
   - Added all product attributes
   - Added interactive buttons

2. **`assets/styles/product.css`** - Enhanced styling
   - Improved card appearance
   - Better hover effects
   - Enhanced responsive design

### No Changes Needed:
- `src/Controller/ProductController.php` ✓ (Already works correctly)
- `templates/product/index.html.twig` ✓ (Already uses _card.html.twig)
- `public/scripts/product.js` ✓ (Already handles filtering)

## How to Use

### Display Products
1. Products automatically load from database
2. Organized by category tabs
3. Can filter by capacity and temperature
4. Images load from files in `public/images_products/`

### Add New Product
1. Create product in admin/database with:
   - `designation` - Product name
   - `description` - Full description
   - `price` - Product price
   - `imgPath` - Filename (e.g., "verre.png")
   - `capacity` - Size (e.g., "500ml")
   - `temperature` - (optional) e.g., "12h"
   - `category` - Assign to category

2. Product appears automatically in:
   - "All Products" section
   - Category-specific section
   - Filterable by capacity/temperature

### Add Product Image
1. Save image to `public/images_products/`
2. Set product's `imgPath` to filename
3. Image displays in card with proper scaling

## Product Attributes

```
Product Fields:
├── id (auto)
├── designation (name) - DISPLAYS
├── description (text) - DISPLAYS (truncated)
├── price (10.2 decimal) - DISPLAYS (formatted)
├── imgPath (filename) - LOADS IMAGE
├── capacity (50 chars) - DISPLAYS
├── temperature (50 chars, optional) - DISPLAYS if set
├── category (FK) - DISPLAYS category name
└── users (M2M)
```

## Product Card Layout

```
┌─────────────────────────────────┐
│   PRODUCT IMAGE (280px height) │
│   (scales with object-fit)      │
├─────────────────────────────────┤
│ Product Name (max 2 lines)      │
│                                 │
│ Short description (2 lines max) │
│                                 │
│ ┌──────────────────────────────┐│
│ │ ✓ Capacité: 500ml           ││
│ │ ✓ Température: 12h          ││
│ │ ✓ Catégorie: Verres         ││
│ └──────────────────────────────┘│
│                                 │
│ 29,99 €                         │
│                                 │
│ ┌──────────────┐ ┌────────────┐│
│ │ Ajouter cart │ │ Voir détails││
│ └──────────────┘ └────────────┘│
└─────────────────────────────────┘
```

## Image Files Available

Located in `public/images_products/`:

| File | Description |
|------|-------------|
| acier_inoxydable.png | Stainless steel bottle |
| acier_inoxydable_vert.png | Green stainless steel |
| glass_green.png | Green glass |
| isothermique_blue.png | Blue thermal bottle |
| isothermique_green.png | Green thermal bottle |
| sky_blue_sans_bpa.png | Sky blue BPA-free |
| transparent_sans_bpa.png | Transparent BPA-free |
| verre.png | Plain glass |
| verre_blue.png | Blue glass |

## CSS Classes (For Custom Styling)

```css
.products-grid              /* Main grid container */
.product-card              /* Individual card */
.product-image-wrapper     /* Image area */
.product-image             /* Image element */
.product-info              /* Content area */
.product-name              /* Product title */
.product-description       /* Short description */
.product-specs             /* Specifications box */
.product-spec-item         /* Individual spec */
.product-price             /* Price display */
.product-actions           /* Buttons area */
.btn-add-cart              /* Add to cart button */
.btn-details               /* Details button */
```

## Responsive Breakpoints

```
Desktop (1024px+):  5-6 products per row
Tablet (768-1024):  3-4 products per row
Mobile (< 768px):   2 products per row
```

## Key Features

✅ **Category Organization**
   - Products grouped by category
   - Tab switching to view by category
   - "All Products" option available

✅ **Advanced Filtering**
   - Filter by capacity (500ml, 750ml, 1L)
   - Filter by temperature (12h, 24h, 48h)
   - Apply multiple filters simultaneously
   - Reset all filters at once

✅ **Professional Appearance**
   - Smooth hover animations
   - Proper image scaling
   - Clear typography
   - Responsive layout
   - Color-coded elements

✅ **Image Management**
   - Automatic image loading
   - Fallback placeholder
   - Proper aspect ratio handling
   - No broken image icons

✅ **Complete Information**
   - All product attributes visible
   - Formatted price with currency
   - Category identification
   - Size and specification info

## Customization Tips

### Change Card Width
Edit `assets/styles/product.css`:
```css
.products-grid {
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  /* Change 280px to desired width */
}
```

### Change Image Height
Edit `assets/styles/product.css`:
```css
.product-image-wrapper {
  height: 280px;  /* Adjust this value */
}
```

### Change Primary Color
Edit `assets/styles/variables.css`:
```css
--primary-color: #1f7a63;  /* Change this hex value */
```

### Add More Filters
Edit `templates/product/index.html.twig`:
```html
<div class="filter-group">
  <div class="filter-group-title">New Filter</div>
  <div class="filter-option">
    <input type="checkbox" value="value" class="new-filter">
    Label
  </div>
</div>
```

Then update JavaScript filter logic in `public/scripts/product.js`.

## Troubleshooting

**Problem: Images not showing**
- Check `imgPath` is set in database
- Verify file exists in `public/images_products/`
- Check browser console for 404 errors
- Placeholder should show as fallback

**Problem: Products not displaying**
- Verify products in database
- Check category assignments
- Check ProductController data passing
- Verify Twig template syntax

**Problem: Filters not working**
- Check checkbox values match data attributes
- Verify JavaScript file loaded
- Check browser console for errors
- Ensure category section is active

**Problem: Images distorted**
- Images use `object-fit: contain` (not crop)
- May show white padding around image
- This is correct behavior for different image sizes
- Adjust wrapper height if needed

## Development Notes

- **No Database Migrations**: Existing schema already supports all attributes
- **No Backend Changes**: ProductController already passes correct data
- **Template Only**: Main template already includes _card.html.twig correctly
- **CSS Pure**: All styling uses standard CSS, no processing required
- **JavaScript Ready**: Existing filtering logic works with new attributes

## Next Steps (Optional)

1. Add cart functionality to buttons
2. Create product detail page
3. Add product ratings/reviews
4. Implement wishlist feature
5. Add product search
6. Create admin product management
7. Add product comparisons
8. Implement stock status

# Product Component - Visual System Design

## Complete System Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    USER E-COMMERCE PRODUCT SYSTEM                            │
└─────────────────────────────────────────────────────────────────────────────┘

                              DATABASE LAYER
                                    │
                    ┌───────────────┼───────────────┐
                    │               │               │
              ┌─────▼────┐    ┌────▼────┐    ┌────▼────┐
              │ Category │    │ Product │    │  User   │
              │  Entity  │    │ Entity  │    │ Entity  │
              └──────────┘    └────┬────┘    └────┬────┘
                                   │              │
                            (ManyToOne)    (ManyToMany)
                                   │
                    ┌──────────────┘└──────────────┐
                    │                              │
            CONTROLLER LAYER                       │
                    │                              │
        ┌───────────▼────────────┐                │
        │  ProductController     │                │
        │  - findAll categories  │                │
        │  - findAll products    │                │
        │  - organize by category│                │
        │  - return templates    │                │
        └───────────┬────────────┘                │
                    │                              │
                    │  passes data                 │
                    │                              │
        ┌───────────▼──────────────────────┐      │
        │  TEMPLATE LAYER                   │      │
        │  templates/product/index.html.twig├──────┘
        │  - Sidebar with filters           │
        │  - Category tabs                  │
        │  - Includes _card.html.twig       │
        └───────────┬──────────────────────┘
                    │
        ┌───────────▼──────────────────┐
        │ _card.html.twig (component)   │
        │ - Product image               │
        │ - All attributes              │
        │ - Price formatted             │
        │ - Action buttons              │
        │ - Data attributes for JS      │
        └───────────┬──────────────────┘
                    │
        ┌───────────▼──────────────────┐
        │  CSS STYLING LAYER            │
        │  assets/styles/product.css    │
        │  - Grid layout                │
        │  - Card styling               │
        │  - Hover effects              │
        │  - Responsive design          │
        │  - Animations                 │
        └───────────┬──────────────────┘
                    │
        ┌───────────▼──────────────────┐
        │  JAVASCRIPT LAYER             │
        │  public/scripts/product.js    │
        │  - Category tab switching     │
        │  - Filtering logic            │
        │  - Button event handlers      │
        │  - DOM manipulation           │
        └───────────┬──────────────────┘
                    │
        ┌───────────▼──────────────────┐
        │  IMAGE STORAGE                │
        │  public/images_products/      │
        │  - 9 product images           │
        │  - Mapped via imgPath field   │
        │  - Fallback for missing       │
        └───────────────────────────────┘
```

---

## Data Flow Sequence

```
┌─────────┐
│ Browser │ Opens /product page
└────┬────┘
     │ HTTP GET request
     │
     ▼
┌─────────────────────────┐
│  ProductController      │ Handles request
│  (/product route)       │
└────┬────────────────────┘
     │ Queries database
     │
     ├─→ SELECT * FROM category
     │   WHERE... (loads all categories)
     │
     ├─→ SELECT * FROM product
     │   JOIN category... (loads all products with categories)
     │
     │ Organizes products
     │
     ├─→ Creates productsByCategory[slug]
     │   For each category, adds its products
     │
     ├─→ Creates allProducts array
     │   All products in flat array
     │
     ▼
┌──────────────────────────┐
│ Return to template with: │
│ - categories             │
│ - productsByCategory     │
│ - allProducts            │
└────┬─────────────────────┘
     │
     ▼
┌─────────────────────────────────┐
│ index.html.twig renders:        │
│                                 │
│ 1. Sidebar filters              │
│ 2. Category tabs for each       │
│ 3. "All products" section       │
│    └─ Loops allProducts         │
│       └─ Includes _card.html    │
│ 4. Per-category sections        │
│    └─ Loops productsByCategory  │
│       └─ Includes _card.html    │
└────┬────────────────────────────┘
     │
     ▼
┌──────────────────────────────┐
│ _card.html.twig for each:   │
│                              │
│ <div class="product-card">  │
│   ├─ Image from imgPath     │
│   ├─ Designation            │
│   ├─ Description            │
│   ├─ Specs box              │
│   │  ├─ Capacity            │
│   │  ├─ Temperature         │
│   │  └─ Category            │
│   ├─ Price                  │
│   └─ Buttons                │
│                              │
│ CSS classes applied         │
│ Data attributes for JS      │
└────┬───────────────────────┘
     │
     ▼
┌──────────────────────────┐
│ product.css applies:     │
│                          │
│ .product-card           │
│ .product-image-wrapper  │
│ .product-image          │
│ .product-info           │
│ .product-price          │
│ .btn-add-cart           │
│ .btn-details            │
│ etc...                  │
└────┬─────────────────────┘
     │
     ▼
┌──────────────────────────────┐
│ Rendered HTML sent to:       │
│ Browser                      │
│                              │
│ Static Product Cards display │
└────┬───────────────────────┘
     │
     │ Browser loads product.js
     │
     ▼
┌────────────────────────────────┐
│ product.js handles:            │
│                                │
│ - Tab click → switch category  │
│ - Filter clicks → collect vals │
│ - Apply btn → filter products  │
│ - Reset btn → show all         │
│ - Card buttons → ready for     │
│   cart/detail integration      │
└────────────────────────────────┘
```

---

## Product Card Component Structure

```
┌─────────────────────────────────────────┐
│      Product Card Component             │
│  (_card.html.twig)                      │
└─────────────────────────────────────────┘
            │
    ┌───────┴────────┐
    │                │
    ▼                ▼
┌────────┐     ┌──────────────┐
│ Images │     │ Product Info │
└────┬───┘     └──────┬───────┘
     │                │
     │ From:          ├─ Designation
     │ imgPath        ├─ Description
     │ field          ├─ Specifications
     │                │  ├─ Capacity
     │ Maps to:       │  ├─ Temperature
     │ public/        │  └─ Category
     │ images_        ├─ Price
     │ products/      └─ Action Buttons
     │
     ├─ Try load image file
     │  │
     │  ├─ If found → Display
     │  │
     │  └─ If not found → onerror event
     │     └─ Load placeholder
     │
     └─ image.png (9 available)
```

---

## Product Card Rendering

```
PRODUCT CARD (HTML Structure)
┌───────────────────────────────────────────┐
│ <div class="product-card">                │
│   data-category="verres"                  │
│   data-price="29.99"                      │
│   data-capacity="500ml"                   │
│   data-temperature="12h"                  │
│                                           │
│   ┌─────────────────────────────────────┐ │
│   │ <div class="product-image-wrapper"> │ │  Height: 280px
│   │   <img src="images_products/...">   │ │  Gradient BG
│   │ </div>                              │ │  object-fit: contain
│   └─────────────────────────────────────┘ │
│                                           │
│   ┌─────────────────────────────────────┐ │
│   │ <div class="product-info">          │ │
│   │                                     │ │
│   │   <h3 class="product-name">        │ │
│   │   Product Name (2 lines max)       │ │
│   │   </h3>                            │ │
│   │                                     │ │
│   │   <p class="product-description">  │ │
│   │   Short description (2 lines)...   │ │
│   │   </p>                             │ │
│   │                                     │ │
│   │   ┌───────────────────────────┐    │ │
│   │   │ <div class="product-specs">   │ │
│   │   │   ✓ Capacity: 500ml        │ │
│   │   │   ✓ Temperature: 12h       │ │
│   │   │   ✓ Category: Verres       │ │
│   │   │ </div>                     │ │
│   │   └───────────────────────────┘    │ │
│   │                                     │ │
│   │   <p class="product-price">        │ │
│   │   29,99 €                          │ │
│   │   </p>                             │ │
│   │                                     │ │
│   │   <div class="product-actions">    │ │
│   │     ┌──────────────────────────┐  │ │
│   │     │ Ajouter au panier        │  │ │
│   │     └──────────────────────────┘  │ │
│   │     ┌──────────────────────────┐  │ │
│   │     │ Voir détails             │  │ │
│   │     └──────────────────────────┘  │ │
│   │   </div>                          │ │
│   │                                     │ │
│   └─────────────────────────────────────┘ │
│                                           │
└───────────────────────────────────────────┘

APPLIED CSS STYLING
├─ Border: 1px solid #E2E8F0
├─ Border-radius: 12px
├─ Box-shadow: 0 2px 8px rgba(0,0,0,0.08)
├─ Display: flex, flex-direction: column
├─ Height: 100% (fills grid cell)
├─ Transition: all 0.3s ease
│
├─ On Hover:
│  ├─ transform: translateY(-8px)
│  ├─ box-shadow: 0 16px 32px rgba(0,0,0,0.15)
│  ├─ border-color: var(--primary-color)
│  │
│  └─ Image:
│     └─ transform: scale(1.08)
│
├─ Image properties:
│  ├─ height: 280px
│  ├─ background: linear-gradient(...)
│  ├─ object-fit: contain
│  ├─ padding: 15px
│
├─ Info section:
│  ├─ flex-grow: 1 (fills space)
│  ├─ padding: 20px
│  ├─ gap: 12px
│  │
│  └─ Specs box:
│     ├─ background: #f8f9fa
│     ├─ border-radius: 8px
│     ├─ padding: 12px
│
├─ Buttons:
│  ├─ display: flex, gap: 10px
│  │
│  ├─ Add to Cart:
│  │  ├─ background: primary-color
│  │  ├─ color: white
│  │  ├─ flex: 1
│  │  └─ On Hover: darker + elevated
│  │
│  └─ Details:
│     ├─ background: transparent
│     ├─ border: 2px solid primary-color
│     ├─ color: primary-color
│     └─ On Hover: inverts colors + elevated
```

---

## Category Organization

```
PRODUCTS GROUPED BY CATEGORY

Products Database:
┌─────────────────────────────────────┐
│ id│ designation   │ category_id │   │
├──┼───────────────┼─────────────┤   │
│1 │ Verre Bleu    │ 1 (verres)  │   │
│2 │ Acier inox    │ 2 (steel)   │   │
│3 │ Verre Vert    │ 1 (verres)  │   │
│4 │ Isotherme Bleu│ 3 (thermal) │   │
│5 │ Sans BPA      │ 2 (steel)   │   │
└─────────────────────────────────────┘

Controller Transformation:
         ↓
productsByCategory = {
  "verres" => {
    "category" => Category(id=1, name="Verres"),
    "products" => [Product(1), Product(3)]
  },
  "steel" => {
    "category" => Category(id=2, name="Acier"),
    "products" => [Product(2), Product(5)]
  },
  "thermal" => {
    "category" => Category(id=3, name="Isotherme"),
    "products" => [Product(4)]
  }
}

allProducts = [Product(1), Product(2), Product(3), Product(4), Product(5)]

         ↓
Template Rendering:
┌─────────────────────────────────────┐
│ "Tous les produits" Tab (ACTIVE)    │
│ ┌───────────────────────────────────┤
│ │ [Card 1] [Card 2] [Card 3]        │
│ │ [Card 4] [Card 5]                 │
│ └───────────────────────────────────┤
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ "Verres" Tab (INACTIVE)             │
│ ┌───────────────────────────────────┤
│ │ [Card 1] [Card 3]                 │
│ └───────────────────────────────────┤
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ "Acier" Tab (INACTIVE)              │
│ ┌───────────────────────────────────┤
│ │ [Card 2] [Card 5]                 │
│ └───────────────────────────────────┤
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ "Isotherme" Tab (INACTIVE)          │
│ ┌───────────────────────────────────┤
│ │ [Card 4]                          │
│ └───────────────────────────────────┤
└─────────────────────────────────────┘

         ↓ (JavaScript handles tab switching)

User clicks "Acier" tab:
- Hide all sections except "acier"
- Show cards 2 and 5
- Apply active styling to tab
- Apply filters if any checked
```

---

## Image Mapping Process

```
PRODUCT IMAGE LOADING PROCESS

Database Layer:
┌────────────────────────────────────┐
│ Product Record                      │
├────────────────────────────────────┤
│ id: 1                               │
│ designation: "Verre Bleu"           │
│ imgPath: "verre_blue.png" ◄─────┐  │
│ ...                              │  │
└────────────────────────────────────┘  │
                                        │
Twig Template Processing:               │
┌────────────────────────────────────┐  │
│ {{ asset('images_products/' ~     │  │
│   product.imgPath) }}             │  │
└────────────────────────────────────┘  │
         │                              │
         │ Concatenates:                │
         │ 'images_products/' + imgPath │
         │                              │
         ▼                              │
┌────────────────────────────────────┐  │
│ Generated URL:                      │  │
│ /images_products/verre_blue.png ◄──┘  │
└────────────────────────────────────┘
         │
         ▼
Browser HTTP Request:
GET /images_products/verre_blue.png

         │
         ▼
File System Lookup:
public/images_products/verre_blue.png

         │
         ├─ File EXISTS ──┐
         │                 ▼
         │          Load & Display
         │          ✓ Image shows
         │
         └─ File NOT FOUND
                  │
                  ▼
           404 Error
                  │
                  ▼
           onerror event fires:
           this.src='images/placeholder.png'
                  │
                  ▼
           Load placeholder:
           public/images/placeholder.png
                  │
                  ▼
           Display placeholder
           ✓ No broken image icon
```

---

## Responsive Layout Diagram

```
DESKTOP (1400px+)
┌──────────┬─────────────────────────────────────────────┐
│ FILTERS  │ [TAB] [TAB] [TAB] [TAB] [TAB]              │
│ ─────────┤ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐
│ Capacity │ │Card1 │ │Card2 │ │Card3 │ │Card4 │ │Card5 │
│ □ 500ml  │ │      │ │      │ │      │ │      │ │      │
│ □ 750ml  │ │      │ │      │ │      │ │      │ │      │
│ □ 1L     │ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘
│ ─────────┤ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐
│ Temp     │ │Card6 │ │Card7 │ │Card8 │ │Card9 │
│ □ 12h    │ │      │ │      │ │      │ │      │
│ □ 24h    │ │      │ │      │ │      │ │      │
│ □ 48h    │ └──────┘ └──────┘ └──────┘ └──────┘
│ ─────────┤
│ [Apply]  │
│ [Reset]  │
└──────────┴─────────────────────────────────────────────┘
  280px              auto-fill, 5-6 cards per row


TABLET (768-1024px)
┌──────────┬──────────────────────────────┐
│ FILTERS  │ [TAB] [TAB] [TAB] [TAB]     │
│ ─────────┤ ┌──────┐ ┌──────┐ ┌──────┐  
│ Capacity │ │Card1 │ │Card2 │ │Card3 │  
│ □ 500ml  │ │      │ │      │ │      │  
│ □ 750ml  │ │      │ │      │ │      │  
│ □ 1L     │ └──────┘ └──────┘ └──────┘  
│ ─────────┤ ┌──────┐ ┌──────┐ ┌──────┐  
│ Temp     │ │Card4 │ │Card5 │ │Card6 │  
│ □ 12h    │ │      │ │      │ │      │  
│ □ 24h    │ │      │ │      │ │      │  
│ □ 48h    │ └──────┘ └──────┘ └──────┘  
│ ─────────┤
│ [Apply]  │
│ [Reset]  │
└──────────┴──────────────────────────────┘
  250px      auto-fill, 3-4 cards


MOBILE (< 768px)
┌──────────────────────────┐
│ [FILTERS BUTTON ⚙️]      │
│ [TAB][TAB][TAB][TAB]    │
│ ┌────────────────────┐  │
│ │ Card 1             │  │
│ │                    │  │
│ │ [Add] [Details]    │  │
│ └────────────────────┘  │
│ ┌────────────────────┐  │
│ │ Card 2             │  │
│ │                    │  │
│ │ [Add] [Details]    │  │
│ └────────────────────┘  │
│ ┌────────────────────┐  │
│ │ Card 3             │  │
│ │                    │  │
│ │ [Add] [Details]    │  │
│ └────────────────────┘  │
└──────────────────────────┘
   Full width, 2-column grid
   Filters hidden/toggle
```

---

## Filter Application Flow

```
User Interface:
┌──────────────────────────────┐
│ Filter Options               │
├──────────────────────────────┤
│ Capacity                     │
│ ☐ 500ml ─────┐              │
│ ☑ 750ml      │ User checks  │
│ ☐ 1L    ─────┤              │
│              │              │
│ Temperature  │              │
│ ☐ 12h   ─────┘              │
│ ☑ 24h        │ User checks  │
│ ☐ 48h   ─────┤              │
│              │              │
│ [Apply Filters] ─────┐      │
│ [Reset Filters]      │      │
└──────────────────────────────┘
                       │
                       ▼
JavaScript Processing:
const capacities = ['750ml']    ◄── Checked values
const temperatures = ['24h']    ◄── Checked values

For each product in active category:
  ├─ Does product.capacity match ANY selected? 
  │  └─ YES → keep for next check
  │  └─ NO  → hide product
  │
  ├─ Does product.temperature match ANY selected?
  │  └─ YES → show product
  │  └─ NO  → hide product

Result: Only products with (750ml OR null) AND (24h) display

                       ▼
DOM Updates:
┌─────────────────────────────────┐
│ Category Section (visible)       │
│ ┌──────────────────────────────┐│
│ │ Card A: 750ml, 24h ✓        ││ VISIBLE
│ └──────────────────────────────┘│
│ ┌──────────────────────────────┐│
│ │ Card E: 750ml, 24h ✓        ││ VISIBLE
│ └──────────────────────────────┘│
│ ┌──────────────────────────────┐│
│ │ Card B: 500ml, 12h ✗        ││ HIDDEN (display:none)
│ └──────────────────────────────┘│
│ ┌──────────────────────────────┐│
│ │ Card C: 500ml, 24h ✗        ││ HIDDEN (capacity 500ml)
│ └──────────────────────────────┘│
│ ┌──────────────────────────────┐│
│ │ Card D: 750ml, 12h ✗        ││ HIDDEN (temperature 12h)
│ └──────────────────────────────┘│
│                                  │
│ (If no products match):          │
│ "Aucun produit correspond..."    │
└─────────────────────────────────┘
```

---

## Button Click Handlers Ready

```
Product Action Buttons - JavaScript Ready

┌─────────────────────────────────────────┐
│ <button class="btn-add-cart"            │
│         data-product-id="1">            │
│   Ajouter au panier                     │
│ </button>                               │
└──────────────┬────────────────┬─────────┘
               │                │
       Event Ready      Product ID Attached
       (ready for          (data-product-id)
        cart logic)
               │                │
               ▼                ▼
        Listen for       Retrieve ID
        click event      when button
                        clicked

        Example Handler (to be created):
        document.addEventListener('click', (e) => {
          if (e.target.classList.contains('btn-add-cart')) {
            const productId = e.target.dataset.productId;
            // Add to cart logic here
            // POST /cart/add
            // payload: { product_id: productId }
          }
        });

┌─────────────────────────────────────────┐
│ <button class="btn-details"             │
│         data-product-id="1">            │
│   Voir détails                          │
│ </button>                               │
└──────────────┬────────────────┬─────────┘
               │                │
       Event Ready      Product ID Attached
       (ready for          (data-product-id)
        detail page)
               │                │
               ▼                ▼
        Listen for       Retrieve ID
        click event      when button
                        clicked

        Example Handler (to be created):
        document.addEventListener('click', (e) => {
          if (e.target.classList.contains('btn-details')) {
            const productId = e.target.dataset.productId;
            // Navigate to detail page
            // window.location.href = `/product/${productId}`;
          }
        });
```

---

## Implementation Summary Diagram

```
COMPLETE PRODUCT COMPONENT SYSTEM

┌─────────────────────────────────────────────────────────────┐
│                    PRODUCT LISTING PAGE                      │
│                                                              │
│  Database → Controller → Template → CSS → JavaScript         │
│     │          │            │         │         │           │
│     │          │            │         │         │           │
│  Products  Organizes   Renders    Styles   Handles          │
│  stored    by category   cards    beautifully interactions   │
│  in DB     into arrays   with     and                        │
│            for template  all      responsively              │
│                          info                               │
│                                                              │
└─────────────────────────────────────────────────────────────┘

RESULT: Professional product listing with:
✓ 9 product images loaded correctly
✓ All attributes displayed (name, desc, specs, price)
✓ Category organization
✓ Advanced filtering (capacity, temperature)
✓ Responsive design (desktop, tablet, mobile)
✓ Smooth animations and interactions
✓ Ready for cart/detail integration
✓ Accessible and SEO-friendly
✓ Performance optimized
✓ Well documented
```

---

This visual system design shows how all components work together to create a seamless product browsing experience.

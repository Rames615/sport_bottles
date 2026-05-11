# Carousel Component Documentation

## Overview

The carousel component is a reusable, infinite-scroll carousel with automatic playback and hover pause functionality. It's fully responsive and supports touch gestures.

## Features

- ✨ **Infinite Loop**: Seamlessly scrolls through items in a continuous loop
- ⏸️ **Hover Pause**: Automatically pauses when hovering over the carousel
- 📱 **Responsive**: Automatically adjusts to different screen sizes
- 👆 **Touch Support**: Swipe gestures on mobile devices
- ♿ **Accessible**: Includes ARIA labels for screen readers
- ⚡ **Reusable**: Built as a modular component

## Files

- `templates/components/_carousel.html.twig` - Main carousel Twig template
- `templates/components/_reassurance_card.html.twig` - Example card component
- `assets/controllers/carousel_controller.js` - JavaScript carousel logic
- `assets/styles/carousel.css` - Carousel styling

## Basic Usage

### Direct HTML Implementation

If you want to use the carousel directly in a template without including the component:

```twig
<div class="carousel-wrapper" 
     data-carousel-id="my-carousel" 
     data-items-per-view="4" 
     data-gap="20" 
     data-autoplay-speed="4000">
    <div class="carousel-container" role="region" aria-label="Carrousel">
        <div class="carousel-track">
            <div class="carousel-item">Item 1</div>
            <div class="carousel-item">Item 2</div>
            <div class="carousel-item">Item 3</div>
            <div class="carousel-item">Item 4</div>
        </div>
    </div>
</div>
```

### Using the Component Include

Alternatively, use the reusable carousel component:

```twig
{% set items = [
    { content: '<div>Item 1</div>' },
    { content: '<div>Item 2</div>' },
    { content: '<div>Item 3</div>' },
    { content: '<div>Item 4</div>' }
] %}

{% include 'components/_carousel.html.twig' with {
    items: items,
    itemsPerView: 4,
    gap: 20,
    autoplaySpeed: 4000,
    carouselId: 'my-carousel'
} only %}
```

## Configuration

### Data Attributes

- `data-carousel-id` (required): Unique identifier for the carousel
- `data-items-per-view` (default: 4): Number of items visible at once
- `data-gap` (default: 20): Gap between items in pixels
- `data-autoplay-speed` (default: 3000): Milliseconds between auto-play slides

### Example

```html
<div class="carousel-wrapper" 
     data-carousel-id="products" 
     data-items-per-view="3" 
     data-gap="25" 
     data-autoplay-speed="5000">
    ...
</div>
```

## Real-World Example: Reassurance Section

The reassurance section in the home page uses the carousel with custom cards:

```twig
<section class="w-full pt-8 pb-10 px-5">
    <h2 class="text-[1.5rem] font-bold text-main mb-8 text-center">Nos garanties</h2>
    <div class="max-w-[1200px] mx-auto">
        {% set reassuranceItems = [
            {
                icon: '🚚',
                title: 'Livraison rapide',
                description: 'Expédié sous 24 h'
            },
            {
                icon: '🔄',
                title: 'Retours faciles',
                description: '30 jours offerts'
            },
            {
                icon: '🔒',
                title: 'Paiement sécurisé',
                description: 'Transactions sécurisées'
            },
            {
                icon: '🌱',
                title: 'Écoresponsable',
                description: 'Emballages recyclés'
            }
        ] %}

        <div class="carousel-wrapper" 
             data-carousel-id="reassurance-carousel" 
             data-items-per-view="4" 
             data-gap="20" 
             data-autoplay-speed="4000">
            <div class="carousel-container" role="region" aria-label="Carrousel des garanties">
                <div class="carousel-track">
                    {% for item in reassuranceItems %}
                        <div class="carousel-item">
                            {% include 'components/_reassurance_card.html.twig' with item only %}
                        </div>
                    {% endfor %}
                </div>
            </div>
        </div>
    </div>
</section>
```

## Responsive Behavior

The carousel automatically handles responsive layouts. You can customize item visibility at different breakpoints by adjusting the `data-items-per-view` attribute.

### Mobile-First Approach

Start with a smaller `items-per-view` for mobile and increase for larger screens using Twig conditionals:

```twig
<div class="carousel-wrapper" 
     data-items-per-view="{% if responsive %}2{% else %}4{% endif %}">
```

## Styling

The carousel includes default CSS styling in `assets/styles/carousel.css`. Customize as needed:

```css
.carousel-wrapper {
    width: 100%;
    overflow: hidden;
    position: relative;
}

.carousel-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
}
```

## JavaScript Events

The carousel automatically initializes on `DOMContentLoaded`. Multiple carousels can exist on the same page and work independently.

### Custom Initialization

If you need to initialize a carousel programmatically after dynamic content is added:

```javascript
import CarouselController from './carousel_controller.js';

const carouselElement = document.querySelector('.carousel-wrapper');
const carousel = new CarouselController(carouselElement);
```

## Touch & Swipe Support

The carousel supports touch gestures:
- **Swipe Left**: Next item
- **Swipe Right**: Previous item
- **Pause on Touch**: Carousel pauses while swiping

## Accessibility

- ARIA labels for screen readers: `role="region"` and `aria-label`
- Keyboard-friendly HTML structure
- Works with assistive technologies

## Common Use Cases

1. **Product Carousel**: Display multiple products in a scrolling list
2. **Testimonials**: Show customer reviews in a carousel
3. **Features**: Highlight product features with cards (like reassurance section)
4. **Images**: Gallery or image showcase
5. **Partner Logos**: Brand showcase

## Troubleshooting

### Carousel not initializing
- Ensure `carousel_controller.js` is imported in `assets/app.js`
- Verify carousel CSS is imported: `import './styles/carousel.css'`
- Check browser console for errors

### Items not displaying correctly
- Verify `data-carousel-id` is unique
- Check that `carousel-item` divs exist in the track
- Ensure container has defined width

### Animation stuttering
- Check for CSS conflicts
- Verify `will-change: transform` is not being overridden
- Test in a different browser

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Future Enhancements

- Arrow navigation buttons
- Dot indicators for current position
- Keyboard arrow navigation
- Variable item sizes
- Lazy loading for items

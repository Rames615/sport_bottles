/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.html.twig',
    './assets/**/*.js',
    // public/scripts are NOT under assets/ — must be listed explicitly
    './public/scripts/**/*.js',
  ],

  /**
   * IMPORTANT — Preflight disabled during Bootstrap coexistence (Phase 2/3 transition).
   * Tailwind's Preflight (base reset) conflicts with Bootstrap's Reboot styles.
   * Re-enable this (delete the corePlugins block) once:
   *   1. The Bootstrap CDN <link> is removed from templates/base.html.twig
   *   Bootstrap CSS CDN has been removed (Phase 3 complete).
   *   Preflight is now enabled — Tailwind's base reset handles normalization.
   */
  /* corePlugins: { preflight: false }, — REMOVED: Bootstrap CSS is gone */

  safelist: [
    /**
     * JS-toggled classes — Tailwind JIT cannot detect these because they
     * are set via classList.add() / className assignment at runtime.
     * Sources documented in assets/styles/app.css (@layer components section).
     */

    // cart.js: classList.add/remove('cart-badge-visible'), classList.add/remove('badge-pop')
    'cart-badge-visible',
    'badge-pop',

    // hero.js + cart.js: el.className = `hero-toast alert alert-${type}`
    'hero-toast',
    'cart-toast',

    // product-filters.js: createElement + className = 'no-products-message alert alert-info'
    'no-products-message',

    // product.js: classList.add/remove('active') on .category-tab and .category-section
    // payment page JS: classList toggle 'active' on .method-tab and .alt-panel
    'active',

    // cart.js line 32: document.querySelector('a.nav-cart-link')
    // Must remain on the cart anchor in nav.html.twig after Phase 3 nav migration.
    'nav-cart-link',

    // product.js lines 36/37/48: querySelectorAll('.category-tab') / querySelectorAll('.category-section')
    // Must remain on tab and section elements in product templates after Phase 3.
    'category-tab',
    'category-section',

    // product.js lines 81/91/102/106: querySelectorAll('.product-card') / querySelector('.empty-category')
    // Must remain on card and empty-state elements in product templates after Phase 3.
    'product-card',
    'empty-category',

    // product-filters.js line 17: document.querySelector('.products-grid')
    // Must remain on the product grid container in product templates after Phase 3.
    'products-grid',

    // product-filters.js lines 83-85: querySelector('.product-name') — used as sort key
    // Must remain on the product title element so name-asc/name-desc sort works.
    'product-name',

    // product.js line 12: filtersSidebar.classList.toggle('open')
    // Mobile filter sidebar state class. Defined in @layer components (app.css).
    // Must remain on #filtersSidebar in product templates after Phase 3.
    'open',

    // product-card.js: classList.add/remove('hidden') on .product-desc-short
    'hidden',
    // nav.html.twig: hamburger toggler must be hidden on lg+ screens
    'lg:hidden',
    // product-card.js: max-h-0 / overflow-hidden / transition-all on .product-desc-full
    'max-h-0',

    // product-filters.js: promo toggle button active state classes
    'border-primary',
    'bg-primary/10',
    'text-primary',
    'border-[#dee2e6]',
  ],

  theme: {
    extend: {
      /**
       * Colors — extracted from CSS variables in variables.css and payment.css (Phase 1D).
       * Usage examples: bg-primary, text-secondary, border-accent, bg-pm-bg-alt, etc.
       */
      colors: {
        primary:         '#1F7A63',   // --primary-color  (nav, buttons, links, badges)
        secondary:       '#0F172A',   // --secondary-color (navbar bg, footer bg)
        accent:          '#22C55E',   // --accent-color   (hover highlights)
        'text-main':     '#0F172A',   // --text-primary   (body text, headings)
        'text-muted':    '#64748B',   // --text-secondary (subtitles, captions, labels)
        'body-bg':       '#F0F9F5',   // body background (light green tint)

        // Green palette — payment page + global emphasis
        'green-dark':    '#176E3B',   // --green-dark  (emphasis, totals, focus rings)
        'green-mid':     '#198443',   // --green       (interactive elements)
        'green-light':   '#C8ECD8',   // --green-light (tint fills, total border)
        'green-xlight':  '#EDFAF3',   // --green-xlight (very soft background tint)

        // Payment page surface palette
        'pm-bg':         '#F4FAF7',   // --bg          (payment page background)
        'pm-bg-alt':     '#EAF5EF',   // --bg-alt      (subtle tint areas)
        'pm-surface':    '#FFFFFF',   // --surface     (card/panel surfaces)
        'pm-surface-2':  '#EAF5EF',   // --surface2    (inset blocks)
        'pm-text':       '#1A2E23',   // --text        (primary text on payment page)
        'pm-text-mid':   '#2D4A38',   // --text-mid    (secondary text)
        'pm-muted':      '#4A7060',   // --muted       (supporting text)

        // Stripe badge
        'stripe':        '#5753E0',   // --stripe      (badge text)
        'stripe-light':  '#EEECFF',   // --stripe-light (badge background)

        // Hero CTA gradient endpoints
        'hero-from':     '#00FF99',
        'hero-to':       '#00D4FF',

        // Semantic overrides (product.css defines its own --success-color)
        'success-alt':   '#28A745',   // product price color (differs from Bootstrap's green)
        'error':         '#B91C1C',   // --error (payment form validation)
      },

      /**
       * Font families — from variables.css (body/headings) and payment.css (display/mono).
       * Usage: font-sans (body), font-display (headings), font-syne (payment titles),
       *        font-dm (payment body), font-mono (IBAN values)
       */
      fontFamily: {
        sans:    ['Inter', 'ui-sans-serif', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'sans-serif'],
        display: ['Poppins', 'Inter', 'sans-serif'],
        syne:    ['Syne', 'sans-serif'],
        dm:      ['"DM Sans"', 'sans-serif'],
        mono:    ['"DM Mono"', 'monospace'],
      },

      /**
       * Box shadows — custom green-tinted shadows from the existing design.
       * Usage: shadow-green-sm, shadow-green-md, shadow-green-lg, shadow-badge, shadow-cta
       */
      boxShadow: {
        'green-sm':   '0 1px 4px rgba(34, 139, 87, 0.08)',
        'green-md':   '0 4px 16px rgba(34, 139, 87, 0.12)',
        'green-lg':   '0 8px 32px rgba(34, 139, 87, 0.16)',
        'badge':      '0 2px 6px rgba(31, 122, 99, 0.5)',
        'cta':        '0 8px 30px rgba(0, 255, 153, 0.25)',
        'cta-hover':  '0 12px 40px rgba(0, 255, 153, 0.45)',
      },

      /**
       * Border radius — the payment.css design token (--radius: 14px).
       * Usage: rounded-token
       */
      borderRadius: {
        'token': '14px',
      },

      /**
       * Keyframes — all custom animations from the existing CSS files.
       * JS-driven animations (badge-pop, slide-right) are also in the safelist above.
       */
      keyframes: {
        // Decorative floating bottle background (variables.css)
        bgFloat: {
          '0%, 100%': { transform: 'var(--rot) translateY(0px)' },
          '50%':       { transform: 'var(--rot) translateY(-18px)' },
        },
        // Hero content wrapper float (hero-section.css)
        floatHero: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%':       { transform: 'translateY(-10px)' },
        },
        // Cart badge spring animation — triggered by cart.js (nav.css)
        'badge-pop': {
          '0%':   { transform: 'scale(1)' },
          '38%':  { transform: 'scale(1.50)' },
          '68%':  { transform: 'scale(0.88)' },
          '85%':  { transform: 'scale(1.10)' },
          '100%': { transform: 'scale(1)' },
        },
        // Product modal slide-in (modal.css)
        modalSlideIn: {
          from: { opacity: '0', transform: 'translateY(-20px)' },
          to:   { opacity: '1', transform: 'translateY(0)' },
        },
        // Toast notifications — triggered by hero.js / cart.js (ui-components.css)
        slideInRight: {
          from: { transform: 'translateX(400px)', opacity: '0' },
          to:   { transform: 'translateX(0)',     opacity: '1' },
        },
        slideOutRight: {
          from: { transform: 'translateX(0)',     opacity: '1' },
          to:   { transform: 'translateX(400px)', opacity: '0' },
        },
        // Registration card entry (registration.css)
        slideUp: {
          from: { opacity: '0', transform: 'translateY(20px)' },
          to:   { opacity: '1', transform: 'translateY(0)' },
        },
        // Promotion section title icon (promotion.css)
        pulseIcon: {
          '0%, 100%': { transform: 'scale(1)' },
          '50%':       { transform: 'scale(1.1)' },
        },
        // Hero feature badges — staggered fade + slide up
        fadeSlideUp: {
          from: { opacity: '0', transform: 'translateY(20px)' },
          to:   { opacity: '1', transform: 'translateY(0)' },
        },
      },

      animation: {
        'bg-float':    'bgFloat 9s ease-in-out infinite',
        'float-hero':  'floatHero 6s ease-in-out infinite',
        'badge-pop':   'badge-pop 0.38s cubic-bezier(0.34, 1.56, 0.64, 1) forwards',
        'modal-in':    'modalSlideIn 0.3s ease-out',
        'slide-right': 'slideInRight 0.3s ease-out',
        'slide-up':    'slideUp 0.4s ease-out',
        'pulse-icon':  'pulseIcon 2s ease-in-out infinite',
      },
    },
  },

  plugins: [],
};

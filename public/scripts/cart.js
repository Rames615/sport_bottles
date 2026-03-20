'use strict';

/**
 * cart.js – Real-time cart badge + AJAX add-to-cart interception.
 *
 * Loaded with data-turbo-eval="false" so this script executes only once
 * per hard page load and event listeners are never duplicated.
 *
 * Exposes:
 *   window.updateCartBadge(count)  — called by hero.js and internally
 */

// ── Badge updater ─────────────────────────────────────────────────────────────
window.updateCartBadge = function (count) {
    const badge = document.getElementById('cart-count-badge');
    if (!badge) return;

    const n = parseInt(count, 10) || 0;
    badge.textContent = n > 0 ? String(n) : '';

    if (n > 0) {
        badge.classList.add('cart-badge-visible');
        // Re-trigger pop animation on each update
        badge.classList.remove('badge-pop');
        void badge.offsetWidth; // force reflow
        badge.classList.add('badge-pop');
    } else {
        badge.classList.remove('cart-badge-visible');
    }

    // Sync tooltip on the cart nav link
    const cartLink = document.querySelector('a.nav-cart-link');
    if (cartLink) {
        cartLink.title = n > 0
            ? `${n} article${n > 1 ? 's' : ''} dans le panier`
            : 'Mon panier';
    }
};

// ── Helpers ───────────────────────────────────────────────────────────────────
function _getCartCsrf() {
    const meta = document.querySelector('meta[name="csrf-cart-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function _showCartToast(message, type) {
    try {
        const el = document.createElement('div');
        el.className = `cart-toast ${type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : type === 'danger' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-blue-50 border border-blue-200 text-blue-700'} rounded-xl px-4 py-3 text-sm font-medium shadow-lg`;
        el.setAttribute('role', 'alert');
        Object.assign(el.style, {
            position:  'fixed',
            top:       '5.5rem',
            right:     '1rem',
            zIndex:    '9999',
            minWidth:  '220px',
            boxShadow: '0 4px 14px rgba(0,0,0,.18)',
            transition:'opacity .3s',
        });
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => {
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 300);
        }, 2700);
    } catch (e) { /* silent */ }
}

async function _ajaxAddToCart(productId, csrfToken) {
    const body = new FormData();
    body.append('_token', csrfToken || _getCartCsrf());
    const resp = await fetch(`/panier/add-ajax/${productId}`, {
        method:      'POST',
        body,
        credentials: 'same-origin',
    });
    return resp.json();
}

// ── AJAX form interception (event delegation on document) ─────────────────────
document.addEventListener('submit', async function (e) {
    const form = e.target;

    // ── 1. Promotion card "Ajouter" forms ─────────────────────────
    if (form.classList.contains('add-to-cart-form-promo')) {
        e.preventDefault();

        const btn       = form.querySelector('[data-product-id]');
        const productId = btn?.dataset.productId;
        if (!productId) return;

        const csrf = form.querySelector('[name="_token"]')?.value || _getCartCsrf();
        if (btn) btn.disabled = true;

        try {
            const data = await _ajaxAddToCart(productId, csrf);
            if (data.ok) {
                window.updateCartBadge(data.count);
                _showCartToast(data.message || 'Ajouté au panier !', 'success');
            } else {
                _showCartToast(data.message || "Erreur lors de l'ajout.", 'danger');
            }
        } catch {
            _showCartToast('Erreur réseau, veuillez réessayer.', 'danger');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // ── 2. Product detail page "Ajouter au panier" form ──────────
    if (form.classList.contains('add-to-cart-form-detail')) {
        e.preventDefault();

        const productId = new URL(form.action).pathname.split('/').pop();
        if (!productId) return;

        const csrf      = form.querySelector('[name="_token"]')?.value || _getCartCsrf();
        const submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const data = await _ajaxAddToCart(productId, csrf);
            if (data.ok) {
                window.updateCartBadge(data.count);
                _showCartToast(data.message || 'Ajouté au panier !', 'success');
            } else {
                _showCartToast(data.message || "Erreur lors de l'ajout.", 'danger');
            }
        } catch {
            _showCartToast('Erreur réseau, veuillez réessayer.', 'danger');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }
}, true); // capturing phase — runs before other listeners

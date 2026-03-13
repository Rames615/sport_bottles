
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-show-more, .btn-view-more');
        if (!btn) return;

        const productData = {
            name:        btn.dataset.productName        ?? '',
            image:       btn.dataset.productImage       ?? '',
            price:       btn.dataset.productPrice       ?? '',
            oldPrice:    btn.dataset.productOldPrice    ?? '',
            description: btn.dataset.productDescription ?? '',
            capacity:    btn.dataset.productCapacity    ?? '',
            temperature: btn.dataset.productTemperature ?? '',
            category:    btn.dataset.productCategory    ?? '',
            csrfToken:   btn.dataset.csrfToken          ?? '',
            cartUrl:     btn.dataset.cartUrl            ?? '',
            productId:   btn.dataset.productId          ?? '',
            specs:       _parseSpecs(btn.dataset.productSpecs),
        };
        console.log('Product data from button:', productData);
        openProductModal(productData);
    });
});

// ── Public API ───────────────────────────────────────────────

/**
 * Opens the product modal and populates it with product data.
 * Can also be called directly from JS if needed.
 *
 * @param {Object}   product
 * @param {string}   product.name
 * @param {string}   product.image
 * @param {string}   product.price
 * @param {string}   [product.oldPrice]
 * @param {string}   product.description
 * @param {Array}    [product.specs]      - [{ label, value }, ...]
 * @param {string}   [product.csrfToken]
 * @param {string}   [product.cartUrl]
 */
function openProductModal(product) {
    console.log('openProductModal called with:', product);
    _setImage(product);
    _setName(product.name);
    _setPrice(product.price, product.oldPrice);
    _setDescription(product.description);
    const specs = _buildSpecs(product);
    console.log('Built specs:', specs);
    _setSpecs(specs);
    _setCartForm(product.csrfToken, product.cartUrl, product.productId);
    _show();
}

// ── Private helpers ──────────────────────────────────────────

function _setImage({ image, name }) {
    const el = document.getElementById('modalProductImage');
    if (!el) return;
    el.src = image ?? '';
    el.alt = name  ?? 'Produit';
}

function _setName(name) {
    const el = document.getElementById('modalProductName');
    if (el) el.textContent = name ?? '';
}

function _setPrice(price, oldPrice) {
    const priceEl    = document.getElementById('modalProductPrice');
    const oldPriceEl = document.getElementById('modalProductOldPrice');

    if (priceEl) priceEl.textContent = price ?? '';

    if (oldPriceEl) {
        oldPriceEl.textContent   = oldPrice ?? '';
        oldPriceEl.style.display = oldPrice ? '' : 'none';
    }
}

function _setDescription(description) {
    const el = document.getElementById('modalProductDescription');
    if (el) el.textContent = description ?? '';
}

function _setSpecs(specs) {
    const container = document.getElementById('modalProductSpecs');
    if (!container) return;

    container.innerHTML = '';

    if (!Array.isArray(specs) || specs.length === 0) return;

    specs.forEach(({ label, value }) => {
        const col = document.createElement('div');
        col.className = 'col-6 col-md-4';
        col.innerHTML = `
            <div class="p-3 rounded-3 h-100 modal-spec-card"
                 style="background-color:#f8faf9; border:1px solid #e2ede9;
                        transition:border-color 0.18s, box-shadow 0.18s; cursor:default;">
                <div class="text-uppercase fw-semibold mb-1"
                     style="font-size:0.65rem; letter-spacing:0.08em; color:#6b7f79;">
                    ${_escape(label)}
                </div>
                <div class="fw-medium" style="font-size:0.9rem; color:#0f1923;">
                    ${_escape(value)}
                </div>
            </div>`;
        container.appendChild(col);
    });

    container.querySelectorAll('.modal-spec-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.borderColor = '#1F7A63';
            card.style.boxShadow   = '0 0 0 3px rgba(31,122,99,0.08)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.borderColor = '#e2ede9';
            card.style.boxShadow   = 'none';
        });
    });
}

function _setCartForm(csrfToken, cartUrl, productId) {
    const form       = document.getElementById('modalAddToCartForm');
    const tokenInput = document.getElementById('modalCsrfToken');
    const productIdInput = document.getElementById('modalProductId');

    if (form && cartUrl)          form.action = cartUrl;
    if (tokenInput && csrfToken)  tokenInput.value = csrfToken;
    if (productIdInput && productId) productIdInput.value = productId;
}

function _buildSpecs(product) {
    const specs = [];
    
    if (product.capacity) {
        specs.push({ label: 'Capacité', value: product.capacity });
    }
    if (product.temperature) {
        specs.push({ label: 'Température', value: product.temperature });
    }
    if (product.category) {
        specs.push({ label: 'Catégorie', value: product.category });
    }
    
    return specs;
}

function _show() {
    const modalEl = document.getElementById('productModal');
    if (!modalEl) return;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

function _parseSpecs(raw) {
    if (!raw) return [];
    try {
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

/** Prevents XSS in dynamically injected spec labels/values */
function _escape(str) {
    return String(str ?? '')
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#039;');
}
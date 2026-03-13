// Cart add behavior for product cards
// - Only handles .btn-add-cart buttons (regular products)
// - NOT .btn-add-promotion buttons (promotions submit their forms normally)
// - Prevents double clicks with a cooldown per product
// - Updates cart badge with AJAX response

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-cart-token"]');
    return meta ? meta.getAttribute('content') : null;
}

function showTemporaryMessage(message, type = 'success') {
    // Simple temporary on-screen message
    // Falls back to alert() if UI toast cannot be created
    try {
        const el = document.createElement('div');
        el.className = `hero-toast alert alert-${type}`;
        el.style.position = 'fixed';
        el.style.right = '1rem';
        el.style.top = '6rem';
        el.style.zIndex = 2000;
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    } catch (e) {
        alert(message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // ONLY select .btn-add-cart buttons (product cards)
    // DO NOT select .btn-add-promotion buttons (they submit forms normally)
    const buttons = document.querySelectorAll('.btn-add-cart[data-product-id]');
    const csrf = getCsrfToken();
    const cooldown = new Set();

    if (!buttons.length) return;

    buttons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const id = btn.dataset.productId;
            if (!id) return;

            // Prevent multiple quick clicks on same product
            if (cooldown.has(id)) {
                e.preventDefault();
                return;
            }

            cooldown.add(id);
            setTimeout(() => cooldown.delete(id), 800);

            btn.disabled = true;
            const form = new FormData();
            if (csrf) form.append('_token', csrf);

            try {
                const resp = await fetch(`/panier/add-ajax/${id}`, {
                    method: 'POST',
                    body: form,
                    credentials: 'same-origin'
                });

                const data = await resp.json();

                if (resp.ok && data.ok) {
                    // Delegate badge update to the shared cart.js API
                    if (window.updateCartBadge) {
                        window.updateCartBadge(data.count);
                    }
                    showTemporaryMessage(data.message || 'Ajouté au panier', 'success');
                } else {
                    const msg = data.message || 'Une erreur est survenue';
                    showTemporaryMessage(msg, 'danger');
                }
            } catch (err) {
                console.error('Error adding to cart:', err);
                showTemporaryMessage('Erreur réseau', 'danger');
            } finally {
                btn.disabled = false;
            }
        });
    });
});


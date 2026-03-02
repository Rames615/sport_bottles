// Hero/add-to-cart behaviour
// - attach to buttons with class .btn-add-cart
// - POST to /panier/add-ajax/{id} with CSRF token
// - update the cart badge (#cart-count-badge)
// - prevent double clicks with a small cooldown

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-cart-token"]');
    return meta ? meta.getAttribute('content') : null;
}

function showTemporaryMessage(message, type = 'success') {
    // Simple temporary on-screen message using alert() fallback
    // For production you may want to integrate a UI toast component
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
    // Select both old (.btn-add-cart) and new (.btn-add-promotion) add to cart buttons
    const buttons = document.querySelectorAll('.btn-add-cart[data-product-id], .btn-add-promotion[data-product-id]');
    const csrf = getCsrfToken();
    const cooldown = new Set();

    if (!buttons.length) return;

    buttons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const id = btn.dataset.productId;
            if (!id) return;

            // prevent multiple quick clicks on same product
            if (cooldown.has(id)) return;
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
                    // update cart badge
                    const badge = document.getElementById('cart-count-badge');
                    if (badge) {
                        if (data.count && parseInt(data.count) > 0) {
                            badge.style.display = 'inline-flex';
                            badge.textContent = data.count;
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                    showTemporaryMessage(data.message || 'Ajouté au panier', 'success');
                } else {
                    const msg = data.message || 'Une erreur est survenue';
                    showTemporaryMessage(msg, 'danger');
                }
            } catch (err) {
                showTemporaryMessage('Erreur réseau', 'danger');
            } finally {
                btn.disabled = false;
            }
        });
    });
});

/* ============================================
   PRODUCT MODAL MANAGEMENT - OPTIMIZED
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    
    // Cache DOM elements
    const productModal = document.getElementById('productModal');
    const modalImage = document.getElementById('modalProductImage');
    const modalName = document.getElementById('modalProductName');
    const modalPrice = document.getElementById('modalProductPrice');
    const modalDescription = document.getElementById('modalProductDescription');
    const modalSpecs = document.getElementById('modalProductSpecs');
    const modalAddForm = document.getElementById('modalAddToCartForm');
    const modalCsrf = document.getElementById('modalCsrfToken');
    
    if (!productModal) return; // Exit if modal not found
    
    // Get CSRF token from meta tag - once on load
    const csrfToken = document.querySelector('meta[name="csrf-cart-token"]')?.getAttribute('content') || '';
    
    // Handle modal open - triggered by Bootstrap's show.bs.modal event
    productModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        if (!button) return;
        
        // Extract product data from button attributes
        const productId = button.getAttribute('data-product-id');
        const productName = button.getAttribute('data-product-name');
        const productPrice = button.getAttribute('data-product-price');
        const productDescription = button.getAttribute('data-product-description');
        const productCapacity = button.getAttribute('data-product-capacity');
        const productTemperature = button.getAttribute('data-product-temperature');
        const productCategory = button.getAttribute('data-product-category');
        const productImage = button.getAttribute('data-product-image');
        
        // Populate modal content
        modalImage.src = productImage;
        modalImage.alt = productName;
        modalName.textContent = productName;
        modalPrice.textContent = parseFloat(productPrice).toLocaleString('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        });
        modalDescription.textContent = productDescription || 'Aucune description disponible';
        
        // Build specifications grid
        modalSpecs.innerHTML = '';
        
        // Capacity spec
        if (productCapacity) {
            const specDiv = createSpecCard('Capacité', productCapacity);
            modalSpecs.appendChild(specDiv);
        }
        
        // Temperature spec
        if (productTemperature) {
            const specDiv = createSpecCard('Température', productTemperature);
            modalSpecs.appendChild(specDiv);
        }
        
        // Category spec
        if (productCategory) {
            const specDiv = createSpecCard('Catégorie', productCategory);
            modalSpecs.appendChild(specDiv);
        }
        
        // Setup form action and CSRF token
        const cartAction = `/panier/add/${productId}`;
        modalAddForm.action = cartAction;
        modalCsrf.value = csrfToken;
    });
    
    // Helper function to create specification cards
    function createSpecCard(label, value) {
        const col = document.createElement('div');
        col.className = 'col-sm-6 col-md-4';
        col.innerHTML = `
            <div class="p-3 rounded-3" style="background: #f8f9fa; border: 1px solid rgba(31, 122, 99, 0.1);">
                <small class="text-muted d-block mb-2" style="text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                    ${label}
                </small>
                <strong class="text-dark">${value}</strong>
            </div>
        `;
        return col;
    }
});

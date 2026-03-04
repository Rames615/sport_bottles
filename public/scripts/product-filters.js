/* ============================================
   PRODUCT FILTERS & SORTING
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    const priceFilter = document.getElementById('priceFilter');
    const sortFilter = document.getElementById('sortFilter');
    const productCards = document.querySelectorAll('.product-card');
    const productsGrid = document.querySelector('.products-grid');

    // Fonction principale de filtrage et tri
    function filterAndSort() {
        const categoryValue = categoryFilter?.value.toLowerCase() || '';
        const priceValue = priceFilter?.value || '';
        const sortValue = sortFilter?.value || '';

        let visibleCards = [];

        // Filtrer les produits
        productCards.forEach(card => {
            let shouldShow = true;

            // Filtre catégorie
            if (categoryValue) {
                const cardCategory = card.getAttribute('data-category').toLowerCase();
                shouldShow = shouldShow && cardCategory === categoryValue;
            }

            // Filtre prix
            if (priceValue && shouldShow) {
                const cardPrice = parseFloat(card.getAttribute('data-price'));
                shouldShow = shouldShow && filterByPriceRange(cardPrice, priceValue);
            }

            card.style.display = shouldShow ? 'flex' : 'none';
            if (shouldShow) {
                visibleCards.push(card);
            }
        });

        // Trier les produits visibles
        if (sortValue && visibleCards.length > 0) {
            visibleCards.sort((a, b) => {
                switch (sortValue) {
                    case 'name-asc':
                        return a.querySelector('.product-name').textContent
                            .localeCompare(b.querySelector('.product-name').textContent);
                    case 'name-desc':
                        return b.querySelector('.product-name').textContent
                            .localeCompare(a.querySelector('.product-name').textContent);
                    case 'price-asc':
                        return parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price'));
                    case 'price-desc':
                        return parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price'));
                    default:
                        return 0;
                }
            });

            // Réordonner les cartes dans le DOM
            visibleCards.forEach(card => {
                productsGrid.appendChild(card);
            });
        }

        // Afficher le message "aucun produit"
        const visibleCount = visibleCards.length;
        updateNoProductsMessage(visibleCount);
    }

    // Fonction pour filtrer par gamme de prix
    function filterByPriceRange(price, range) {
        switch (range) {
            case '0-50':
                return price >= 0 && price <= 50;
            case '50-100':
                return price > 50 && price <= 100;
            case '100-500':
                return price > 100 && price <= 500;
            case '500+':
                return price > 500;
            default:
                return true;
        }
    }

    // Afficher/masquer le message aucun produit
    function updateNoProductsMessage(count) {
        let noProductsDiv = document.querySelector('.no-products');

        if (count === 0) {
            if (!noProductsDiv) {
                noProductsDiv = document.createElement('div');
                noProductsDiv.className = 'no-products';
                noProductsDiv.innerHTML = '<p>Aucun produit ne correspond à vos critères de recherche.</p>';
                productsGrid.appendChild(noProductsDiv);
            }
            noProductsDiv.style.display = 'block';
        } else if (noProductsDiv) {
            noProductsDiv.style.display = 'none';
        }
    }

    // Event listeners pour les filtres
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterAndSort);
    }

    if (priceFilter) {
        priceFilter.addEventListener('change', filterAndSort);
    }

    if (sortFilter) {
        sortFilter.addEventListener('change', filterAndSort);
    }
});

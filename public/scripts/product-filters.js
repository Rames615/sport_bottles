/* ============================================
   PRODUCT FILTERS & SORTING
   ============================================ */

(function() {
    let _controller = null;

    function initFilters() {
        // Abort previous listeners if re-initializing (Turbo navigation)
        if (_controller) _controller.abort();
        _controller = new AbortController();
        var signal = _controller.signal;

        const categoryFilter = document.getElementById('categoryFilter');
        const priceFilter = document.getElementById('priceFilter');
        const sortFilter = document.getElementById('sortFilter');
        const productsGrid = document.querySelector('.products-grid');
        if (!productsGrid) return;
        const productCards = Array.from(productsGrid.querySelectorAll('.product-card'));
        const collator = new Intl.Collator('fr', { sensitivity: 'base' });

    productCards.forEach((card, index) => {
        card.dataset.originalIndex = String(index);
    });

    // Fonction principale de filtrage et tri
    function filterAndSort() {
        const categoryValue = normalize(categoryFilter?.value || '');
        const priceValue = priceFilter?.value || '';
        const sortValue = sortFilter?.value || '';
        const visibleCards = [];
        const hiddenCards = [];

        // Filtrer les produits
        productCards.forEach(card => {
            let shouldShow = true;
            const cardCategory = normalize(card.getAttribute('data-category') || '');
            const cardPrice = parsePrice(card.getAttribute('data-price'));

            // Filtre catégorie
            if (categoryValue) {
                shouldShow = shouldShow && cardCategory === categoryValue;
            }

            // Filtre prix
            if (priceValue && shouldShow) {
                shouldShow = shouldShow && filterByPriceRange(cardPrice, priceValue);
            }

            card.style.display = shouldShow ? 'flex' : 'none';
            if (shouldShow) {
                visibleCards.push(card);
            } else {
                hiddenCards.push(card);
            }
        });

        // Trier les produits visibles
        visibleCards.sort((a, b) => compareCards(a, b, sortValue));
        hiddenCards.sort((a, b) => Number(a.dataset.originalIndex) - Number(b.dataset.originalIndex));

        // Réordonner les cartes dans le DOM
        const fragment = document.createDocumentFragment();
        visibleCards.forEach(card => {
            card.style.display = 'flex';
            fragment.appendChild(card);
        });
        hiddenCards.forEach(card => {
            card.style.display = 'none';
            fragment.appendChild(card);
        });
        productsGrid.appendChild(fragment);

        // Afficher le message "aucun produit"
        const visibleCount = visibleCards.length;
        updateNoProductsMessage(visibleCount);
    }

    function compareCards(a, b, sortValue) {
        const nameA = normalize(a.querySelector('.product-name')?.textContent || '');
        const nameB = normalize(b.querySelector('.product-name')?.textContent || '');
        const priceA = parsePrice(a.getAttribute('data-price'));
        const priceB = parsePrice(b.getAttribute('data-price'));
        const indexA = Number(a.dataset.originalIndex);
        const indexB = Number(b.dataset.originalIndex);

        switch (sortValue) {
            case 'name-asc':
                return collator.compare(nameA, nameB) || indexA - indexB;
            case 'name-desc':
                return collator.compare(nameB, nameA) || indexA - indexB;
            case 'price-asc':
                return (priceA - priceB) || indexA - indexB;
            case 'price-desc':
                return (priceB - priceA) || indexA - indexB;
            default:
                return indexA - indexB;
        }
    }

    function normalize(value) {
        return String(value).trim().toLowerCase();
    }

    function parsePrice(value) {
        const parsed = Number.parseFloat(String(value).replace(',', '.'));
        return Number.isFinite(parsed) ? parsed : 0;
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
        let noProductsDiv = document.querySelector('.no-products-message');

        if (count === 0) {
            if (!noProductsDiv) {
                noProductsDiv = document.createElement('div');
                noProductsDiv.className = 'no-products-message alert alert-info';
                noProductsDiv.innerHTML = '<p class="mb-0">Aucun produit ne correspond à vos critères de recherche.</p>';
                productsGrid.appendChild(noProductsDiv);
            }
            noProductsDiv.style.display = 'block';
        } else if (noProductsDiv) {
            noProductsDiv.style.display = 'none';
        }
    }

    // Event listeners pour les filtres
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterAndSort, { signal: signal });
    }

    if (priceFilter) {
        priceFilter.addEventListener('change', filterAndSort, { signal: signal });
    }

    if (sortFilter) {
        sortFilter.addEventListener('change', filterAndSort, { signal: signal });
    }

    filterAndSort();
    }

    // Turbo-compatible: fires on both initial load and Turbo navigations
    document.addEventListener('turbo:load', initFilters);
    // Fallback for non-Turbo page loads
    document.addEventListener('DOMContentLoaded', initFilters);
})();

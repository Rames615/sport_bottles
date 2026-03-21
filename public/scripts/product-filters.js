/* ============================================
   PRODUCT FILTERS & SORTING
   ============================================ */

(function() {
    // Use a window-level controller so that when Turbo re-evaluates this script
    // on navigation, the PREVIOUS run's event listeners are properly aborted.
    // (A closed-over variable cannot be accessed by the next IIFE invocation.)
    if (window._pfAbort) window._pfAbort.abort();
    const _abort = new AbortController();
    window._pfAbort = _abort;
    var signal = _abort.signal;

        const categoryFilter = document.getElementById('categoryFilter');
        const priceFilter = document.getElementById('priceFilter');
        const sortFilter = document.getElementById('sortFilter');
        const promoFilter = document.getElementById('promo-toggle-btn');
        const resetBtn = document.getElementById('resetFilters');
        const productCountEl = document.getElementById('productCount');
        const productsGrid = document.querySelector('.products-grid');
        if (!productsGrid) return;
        const productCards = Array.from(productsGrid.querySelectorAll('.product-card'));
        const totalCount = productCards.length;
        const collator = new Intl.Collator('fr', { sensitivity: 'base' });
        let promoActive = false;

    productCards.forEach((card, index) => {
        card.dataset.originalIndex = String(index);
    });

    // Style the promo button based on state
    function updatePromoButtonStyle() {
        if (!promoFilter) return;
        promoFilter.setAttribute('aria-pressed', String(promoActive));
        promoFilter.dataset.active = String(promoActive);
        if (promoActive) {
            promoFilter.style.borderColor = '#1F7A63';
            promoFilter.style.backgroundColor = 'rgba(31,122,99,.12)';
            promoFilter.style.color = '#1F7A63';
            promoFilter.style.fontWeight = '600';
        } else {
            promoFilter.style.borderColor = '#dee2e6';
            promoFilter.style.backgroundColor = '#fff';
            promoFilter.style.color = '#212529';
            promoFilter.style.fontWeight = '500';
        }
    }

    // Highlight selects that have a non-default value
    function updateSelectStyles() {
        [categoryFilter, priceFilter, sortFilter].forEach(function(sel) {
            if (!sel) return;
            if (sel.value) {
                sel.style.borderColor = '#1F7A63';
                sel.style.backgroundColor = 'rgba(31,122,99,.06)';
                sel.style.color = '#1F7A63';
                sel.style.fontWeight = '600';
            } else {
                sel.style.borderColor = '#dee2e6';
                sel.style.backgroundColor = '#fff';
                sel.style.color = '#212529';
                sel.style.fontWeight = '400';
            }
        });
    }

    // Show/hide reset button
    function updateResetButton() {
        if (!resetBtn) return;
        var hasActive = promoActive
            || (categoryFilter && categoryFilter.value)
            || (priceFilter && priceFilter.value)
            || (sortFilter && sortFilter.value);
        resetBtn.style.display = hasActive ? 'inline-flex' : 'none';
    }

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
            const cardPromo = card.getAttribute('data-promotion') === '1';

            // Filtre promotion
            if (promoActive) {
                shouldShow = shouldShow && cardPromo;
            }

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

        // Update visible count
        const visibleCount = visibleCards.length;
        if (productCountEl) {
            productCountEl.textContent = visibleCount + '/' + totalCount + ' produit(s)';
        }

        // Update UI states
        updatePromoButtonStyle();
        updateSelectStyles();
        updateResetButton();
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
                noProductsDiv.className = 'no-products-message';
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

    if (promoFilter) {
        promoFilter.addEventListener('click', function () {
            promoActive = !promoActive;
            filterAndSort();
            if (promoActive) {
                var promoSection = document.getElementById('promotions-section');
                if (promoSection) {
                    promoSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }, { signal: signal });
    }

    // Reset all filters
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            promoActive = false;
            if (categoryFilter) categoryFilter.value = '';
            if (priceFilter) priceFilter.value = '';
            if (sortFilter) sortFilter.value = '';
            filterAndSort();
        }, { signal: signal });
    }

    // Initial render
    filterAndSort();
})();

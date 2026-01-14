
document.addEventListener('DOMContentLoaded', () => {

    /* =========================
       MOBILE FILTERS TOGGLE
       ========================= */
    const filtersToggle = document.getElementById('filtersToggle');
    const filtersSidebar = document.getElementById('filtersSidebar');
    
    if (filtersToggle && filtersSidebar) {
        filtersToggle.addEventListener('click', () => {
            filtersSidebar.classList.toggle('open');
            const isOpen = filtersSidebar.classList.contains('open');
            filtersToggle.setAttribute('aria-expanded', isOpen);
            filtersToggle.textContent = isOpen ? '✕ Fermer les filtres' : '⚙️ Filtrer les produits';
        });
        
        // Close filters when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (filtersSidebar.classList.contains('open') && 
                    !filtersSidebar.contains(e.target) && 
                    !filtersToggle.contains(e.target)) {
                    filtersSidebar.classList.remove('open');
                    filtersToggle.setAttribute('aria-expanded', 'false');
                    filtersToggle.textContent = '⚙️ Filtrer les produits';
                }
            }
        });
    }

    /* =========================
       CATEGORY TAB SWITCHING
       ========================= */

    const tabs = document.querySelectorAll('.category-tab');
    const sections = document.querySelectorAll('.category-section');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const category = tab.dataset.category;

            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));

            tab.classList.add('active');

            const targetSection = document.querySelector(
                `.category-section[data-category="${category}"]`
            );

            if (targetSection) {
                targetSection.classList.add('active');
                applyFilters(); // re-apply filters when category changes
            } else {
                console.error('Category section not found:', category);
            }
        });
    });

    /* =========================
       FILTERING LOGIC
       ========================= */

    const applyBtn = document.getElementById('applyFiltersBtn');
    const resetBtn = document.getElementById('resetFiltersBtn');

    if (applyBtn) applyBtn.addEventListener('click', applyFilters);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);

    function applyFilters() {
        const capacities = getCheckedValues('.capacity-filter');
        const usages = getCheckedValues('.usage-filter');
        const temperatures = getCheckedValues('.temperature-filter');

        const activeSection = document.querySelector('.category-section.active');
        if (!activeSection) return;

        let visibleCount = 0;

        activeSection.querySelectorAll('.product-card').forEach(card => {
            const matches =
                matchFilter(card.dataset.capacity, capacities) &&
                matchFilter(card.dataset.usage, usages) &&
                matchFilter(card.dataset.temperature, temperatures);

            card.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        const emptyMessage = activeSection.querySelector('.empty-category');
        if (emptyMessage) {
            emptyMessage.hidden = visibleCount > 0;
        }
    }

    function resetFilters() {
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });

        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = '';
        });

        document.querySelectorAll('.empty-category').forEach(msg => {
            msg.hidden = true;
        });
    }

    /* =========================
       HELPERS
       ========================= */

    function getCheckedValues(selector) {
        return Array.from(
            document.querySelectorAll(selector + ':checked')
        ).map(cb => cb.value);
    }

    function matchFilter(value, selectedValues) {
        return selectedValues.length === 0 || selectedValues.includes(value);
    }

});


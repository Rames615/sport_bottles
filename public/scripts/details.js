// Product "See More" details button handler
// - Toggles visibility of product-details-expanded section
// - Changes button text between "Voir plus" and "Masquer détails"
// - Only handles .btn-show-more buttons (NOT .btn-add-cart or AJAX)

document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons "Voir plus"
    const showMoreButtons = document.querySelectorAll('.btn-show-more');

    showMoreButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Récupérer l'ID du produit depuis l'attribut data-product-id
            const productId = this.getAttribute('data-product-id');
            
            // Trouver la div product-details-expanded correspondante
            const productCard = this.closest('.product-card');
            const detailsDiv = productCard.querySelector('.product-details-expanded');
            
            if (detailsDiv) {
                // Basculer la visibilité
                detailsDiv.classList.toggle('visible');
                
                // Changer le texte du bouton
                if (detailsDiv.classList.contains('visible')) {
                    this.textContent = 'Voir moins';
                } else {
                    this.textContent = 'Voir plus';
                }
            }
        });
    });
});


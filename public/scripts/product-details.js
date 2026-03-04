/* ============================================
   PRODUCT DETAILS TOGGLE
   ============================================ */

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
                const isVisible = detailsDiv.classList.toggle('visible');
                
                // Changer le texte du bouton et l'icône
                const icon = this.querySelector('i');
                if (isVisible) {
                    this.innerHTML = '<i class="fas fa-chevron-up"></i> Voir moins';
                } else {
                    this.innerHTML = '<i class="fas fa-chevron-down"></i> Voir plus';
                }
                
                // Scroll smooth vers la section déroulée si elle s'ouvre
                if (isVisible) {
                    setTimeout(() => {
                        detailsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 150);
                }
            }
        });
    });
});

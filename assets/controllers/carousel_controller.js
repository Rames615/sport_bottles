/**
 * Carousel Controller
 * Manages infinite carousel functionality with hover pause
 */

class CarouselController {
    constructor(wrapper) {
        this.wrapper = wrapper;
        this.container = wrapper.querySelector('.carousel-container');
        this.track = wrapper.querySelector('.carousel-track');
        this.items = wrapper.querySelectorAll('.carousel-item');
        
        // Get data attributes
        this.itemsPerView = parseInt(wrapper.dataset.itemsPerView) || 4;
        this.gap = parseInt(wrapper.dataset.gap) || 20;
        this.autoplaySpeed = parseInt(wrapper.dataset.autoplaySpeed) || 3000;
        
        this.isAnimating = false;
        this.isPaused = false;
        this.autoplayInterval = null;
        
        this.init();
    }
    
    init() {
        this.setupCarousel();
        this.attachEventListeners();
        this.startAutoplay();
    }
    
    setupCarousel() {
        // Clone items for infinite scroll effect
        const clonedItems = Array.from(this.items).map(item => item.cloneNode(true));
        clonedItems.forEach(item => {
            this.track.appendChild(item);
        });
        
        // Update items reference after cloning
        this.items = this.track.querySelectorAll('.carousel-item');
        
        // Calculate item width
        this.updateDimensions();
        
        // Set initial position
        this.resetPosition();
    }
    
    updateDimensions() {
        const containerWidth = this.container.offsetWidth;
        const itemWidth = (containerWidth - (this.itemsPerView - 1) * this.gap) / this.itemsPerView;
        const totalItemWidth = itemWidth + this.gap;
        
        this.itemWidth = itemWidth;
        this.totalItemWidth = totalItemWidth;
        this.containerWidth = containerWidth;
        
        // Apply width to items
        this.items.forEach(item => {
            item.style.minWidth = `${itemWidth}px`;
            item.style.marginRight = `${this.gap}px`;
        });
    }
    
    resetPosition() {
        this.track.style.transform = 'translateX(0)';
        this.currentPosition = 0;
    }
    
    attachEventListeners() {
        // Hover pause/resume - bind methods to preserve 'this' context
        this.pauseHandler = () => this.pause();
        this.resumeHandler = () => this.resume();
        this.resizeHandler = () => this.handleResize();
        
        this.wrapper.addEventListener('mouseenter', this.pauseHandler);
        this.wrapper.addEventListener('mouseleave', this.resumeHandler);
        
        // Handle window resize
        window.addEventListener('resize', this.resizeHandler);
        
        // Touch support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        this.container.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            this.pause();
        }, false);
        
        this.container.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
            this.resume();
        }, false);
    }
    
    handleResize() {
        const oldItemWidth = this.itemWidth;
        this.updateDimensions();
        
        // Adjust position based on new dimensions
        if (oldItemWidth > 0) {
            const positionRatio = this.currentPosition / oldItemWidth;
            this.currentPosition = positionRatio * this.itemWidth;
            this.track.style.transform = `translateX(-${this.currentPosition}px)`;
        }
    }
    
    handleSwipe(startX, endX) {
        const diff = startX - endX;
        const threshold = 50;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.slideNext();
            } else {
                this.slidePrev();
            }
        }
    }
    
    slideNext() {
        if (this.isAnimating) return;
        
        this.isAnimating = true;
        
        // Move to next item
        this.currentPosition += this.totalItemWidth;
        this.animateTrack(() => {
            this.isAnimating = false;
            
            // Check if we've scrolled through all original items
            const originalItemsPixels = (this.items.length / 2) * this.totalItemWidth;
            
            if (this.currentPosition >= originalItemsPixels) {
                // Jump back to start without animation
                this.track.style.transition = 'none';
                this.currentPosition = 0;
                this.track.style.transform = 'translateX(0)';
                
                // Re-enable animation
                setTimeout(() => {
                    this.track.style.transition = 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                }, 50);
            }
        });
    }
    
    slidePrev() {
        if (this.isAnimating) return;
        
        this.isAnimating = true;
        
        if (this.currentPosition <= 0) {
            // Jump to end without animation
            const originalItemsPixels = (this.items.length / 2) * this.totalItemWidth;
            this.track.style.transition = 'none';
            this.currentPosition = originalItemsPixels - this.totalItemWidth;
            this.track.style.transform = `translateX(-${this.currentPosition}px)`;
            
            // Re-enable animation
            setTimeout(() => {
                this.track.style.transition = 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                this.currentPosition -= this.totalItemWidth;
                this.animateTrack(() => {
                    this.isAnimating = false;
                });
            }, 50);
        } else {
            this.currentPosition -= this.totalItemWidth;
            this.animateTrack(() => {
                this.isAnimating = false;
            });
        }
    }
    
    animateTrack(callback) {
        this.track.style.transform = `translateX(-${this.currentPosition}px)`;
        
        if (callback) {
            this.track.addEventListener('transitionend', () => {
                callback();
            }, { once: true });
        }
    }
    
    startAutoplay() {
        if (this.autoplayInterval) clearInterval(this.autoplayInterval);
        
        this.autoplayInterval = setInterval(() => {
            if (!this.isPaused) {
                this.slideNext();
            }
        }, this.autoplaySpeed);
    }
    
    pause() {
        this.isPaused = true;
    }
    
    resume() {
        this.isPaused = false;
    }
    
    destroy() {
        if (this.autoplayInterval) clearInterval(this.autoplayInterval);
        
        // Remove event listeners properly
        if (this.pauseHandler) {
            this.wrapper.removeEventListener('mouseenter', this.pauseHandler);
        }
        if (this.resumeHandler) {
            this.wrapper.removeEventListener('mouseleave', this.resumeHandler);
        }
        if (this.resizeHandler) {
            window.removeEventListener('resize', this.resizeHandler);
        }
    }
}

// Initialize carousels on page load
document.addEventListener('DOMContentLoaded', () => {
    const carouselWrappers = document.querySelectorAll('.carousel-wrapper');
    carouselWrappers.forEach(wrapper => {
        new CarouselController(wrapper);
    });
});

// Export for use in other modules
export default CarouselController;

/**
 * Carousel Controller
 * Manages infinite carousel functionality with smooth continuous scrolling
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
        this.originalItemsCount = this.items.length;
        this.currentPosition = 0;
        
        this.init();
    }
    
    init() {
        this.setupCarousel();
        this.attachEventListeners();
        this.startAutoplay();
    }
    
    setupCarousel() {
        // Clone items multiple times for truly infinite scroll
        // We need at least 2x the items for seamless loop
        const originalItems = Array.from(this.items);
        
        // Clone items 3 times total (original + 2 clones) for extra safety
        originalItems.forEach(item => {
            this.track.appendChild(item.cloneNode(true));
        });
        originalItems.forEach(item => {
            this.track.appendChild(item.cloneNode(true));
        });
        
        // Update items reference after cloning
        this.items = this.track.querySelectorAll('.carousel-item');
        
        // Calculate dimensions
        this.updateDimensions();
        
        // Disable transition initially for positioning
        this.track.style.transition = 'none';
        this.resetPosition();
        
        // Enable transition after initial setup
        setTimeout(() => {
            this.track.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        }, 50);
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
        this.currentPosition += this.totalItemWidth;
        
        this.track.style.transform = `translateX(-${this.currentPosition}px)`;
        
        // Check if we need to reset position for infinite loop
        setTimeout(() => {
            const maxScroll = this.originalItemsCount * this.totalItemWidth;
            
            if (this.currentPosition >= maxScroll * 2) {
                // Instantly jump back to start without visual break
                this.track.style.transition = 'none';
                this.currentPosition = maxScroll;
                this.track.style.transform = `translateX(-${this.currentPosition}px)`;
                
                // Force reflow to ensure instant jump
                void this.track.offsetHeight;
                
                // Re-enable smooth transition
                this.track.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            }
            
            this.isAnimating = false;
        }, 600); // Match animation duration
    }
    
    slidePrev() {
        if (this.isAnimating) return;
        
        this.isAnimating = true;
        
        const maxScroll = this.originalItemsCount * this.totalItemWidth;
        
        if (this.currentPosition <= 0) {
            // Jump to end instantly without visual break
            this.track.style.transition = 'none';
            this.currentPosition = maxScroll * 2 - this.totalItemWidth;
            this.track.style.transform = `translateX(-${this.currentPosition}px)`;
            
            // Force reflow to ensure instant jump
            void this.track.offsetHeight;
            
            // Re-enable smooth transition
            this.track.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            
            setTimeout(() => {
                this.currentPosition -= this.totalItemWidth;
                this.track.style.transform = `translateX(-${this.currentPosition}px)`;
                this.isAnimating = false;
            }, 50);
        } else {
            this.currentPosition -= this.totalItemWidth;
            this.track.style.transform = `translateX(-${this.currentPosition}px)`;
            
            setTimeout(() => {
                this.isAnimating = false;
            }, 600);
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

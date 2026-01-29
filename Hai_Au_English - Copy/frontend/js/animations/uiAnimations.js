// UI animations and behaviors separated from business logic

// Animate elements with .animate-on-scroll class
const animateOnScroll = () => {
    const elements = document.querySelectorAll('.animate-on-scroll');
    elements.forEach(element => {
        const rect = element.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            element.classList.add('animated');
        }
    });
};

// Lazy load images with data-src
const initLazyImages = () => {
    const images = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    obs.unobserve(img);
                }
            });
        });
        images.forEach(img => observer.observe(img));
    } else {
        // Fallback
        images.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
};

// Scroll-to-top button behavior
const initScrollToTop = () => {
    const scrollToTopBtn = document.getElementById('scroll-to-top');
    if (!scrollToTopBtn) return;

    const onScroll = () => {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('show');
            scrollToTopBtn.style.opacity = '1';
            scrollToTopBtn.style.visibility = 'visible';
        } else {
            scrollToTopBtn.classList.remove('show');
            scrollToTopBtn.style.opacity = '0';
            scrollToTopBtn.style.visibility = 'invisible';
        }
    };

    window.addEventListener('scroll', onScroll);
    scrollToTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    // init state
    onScroll();
};

// Smooth anchor scrolling
const initSmoothAnchors = () => {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
};

// Initialize all animations
export const initUIAnimations = () => {
    initLazyImages();
    initScrollToTop();
    initSmoothAnchors();
    animateOnScroll();
    window.addEventListener('scroll', animateOnScroll);
    window.addEventListener('load', animateOnScroll);
};

// Auto initialize on DOM ready
document.addEventListener('DOMContentLoaded', initUIAnimations);

export default initUIAnimations;

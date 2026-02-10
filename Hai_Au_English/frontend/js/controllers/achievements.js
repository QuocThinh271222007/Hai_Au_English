/**
 * Achievements Controller - Xử lý UI cho phần thành tích học viên
 */
import { AchievementService } from '../services/achievementService.js';
import { BASE_PATH } from '../config.js';

class AchievementsController {
    constructor() {
        this.achievements = [];
        this.currentSlide = 0;
        this.currentLightboxIndex = 0;
        this.autoSlideInterval = null;
        
        this.init();
    }

    // Sửa đường dẫn ảnh cho đúng với base path
    fixImageUrl(url) {
        if (!url) return '../assets/images/placeholder-student.jpg';
        // Nếu URL bắt đầu bằng /frontend, thêm BASE_PATH vào trước
        if (url.startsWith('/frontend')) {
            const fixedUrl = BASE_PATH + url;
            console.log('Fixed image URL:', url, ' -> ', fixedUrl);
            return fixedUrl;
        }
        return url;
    }

    async init() {
        this.createLightbox();
        await this.loadAchievements();
        this.setupEventListeners();
        this.startAutoSlide();
    }

    createLightbox() {
        // Create lightbox overlay if not exists
        if (document.getElementById('achievement-lightbox')) return;
        
        const lightbox = document.createElement('div');
        lightbox.id = 'achievement-lightbox';
        lightbox.className = 'lightbox-overlay';
        lightbox.innerHTML = `
            <div class="lightbox-content">
                <button class="lightbox-close" aria-label="Đóng">×</button>
                <button class="lightbox-nav lightbox-prev" aria-label="Trước">‹</button>
                <button class="lightbox-nav lightbox-next" aria-label="Sau">›</button>
                <img class="lightbox-image" src="" alt="">
                <div class="lightbox-info">
                    <h3 class="lightbox-name"></h3>
                    <p class="lightbox-title"></p>
                    <span class="lightbox-score"></span>
                </div>
            </div>
        `;
        document.body.appendChild(lightbox);
        
        // Lightbox events
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
                this.closeLightbox();
            }
        });
        
        lightbox.querySelector('.lightbox-prev').addEventListener('click', (e) => {
            e.stopPropagation();
            this.prevLightboxImage();
        });
        
        lightbox.querySelector('.lightbox-next').addEventListener('click', (e) => {
            e.stopPropagation();
            this.nextLightboxImage();
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') this.closeLightbox();
            if (e.key === 'ArrowLeft') this.prevLightboxImage();
            if (e.key === 'ArrowRight') this.nextLightboxImage();
        });
    }

    openLightbox(index) {
        const lightbox = document.getElementById('achievement-lightbox');
        if (!lightbox || !this.achievements[index]) return;
        
        this.currentLightboxIndex = index;
        this.updateLightboxContent();
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
        this.stopAutoSlide();
    }

    closeLightbox() {
        const lightbox = document.getElementById('achievement-lightbox');
        if (lightbox) {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
            this.startAutoSlide();
        }
    }

    updateLightboxContent() {
        const lightbox = document.getElementById('achievement-lightbox');
        const achievement = this.achievements[this.currentLightboxIndex];
        if (!lightbox || !achievement) return;
        
        const img = lightbox.querySelector('.lightbox-image');
        const name = lightbox.querySelector('.lightbox-name');
        const title = lightbox.querySelector('.lightbox-title');
        const score = lightbox.querySelector('.lightbox-score');
        
        img.src = this.fixImageUrl(achievement.image_url);
        img.alt = achievement.student_name;
        name.textContent = achievement.student_name;
        title.textContent = achievement.achievement_title;
        
        if (achievement.score) {
            score.textContent = `Điểm: ${achievement.score}`;
            score.style.display = 'inline-block';
        } else {
            score.style.display = 'none';
        }
    }

    prevLightboxImage() {
        this.currentLightboxIndex = (this.currentLightboxIndex - 1 + this.achievements.length) % this.achievements.length;
        this.updateLightboxContent();
    }

    nextLightboxImage() {
        this.currentLightboxIndex = (this.currentLightboxIndex + 1) % this.achievements.length;
        this.updateLightboxContent();
    }

    async loadAchievements() {
        const container = document.getElementById('achievements-carousel');
        if (!container) return;

        container.innerHTML = '<div class="loading-spinner">Đang tải thành tích...</div>';

        const result = await AchievementService.getAchievements(true, 20);
        console.log('Achievements API result:', result);
        
        if (result.success && result.data && result.data.length > 0) {
            this.achievements = result.data;
            console.log('Achievements data:', this.achievements);
            this.renderAchievements();
        } else {
            console.log('No achievements or error:', result);
            container.innerHTML = '<p class="text-center text-gray-500 py-8">Chưa có thành tích nào được cập nhật.</p>';
        }
    }

    renderAchievements() {
        const container = document.getElementById('achievements-carousel');
        if (!container || this.achievements.length === 0) return;

        const cardsHTML = this.achievements.map((achievement, index) => `
            <div class="achievement-card" data-index="${index}">
                <div class="achievement-card-inner">
                    <div class="achievement-image">
                        <img src="${this.fixImageUrl(achievement.image_url)}" 
                             alt="${this.escapeHtml(achievement.student_name)}"
                             onerror="this.style.display='none'">
                        ${achievement.score ? `<div class="achievement-score">${this.escapeHtml(achievement.score)}</div>` : ''}
                    </div>
                    <div class="achievement-content">
                        <h4 class="achievement-name">${this.escapeHtml(achievement.student_name)}</h4>
                        <p class="achievement-title">${this.escapeHtml(achievement.achievement_title)}</p>
                        ${achievement.course_name ? `<span class="achievement-course">${this.escapeHtml(achievement.course_name)}</span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');

        container.innerHTML = `
            <button class="carousel-nav-btn prev" id="achievements-prev" aria-label="Previous">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="carousel-track-wrapper">
                <div class="achievements-track" id="achievements-track">
                    ${cardsHTML}
                </div>
            </div>
            <button class="carousel-nav-btn next" id="achievements-next" aria-label="Next">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        `;

        // Add event listeners for nav buttons
        document.getElementById('achievements-prev')?.addEventListener('click', () => this.prevSlide());
        document.getElementById('achievements-next')?.addEventListener('click', () => this.nextSlide());

        this.updateCarouselPosition();
    }

    getCardsPerView() {
        // Responsive: 4 desktop, 3 tablet, 2 small tablet, 1 mobile
        if (window.innerWidth >= 1200) return 4;
        if (window.innerWidth >= 992) return 3;
        if (window.innerWidth >= 768) return 2;
        return 1;
    }

    updateCarouselPosition() {
        const track = document.getElementById('achievements-track');
        if (!track) return;

        const cards = track.querySelectorAll('.achievement-card');
        if (cards.length === 0) return;

        const cardsPerView = this.getCardsPerView();
        const totalSlides = Math.ceil(cards.length / cardsPerView);
        
        // Clamp currentSlide
        if (this.currentSlide >= totalSlides) {
            this.currentSlide = 0;
        }
        
        // Calculate offset based on individual card movement
        const cardWidthPercent = 100 / cardsPerView;
        const offset = this.currentSlide * cardsPerView * cardWidthPercent;
        
        // Clamp offset to prevent over-scrolling
        const maxOffset = Math.max(0, (cards.length - cardsPerView) * cardWidthPercent);
        const clampedOffset = Math.min(offset, maxOffset);
        
        track.style.transform = `translateX(-${clampedOffset}%)`;
        
        // Update dots
        const dots = document.querySelectorAll('#achievements-dots .carousel-dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentSlide);
        });
    }

    nextSlide() {
        const totalSlides = Math.ceil(this.achievements.length / this.getCardsPerView());
        this.currentSlide = (this.currentSlide + 1) % totalSlides;
        this.updateCarouselPosition();
    }

    prevSlide() {
        const totalSlides = Math.ceil(this.achievements.length / this.getCardsPerView());
        this.currentSlide = (this.currentSlide - 1 + totalSlides) % totalSlides;
        this.updateCarouselPosition();
    }

    goToSlide(index) {
        this.currentSlide = index;
        this.updateCarouselPosition();
    }

    startAutoSlide() {
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
        }
        this.autoSlideInterval = setInterval(() => {
            this.nextSlide();
        }, 4000);
    }

    stopAutoSlide() {
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
            this.autoSlideInterval = null;
        }
    }

    setupEventListeners() {
        // Click on card to open lightbox
        document.addEventListener('click', (e) => {
            const card = e.target.closest('.achievement-card');
            if (card && card.dataset.index !== undefined) {
                const index = parseInt(card.dataset.index);
                this.openLightbox(index);
            }
        });

        // Pause on hover
        const carousel = document.getElementById('achievements-carousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', () => this.stopAutoSlide());
            carousel.addEventListener('mouseleave', () => this.startAutoSlide());
        }

        // Window resize
        window.addEventListener('resize', () => {
            this.currentSlide = 0;
            this.updateCarouselPosition();
        });
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('achievements-section')) {
        new AchievementsController();
    }
});

export default AchievementsController;

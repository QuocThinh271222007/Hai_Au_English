/**
 * Teacher Reviews Controller - Xử lý UI cho phần đánh giá giảng viên (trang teachers)
 */
import { BASE_PATH } from '../config.js';

// Use global showToast from toast.js
function showToast(msg, type) {
    if (window.showToast) {
        window.showToast(msg, type);
    } else {
        console.log(type + ': ' + msg);
        alert(msg);
    }
}

class TeacherReviewsController {
    constructor() {
        this.reviews = [];
        this.currentSlide = 0;
        this.autoSlideInterval = null;
        this.isLoggedIn = false;
        
        // Store reference globally
        window.teacherReviewsController = this;
        
        this.init();
    }

    async init() {
        // Kiểm tra trạng thái đăng nhập
        await this.checkLoginStatus();
        
        // Load reviews
        await this.loadReviews();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Start auto slide
        this.startAutoSlide();
    }

    async checkLoginStatus() {
        try {
            const response = await fetch(BASE_PATH + '/backend/php/auth.php?action=check', {
                credentials: 'include'
            });
            const data = await response.json();
            this.isLoggedIn = data.success && data.user;
        } catch (error) {
            console.error('Auth check error:', error);
            this.isLoggedIn = false;
        }
        
        // Update form visibility
        const reviewForm = document.getElementById('teacher-review-form-container');
        const loginPrompt = document.getElementById('teacher-review-login-prompt');
        
        if (reviewForm && loginPrompt) {
            if (this.isLoggedIn) {
                reviewForm.classList.remove('hidden');
                loginPrompt.classList.add('hidden');
            } else {
                reviewForm.classList.add('hidden');
                loginPrompt.classList.remove('hidden');
            }
        }
    }

    async loadReviews() {
        const container = document.getElementById('teacher-reviews-carousel');
        if (!container) return;

        // Show loading
        container.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';

        try {
            const response = await fetch(BASE_PATH + '/backend/php/teacher_reviews.php?action=list', {
                credentials: 'include'
            });
            const result = await response.json();
            
            if (result.success && result.data && result.data.length > 0) {
                this.reviews = result.data;
                this.renderReviews();
                this.updateStats();
            } else {
                container.innerHTML = '<p class="text-center text-gray-500 py-8">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>';
            }
        } catch (error) {
            console.error('Load teacher reviews error:', error);
            container.innerHTML = '<p class="text-center text-red-500 py-8">Không thể tải đánh giá</p>';
        }
    }

    renderReviews() {
        const container = document.getElementById('teacher-reviews-carousel');
        if (!container || this.reviews.length === 0) return;

        const slidesHTML = this.reviews.map((review, index) => {
            const initials = review.reviewer_avatar || (review.reviewer_name ? review.reviewer_name.substring(0, 2).toUpperCase() : 'HV');
            return `
            <div class="review-card" data-index="${index}">
                <div class="review-header">
                    <div class="review-avatar">
                        <span>${this.escapeHtml(initials)}</span>
                    </div>
                    <div class="review-user-info">
                        <h4 class="review-user-name">${this.escapeHtml(review.reviewer_name)}</h4>
                        <div class="review-date">${review.reviewer_info ? this.escapeHtml(review.reviewer_info) : ''}</div>
                        <div class="review-rating">${this.renderStars(review.rating)}</div>
                    </div>
                </div>
                <div class="review-content-wrapper">
                    <div class="review-text-area">
                        <p class="review-comment">${this.escapeHtml(review.comment)}</p>
                    </div>
                </div>
            </div>
        `}).join('');

        container.innerHTML = `
            <button class="carousel-nav-btn prev" id="teacher-reviews-prev" aria-label="Previous">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="carousel-track-wrapper">
                <div class="reviews-track" id="teacher-reviews-track">
                    ${slidesHTML}
                </div>
            </div>
            <button class="carousel-nav-btn next" id="teacher-reviews-next" aria-label="Next">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        `;

        // Add event listeners for nav buttons
        document.getElementById('teacher-reviews-prev')?.addEventListener('click', () => this.prevSlide());
        document.getElementById('teacher-reviews-next')?.addEventListener('click', () => this.nextSlide());

        this.updateCarouselPosition();
    }

    renderStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '<span class="star filled" style="display:inline-block">★</span>';
            } else {
                stars += '<span class="star" style="display:inline-block">☆</span>';
            }
        }
        return `<div style="display:flex;flex-direction:row;gap:2px">${stars}</div>`;
    }

    getCardsPerView() {
        if (window.innerWidth >= 1024) return 3;
        if (window.innerWidth >= 768) return 2;
        return 1;
    }

    updateCarouselPosition() {
        const track = document.getElementById('teacher-reviews-track');
        if (!track) return;

        const cardsPerView = this.getCardsPerView();
        const totalCards = this.reviews.length;
        const maxSlide = Math.ceil(totalCards / cardsPerView) - 1;
        
        if (this.currentSlide > maxSlide) {
            this.currentSlide = 0;
        }
        
        const cardWidth = 100 / cardsPerView;
        const offset = this.currentSlide * cardWidth * cardsPerView;
        const maxOffset = (totalCards - cardsPerView) * cardWidth;
        const clampedOffset = Math.min(offset, Math.max(0, maxOffset));
        
        track.style.transform = `translateX(-${clampedOffset}%)`;
    }

    nextSlide() {
        const totalSlides = Math.ceil(this.reviews.length / this.getCardsPerView());
        this.currentSlide = (this.currentSlide + 1) % totalSlides;
        this.updateCarouselPosition();
    }

    prevSlide() {
        const totalSlides = Math.ceil(this.reviews.length / this.getCardsPerView());
        this.currentSlide = (this.currentSlide - 1 + totalSlides) % totalSlides;
        this.updateCarouselPosition();
    }

    startAutoSlide() {
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
        }
        this.autoSlideInterval = setInterval(() => {
            this.nextSlide();
        }, 5000);
    }

    stopAutoSlide() {
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
            this.autoSlideInterval = null;
        }
    }

    updateStats() {
        const avgEl = document.getElementById('teacher-reviews-avg-rating');
        const totalEl = document.getElementById('teacher-reviews-total');
        
        if (this.reviews.length > 0) {
            const total = this.reviews.length;
            const avg = (this.reviews.reduce((sum, r) => sum + (r.rating || 5), 0) / total).toFixed(1);
            
            if (avgEl) avgEl.textContent = avg;
            if (totalEl) totalEl.textContent = total;
        }
    }

    setupEventListeners() {
        // Star rating selection
        const starInputs = document.querySelectorAll('#teacher-review-form .star-rating-input .star-btn');
        starInputs.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const rating = parseInt(e.currentTarget.dataset.rating);
                this.setRating(rating);
            });
            
            btn.addEventListener('mouseenter', (e) => {
                const rating = parseInt(e.currentTarget.dataset.rating);
                this.highlightStars(rating);
            });
        });

        const starContainer = document.querySelector('#teacher-review-form .star-rating-input');
        if (starContainer) {
            starContainer.addEventListener('mouseleave', () => {
                const currentRating = parseInt(document.getElementById('teacher-review-rating').value) || 0;
                this.highlightStars(currentRating);
            });
        }

        // Form submission
        const form = document.getElementById('teacher-review-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Pause auto-slide on hover
        const carousel = document.getElementById('teacher-reviews-carousel');
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

    setRating(rating) {
        const input = document.getElementById('teacher-review-rating');
        if (input) input.value = rating;
        this.highlightStars(rating);
    }

    highlightStars(rating) {
        const stars = document.querySelectorAll('#teacher-review-form .star-rating-input .star-btn');
        stars.forEach((star, index) => {
            star.classList.toggle('active', index < rating);
        });
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        const rating = parseInt(document.getElementById('teacher-review-rating').value);
        const comment = document.getElementById('teacher-review-comment').value.trim();
        
        // Validate
        if (!rating || rating < 1 || rating > 5) {
            showToast('Vui lòng chọn số sao đánh giá', 'error');
            return;
        }
        
        if (!comment || comment.length < 10) {
            showToast('Nhận xét phải có ít nhất 10 ký tự', 'error');
            return;
        }

        // Show loading
        const submitBtn = document.getElementById('teacher-review-submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang gửi...';

        try {
            const response = await fetch(BASE_PATH + '/backend/php/teacher_reviews.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    rating: rating,
                    content: comment
                })
            });
            
            const result = await response.json();
            
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;

            if (result.success) {
                showToast('Đánh giá của bạn đã được gửi thành công!', 'success');
                
                // Reset form
                document.getElementById('teacher-review-form').reset();
                this.setRating(0);
                
                // Reload reviews
                await this.loadReviews();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            showToast('Có lỗi xảy ra khi gửi đánh giá', 'error');
        }
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
    // Check if we're on the teachers page with reviews section
    if (document.getElementById('teacher-reviews-section')) {
        new TeacherReviewsController();
    }
});

export default TeacherReviewsController;

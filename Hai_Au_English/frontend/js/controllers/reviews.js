/**
 * Reviews Controller - Xử lý UI cho phần đánh giá
 */
import { ReviewService } from '../services/reviewService.js';
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

class ReviewsController {
    constructor() {
        this.reviews = [];
        this.currentSlide = 0;
        this.autoSlideInterval = null;
        this.isLoggedIn = false;
        
        // Store reference globally for remove image button
        window.reviewsController = this;
        
        this.init();
    }

    // Sửa đường dẫn ảnh cho đúng với base path
    fixImageUrl(url) {
        if (!url) return null;
        // Nếu URL bắt đầu bằng /frontend, thêm BASE_PATH vào trước
        if (url.startsWith('/frontend')) {
            return BASE_PATH + url;
        }
        return url;
    }

    async init() {
        // Kiểm tra trạng thái đăng nhập từ session
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
            console.log('Checking login status with BASE_PATH:', BASE_PATH);
            const response = await fetch(BASE_PATH + '/backend/php/auth.php?action=check', {
                credentials: 'include'
            });
            const data = await response.json();
            console.log('Auth check response:', data);
            this.isLoggedIn = data.success && data.user;
            console.log('Is logged in:', this.isLoggedIn);
        } catch (error) {
            console.error('Auth check error:', error);
            this.isLoggedIn = false;
        }
        
        // Update form visibility
        const reviewForm = document.getElementById('review-form-container');
        const loginPrompt = document.getElementById('review-login-prompt');
        
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
        const container = document.getElementById('reviews-carousel');
        if (!container) return;

        // Show loading
        container.innerHTML = '<div class="loading-spinner">Đang tải đánh giá...</div>';

        const result = await ReviewService.getReviews(1, 20);
        console.log('Reviews API result:', result);
        
        if (result.success && result.data && result.data.length > 0) {
            this.reviews = result.data;
            console.log('Reviews data:', this.reviews);
            this.renderReviews();
            this.updateStats(result.average_rating, result.pagination.total);
        } else {
            console.log('No reviews or error:', result);
            container.innerHTML = '<p class="text-center text-gray-500 py-8">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>';
        }
    }

    // Truncate text
    truncateText(text, maxLength = 100) {
        if (text.length <= maxLength) return { text, isTruncated: false };
        return { text: text.substring(0, maxLength).trim() + '...', isTruncated: true };
    }

    // Create text modal for reading full comment
    createTextModal() {
        if (document.getElementById('review-text-modal')) return;
        
        const modal = document.createElement('div');
        modal.id = 'review-text-modal';
        modal.className = 'review-text-modal';
        modal.innerHTML = `
            <div class="review-text-modal-content">
                <button class="review-text-modal-close" onclick="window.reviewsController?.closeTextModal()">&times;</button>
                <div class="review-text-modal-header">
                    <div class="review-text-modal-avatar"></div>
                    <div class="review-text-modal-info">
                        <h3 class="review-text-modal-name"></h3>
                        <div class="review-text-modal-date"></div>
                        <div class="review-text-modal-rating"></div>
                    </div>
                </div>
                <div class="review-text-modal-body"></div>
            </div>
        `;
        document.body.appendChild(modal);
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) this.closeTextModal();
        });
    }

    openTextModal(index) {
        const review = this.reviews[index];
        if (!review) return;
        
        this.createTextModal();
        const modal = document.getElementById('review-text-modal');
        
        // Fill modal content - only text, no image
        modal.querySelector('.review-text-modal-avatar').innerHTML = review.user_avatar 
            ? `<img src="${this.fixImageUrl(review.user_avatar)}" alt="${review.user_name}">`
            : `<span>${review.user_name.charAt(0).toUpperCase()}</span>`;
        modal.querySelector('.review-text-modal-name').textContent = review.user_name;
        modal.querySelector('.review-text-modal-date').textContent = this.formatDate(review.created_at);
        modal.querySelector('.review-text-modal-rating').innerHTML = this.renderStars(review.rating);
        modal.querySelector('.review-text-modal-body').textContent = review.comment;
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    closeTextModal() {
        const modal = document.getElementById('review-text-modal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    renderReviews() {
        const container = document.getElementById('reviews-carousel');
        if (!container || this.reviews.length === 0) return;

        // Render all reviews - CSS will handle truncation with line-clamp
        const slidesHTML = this.reviews.map((review, index) => {
            return `
            <div class="review-card" data-index="${index}">
                <div class="review-header">
                    <div class="review-avatar">
                        ${review.user_avatar 
                            ? `<img src="${this.fixImageUrl(review.user_avatar)}" alt="${review.user_name}">`
                            : `<span>${review.user_name.charAt(0).toUpperCase()}</span>`
                        }
                    </div>
                    <div class="review-user-info">
                        <h4 class="review-user-name">${this.escapeHtml(review.user_name)}</h4>
                        <div class="review-date">${this.formatDate(review.created_at)}</div>
                        <div class="review-rating">${this.renderStars(review.rating)}</div>
                    </div>
                </div>
                <div class="review-content-wrapper">
                    <div class="review-text-area clickable" onclick="window.reviewsController?.openTextModal(${index})">
                        <p class="review-comment">${this.escapeHtml(review.comment)}</p>
                    </div>
                    ${review.image_url ? `
                        <div class="review-image">
                            <img src="${this.fixImageUrl(review.image_url)}" 
                                 alt="Review image" 
                                 onclick="openImageModal('${this.fixImageUrl(review.image_url)}')"
                                 onerror="this.parentElement.style.display='none'">
                        </div>
                    ` : ''}
                </div>
            </div>
        `}).join('');

        container.innerHTML = `
            <button class="carousel-nav-btn prev" id="reviews-prev" aria-label="Previous">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="carousel-track-wrapper">
                <div class="reviews-track" id="reviews-track">
                    ${slidesHTML}
                </div>
            </div>
            <button class="carousel-nav-btn next" id="reviews-next" aria-label="Next">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        `;

        // Add event listeners for nav buttons
        document.getElementById('reviews-prev')?.addEventListener('click', () => this.prevSlide());
        document.getElementById('reviews-next')?.addEventListener('click', () => this.nextSlide());

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
        const track = document.getElementById('reviews-track');
        if (!track) return;

        const cardsPerView = this.getCardsPerView();
        const totalCards = this.reviews.length;
        const maxSlide = Math.ceil(totalCards / cardsPerView) - 1;
        
        // Ensure currentSlide is within bounds
        if (this.currentSlide > maxSlide) {
            this.currentSlide = 0;
        }
        
        const cardWidth = 100 / cardsPerView;
        const offset = this.currentSlide * cardWidth * cardsPerView;
        
        // Clamp offset to prevent over-scrolling
        const maxOffset = (totalCards - cardsPerView) * cardWidth;
        const clampedOffset = Math.min(offset, Math.max(0, maxOffset));
        
        track.style.transform = `translateX(-${clampedOffset}%)`;

        // Update dots
        const dots = document.querySelectorAll('#reviews-dots .carousel-dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentSlide);
        });
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
        }, 5000); // Auto slide every 5 seconds
    }

    stopAutoSlide() {
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
            this.autoSlideInterval = null;
        }
    }

    updateStats(avgRating, totalReviews) {
        const avgEl = document.getElementById('reviews-avg-rating');
        const totalEl = document.getElementById('reviews-total');
        
        if (avgEl) avgEl.textContent = avgRating || '5.0';
        if (totalEl) totalEl.textContent = totalReviews || 0;
    }

    setupEventListeners() {
        // Star rating selection
        const starInputs = document.querySelectorAll('.star-rating-input .star-btn');
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

        const starContainer = document.querySelector('.star-rating-input');
        if (starContainer) {
            starContainer.addEventListener('mouseleave', () => {
                const currentRating = parseInt(document.getElementById('review-rating').value) || 0;
                this.highlightStars(currentRating);
            });
        }

        // Form submission
        const form = document.getElementById('review-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Image preview
        const imageInput = document.getElementById('review-image');
        if (imageInput) {
            imageInput.addEventListener('change', (e) => this.handleImagePreview(e));
        }

        // Pause auto-slide on hover
        const carousel = document.getElementById('reviews-carousel');
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
        const input = document.getElementById('review-rating');
        if (input) input.value = rating;
        this.highlightStars(rating);
    }

    highlightStars(rating) {
        const stars = document.querySelectorAll('.star-rating-input .star-btn');
        stars.forEach((star, index) => {
            star.classList.toggle('active', index < rating);
        });
    }

    handleImagePreview(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('image-preview');
        const previewContainer = document.getElementById('image-preview-container');
        
        if (file && preview && previewContainer) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                previewContainer.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    removeImagePreview() {
        const input = document.getElementById('review-image');
        const previewContainer = document.getElementById('image-preview-container');
        
        if (input) input.value = '';
        if (previewContainer) previewContainer.classList.add('hidden');
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        const rating = parseInt(document.getElementById('review-rating').value);
        const comment = document.getElementById('review-comment').value.trim();
        const imageInput = document.getElementById('review-image');
        const imageFile = imageInput?.files[0] || null;
        
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
        const submitBtn = document.getElementById('review-submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang gửi...';

        const result = await ReviewService.createReview(rating, comment, imageFile);

        submitBtn.disabled = false;
        submitBtn.textContent = originalText;

        if (result.success) {
            showToast('Đánh giá của bạn đã được gửi thành công!', 'success');
            
            // Reset form
            document.getElementById('review-form').reset();
            this.setRating(0);
            this.removeImagePreview();
            
            // Reload reviews
            await this.loadReviews();
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Image modal function (global)
window.openImageModal = function(src) {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="image-modal-overlay" onclick="this.parentElement.remove()"></div>
        <div class="image-modal-content">
            <img src="${src}" alt="Review image">
            <button class="image-modal-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    document.body.appendChild(modal);
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on the index page with reviews section
    if (document.getElementById('reviews-section')) {
        new ReviewsController();
    }
});

export default ReviewsController;

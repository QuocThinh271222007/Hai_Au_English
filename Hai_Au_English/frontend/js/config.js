/**
 * Config file for Hai Au English
 * Cấu hình API endpoints cho production (Hostinger)
 * 
 * HƯỚNG DẪN:
 * - Khi deploy lên Hostinger, chỉ cần thay đổi SITE_URL bên dưới
 * - Nếu frontend và backend cùng domain: sử dụng '' (empty string)
 * - Nếu khác domain: sử dụng full URL như 'https://yourdomain.com'
 */

// ============================================
// CẤU HÌNH CHÍNH - THAY ĐỔI KHI DEPLOY
// ============================================

// Tự động detect domain hiện tại
const CURRENT_ORIGIN = window.location.origin;

// Tự động detect base path từ URL
function getBasePath() {
    const path = window.location.pathname.toLowerCase();
    
    // Nếu URL chứa /hai_au_english/ (XAMPP local - case insensitive)
    if (path.includes('/hai_au_english')) {
        // Lấy đúng case từ pathname gốc
        const match = window.location.pathname.match(/\/hai_au_english/i);
        return match ? match[0] : '/hai_au_english';
    }
    
    // Nếu URL chứa /frontend/ (local dev)
    if (path.includes('/frontend/')) {
        return window.location.pathname.split('/frontend/')[0];
    }
    
    // Production (Hostinger) - clean URL
    return '';
}

const BASE_PATH = getBasePath();

// Production URL (Hostinger) - để trống nếu cùng domain
// Thay đổi thành domain của bạn nếu cần
const SITE_URL = BASE_PATH;  // Tự động detect

// Fallback nếu cần absolute URL
// const SITE_URL = 'https://haiauenglish.edu.vn';

// ============================================
// API ENDPOINTS - KHÔNG CẦN THAY ĐỔI
// ============================================

const API_CONFIG = {
    // Base paths
    BASE_URL: SITE_URL,
    API_PATH: '/backend/php',
    
    // Individual endpoints
    get AUTH() {
        return `${this.BASE_URL}${this.API_PATH}/auth.php`;
    },
    get COURSES() {
        return `${this.BASE_URL}${this.API_PATH}/courses.php`;
    },
    get TEACHERS() {
        return `${this.BASE_URL}${this.API_PATH}/teachers.php`;
    },
    get CONTACT() {
        return `${this.BASE_URL}${this.API_PATH}/contact.php`;
    },
    get PROFILE() {
        return `${this.BASE_URL}${this.API_PATH}/profile.php`;
    },
    get ADMIN() {
        return `${this.BASE_URL}${this.API_PATH}/admin.php`;
    },
    get USERS() {
        return `${this.BASE_URL}${this.API_PATH}/users.php`;
    },
    get REVIEWS() {
        return `${this.BASE_URL}${this.API_PATH}/reviews.php`;
    },
    get ACHIEVEMENTS() {
        return `${this.BASE_URL}${this.API_PATH}/achievements.php`;
    }
};

// API Base URL for services (backward compatibility)
const API_BASE_URL = `${SITE_URL}/backend/php`;

// Export for ES modules
export { API_CONFIG, SITE_URL, CURRENT_ORIGIN, API_BASE_URL, BASE_PATH };
export default API_CONFIG;

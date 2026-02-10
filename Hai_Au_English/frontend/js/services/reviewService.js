/**
 * Review Service - API calls for reviews
 */
import { API_BASE_URL } from '../config.js';

const REVIEWS_API = `${API_BASE_URL}/reviews.php`;

export const ReviewService = {
    /**
     * Lấy danh sách đánh giá
     */
    async getReviews(page = 1, limit = 10) {
        try {
            const response = await fetch(`${REVIEWS_API}?page=${page}&limit=${limit}`, {
                method: 'GET',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching reviews:', error);
            return { success: false, message: 'Lỗi kết nối server' };
        }
    },

    /**
     * Tạo đánh giá mới (cần đăng nhập)
     */
    async createReview(rating, comment, imageFile = null) {
        try {
            let body;
            let headers = {};

            if (imageFile) {
                // Sử dụng FormData nếu có ảnh
                body = new FormData();
                body.append('rating', rating);
                body.append('comment', comment);
                body.append('image', imageFile);
            } else {
                // Sử dụng JSON nếu không có ảnh
                headers['Content-Type'] = 'application/json';
                body = JSON.stringify({ rating, comment });
            }

            const response = await fetch(REVIEWS_API, {
                method: 'POST',
                credentials: 'include',
                headers,
                body
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error creating review:', error);
            return { success: false, message: 'Lỗi kết nối server' };
        }
    },

    /**
     * Xóa đánh giá
     */
    async deleteReview(reviewId) {
        try {
            const response = await fetch(`${REVIEWS_API}?id=${reviewId}`, {
                method: 'DELETE',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            console.error('Error deleting review:', error);
            return { success: false, message: 'Lỗi kết nối server' };
        }
    }
};

export default ReviewService;

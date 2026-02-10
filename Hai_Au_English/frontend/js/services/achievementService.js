/**
 * Achievement Service - API calls for student achievements
 */
import { API_BASE_URL } from '../config.js';

const ACHIEVEMENTS_API = `${API_BASE_URL}/achievements.php`;

export const AchievementService = {
    /**
     * Lấy danh sách thành tích
     */
    async getAchievements(featured = null, limit = 20) {
        try {
            let url = `${ACHIEVEMENTS_API}?limit=${limit}`;
            if (featured !== null) {
                url += `&featured=${featured}`;
            }
            
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            console.error('Error fetching achievements:', error);
            return { success: false, message: 'Lỗi kết nối server' };
        }
    },

    /**
     * Tạo thành tích mới (chỉ admin)
     */
    async createAchievement(data, imageFile = null) {
        try {
            let body;
            let headers = {};

            if (imageFile) {
                body = new FormData();
                Object.keys(data).forEach(key => {
                    body.append(key, data[key]);
                });
                body.append('image', imageFile);
            } else {
                headers['Content-Type'] = 'application/json';
                body = JSON.stringify(data);
            }

            const response = await fetch(ACHIEVEMENTS_API, {
                method: 'POST',
                credentials: 'include',
                headers,
                body
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error creating achievement:', error);
            return { success: false, message: 'Lỗi kết nối server' };
        }
    },

    /**
     * Cập nhật thành tích (chỉ admin)
     */
    async updateAchievement(id, data) {
        try {
            const response = await fetch(`${ACHIEVEMENTS_API}?id=${id}`, {
                method: 'PUT',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('Error updating achievement:', error);
            return { success: false, message: 'Lỗi kết nối server' };
        }
    },

    /**
     * Xóa thành tích (chỉ admin)
     */
    async deleteAchievement(id) {
        try {
            const response = await fetch(`${ACHIEVEMENTS_API}?id=${id}`, {
                method: 'DELETE',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            console.error('Error deleting achievement:', error);
            return { success: false, message: 'Lỗi kết nối server' };
        }
    }
};

export default AchievementService;

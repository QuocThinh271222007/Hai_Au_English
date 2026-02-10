// profileService.js - API service cho profile
import { API_CONFIG } from '../config.js';

const API_BASE = API_CONFIG.BASE_URL + API_CONFIG.API_PATH;

export const profileService = {
    // Lấy thông tin profile
    async getProfile() {
        const response = await fetch(`${API_BASE}/profile.php?action=profile`, {
            credentials: 'include'
        });
            try {
                return await response.json();
            } catch (err) {
                return { success: false, error: 'Lỗi phản hồi từ server' };
            }
    },

    // Lấy danh sách khóa học đã đăng ký
    async getEnrollments() {
        const response = await fetch(`${API_BASE}/profile.php?action=enrollments`, {
            credentials: 'include'
        });
        try {
            return await response.json();
        } catch (err) {
            return { success: false, error: 'Lỗi phản hồi từ server' };
        }
    },

    // Lấy điểm số
    async getScores() {
        const response = await fetch(`${API_BASE}/profile.php?action=scores`, {
            credentials: 'include'
        });
        try {
            return await response.json();
        } catch (err) {
            return { success: false, error: 'Lỗi phản hồi từ server' };
        }
    },

    // Lấy điểm cho biểu đồ
    async getScoresChart() {
        try {
            const response = await fetch(`${API_BASE}/profile.php?action=scores-chart`, {
                credentials: 'include'
            });
            if (!response.ok) {
                return { success: false, error: 'Lỗi server' };
            }
            const text = await response.text();
            if (!text || text.trim() === '') {
                return { success: false, timeline: [], averages: {} };
            }
            return JSON.parse(text);
        } catch (err) {
            return { success: false, error: 'Lỗi phản hồi từ server' };
        }
    },

    // Lấy tiến độ học tập
    async getProgress() {
        try {
            const response = await fetch(`${API_BASE}/profile.php?action=progress`, {
                credentials: 'include'
            });
            if (!response.ok) {
                return { success: false, error: 'Lỗi server' };
            }
            const text = await response.text();
            if (!text || text.trim() === '') {
                return { success: true, enrollments: [] };
            }
            return JSON.parse(text);
        } catch (err) {
            return { success: false, error: 'Lỗi phản hồi từ server' };
        }
    },

    // Lấy nhận xét từ giảng viên
    async getFeedback() {
        try {
            const response = await fetch(`${API_BASE}/profile.php?action=feedback`, {
                credentials: 'include'
            });
            if (!response.ok) {
                return { success: false, error: 'Lỗi server' };
            }
            const text = await response.text();
            if (!text || text.trim() === '') {
                return { success: true, feedback: [] };
            }
            return JSON.parse(text);
        } catch (err) {
            return { success: false, error: 'Lỗi phản hồi từ server' };
        }
    },

    // Lấy thời khóa biểu
    async getSchedule() {
        try {
            const response = await fetch(`${API_BASE}/profile.php?action=schedule`, {
                credentials: 'include'
            });
            if (!response.ok) {
                return { success: false, error: 'Lỗi server: ' + response.status };
            }
            const text = await response.text();
            if (!text || text.trim() === '') {
                return { success: true, schedules: [] };
            }
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e, 'Response:', text);
                return { success: false, error: 'Lỗi phân tích dữ liệu' };
            }
        } catch (err) {
            console.error('Network error:', err);
            return { success: false, error: 'Lỗi kết nối: ' + err.message };
        }
    },

    // Lấy thống kê cho dashboard
    async getStats() {
        try {
            const response = await fetch(`${API_BASE}/profile.php?action=stats`, {
                credentials: 'include'
            });
            if (!response.ok) {
                return { success: false, error: 'Lỗi server' };
            }
            const text = await response.text();
            if (!text || text.trim() === '') {
                return { success: false, stats: {} };
            }
            return JSON.parse(text);
        } catch (err) {
            return { success: false, error: 'Lỗi phản hồi từ server' };
        }
    },

    // Lấy dữ liệu dashboard
    async getDashboard() {
        try {
            // Lấy stats
            const statsRes = await fetch(`${API_BASE}/profile.php?action=stats`, {
                credentials: 'include'
            });
            const statsData = await statsRes.json();

            // Lấy recent scores
            const scoresRes = await fetch(`${API_BASE}/profile.php?action=scores`, {
                credentials: 'include'
            });
            const scoresData = await scoresRes.json();

            return {
                success: true,
                stats: statsData.stats || {},
                recent_scores: (scoresData.scores || []).slice(0, 5)
            };
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Cập nhật profile
    async updateProfile(data) {
        const response = await fetch(`${API_BASE}/profile.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(data)
        });
        return response.json();
    },

    // Đổi mật khẩu
    async changePassword(currentPassword, newPassword) {
        const response = await fetch(`${API_BASE}/profile.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                action: 'change_password',
                current_password: currentPassword,
                new_password: newPassword
            })
        });
        return response.json();
    },

    // Upload avatar
    async uploadAvatar(file) {
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('action', 'upload-avatar');
        
        const response = await fetch(`${API_BASE}/profile.php?action=upload-avatar`, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        return response.json();
    }
};

export default profileService;

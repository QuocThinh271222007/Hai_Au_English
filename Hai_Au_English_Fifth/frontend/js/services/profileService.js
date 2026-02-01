// profileService.js - API service cho profile
const API_BASE = '/hai_au_english/backend/php';

export const profileService = {
    // Lấy thông tin profile
    async getProfile() {
        const response = await fetch(`${API_BASE}/profile.php?action=profile`, {
            credentials: 'include'
        });
        return response.json();
    },

    // Lấy danh sách khóa học đã đăng ký
    async getEnrollments() {
        const response = await fetch(`${API_BASE}/profile.php?action=enrollments`, {
            credentials: 'include'
        });
        return response.json();
    },

    // Lấy điểm số
    async getScores() {
        const response = await fetch(`${API_BASE}/profile.php?action=scores`, {
            credentials: 'include'
        });
        return response.json();
    },

    // Lấy điểm cho biểu đồ
    async getScoresChart() {
        const response = await fetch(`${API_BASE}/profile.php?action=scores-chart`, {
            credentials: 'include'
        });
        return response.json();
    },

    // Lấy tiến độ học tập
    async getProgress() {
        const response = await fetch(`${API_BASE}/profile.php?action=progress`, {
            credentials: 'include'
        });
        return response.json();
    },

    // Lấy nhận xét từ giảng viên
    async getFeedback() {
        const response = await fetch(`${API_BASE}/profile.php?action=feedback`, {
            credentials: 'include'
        });
        return response.json();
    },

    // Lấy thời khóa biểu
    async getSchedule() {
        const response = await fetch(`${API_BASE}/profile.php?action=schedule`, {
            credentials: 'include'
        });
        return response.json();
    },

    // Lấy thống kê cho dashboard
    async getStats() {
        const response = await fetch(`${API_BASE}/profile.php?action=stats`, {
            credentials: 'include'
        });
        return response.json();
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
    }
};

export default profileService;

/**
 * Admin Service - Gọi API admin
 */

const API_URL = '../../backend/php/admin.php';

export const adminService = {
    // ==================== DASHBOARD ====================
    async getDashboard() {
        try {
            const response = await fetch(`${API_URL}?action=stats`, {
                credentials: 'include'
            });
            const stats = await response.json();
            
            if (!stats.success) {
                return { error: stats.message };
            }

            // Lấy recent enrollments
            const enrollResponse = await fetch(`${API_URL}?action=recent-enrollments`, {
                credentials: 'include'
            });
            const enrollData = await enrollResponse.json();

            return {
                success: true,
                stats: stats.data,
                recent_enrollments: enrollData.data || []
            };
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== USERS ====================
    async getUsers() {
        try {
            const response = await fetch(`${API_URL}?action=users`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, users: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async createUser(userData) {
        try {
            const response = await fetch(`${API_URL}?action=user-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(userData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateUser(userData) {
        try {
            const response = await fetch(`${API_URL}?action=user-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(userData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateUserStatus(userId, isActive) {
        try {
            const response = await fetch(`${API_URL}?action=user-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: userId, is_active: isActive })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteUser(userId) {
        try {
            const response = await fetch(`${API_URL}?action=user-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: userId })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== COURSES ====================
    async getCourses() {
        try {
            const response = await fetch(`${API_URL}?action=courses`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, courses: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async createCourse(courseData) {
        try {
            const response = await fetch(`${API_URL}?action=course-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(courseData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateCourse(courseData) {
        try {
            const response = await fetch(`${API_URL}?action=course-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(courseData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteCourse(courseId) {
        try {
            const response = await fetch(`${API_URL}?action=course-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: courseId })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== ENROLLMENTS ====================
    async getEnrollments(status = null) {
        try {
            let url = `${API_URL}?action=enrollments`;
            if (status) url += `&status=${status}`;
            
            const response = await fetch(url, { credentials: 'include' });
            const data = await response.json();
            return { success: data.success, enrollments: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async createEnrollment(enrollmentData) {
        try {
            const response = await fetch(`${API_URL}?action=enrollment-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(enrollmentData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateEnrollment(enrollmentData) {
        try {
            const response = await fetch(`${API_URL}?action=enrollment-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(enrollmentData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteEnrollment(enrollmentId) {
        try {
            const response = await fetch(`${API_URL}?action=enrollment-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: enrollmentId })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== TEACHERS ====================
    async getTeachers() {
        try {
            const response = await fetch(`${API_URL}?action=teachers`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, teachers: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async createTeacher(teacherData) {
        try {
            const response = await fetch(`${API_URL}?action=teacher-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(teacherData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateTeacher(teacherData) {
        try {
            const response = await fetch(`${API_URL}?action=teacher-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(teacherData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteTeacher(teacherId) {
        try {
            const response = await fetch(`${API_URL}?action=teacher-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: teacherId })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== SCORES ====================
    async getScores() {
        try {
            const response = await fetch(`${API_URL}?action=scores`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, scores: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async createScore(scoreData) {
        try {
            const response = await fetch(`${API_URL}?action=score-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(scoreData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateScore(scoreData) {
        try {
            const response = await fetch(`${API_URL}?action=score-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(scoreData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteScore(scoreId) {
        try {
            const response = await fetch(`${API_URL}?action=score-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: scoreId })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== FEEDBACK ====================
    async getFeedback() {
        try {
            const response = await fetch(`${API_URL}?action=feedback`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, feedback: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async createFeedback(feedbackData) {
        try {
            const response = await fetch(`${API_URL}?action=feedback-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(feedbackData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteFeedback(feedbackId) {
        try {
            const response = await fetch(`${API_URL}?action=feedback-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: feedbackId })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== SCHEDULES (THỜI KHÓA BIỂU) ====================
    async getSchedules() {
        try {
            const response = await fetch(`${API_URL}?action=schedules`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, schedules: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async createSchedule(scheduleData) {
        try {
            const response = await fetch(`${API_URL}?action=schedule-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(scheduleData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateSchedule(scheduleData) {
        try {
            const response = await fetch(`${API_URL}?action=schedule-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(scheduleData)
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteSchedule(scheduleId) {
        try {
            const response = await fetch(`${API_URL}?action=schedule-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: scheduleId })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== TRASH (THÙNG RÁC) ====================
    async getTrash(table = null) {
        try {
            let url = `${API_URL}?action=trash`;
            if (table) url += `&table=${table}`;
            
            const response = await fetch(url, { credentials: 'include' });
            const data = await response.json();
            return { success: data.success, trash: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async restoreFromTrash(trashId) {
        try {
            const response = await fetch(`${API_URL}?action=trash-restore`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: trashId })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deletePermanent(trashId) {
        try {
            const response = await fetch(`${API_URL}?action=trash-delete-permanent`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: trashId })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async emptyTrash(table = null) {
        try {
            let url = `${API_URL}?action=trash-empty`;
            if (table) url += `&table=${table}`;
            
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    }
};

/**
 * Admin Service - Gọi API admin
 */
import { API_CONFIG } from '../config.js';

// Use getter function to ensure correct URL each time
function getApiUrl() {
    return API_CONFIG.ADMIN;
}

export const adminService = {
    // ==================== ADMIN PROFILE ====================
    async getProfile() {
        try {
            const response = await fetch(`${getApiUrl()}?action=get-profile`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async uploadAvatar(file) {
        try {
            const formData = new FormData();
            formData.append('avatar', file);
            
            const response = await fetch(`${getApiUrl()}?action=upload-avatar`, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async changePassword(currentPassword, newPassword) {
        try {
            const response = await fetch(`${getApiUrl()}?action=change-password`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword
                })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== DASHBOARD ====================
    async getDashboard() {
        try {
            const response = await fetch(`${getApiUrl()}?action=stats`, {
                credentials: 'include'
            });
            const stats = await response.json();
            
            if (!stats.success) {
                return { error: stats.message };
            }

            // Lấy recent enrollments
            const enrollResponse = await fetch(`${getApiUrl()}?action=recent-enrollments`, {
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
            const response = await fetch(`${getApiUrl()}?action=users`, {
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
            const response = await fetch(`${getApiUrl()}?action=user-create`, {
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
            const response = await fetch(`${getApiUrl()}?action=user-update`, {
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
            const response = await fetch(`${getApiUrl()}?action=user-update`, {
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
            const response = await fetch(`${getApiUrl()}?action=user-delete`, {
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
            const response = await fetch(`${getApiUrl()}?action=courses`, {
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
            const response = await fetch(`${getApiUrl()}?action=course-create`, {
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

    async createCourseWithFile(formData) {
        try {
            const response = await fetch(`${getApiUrl()}?action=course-create`, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateCourse(courseData) {
        try {
            const response = await fetch(`${getApiUrl()}?action=course-update`, {
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

    async updateCourseWithFile(formData) {
        try {
            const response = await fetch(`${getApiUrl()}?action=course-update`, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteCourse(courseId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=course-delete`, {
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
            let url = `${getApiUrl()}?action=enrollments`;
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
            const response = await fetch(`${getApiUrl()}?action=enrollment-create`, {
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
            const response = await fetch(`${getApiUrl()}?action=enrollment-update`, {
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
            const response = await fetch(`${getApiUrl()}?action=enrollment-delete`, {
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

    // ==================== CLASSES (LỚP HỌC) ====================
    async getClasses() {
        try {
            const response = await fetch(`${getApiUrl()}?action=classes`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, classes: data.data || [] };
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async createClass(classData) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(classData)
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async updateClass(classData) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(classData)
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async deleteClass(id) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id })
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async getClassStudents(classId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-students&class_id=${classId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async assignStudentToClass(enrollmentId, classId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-assign-student`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ enrollment_id: enrollmentId, class_id: classId })
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async getAvailableStudentsForClass(classId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-available-students&class_id=${classId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Get ALL users for adding to class (not just enrolled students)
    async getAllUsersForClass(classId, search = '') {
        try {
            let url = `${getApiUrl()}?action=class-all-users&class_id=${classId}`;
            if (search) {
                url += `&search=${encodeURIComponent(search)}`;
            }
            const response = await fetch(url, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Add user to class (auto-create enrollment if needed)
    async addUserToClass(userId, classId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-add-user`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ user_id: userId, class_id: classId })
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Remove student from class
    async removeStudentFromClass(enrollmentId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-remove-student`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ enrollment_id: enrollmentId })
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Get detailed student info
    async getStudentDetails(userId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=student-details&user_id=${userId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Get class statistics
    async getClassStatistics(classId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-statistics&class_id=${classId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // ==================== TEACHERS ====================
    async getTeachers() {
        try {
            const response = await fetch(`${getApiUrl()}?action=teachers`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, teachers: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async createTeacher(teacherData, imageFile = null) {
        try {
            let body;
            let headers = {};
            
            if (imageFile) {
                // Use FormData for file upload
                body = new FormData();
                body.append('image', imageFile);
                Object.keys(teacherData).forEach(key => {
                    if (key === 'specialties' && Array.isArray(teacherData[key])) {
                        body.append(key, JSON.stringify(teacherData[key]));
                    } else {
                        body.append(key, teacherData[key]);
                    }
                });
            } else {
                headers['Content-Type'] = 'application/json';
                body = JSON.stringify(teacherData);
            }
            
            const response = await fetch(`${getApiUrl()}?action=teacher-create`, {
                method: 'POST',
                credentials: 'include',
                headers,
                body
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateTeacher(teacherData, imageFile = null) {
        try {
            let body;
            let headers = {};
            
            if (imageFile) {
                // Use FormData for file upload
                body = new FormData();
                body.append('image', imageFile);
                Object.keys(teacherData).forEach(key => {
                    if (key === 'specialties' && Array.isArray(teacherData[key])) {
                        body.append(key, JSON.stringify(teacherData[key]));
                    } else {
                        body.append(key, teacherData[key]);
                    }
                });
            } else {
                headers['Content-Type'] = 'application/json';
                body = JSON.stringify(teacherData);
            }
            
            const response = await fetch(`${getApiUrl()}?action=teacher-update`, {
                method: 'POST',
                credentials: 'include',
                headers,
                body
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async uploadTeacherImage(imageFile) {
        try {
            const formData = new FormData();
            formData.append('image', imageFile);
            
            const response = await fetch(`${getApiUrl()}?action=teacher-upload-image`, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteTeacher(teacherId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=teacher-delete`, {
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
            const response = await fetch(`${getApiUrl()}?action=scores`, {
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
            const response = await fetch(`${getApiUrl()}?action=score-create`, {
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
            const response = await fetch(`${getApiUrl()}?action=score-update`, {
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
            const response = await fetch(`${getApiUrl()}?action=score-delete`, {
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
            const response = await fetch(`${getApiUrl()}?action=feedback`, {
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
            const response = await fetch(`${getApiUrl()}?action=feedback-create`, {
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
            const response = await fetch(`${getApiUrl()}?action=feedback-delete`, {
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
            const response = await fetch(`${getApiUrl()}?action=schedules`, {
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
            const response = await fetch(`${getApiUrl()}?action=schedule-create`, {
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
            const response = await fetch(`${getApiUrl()}?action=schedule-update`, {
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
            const response = await fetch(`${getApiUrl()}?action=schedule-delete`, {
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
            let url = `${getApiUrl()}?action=trash`;
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
            const response = await fetch(`${getApiUrl()}?action=trash-restore`, {
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
            const response = await fetch(`${getApiUrl()}?action=trash-delete-permanent`, {
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
            let url = `${getApiUrl()}?action=trash-empty`;
            if (table) url += `&table=${table}`;
            
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== REVIEWS ====================
    async getReviews(filter = '') {
        try {
            let url = `${getApiUrl()}?action=reviews`;
            if (filter) url += `&filter=${filter}`;
            
            const response = await fetch(url, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async approveReview(reviewId, isApproved = true) {
        try {
            const response = await fetch(`${getApiUrl()}?action=review-approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id: reviewId, is_approved: isApproved ? 1 : 0 })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async deleteReview(reviewId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=review-delete&id=${reviewId}`, {
                method: 'DELETE',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== SITE CONTENT MANAGEMENT ====================
    async getSiteContent(page = null) {
        try {
            let url = `${getApiUrl()}?action=site-content`;
            if (page) url += `&page=${page}`;
            
            const response = await fetch(url, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateSiteContent(page, section, contentKey, contentValue, contentType = 'text') {
        try {
            const response = await fetch(`${getApiUrl()}?action=site-content-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    page,
                    section,
                    content_key: contentKey,
                    content_value: contentValue,
                    content_type: contentType
                })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async bulkUpdateSiteContent(items) {
        try {
            const response = await fetch(`${getApiUrl()}?action=site-content-bulk-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ items })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== SITE SETTINGS MANAGEMENT ====================
    async getSiteSettings() {
        try {
            const response = await fetch(`${getApiUrl()}?action=site-settings`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async updateSiteSetting(settingKey, settingValue, settingType = 'text', description = '') {
        try {
            const response = await fetch(`${getApiUrl()}?action=site-settings-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    setting_key: settingKey,
                    setting_value: settingValue,
                    setting_type: settingType,
                    description
                })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    async bulkUpdateSiteSettings(settings) {
        try {
            const response = await fetch(`${getApiUrl()}?action=site-settings-bulk-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ settings })
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== SEARCH FUNCTIONS ====================
    async searchUsers(query) {
        try {
            const response = await fetch(`${getApiUrl()}?action=search-users&q=${encodeURIComponent(query)}`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, users: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async searchEnrollments(query, status = '') {
        try {
            let url = `${getApiUrl()}?action=search-enrollments&q=${encodeURIComponent(query)}`;
            if (status) url += `&status=${status}`;
            const response = await fetch(url, { credentials: 'include' });
            const data = await response.json();
            return { success: data.success, enrollments: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async searchTeachers(query) {
        try {
            const response = await fetch(`${getApiUrl()}?action=search-teachers&q=${encodeURIComponent(query)}`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, teachers: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async searchScores(query) {
        try {
            const response = await fetch(`${getApiUrl()}?action=search-scores&q=${encodeURIComponent(query)}`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, scores: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async searchFeedback(query) {
        try {
            const response = await fetch(`${getApiUrl()}?action=search-feedback&q=${encodeURIComponent(query)}`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, feedback: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async searchAchievements(query) {
        try {
            const response = await fetch(`${getApiUrl()}?action=search-achievements&q=${encodeURIComponent(query)}`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, achievements: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async searchReviews(query, filter = '') {
        try {
            let url = `${getApiUrl()}?action=search-reviews&q=${encodeURIComponent(query)}`;
            if (filter) url += `&filter=${filter}`;
            const response = await fetch(url, { credentials: 'include' });
            const data = await response.json();
            return { success: data.success, reviews: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    async searchSchedules(query) {
        try {
            const response = await fetch(`${getApiUrl()}?action=search-schedules&q=${encodeURIComponent(query)}`, {
                credentials: 'include'
            });
            const data = await response.json();
            return { success: data.success, schedules: data.data || [] };
        } catch (error) {
            return { error: error.message };
        }
    },

    // ==================== CONTENT IMAGE UPLOAD ====================
    async uploadContentImage(file, page, section, key) {
        try {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('page', page);
            formData.append('section', section);
            formData.append('key', key);
            
            const response = await fetch(`${getApiUrl()}?action=content-image-upload`, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            return await response.json();
        } catch (error) {
            return { error: error.message };
        }
    },

    // Delete content image
    async deleteContentImage(page, section, key) {
        try {
            const response = await fetch(`${getApiUrl()}?action=content-image-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ page, section, key })
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // ==================== TEACHER REVIEWS ====================
    async getTeacherReviews(activeOnly = false) {
        try {
            let url = `${API_CONFIG.BASE_URL}/backend/php/teacher_reviews.php?action=list`;
            if (!activeOnly) url += '&active=0';
            const response = await fetch(url, { credentials: 'include' });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async createTeacherReview(data) {
        try {
            const response = await fetch(`${API_CONFIG.BASE_URL}/backend/php/teacher_reviews.php?action=create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async updateTeacherReview(data) {
        try {
            const response = await fetch(`${API_CONFIG.BASE_URL}/backend/php/teacher_reviews.php?action=update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async deleteTeacherReview(id) {
        try {
            const response = await fetch(`${API_CONFIG.BASE_URL}/backend/php/teacher_reviews.php?action=delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id })
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // ==================== COURSE FEES ====================
    async getCourseFees(category = null, activeOnly = false) {
        try {
            let url = `${API_CONFIG.BASE_URL}/backend/php/course_fees.php?action=list`;
            if (category) url += `&category=${category}`;
            if (!activeOnly) url += '&active=0';
            const response = await fetch(url, { credentials: 'include' });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async createCourseFee(data) {
        try {
            const response = await fetch(`${API_CONFIG.BASE_URL}/backend/php/course_fees.php?action=create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async updateCourseFee(data) {
        try {
            const response = await fetch(`${API_CONFIG.BASE_URL}/backend/php/course_fees.php?action=update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async deleteCourseFee(id) {
        try {
            const response = await fetch(`${API_CONFIG.BASE_URL}/backend/php/course_fees.php?action=delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ id })
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // ==================== ADVANCED STATISTICS & LINKED DATA ====================
    
    // Lấy thống kê chi tiết cho dashboard
    async getDetailedStats() {
        try {
            const response = await fetch(`${getApiUrl()}?action=dashboard-detailed-stats`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Lấy tất cả đăng ký của một học viên
    async getUserEnrollments(userId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=user-enrollments&user_id=${userId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Lấy tất cả lớp của một giảng viên
    async getTeacherClasses(teacherId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=teacher-classes&teacher_id=${teacherId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Lấy thống kê chi tiết của một khóa học
    async getCourseStats(courseId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=course-stats&course_id=${courseId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Cập nhật hàng loạt đăng ký
    async bulkUpdateEnrollments(ids, data) {
        try {
            const response = await fetch(`${getApiUrl()}?action=enrollment-bulk-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ ids, ...data })
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // ==================== CLASS SCHEDULES (Thời khóa biểu) ====================

    // Lấy thời khóa biểu của một lớp
    async getClassSchedules(classId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=class-schedules&class_id=${classId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Lấy tất cả thời khóa biểu (có thể filter)
    async getAllSchedules(filters = {}) {
        try {
            let url = `${getApiUrl()}?action=all-schedules`;
            if (filters.courseId) url += `&course_id=${filters.courseId}`;
            if (filters.teacherId) url += `&teacher_id=${filters.teacherId}`;
            if (filters.classId) url += `&class_id=${filters.classId}`;
            
            const response = await fetch(url, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Tạo lịch học mới
    async createSchedule(data) {
        try {
            const response = await fetch(`${getApiUrl()}?action=schedule-create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Cập nhật lịch học
    async updateSchedule(data) {
        try {
            const response = await fetch(`${getApiUrl()}?action=schedule-update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Xóa lịch học
    async deleteSchedule(scheduleId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=schedule-delete&id=${scheduleId}`, {
                method: 'DELETE',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Lấy thời khóa biểu của học viên
    async getStudentSchedule(userId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=student-schedule&user_id=${userId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Lấy lịch dạy của giảng viên
    async getTeacherSchedule(teacherId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=teacher-schedule&teacher_id=${teacherId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Lấy thời khóa biểu của khóa học (tất cả các lớp)
    async getCourseSchedule(courseId) {
        try {
            const response = await fetch(`${getApiUrl()}?action=course-schedule&course_id=${courseId}`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // ==================== NOTIFICATIONS ====================
    async getNotifications(options = {}) {
        try {
            const params = new URLSearchParams();
            if (options.type) params.append('type', options.type);
            if (options.unread_only) params.append('unread_only', '1');
            if (options.limit) params.append('limit', options.limit);
            if (options.page) params.append('page', options.page);
            
            const url = `${API_CONFIG.NOTIFICATIONS}${params.toString() ? '?' + params.toString() : ''}`;
            const response = await fetch(url, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async markNotificationAsRead(id) {
        try {
            const response = await fetch(`${API_CONFIG.NOTIFICATIONS}?id=${id}`, {
                method: 'PUT',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async markAllNotificationsAsRead() {
        try {
            const response = await fetch(`${API_CONFIG.NOTIFICATIONS}?action=read_all`, {
                method: 'PUT',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async deleteNotification(id) {
        try {
            const response = await fetch(`${API_CONFIG.NOTIFICATIONS}?id=${id}`, {
                method: 'DELETE',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    // Lấy settings (bao gồm limits)
    async getSettings() {
        try {
            const response = await fetch(`${getApiUrl()}?action=settings`, {
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    async updateSettings(settings) {
        try {
            const response = await fetch(`${getApiUrl()}?action=update-settings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(settings)
            });
            return await response.json();
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
};

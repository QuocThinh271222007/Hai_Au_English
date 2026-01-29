// Frontend Course Service - Xử lý tất cả yêu cầu liên quan khóa học

import APIClient from '../api.js';

export const courseService = {
  // Lấy tất cả khóa học
  async getAllCourses() {
    try {
      const response = await APIClient.get('/courses');
      return response.courses || [];
    } catch (error) {
      console.error('Error fetching courses:', error);
      throw error;
    }
  },

  // Lấy khóa học theo ID
  async getCourseById(id) {
    try {
      const response = await APIClient.get(`/courses/${id}`);
      return response;
    } catch (error) {
      console.error('Error fetching course:', error);
      throw error;
    }
  },

  // Tạo khóa học (admin)
  async createCourse(courseData) {
    try {
      const response = await APIClient.post('/courses', courseData);
      return response;
    } catch (error) {
      console.error('Error creating course:', error);
      throw error;
    }
  },

  // Cập nhật khóa học (admin)
  async updateCourse(id, courseData) {
    try {
      const response = await APIClient.put(`/courses/${id}`, courseData);
      return response;
    } catch (error) {
      console.error('Error updating course:', error);
      throw error;
    }
  },

  // Xóa khóa học (admin)
  async deleteCourse(id) {
    try {
      const response = await APIClient.delete(`/courses/${id}`);
      return response;
    } catch (error) {
      console.error('Error deleting course:', error);
      throw error;
    }
  }
};

export default courseService;

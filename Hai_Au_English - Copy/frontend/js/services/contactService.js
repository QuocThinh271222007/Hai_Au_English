// Frontend Contact Service - Xử lý tất cả yêu cầu liên quan contact

import APIClient from '../api.js';

export const contactService = {
  // Gửi form liên hệ
  async submitContact(contactData) {
    try {
      const response = await APIClient.post('/contacts', contactData);
      return response;
    } catch (error) {
      console.error('Error submitting contact:', error);
      throw error;
    }
  },

  // Lấy tất cả contacts (admin)
  async getAllContacts() {
    try {
      const response = await APIClient.get('/contacts');
      return response.contacts || [];
    } catch (error) {
      console.error('Error fetching contacts:', error);
      throw error;
    }
  },

  // Lấy contact theo ID (admin)
  async getContactById(id) {
    try {
      const response = await APIClient.get(`/contacts/${id}`);
      return response;
    } catch (error) {
      console.error('Error fetching contact:', error);
      throw error;
    }
  },

  // Cập nhật trạng thái contact (admin)
  async updateContactStatus(id, status) {
    try {
      const response = await APIClient.put(`/contacts/${id}/status`, { status });
      return response;
    } catch (error) {
      console.error('Error updating contact:', error);
      throw error;
    }
  },

  // Xóa contact (admin)
  async deleteContact(id) {
    try {
      const response = await APIClient.delete(`/contacts/${id}`);
      return response;
    } catch (error) {
      console.error('Error deleting contact:', error);
      throw error;
    }
  }
};

export default contactService;

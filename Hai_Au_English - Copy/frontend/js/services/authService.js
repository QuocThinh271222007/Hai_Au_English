// Frontend Auth Service - Xử lý tất cả yêu cầu authentication

import APIClient from './api.js';

export const authService = {
  // Đăng ký
  async register(fullName, email, password, confirmPassword) {
    try {
      const response = await APIClient.post('/auth/register', {
        fullName,
        email,
        password,
        confirmPassword
      });
      
      if (response.token) {
        APIClient.setToken(response.token);
      }
      
      return response;
    } catch (error) {
      throw error;
    }
  },

  // Đăng nhập
  async login(email, password, rememberMe = false) {
    try {
      const response = await APIClient.post('/auth/login', {
        email,
        password
      });
      
      if (response.token) {
        APIClient.setToken(response.token);
        if (rememberMe) {
          localStorage.setItem('rememberMe', 'true');
        }
      }
      
      return response;
    } catch (error) {
      throw error;
    }
  },

  // Đăng xuất
  async logout() {
    try {
      await APIClient.post('/auth/logout', {});
      APIClient.clearToken();
      localStorage.removeItem('rememberMe');
      return { message: 'Đăng xuất thành công' };
    } catch (error) {
      throw error;
    }
  },

  // Kiểm tra đã đăng nhập
  isLoggedIn() {
    return !!localStorage.getItem('token');
  },

  // Lấy token
  getToken() {
    return localStorage.getItem('token');
  }
};

export default authService;

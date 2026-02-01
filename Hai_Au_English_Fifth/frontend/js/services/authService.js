// authService.js - Giao tiếp với backend PHP cho đăng ký/đăng nhập
const API_BASE = '/hai_au_english/backend/php/auth.php';

export const authService = {
  async register({ fullname, email, password, phone }) {
    try {
      const res = await fetch(`${API_BASE}?action=register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ fullname, email, password, phone })
      });
      
      // Parse response
      let data;
      try {
        data = await res.json();
      } catch (parseErr) {
        console.error('JSON parse error:', parseErr);
        return { success: false, error: 'Lỗi phản hồi từ server' };
      }
      
      // Nếu HTTP error hoặc có error trong response
      if (!res.ok || data.error) {
        return { success: false, error: data.error || 'Đăng ký thất bại' };
      }
      
      return data;
    } catch (err) {
      console.error('Register error:', err);
      return { success: false, error: 'Lỗi kết nối server' };
    }
  },

  async login({ email, password }) {
    try {
      const res = await fetch(`${API_BASE}?action=login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ email, password })
      });
      
      // Parse response
      let data;
      try {
        data = await res.json();
      } catch (parseErr) {
        console.error('JSON parse error:', parseErr);
        return { success: false, error: 'Lỗi phản hồi từ server' };
      }
      
      // Nếu HTTP error hoặc có error trong response
      if (!res.ok || data.error) {
        return { success: false, error: data.error || 'Đăng nhập thất bại' };
      }
      
      return data;
    } catch (err) {
      console.error('Login error:', err);
      return { success: false, error: 'Lỗi kết nối server' };
    }
  },

  async checkAuth() {
    const res = await fetch(`${API_BASE}?action=check`, {
      credentials: 'include'
    });
    const data = await res.json();
    return data;
  },

  async logout() {
    const res = await fetch(`${API_BASE}?action=logout`, {
      credentials: 'include'
    });
    localStorage.removeItem('user');
    return res.json();
  }
};

export default authService;

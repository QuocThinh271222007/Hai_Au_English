// authService.js - Giao tiếp với backend PHP cho đăng ký/đăng nhập
const API_BASE = '/hai_au_english/backend/php/auth.php';

export const authService = {
  async register({ fullname, email, password }) {
    const res = await fetch(`${API_BASE}?action=register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ fullname, email, password })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi đăng ký');
    return data;
  },
  async login({ email, password }) {
    const res = await fetch(`${API_BASE}?action=login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi đăng nhập');
    return data;
  }
};

export default authService;

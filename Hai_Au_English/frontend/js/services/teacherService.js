// teacherService.js - Giao tiếp với backend PHP cho giảng viên
import { API_CONFIG } from '../config.js';

const API_BASE = API_CONFIG.TEACHERS;

export const teacherService = {
  async getAllTeachers(featuredOnly = false) {
    const url = featuredOnly ? `${API_BASE}?featured=1` : API_BASE;
    const res = await fetch(url);
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi lấy danh sách giảng viên');
    return data.teachers || [];
  },
  
  async getTeacherById(id) {
    const res = await fetch(`${API_BASE}?id=${id}`);
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi lấy thông tin giảng viên');
    return data.teacher || null;
  },
  
  async addTeacher(teacherData) {
    const res = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(teacherData)
    });
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi thêm giảng viên');
    return data;
  },
  
  async updateTeacher(id, teacherData) {
    const res = await fetch(API_BASE, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, ...teacherData })
    });
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi cập nhật giảng viên');
    return data;
  },
  
  async deleteTeacher(id) {
    const res = await fetch(`${API_BASE}?id=${id}`, { method: 'DELETE' });
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi xóa giảng viên');
    return data;
  },
  
  // Helper: Format số học viên
  formatStudentsCount(count) {
    return count >= 1000 
      ? (count / 1000).toFixed(1) + 'k+' 
      : count + '+';
  },
  
  // Lấy thời khóa biểu của giáo viên
  async getTeacherSchedule(teacherId) {
    const adminApi = API_CONFIG.ADMIN || API_CONFIG.BASE + '/admin.php';
    const res = await fetch(`${adminApi}?action=teacher-schedule&teacher_id=${teacherId}`, {
      credentials: 'include'
    });
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi lấy thời khóa biểu giáo viên');
    return data.schedules || [];
  }
};

export default teacherService;

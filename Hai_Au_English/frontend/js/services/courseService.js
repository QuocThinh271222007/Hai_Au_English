// courseService.js - Giao tiếp với backend PHP cho khóa học
import { API_CONFIG } from '../config.js';

// Sử dụng config tập trung - tự động detect domain
const API_BASE = API_CONFIG.COURSES;

export const courseService = {
  async getAllCourses(category = null) {
    const url = category && category !== 'all' 
      ? `${API_BASE}?category=${category}` 
      : API_BASE;
    const res = await fetch(url);
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi lấy danh sách khóa học');
    return data.courses || [];
  },
  
  async getCourseById(id) {
    const res = await fetch(`${API_BASE}?id=${id}`);
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi lấy thông tin khóa học');
    return data.course || null;
  },
  
  async addCourse(courseData) {
    const res = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(courseData)
    });
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi thêm khóa học');
    return data;
  },
  
  async updateCourse(id, courseData) {
    const res = await fetch(API_BASE, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, ...courseData })
    });
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi cập nhật khóa học');
    return data;
  },
  
  async deleteCourse(id) {
    const res = await fetch(`${API_BASE}?id=${id}`, { method: 'DELETE' });
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi xóa khóa học');
    return data;
  },
  
  // Lấy danh sách lớp học và thời khóa biểu của khóa học
  async getCourseClasses(courseId) {
    const res = await fetch(`${API_BASE}?action=classes&course_id=${courseId}`);
    let data;
    try {
      data = await res.json();
    } catch (err) {
      throw new Error('Lỗi phản hồi từ server');
    }
    if (!res.ok) throw new Error(data?.error || 'Lỗi lấy danh sách lớp học');
    return data.classes || [];
  },
  
  // Helper: Format giá tiền
  formatPrice(price, unit = '/khóa') {
    return new Intl.NumberFormat('vi-VN').format(price) + 'đ' + (unit ? ` ${unit}` : '');
  },
  
  // Helper: Map level sang tiếng Việt và class CSS
  getLevelInfo(level) {
    const levelMap = {
      'beginner': { text: 'Cơ bản', class: 'level-beginner' },
      'intermediate': { text: 'Trung cấp', class: 'level-intermediate' },
      'advanced': { text: 'Nâng cao', class: 'level-advanced' },
      'all': { text: 'Mọi trình độ', class: 'level-all' }
    };
    return levelMap[level] || levelMap['beginner'];
  }
};

export default courseService;

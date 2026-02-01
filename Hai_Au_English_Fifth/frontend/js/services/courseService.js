// courseService.js - Giao tiếp với backend PHP cho khóa học
const API_BASE = '/hai_au_english/backend/php/courses.php';

export const courseService = {
  async getAllCourses(category = null) {
    const url = category && category !== 'all' 
      ? `${API_BASE}?category=${category}` 
      : API_BASE;
    const res = await fetch(url);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi lấy danh sách khóa học');
    return data.courses || [];
  },
  
  async getCourseById(id) {
    const res = await fetch(`${API_BASE}?id=${id}`);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi lấy thông tin khóa học');
    return data.course || null;
  },
  
  async addCourse(courseData) {
    const res = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(courseData)
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi thêm khóa học');
    return data;
  },
  
  async updateCourse(id, courseData) {
    const res = await fetch(API_BASE, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, ...courseData })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi cập nhật khóa học');
    return data;
  },
  
  async deleteCourse(id) {
    const res = await fetch(`${API_BASE}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi xóa khóa học');
    return data;
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

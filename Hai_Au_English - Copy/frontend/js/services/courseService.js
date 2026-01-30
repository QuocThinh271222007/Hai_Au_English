// courseService.js - Giao tiếp với backend PHP cho khóa học
const API_BASE = '/hai_au_english/backend/php/courses.php';

export const courseService = {
  async getAllCourses() {
    const res = await fetch(API_BASE);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi lấy danh sách khóa học');
    return data.courses || [];
  },
  async addCourse({ name, description }) {
    const res = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, description })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi thêm khóa học');
    return data;
  },
  async deleteCourse(id) {
    const res = await fetch(`${API_BASE}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi xóa khóa học');
    return data;
  }
};

export default courseService;

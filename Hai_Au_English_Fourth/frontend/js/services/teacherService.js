// teacherService.js - Giao tiếp với backend PHP cho giảng viên
const API_BASE = '/hai_au_english/backend/php/teachers.php';

export const teacherService = {
  async getAllTeachers(featuredOnly = false) {
    const url = featuredOnly ? `${API_BASE}?featured=1` : API_BASE;
    const res = await fetch(url);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi lấy danh sách giảng viên');
    return data.teachers || [];
  },
  
  async getTeacherById(id) {
    const res = await fetch(`${API_BASE}?id=${id}`);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi lấy thông tin giảng viên');
    return data.teacher || null;
  },
  
  async addTeacher(teacherData) {
    const res = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(teacherData)
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi thêm giảng viên');
    return data;
  },
  
  async updateTeacher(id, teacherData) {
    const res = await fetch(API_BASE, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, ...teacherData })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi cập nhật giảng viên');
    return data;
  },
  
  async deleteTeacher(id) {
    const res = await fetch(`${API_BASE}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi xóa giảng viên');
    return data;
  },
  
  // Helper: Format số học viên
  formatStudentsCount(count) {
    return count >= 1000 
      ? (count / 1000).toFixed(1) + 'k+' 
      : count + '+';
  }
};

export default teacherService;

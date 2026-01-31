// teachers.js - Xử lý hiển thị giảng viên với backend PHP
import teacherService from '../services/teacherService.js';

document.addEventListener('DOMContentLoaded', function() {
  const teachersGrid = document.getElementById('teachers-grid');
  
  // Render một teacher card
  function renderTeacherCard(teacher) {
    const specialties = Array.isArray(teacher.specialties) ? teacher.specialties : [];
    
    return `
      <div class="teacher-card">
        <div class="teacher-image">
          <img src="${teacher.image_url}" alt="${teacher.name}">
          <div class="teacher-badge">${teacher.ielts_score} IELTS</div>
        </div>
        <div class="teacher-content">
          <h3 class="teacher-name">${teacher.name}</h3>
          <p class="teacher-title">${teacher.title}</p>
          <p class="teacher-description">${teacher.description}</p>
          <div class="teacher-specialties">
            ${specialties.map(s => `<span class="specialty-tag">${s}</span>`).join('')}
          </div>
          <div class="teacher-stats">
            <div class="stat-item">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
              <span>${teacher.students_count}+ học viên</span>
            </div>
            <div class="stat-item">
              <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              <span>${teacher.rating}/5.0</span>
            </div>
          </div>
        </div>
      </div>
    `;
  }
  
  // Load và hiển thị teachers
  async function loadTeachers() {
    if (!teachersGrid) return;
    
    try {
      teachersGrid.innerHTML = `
        <div class="col-span-full text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600">Đang tải danh sách giảng viên...</p>
        </div>
      `;
      
      const teachers = await teacherService.getAllTeachers(true); // Chỉ lấy featured
      
      if (teachers.length === 0) {
        teachersGrid.innerHTML = `
          <div class="col-span-full text-center py-8">
            <p class="text-gray-600">Chưa có thông tin giảng viên.</p>
          </div>
        `;
        return;
      }
      
      teachersGrid.innerHTML = teachers.map(renderTeacherCard).join('');
    } catch (err) {
      console.error('Lỗi load teachers:', err);
      teachersGrid.innerHTML = `
        <div class="col-span-full text-center py-8">
          <p class="text-red-600">Lỗi: ${err.message}</p>
          <button onclick="location.reload()" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Thử lại
          </button>
        </div>
      `;
    }
  }
  
  // Load teachers khi trang load
  if (teachersGrid) {
    loadTeachers();
  }
});

export default null;

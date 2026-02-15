// teachers.js - Xử lý hiển thị giảng viên với backend PHP
import teacherService from '../services/teacherService.js';
import { BASE_PATH, API_CONFIG } from '../config.js';

// Helper: Fix image URL with base path
function getImageUrl(url) {
  if (!url) return BASE_PATH + '/frontend/assets/images/default-teacher.png';
  if (url.startsWith('http')) return url;
  if (url.startsWith('/') && !url.toLowerCase().startsWith('/hai_au_english')) {
    return BASE_PATH + url;
  }
  return url;
}

// Day map
const dayMap = {
  'monday': 'Thứ 2', 'tuesday': 'Thứ 3', 'wednesday': 'Thứ 4',
  'thursday': 'Thứ 5', 'friday': 'Thứ 6', 'saturday': 'Thứ 7', 'sunday': 'Chủ nhật'
};

// Format time
function formatTime(timeStr) {
  if (!timeStr) return '';
  return timeStr.substring(0, 5);
}

document.addEventListener('DOMContentLoaded', function() {
  const teachersGrid = document.getElementById('teachers-grid');
  
  // Render một teacher card
  function renderTeacherCard(teacher) {
    const specialties = Array.isArray(teacher.specialties) ? teacher.specialties : [];
    const imageUrl = getImageUrl(teacher.image_url);
    
    return `
      <div class="teacher-card" data-teacher-id="${teacher.id}">
        <!-- TODO: Bật lại chức năng xem lịch dạy sau khi hoàn thiện backend
        <div class="teacher-image" style="cursor:pointer" onclick="window.showTeacherSchedule(${teacher.id}, '${teacher.name.replace(/'/g, "\\'")}', '${imageUrl.replace(/'/g, "\\'")}')">
        -->
        <div class="teacher-image">
          <img src="${imageUrl}" alt="${teacher.name}" onerror="this.src='${BASE_PATH}/frontend/assets/images/default-avatar.svg'">
          <!-- TODO: Tạm ẩn IELTS badge - bật lại khi cần
          <div class="teacher-badge">${teacher.ielts_score} IELTS</div>
          -->
          <!-- TODO: Bật lại overlay xem lịch dạy sau
          <div class="teacher-overlay">
            <span class="view-schedule-btn">📅 Xem lịch dạy</span>
          </div>
          -->
        </div>
        <div class="teacher-content">
          <!-- TODO: Bật lại onclick xem lịch dạy sau
          <h3 class="teacher-name" style="cursor:pointer" onclick="window.showTeacherSchedule(${teacher.id}, '${teacher.name.replace(/'/g, "\\'")}', '${imageUrl.replace(/'/g, "\\'")}')">${teacher.name}</h3>
          -->
          <h3 class="teacher-name">${teacher.name}</h3>
          <p class="teacher-title">${teacher.title}</p>
          <!-- <p class="teacher-description">${teacher.description}</p> -->
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
      
      const teachers = await teacherService.getAllTeachers(false); // Lấy tất cả giáo viên active
      
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
  
  /* ==========================================================================
   * TODO: BẬT LẠI CHỨC NĂNG XEM LỊCH DẠY SAU KHI HOÀN THIỆN
   * 
   * Chức năng này cho phép:
   * - Click vào ảnh/tên giáo viên để xem lịch dạy
   * - Hiển thị modal với thông tin lịch dạy theo ngày
   * - Cần backend API: teachers.php?action=schedule&teacher_id=X
   * 
   * Để bật lại:
   * 1. Uncomment phần renderTeacherCard ở trên (onclick, overlay)
   * 2. Uncomment function showTeacherSchedule bên dưới
   * ========================================================================== */
  
  /*
  // Show teacher schedule modal
  window.showTeacherSchedule = async function(teacherId, teacherName, teacherImage) {
    const existingModal = document.getElementById('teacher-schedule-modal');
    if (existingModal) existingModal.remove();

    const modal = document.createElement('div');
    modal.id = 'teacher-schedule-modal';
    modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4';
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
    
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-6 py-4 flex justify-between items-center">
          <div class="flex items-center gap-4">
            <img src="${teacherImage}" alt="${teacherName}" class="w-14 h-14 rounded-full object-cover border-2 border-white/50" onerror="this.src='${BASE_PATH}/frontend/assets/images/default-avatar.svg'">
            <div>
              <h2 class="text-xl font-bold">${teacherName}</h2>
              <p class="text-blue-100 text-sm">Lịch dạy học</p>
            </div>
          </div>
          <button onclick="document.getElementById('teacher-schedule-modal').remove()" class="text-white hover:bg-white/20 p-2 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-100px)]" id="teacher-schedule-content">
          <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Đang tải lịch dạy...</p>
          </div>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
    
    // Load schedule
    try {
      // Use teachers API (public, no auth required)
      const teachersApi = (API_CONFIG.TEACHERS || API_CONFIG.BASE + '/teachers.php');
      const res = await fetch(`${teachersApi}?action=schedule&teacher_id=${teacherId}`);
      const data = await res.json();
      const schedules = data.schedules || [];
      const content = document.getElementById('teacher-schedule-content');
      
      if (!schedules || schedules.length === 0) {
        content.innerHTML = `
          <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-700 mb-2">Chưa có lịch dạy</h3>
            <p class="text-gray-500">Giáo viên này hiện chưa có lịch dạy trong hệ thống.</p>
          </div>
        `;
        return;
      }
      
      // Group schedules by day
      const groupedByDay = {};
      schedules.forEach(s => {
        const day = s.day_of_week;
        if (!groupedByDay[day]) groupedByDay[day] = [];
        groupedByDay[day].push(s);
      });
      
      const dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
      
      content.innerHTML = `
        <div class="space-y-4">
          ${dayOrder.filter(day => groupedByDay[day]).map(day => `
            <div class="border rounded-lg overflow-hidden">
              <div class="bg-blue-50 px-4 py-2 font-semibold text-blue-800 border-b">
                ${dayMap[day] || day}
              </div>
              <div class="divide-y">
                ${groupedByDay[day].map(s => `
                  <div class="px-4 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div class="flex items-center gap-4">
                      <span class="text-lg font-medium text-blue-600 min-w-[120px]">
                        ${formatTime(s.start_time)} - ${formatTime(s.end_time)}
                      </span>
                      <div>
                        <p class="font-medium text-gray-800">${s.class_name || 'Lớp N/A'}</p>
                        <p class="text-sm text-gray-500">${s.course_name || ''}</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-sm ${s.is_online ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}">
                        ${s.is_online ? '🌐 Online' : '📍 ' + (s.room || 'N/A')}
                      </span>
                    </div>
                  </div>
                `).join('')}
              </div>
            </div>
          `).join('')}
        </div>
      `;
    } catch (error) {
      console.error('Error loading teacher schedule:', error);
      document.getElementById('teacher-schedule-content').innerHTML = `
        <div class="text-center py-8 text-red-500">
          <p>Có lỗi xảy ra khi tải lịch dạy.</p>
          <p class="text-sm">${error.message}</p>
        </div>
      `;
    }
  };
  */ // END TODO: Chức năng xem lịch dạy
  
  // Load teachers khi trang load
  if (teachersGrid) {
    loadTeachers();
  }
});

export default null;

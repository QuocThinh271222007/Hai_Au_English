// courses.js - Xử lý hiển thị và quản lý khóa học với backend PHP
import courseService from '../services/courseService.js';
import { BASE_PATH } from '../config.js';

// Helper: Fix image URL with base path
function getImageUrl(url) {
  if (!url) return 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&h=250&fit=crop';
  if (url.startsWith('http')) return url;
  if (url.startsWith('/') && !url.toLowerCase().startsWith('/hai_au_english')) {
    return BASE_PATH + url;
  }
  return url;
}

// Format tiền VND
function formatMoney(amount) {
  if (!amount) return '0';
  return new Intl.NumberFormat('vi-VN').format(amount);
}

// Format date
function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN');
}

// Day map for schedule display
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
  const filterTabs = document.querySelectorAll('.filter-tab');
  const courseSections = document.querySelectorAll('.course-section');
  
  // Category grids và tables
  const courseGrids = {
    tieuhoc: document.getElementById('courses-grid-tieuhoc'),
    thcs: document.getElementById('courses-grid-thcs'),
    ielts: document.getElementById('courses-grid-ielts')
  };
  
  const courseTables = {
    tieuhoc: document.getElementById('tbody-tieuhoc'),
    thcs: document.getElementById('tbody-thcs'),
    ielts: document.getElementById('tbody-ielts')
  };
  
  // ====== STICKY FILTER TABS ======
  const filterSection = document.getElementById('filter-tabs-section');
  const header = document.querySelector('header');
  
  if (filterSection && header) {
    const placeholder = document.createElement('div');
    placeholder.className = 'filter-tabs-placeholder';
    filterSection.parentNode.insertBefore(placeholder, filterSection.nextSibling);
    
    let filterSectionTop = null;
    let headerHeight = null;
    
    function updateStickyValues() {
      headerHeight = header.offsetHeight;
      if (!filterSection.classList.contains('sticky')) {
        filterSectionTop = filterSection.offsetTop;
      }
    }
    
    function handleScroll() {
      if (filterSectionTop === null) return;
      
      const scrollY = window.scrollY || window.pageYOffset;
      const triggerPoint = filterSectionTop - headerHeight;
      
      if (scrollY >= triggerPoint) {
        if (!filterSection.classList.contains('sticky')) {
          placeholder.style.height = filterSection.offsetHeight + 'px';
          placeholder.classList.add('active');
          filterSection.classList.add('sticky');
          filterSection.style.top = headerHeight + 'px';
        }
      } else {
        if (filterSection.classList.contains('sticky')) {
          filterSection.classList.remove('sticky');
          filterSection.style.top = '';
          placeholder.classList.remove('active');
          placeholder.style.height = '';
          setTimeout(() => {
            filterSectionTop = filterSection.offsetTop;
          }, 50);
        }
      }
    }
    
    updateStickyValues();
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('resize', function() {
      updateStickyValues();
      handleScroll();
    });
    handleScroll();
  }
  // ====== END STICKY FILTER TABS ======
  
  // Render một course card (đơn giản hơn)
  function renderCourseCard(course) {
    const features = Array.isArray(course.features) ? course.features : [];
    const badgeHtml = course.badge 
      ? `<div class="course-badge ${course.badge_type || ''}">${course.badge}</div>` 
      : '';
    const imageUrl = getImageUrl(course.image_url);
    
    return `
      <div class="course-card-simple" data-category="${course.category}" data-course-id="${course.id}">
        <div class="course-card-image" style="cursor:pointer;" onclick="window.showCourseClasses(${course.id}, '${course.name.replace(/'/g, "\\'")}')">
          <img src="${imageUrl}" alt="${course.name}" onerror="this.src='https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&h=250&fit=crop'">
          ${badgeHtml}
          <div class="course-overlay">
            <span class="view-classes-btn">🗓️ Xem lịch học</span>
          </div>
        </div>
        <div class="course-card-body">
          <h3 class="course-card-title" style="cursor:pointer;" onclick="window.showCourseClasses(${course.id}, '${course.name.replace(/'/g, "\\'")}')">${course.name}</h3>
          <p class="course-card-desc">${course.description}</p>
          <ul class="course-card-features">
            ${features.slice(0, 3).map(f => `<li>✓ ${f}</li>`).join('')}
          </ul>
          <div class="course-card-footer">
            <span class="course-card-price">${formatMoney(course.price)}đ<small>/tháng</small></span>
            <button class="course-card-btn enroll-course-btn" data-course-id="${course.id}" data-course-name="${course.name.replace(/"/g, '&quot;')}">Đăng ký</button>
          </div>
        </div>
      </div>
    `;
  }
  
  // Render bảng chi tiết
  function renderTableRow(course, isHighlight = false) {
    const highlightClass = course.name.toLowerCase().includes('luyện thi') || course.name.toLowerCase().includes('lt ') ? 'highlight-row' : '';
    return `
      <tr class="${highlightClass}">
        <td class="px-4 py-3 font-medium">${course.level || course.name}</td>
        <td class="px-4 py-3">${course.curriculum || '-'}</td>
        <td class="px-4 py-3">${course.duration || '-'}</td>
        <td class="px-4 py-3 font-semibold">${formatMoney(course.price)}</td>
      </tr>
    `;
  }
  
  // Load courses cho một category cụ thể
  async function loadCoursesByCategory(category, grid, tableBody) {
    if (!grid) return false;
    
    try {
      const courses = await courseService.getAllCourses(category);
      
      if (courses.length === 0) {
        grid.innerHTML = `
          <div class="col-span-full text-center py-8">
            <p class="text-gray-500">Chưa có khóa học nào trong danh mục này.</p>
          </div>
        `;
        if (tableBody) {
          tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Chưa có dữ liệu</td></tr>';
        }
        return false;
      }
      
      // Render cards
      grid.innerHTML = courses.map(renderCourseCard).join('');
      
      // Render table
      if (tableBody) {
        tableBody.innerHTML = courses.map(c => renderTableRow(c)).join('');
      }
      
      return true;
    } catch (err) {
      console.error(`Lỗi load courses cho ${category}:`, err);
      grid.innerHTML = `
        <div class="col-span-full text-center py-8">
          <p class="text-gray-500">Không thể tải khóa học. Vui lòng thử lại sau.</p>
        </div>
      `;
      return false;
    }
  }
  
  // Load tất cả courses theo category
  async function loadAllCourses() {
    const categories = ['tieuhoc', 'thcs', 'ielts'];
    
    for (const cat of categories) {
      const grid = courseGrids[cat];
      const tableBody = courseTables[cat];
      const section = document.querySelector(`.course-section[data-section="${cat}"]`);
      
      if (grid) {
        const hasItems = await loadCoursesByCategory(cat, grid, tableBody);
        // Hide section if no courses
        if (section && !hasItems) {
          section.style.display = 'none';
        }
      }
    }
  }
  
  // Xử lý filter tabs
  if (filterTabs.length > 0) {
    filterTabs.forEach(tab => {
      tab.addEventListener('click', function() {
        // Update active state
        filterTabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const category = (this.getAttribute('data-filter') || 'all').toLowerCase().trim();
        
        // Show/hide sections based on filter
        courseSections.forEach(section => {
          const sectionCat = section.getAttribute('data-section');
          if (category === 'all' || sectionCat === category) {
            section.style.display = '';
          } else {
            section.style.display = 'none';
          }
        });
      });
    });
  }
  
  // Load courses khi trang load
  if (Object.values(courseGrids).some(g => g !== null)) {
    loadAllCourses();
  }

  // ====== COURSE CLASSES MODAL ======
  window.showCourseClasses = async function(courseId, courseName) {
    // Create modal
    const existingModal = document.getElementById('course-classes-modal');
    if (existingModal) existingModal.remove();

    const modal = document.createElement('div');
    modal.id = 'course-classes-modal';
    modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4';
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
    
    modal.innerHTML = `
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold">📚 ${courseName}</h2>
            <p class="text-blue-100 text-sm">Danh sách lớp học & thời khóa biểu</p>
          </div>
          <button onclick="document.getElementById('course-classes-modal').remove()" class="text-white hover:bg-blue-700 p-2 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]" id="course-classes-content">
          <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Đang tải danh sách lớp học...</p>
          </div>
        </div>
      </div>
    `;
    
    document.body.appendChild(modal);
    
    // Load classes
    try {
      const classes = await courseService.getCourseClasses(courseId);
      const content = document.getElementById('course-classes-content');
      
      if (!classes || classes.length === 0) {
        content.innerHTML = `
          <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-700 mb-2">Chưa có lớp học</h3>
            <p class="text-gray-500">Khóa học này hiện chưa mở lớp. Vui lòng liên hệ để được tư vấn.</p>
            <a href="${BASE_PATH}/LienHe" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              Liên hệ tư vấn
            </a>
          </div>
        `;
        return;
      }
      
      content.innerHTML = `
        <div class="space-y-6">
          ${classes.map(cls => `
            <div class="border rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-4 border-b">
                <div class="flex justify-between items-start">
                  <div>
                    <h3 class="text-lg font-bold text-gray-800">${cls.name}</h3>
                    <p class="text-sm text-gray-500">Mã lớp: ${cls.class_code || 'N/A'}</p>
                  </div>
                  <span class="px-3 py-1 rounded-full text-sm font-medium ${
                    cls.status === 'active' ? 'bg-green-100 text-green-700' :
                    cls.status === 'upcoming' ? 'bg-yellow-100 text-yellow-700' :
                    'bg-gray-100 text-gray-700'
                  }">
                    ${cls.status === 'active' ? '🟢 Đang học' : cls.status === 'upcoming' ? '🟡 Sắp khai giảng' : cls.status}
                  </span>
                </div>
              </div>
              
              <div class="p-5">
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                  <div class="flex items-center gap-2">
                    <span class="text-blue-600">👨‍🏫</span>
                    <span class="text-gray-600">Giáo viên:</span>
                    <span class="font-medium">${cls.teacher_name || 'Đang cập nhật'}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-blue-600">👥</span>
                    <span class="text-gray-600">Sĩ số:</span>
                    <span class="font-medium">${cls.student_count || 0}/${cls.max_students || 20}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-blue-600">📅</span>
                    <span class="text-gray-600">Bắt đầu:</span>
                    <span class="font-medium">${formatDate(cls.start_date)}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-blue-600">🏁</span>
                    <span class="text-gray-600">Kết thúc:</span>
                    <span class="font-medium">${formatDate(cls.end_date)}</span>
                  </div>
                </div>
                
                ${cls.schedules && cls.schedules.length > 0 ? `
                  <div class="mt-4 pt-4 border-t">
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                      <span class="text-blue-600">🗓️</span> Thời khóa biểu
                    </h4>
                    <div class="grid gap-2">
                      ${cls.schedules.map(s => `
                        <div class="flex items-center gap-4 py-2 px-3 bg-gray-50 rounded-lg">
                          <span class="font-medium text-blue-700 min-w-[80px]">${dayMap[s.day_of_week] || s.day_of_week}</span>
                          <span class="text-gray-600">${formatTime(s.start_time)} - ${formatTime(s.end_time)}</span>
                          <span class="text-gray-500">📍 ${s.is_online ? '🌐 Online' : (s.room || 'Phòng N/A')}</span>
                          ${s.teacher_name && s.teacher_name !== cls.teacher_name ? `<span class="text-gray-500">GV: ${s.teacher_name}</span>` : ''}
                        </div>
                      `).join('')}
                    </div>
                  </div>
                ` : `
                  <div class="mt-4 pt-4 border-t text-center text-gray-500">
                    <p>📅 Thời khóa biểu: ${cls.schedule || cls.schedule_formatted || 'Đang cập nhật'}</p>
                  </div>
                `}
                
                <div class="mt-4 pt-4 border-t flex justify-end gap-3">
                  <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors enroll-course-btn" data-course-id="${course.id}" data-course-name="${courseName.replace(/"/g, '&quot;')}">
                    Đăng ký lớp này
                  </button>
                </div>
              </div>
            </div>
          `).join('')}
        </div>
      `;
    } catch (error) {
      console.error('Error loading classes:', error);
      document.getElementById('course-classes-content').innerHTML = `
        <div class="text-center py-8 text-red-500">
          <p>Có lỗi xảy ra khi tải danh sách lớp học.</p>
          <p class="text-sm">${error.message}</p>
        </div>
      `;
    }
  };
  
  // ====== ENROLLMENT HANDLER ======
  // Handle enrollment button clicks
  document.body.addEventListener('click', async function(e) {
    const enrollBtn = e.target.closest('.enroll-course-btn');
    if (!enrollBtn) return;
    
    e.preventDefault();
    
    const courseId = enrollBtn.dataset.courseId;
    const courseName = enrollBtn.dataset.courseName;
    
    // Check if user is logged in by checking the user-menu visibility or localStorage cache
    const userMenu = document.getElementById('user-menu');
    const isLoggedIn = (userMenu && !userMenu.classList.contains('hidden')) || 
                       localStorage.getItem('hai_au_user_id');
    
    if (!isLoggedIn) {
      // Try to verify with API first
      try {
        const checkResponse = await fetch(`${BASE_PATH}/backend/php/auth.php?action=check`, {
          credentials: 'include'
        });
        const checkResult = await checkResponse.json();
        
        if (!checkResult.loggedIn) {
          if (confirm(`Bạn cần đăng nhập để đăng ký khóa học "${courseName}".\nBạn có muốn đăng nhập ngay?`)) {
            window.location.href = BASE_PATH + '/DangNhap?redirect=' + encodeURIComponent(window.location.pathname);
          }
          return;
        }
      } catch (e) {
        if (confirm(`Bạn cần đăng nhập để đăng ký khóa học "${courseName}".\nBạn có muốn đăng nhập ngay?`)) {
          window.location.href = BASE_PATH + '/DangNhap?redirect=' + encodeURIComponent(window.location.pathname);
        }
        return;
      }
    }
    
    // Confirm enrollment
    if (!confirm(`Bạn muốn đăng ký khóa học "${courseName}"?\n\nSau khi đăng ký, admin sẽ xác nhận và liên hệ với bạn.`)) {
      return;
    }
    
    // Disable button and show loading
    const originalText = enrollBtn.innerHTML;
    enrollBtn.disabled = true;
    enrollBtn.innerHTML = '<span class="animate-spin inline-block mr-2">⏳</span> Đang xử lý...';
    
    try {
      const response = await fetch(`${BASE_PATH}/backend/php/profile.php?action=enroll-course`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ course_id: parseInt(courseId) })
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Show success message
        showToast(result.message || 'Đăng ký thành công!', 'success');
        enrollBtn.innerHTML = '✅ Đã đăng ký';
        enrollBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        enrollBtn.classList.add('bg-green-600', 'cursor-not-allowed');
      } else {
        showToast(result.error || 'Có lỗi xảy ra', 'error');
        enrollBtn.disabled = false;
        enrollBtn.innerHTML = originalText;
      }
    } catch (error) {
      console.error('Enrollment error:', error);
      showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
      enrollBtn.disabled = false;
      enrollBtn.innerHTML = originalText;
    }
  });
  
  // Simple toast notification
  function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.course-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `course-toast fixed bottom-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ${
      type === 'success' ? 'bg-green-600 text-white' : 
      type === 'error' ? 'bg-red-600 text-white' : 
      'bg-gray-800 text-white'
    }`;
    toast.innerHTML = `
      <div class="flex items-center gap-2">
        <span>${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span>
        <span>${message}</span>
      </div>
    `;
    document.body.appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
      toast.classList.add('opacity-0', 'translate-y-2');
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }
});

export default null;

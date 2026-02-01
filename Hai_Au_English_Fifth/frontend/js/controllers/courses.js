// courses.js - Xử lý hiển thị và quản lý khóa học với backend PHP
import courseService from '../services/courseService.js';

document.addEventListener('DOMContentLoaded', function() {
  const coursesGrid = document.querySelector('.grid.md\\:grid-cols-2.lg\\:grid-cols-3');
  const filterTabs = document.querySelectorAll('.filter-tab');
  
  // Render một course card
  function renderCourseCard(course) {
    const levelInfo = courseService.getLevelInfo(course.level);
    const features = Array.isArray(course.features) ? course.features : [];
    const badgeHtml = course.badge 
      ? `<div class="course-badge ${course.badge_type || ''}">${course.badge}</div>` 
      : '';
    
    return `
      <div class="course-card" data-category="${course.category}">
        <div class="course-image">
          <img src="${course.image_url}" alt="${course.name}">
          ${badgeHtml}
        </div>
        <div class="course-content">
          <div class="course-meta">
            <span class="course-level ${levelInfo.class}">${levelInfo.text}</span>
            <span class="course-duration">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              ${course.duration}
            </span>
          </div>
          <h3 class="course-title">${course.name}</h3>
          <p class="course-description">${course.description}</p>
          <ul class="course-features">
            ${features.map(f => `
              <li>
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                ${f}
              </li>
            `).join('')}
          </ul>
          <div class="course-footer">
            <div class="course-price">
              <span class="price-amount">${courseService.formatPrice(course.price, '')}</span>
              <span class="price-label">${course.price_unit}</span>
            </div>
            <a href="contact.html" class="course-button">${course.category === 'private' ? 'Tư vấn ngay' : 'Đăng ký ngay'}</a>
          </div>
        </div>
      </div>
    `;
  }
  
  // Load và hiển thị courses
  async function loadCourses(category = 'all') {
    if (!coursesGrid) return;
    
    try {
      coursesGrid.innerHTML = `
        <div class="col-span-full text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600">Đang tải khóa học...</p>
        </div>
      `;
      
      const courses = await courseService.getAllCourses(category);
      
      if (courses.length === 0) {
        coursesGrid.innerHTML = `
          <div class="col-span-full text-center py-8">
            <p class="text-gray-600">Không có khóa học nào trong danh mục này.</p>
          </div>
        `;
        return;
      }
      
      coursesGrid.innerHTML = courses.map(renderCourseCard).join('');
    } catch (err) {
      console.error('Lỗi load courses:', err);
      coursesGrid.innerHTML = `
        <div class="col-span-full text-center py-8">
          <p class="text-red-600">Lỗi: ${err.message}</p>
          <button onclick="location.reload()" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Thử lại
          </button>
        </div>
      `;
    }
  }
  
  // Xử lý filter tabs
  if (filterTabs.length > 0) {
    filterTabs.forEach(tab => {
      tab.addEventListener('click', function() {
        // Update active state
        filterTabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // Load courses theo category
        const category = this.getAttribute('data-filter');
        loadCourses(category);
      });
    });
  }
  
  // Load courses khi trang load
  if (coursesGrid) {
    loadCourses();
  }
});

export default null;




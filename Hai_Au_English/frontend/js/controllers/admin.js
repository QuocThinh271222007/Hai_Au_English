/**
 * Admin Controller - Điều khiển trang admin dashboard
 * Bao gồm CRUD cho tất cả các bảng và quản lý thùng rác
 */

import { adminService } from '../services/adminService.js';
import { BASE_PATH } from '../config.js';

// Helper function để tạo URL đúng với base path
function getUrl(path) {
    return BASE_PATH + path;
}

// Use global showToast from toast.js (will be available after DOMContentLoaded)
function showToast(msg, type) {
    if (window.showToast) {
        window.showToast(msg, type);
    } else {
        console.log(type + ': ' + msg);
        alert(msg);
    }
}

// ==================== HELPER FUNCTIONS ====================

// Escape HTML special characters
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Biến global lưu trạng thái admin đã được xác thực
let isAdminVerified = false;
let cachedDashboardData = null;

// Kiểm tra quyền admin
async function checkAdmin() {
    try {
        const result = await adminService.getDashboard();
        console.log('Dashboard API result:', result);
        
        if (result.error || !result.success) {
            // Log error but don't redirect - PHP already handles auth
            console.error('Dashboard load error:', result.error || result.message);
            isAdminVerified = false;
            // Show error message instead of redirect
            showToast(result.error || result.message || 'Không thể tải dữ liệu', 'error');
            return null;
        }
        isAdminVerified = true;
        cachedDashboardData = result;
        return result;
    } catch (error) {
        console.error('Dashboard exception:', error);
        isAdminVerified = false;
        showToast('Lỗi kết nối: ' + error.message, 'error');
        return null;
    }
}

// Format ngày
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN');
}

// Format ngày giờ
function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleString('vi-VN');
}

// Format số tiền
function formatMoney(amount) {
    if (!amount) return '-';
    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
}

// Format status badge
function getStatusBadge(status) {
    const labels = {
        'active': 'Đang học',
        'pending': 'Chờ xử lý',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy'
    };
    return `<span class="status-badge ${status}">${labels[status] || status}</span>`;
}

// Format table name
function getTableLabel(tableName) {
    const labels = {
        'users': 'Học viên',
        'courses': 'Khóa học',
        'enrollments': 'Đăng ký',
        'teachers': 'Giảng viên',
        'scores': 'Điểm số',
        'feedback': 'Nhận xét'
    };
    return labels[tableName] || tableName;
}

// Lấy mô tả dữ liệu trong thùng rác
function getTrashItemDescription(item) {
    const data = item.data;
    switch (item.original_table) {
        case 'users':
            return `${data.fullname || 'N/A'} (${data.email || 'N/A'})`;
        case 'courses':
            return data.name || 'Khóa học không tên';
        case 'enrollments':
            return `Đăng ký #${data.id}`;
        case 'teachers':
            return data.name || 'Giảng viên không tên';
        case 'scores':
            return `Điểm: L${data.listening || 0} R${data.reading || 0} W${data.writing || 0} S${data.speaking || 0}`;
        case 'feedback':
            return (data.content || '').substring(0, 50) + '...';
        default:
            return `ID: ${data.id}`;
    }
}

// ==================== RENDER FUNCTIONS ====================

// Render Dashboard
async function renderDashboard() {
    try {
        // Sử dụng cached data nếu có, tránh gọi API trùng lặp
        let result;
        if (cachedDashboardData && cachedDashboardData.success) {
            result = cachedDashboardData;
            cachedDashboardData = null; // Clear cache sau khi dùng
        } else {
            result = await adminService.getDashboard();
        }
        
        if (!result.success) return;

        const { stats, recent_enrollments } = result;

        // Stats
        document.getElementById('stat-users').textContent = stats.users || 0;
        document.getElementById('stat-enrollments').textContent = stats.enrollments || 0;
        document.getElementById('stat-courses').textContent = stats.courses || 0;
        document.getElementById('stat-teachers').textContent = stats.teachers || 0;

        // Update trash badge
        updateTrashBadge(stats.trash || 0);

        // Recent enrollments
        const tbody = document.getElementById('recent-enrollments-tbody');
        if (recent_enrollments?.length) {
            tbody.innerHTML = recent_enrollments.map(e => `
                <tr>
                    <td>${e.fullname}</td>
                    <td>${e.email}</td>
                    <td>${e.course_name}</td>
                    <td>${getStatusBadge(e.status)}</td>
                    <td>${formatDate(e.created_at)}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-500">Chưa có đăng ký</td></tr>';
        }
        
        // Render Charts
        await renderDashboardCharts();
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

// Render Dashboard Charts
async function renderDashboardCharts() {
    try {
        // Get data for charts
        const [enrollmentsResult, coursesResult, scoresResult] = await Promise.all([
            adminService.getEnrollments(),
            adminService.getCourses(),
            adminService.getScores()
        ]);

        // 1. Enrollment Status Pie Chart
        if (enrollmentsResult.success && enrollmentsResult.enrollments) {
            const statusCounts = {};
            enrollmentsResult.enrollments.forEach(e => {
                statusCounts[e.status] = (statusCounts[e.status] || 0) + 1;
            });
            
            const statusLabels = {
                'pending': 'Chờ xử lý',
                'active': 'Đang học',
                'completed': 'Hoàn thành',
                'cancelled': 'Đã hủy'
            };
            
            const enrollmentCtx = document.getElementById('enrollmentPieChart');
            if (enrollmentCtx) {
                new Chart(enrollmentCtx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(statusCounts).map(k => statusLabels[k] || k),
                        datasets: [{
                            data: Object.values(statusCounts),
                            backgroundColor: ['#fbbf24', '#3b82f6', '#10b981', '#ef4444'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        }

        // 2. Course Distribution Pie Chart
        if (coursesResult.success && coursesResult.courses && enrollmentsResult.success) {
            const courseCounts = {};
            enrollmentsResult.enrollments.forEach(e => {
                const courseName = e.course_name || 'Khác';
                courseCounts[courseName] = (courseCounts[courseName] || 0) + 1;
            });
            
            const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#84cc16'];
            
            const courseCtx = document.getElementById('coursePieChart');
            if (courseCtx) {
                new Chart(courseCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(courseCounts),
                        datasets: [{
                            data: Object.values(courseCounts),
                            backgroundColor: colors.slice(0, Object.keys(courseCounts).length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        }

        // 3. Monthly Enrollments Line + Bar Chart
        if (enrollmentsResult.success && enrollmentsResult.enrollments) {
            const monthlyData = {};
            const now = new Date();
            
            // Initialize last 6 months
            for (let i = 5; i >= 0; i--) {
                const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
                monthlyData[key] = { total: 0, active: 0, completed: 0 };
            }
            
            enrollmentsResult.enrollments.forEach(e => {
                if (e.created_at) {
                    const date = new Date(e.created_at);
                    const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                    if (monthlyData[key]) {
                        monthlyData[key].total++;
                        if (e.status === 'active') monthlyData[key].active++;
                        if (e.status === 'completed') monthlyData[key].completed++;
                    }
                }
            });
            
            const months = Object.keys(monthlyData);
            const monthLabels = months.map(m => {
                const [y, mo] = m.split('-');
                return `T${parseInt(mo)}/${y}`;
            });
            
            const monthlyCtx = document.getElementById('monthlyEnrollmentChart');
            if (monthlyCtx) {
                new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: monthLabels,
                        datasets: [
                            {
                                type: 'line',
                                label: 'Tổng đăng ký',
                                data: months.map(m => monthlyData[m].total),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                fill: true,
                                yAxisID: 'y'
                            },
                            {
                                type: 'bar',
                                label: 'Đang học',
                                data: months.map(m => monthlyData[m].active),
                                backgroundColor: '#10b981',
                                yAxisID: 'y'
                            },
                            {
                                type: 'bar',
                                label: 'Hoàn thành',
                                data: months.map(m => monthlyData[m].completed),
                                backgroundColor: '#8b5cf6',
                                yAxisID: 'y'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        }

        // 4. Score Distribution Bar Chart
        if (scoresResult.success && scoresResult.scores && scoresResult.scores.length > 0) {
            const scoreRanges = {
                '0-3.5': 0,
                '4.0-5.0': 0,
                '5.5-6.5': 0,
                '7.0-7.5': 0,
                '8.0-9.0': 0
            };
            
            scoresResult.scores.forEach(s => {
                const overall = parseFloat(s.overall) || 0;
                if (overall <= 3.5) scoreRanges['0-3.5']++;
                else if (overall <= 5.0) scoreRanges['4.0-5.0']++;
                else if (overall <= 6.5) scoreRanges['5.5-6.5']++;
                else if (overall <= 7.5) scoreRanges['7.0-7.5']++;
                else scoreRanges['8.0-9.0']++;
            });
            
            const scoreCtx = document.getElementById('scoreDistributionChart');
            if (scoreCtx) {
                new Chart(scoreCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(scoreRanges),
                        datasets: [{
                            label: 'Số học viên',
                            data: Object.values(scoreRanges),
                            backgroundColor: [
                                '#ef4444',
                                '#f59e0b',
                                '#3b82f6',
                                '#10b981',
                                '#8b5cf6'
                            ],
                            borderWidth: 0,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                title: {
                                    display: true,
                                    text: 'Số học viên'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Khoảng điểm IELTS'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        } else {
            // No scores data - show placeholder
            const scoreCtx = document.getElementById('scoreDistributionChart');
            if (scoreCtx) {
                const ctx = scoreCtx.getContext('2d');
                ctx.font = '16px Arial';
                ctx.fillStyle = '#9ca3af';
                ctx.textAlign = 'center';
                ctx.fillText('Chưa có dữ liệu điểm số', scoreCtx.width / 2, scoreCtx.height / 2);
            }
        }
    } catch (error) {
        console.error('Error rendering charts:', error);
    }
}

// Render Users
async function renderUsers() {
    const tbody = document.getElementById('users-tbody');
    try {
        const result = await adminService.getUsers();
        if (!result.success || !result.users?.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-gray-500">Chưa có học viên</td></tr>';
            return;
        }

        tbody.innerHTML = result.users.map(u => `
            <tr>
                <td>${u.id}</td>
                <td>${escapeHtml(u.fullname)}</td>
                <td>${escapeHtml(u.email)}</td>
                <td>${escapeHtml(u.phone) || '-'}</td>
                <td>
                    <span class="status-badge ${u.is_active ? 'active' : 'cancelled'}">
                        ${u.is_active ? 'Hoạt động' : 'Bị khóa'}
                    </span>
                </td>
                <td>${formatDate(u.created_at)}</td>
                <td>
                    <button class="admin-action-btn info view-profile-btn" 
                            data-id="${u.id}" data-user='${JSON.stringify(u).replace(/'/g, "&#39;")}' title="Xem profile">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Xem
                    </button>
                    ${u.role !== 'admin' ? `
                        <button class="admin-action-btn secondary edit-user-btn" 
                                data-user='${JSON.stringify(u).replace(/'/g, "&#39;")}'>Sửa</button>
                    ` : ''}
                    <button class="admin-action-btn ${u.is_active ? 'warning' : 'primary'} toggle-user-btn" 
                            data-id="${u.id}" data-active="${u.is_active ? '0' : '1'}">
                        ${u.is_active ? 'Khóa' : 'Mở khóa'}
                    </button>
                    ${u.role !== 'admin' ? `
                        <button class="admin-action-btn danger delete-user-btn" data-id="${u.id}">Xóa</button>
                    ` : ''}
                </td>
            </tr>
        `).join('');

        // View profile handlers
        tbody.querySelectorAll('.view-profile-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const user = JSON.parse(btn.dataset.user);
                showUserProfileModal(user);
            });
        });

        // Edit handlers
        tbody.querySelectorAll('.edit-user-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const user = JSON.parse(btn.dataset.user);
                showUserModal(user);
            });
        });

        // Toggle status handlers
        tbody.querySelectorAll('.toggle-user-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const userId = btn.dataset.id;
                const isActive = btn.dataset.active === '1' ? 1 : 0;
                try {
                    const result = await adminService.updateUserStatus(userId, isActive);
                    if (result.success) {
                        showToast('Cập nhật thành công!', 'success');
                        renderUsers();
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });

        // Delete handlers
        tbody.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn có chắc muốn xóa học viên này?\nDữ liệu sẽ được chuyển vào thùng rác.')) return;
                try {
                    const result = await adminService.deleteUser(btn.dataset.id);
                    if (result.success) {
                        showToast('Đã chuyển vào thùng rác!', 'success');
                        renderUsers();
                        updateTrashCount();
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-red-500">Lỗi tải dữ liệu</td></tr>';
    }
}

// Render Enrollments - Show courses with enrollment counts
async function renderEnrollments(courseStatus = '', categoryFilter = '') {
    const grid = document.getElementById('enrollment-courses-grid');
    if (!grid) return;
    
    const categoryLabels = {
        tieuhoc: { text: 'Tiểu học', bg: '#dcfce7', color: '#166534' },
        thcs: { text: 'THCS', bg: '#dbeafe', color: '#1e40af' },
        ielts: { text: 'IELTS', bg: '#f3e8ff', color: '#7c3aed' }
    };
    
    try {
        // Get courses and enrollments
        const [coursesResult, enrollmentsResult] = await Promise.all([
            adminService.getCourses(),
            adminService.getEnrollments()
        ]);
        
        if (!coursesResult.success || !coursesResult.courses?.length) {
            grid.innerHTML = '<div class="col-span-full text-center text-gray-500 py-8">Không có khóa học nào</div>';
            return;
        }
        
        // Count enrollments per course
        const enrollmentCounts = {};
        if (enrollmentsResult.success && enrollmentsResult.enrollments) {
            enrollmentsResult.enrollments.forEach(e => {
                if (!enrollmentCounts[e.course_id]) {
                    enrollmentCounts[e.course_id] = { total: 0, active: 0, pending: 0 };
                }
                enrollmentCounts[e.course_id].total++;
                if (e.status === 'active') enrollmentCounts[e.course_id].active++;
                if (e.status === 'pending') enrollmentCounts[e.course_id].pending++;
            });
        }
        
        // Filter courses
        let filteredCourses = coursesResult.courses;
        
        if (courseStatus === 'open') {
            filteredCourses = filteredCourses.filter(c => c.is_active === 1 || c.is_active === '1');
        } else if (courseStatus === 'closed') {
            filteredCourses = filteredCourses.filter(c => c.is_active === 0 || c.is_active === '0');
        }
        
        if (categoryFilter) {
            filteredCourses = filteredCourses.filter(c => c.age_group === categoryFilter);
        }
        
        if (!filteredCourses.length) {
            grid.innerHTML = '<div class="col-span-full text-center text-gray-500 py-8">Không tìm thấy khóa học phù hợp</div>';
            return;
        }
        
        grid.innerHTML = filteredCourses.map(course => {
            const counts = enrollmentCounts[course.id] || { total: 0, active: 0, pending: 0 };
            const categoryStyle = categoryLabels[course.age_group] || { text: course.age_group, bg: '#f3f4f6', color: '#374151' };
            const defaultImg = 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=400&h=250&fit=crop';
            const imgUrl = course.image_url ? (course.image_url.startsWith('http') ? course.image_url : BASE_PATH + course.image_url) : defaultImg;
            
            return `
                <div class="enrollment-course-card bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                     data-course-id="${course.id}" data-course='${JSON.stringify(course).replace(/'/g, "&#39;")}'>
                    <div class="relative">
                        <img src="${imgUrl}" alt="${course.name}" 
                             class="w-full h-32 object-cover"
                             onerror="this.src='${defaultImg}'">
                        <span class="absolute top-2 right-2 px-2 py-1 text-xs font-medium rounded"
                              style="background: ${categoryStyle.bg}; color: ${categoryStyle.color}">
                            ${categoryStyle.text}
                        </span>
                        ${course.is_active ? '' : '<span class="absolute top-2 left-2 px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-600">Đã đóng</span>'}
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">${course.name}</h3>
                        <p class="text-sm text-gray-600 mb-3">${course.curriculum || 'Chưa cập nhật giáo trình'}</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-lg font-bold text-blue-600">${counts.total}</span>
                                <span class="text-sm text-gray-500">học viên</span>
                            </div>
                            ${counts.pending > 0 ? `<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">${counts.pending} chờ duyệt</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Add click handlers
        grid.querySelectorAll('.enrollment-course-card').forEach(card => {
            card.addEventListener('click', () => {
                const course = JSON.parse(card.dataset.course.replace(/&#39;/g, "'"));
                showEnrolledStudentsModal(course);
            });
        });
        
    } catch (error) {
        console.error('Error loading enrollments:', error);
        grid.innerHTML = '<div class="col-span-full text-center text-red-500 py-8">Lỗi tải dữ liệu</div>';
    }
}

// Show enrolled students modal
async function showEnrolledStudentsModal(course) {
    const modal = document.getElementById('enrolled-students-modal');
    const tbody = document.getElementById('enrolled-students-tbody');
    const categoryLabels = {
        tieuhoc: 'Tiểu học',
        thcs: 'THCS',
        ielts: 'IELTS'
    };
    
    const defaultImg = 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=200&h=120&fit=crop';
    const imgUrl = course.image_url ? (course.image_url.startsWith('http') ? course.image_url : BASE_PATH + course.image_url) : defaultImg;
    
    // Set course info
    document.getElementById('enrolled-modal-title').textContent = `Học viên đăng ký: ${course.name}`;
    const courseImg = document.getElementById('enrolled-course-image');
    courseImg.src = imgUrl;
    courseImg.onerror = function() { this.src = defaultImg; };
    document.getElementById('enrolled-course-name').textContent = course.name;
    document.getElementById('enrolled-course-info').textContent = `${categoryLabels[course.age_group] || course.age_group} | ${course.curriculum || 'Chưa cập nhật'}`;
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Load enrolled students
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner"></div></td></tr>';
    
    try {
        const result = await adminService.getEnrollments();
        if (!result.success) throw new Error('Failed to load enrollments');
        
        const courseEnrollments = (result.enrollments || []).filter(e => e.course_id == course.id);
        
        if (!courseEnrollments.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-gray-500 py-4">Chưa có học viên đăng ký khóa học này</td></tr>';
            return;
        }
        
        tbody.innerHTML = courseEnrollments.map((e, index) => `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>
                    <div class="font-medium">${e.fullname}</div>
                    <div class="text-xs text-gray-500">${e.student_code || ''}</div>
                </td>
                <td>${e.email || '-'}</td>
                <td>${e.created_at ? new Date(e.created_at).toLocaleDateString('vi-VN') : '-'}</td>
                <td>${getStatusBadge(e.status)}</td>
                <td>
                    <select class="enrollment-status-select text-sm border rounded px-2 py-1" data-enrollment-id="${e.id}">
                        <option value="pending" ${e.status === 'pending' ? 'selected' : ''}>Chờ duyệt</option>
                        <option value="active" ${e.status === 'active' ? 'selected' : ''}>Đang học</option>
                        <option value="completed" ${e.status === 'completed' ? 'selected' : ''}>Hoàn thành</option>
                        <option value="cancelled" ${e.status === 'cancelled' ? 'selected' : ''}>Đã hủy</option>
                    </select>
                </td>
            </tr>
        `).join('');
        
        // Add status change handlers
        tbody.querySelectorAll('.enrollment-status-select').forEach(select => {
            select.addEventListener('change', async (e) => {
                const enrollmentId = select.dataset.enrollmentId;
                const newStatus = select.value;
                try {
                    const result = await adminService.updateEnrollment({ id: enrollmentId, status: newStatus });
                    if (result.success) {
                        showToast('Đã cập nhật trạng thái', 'success');
                        renderEnrollments(); // Refresh grid
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                        showEnrolledStudentsModal(course); // Reload modal
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });
        
    } catch (error) {
        console.error('Error:', error);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-red-500 py-4">Lỗi tải dữ liệu</td></tr>';
    }
    
    // Close modal handler
    modal.querySelector('.close-enrolled-modal').onclick = () => modal.classList.add('hidden');
    modal.onclick = (e) => {
        if (e.target === modal) modal.classList.add('hidden');
    };
}

// Store all courses for filtering
let allCoursesData = [];

// Render Courses
async function renderCourses(searchTerm = '', categoryFilter = 'all') {
    const tbody = document.getElementById('courses-tbody');
    const categoryLabels = {
        tieuhoc: { text: 'Tiểu học', bg: '#dcfce7', color: '#166534' },
        thcs: { text: 'THCS', bg: '#dbeafe', color: '#1e40af' },
        ielts: { text: 'IELTS', bg: '#f3e8ff', color: '#7c3aed' }
    };
    
    // Helper to get course image URL
    const getCourseImageUrl = (url) => {
        if (!url) return 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=80&h=50&fit=crop';
        if (url.startsWith('http')) return url;
        if (url.startsWith('/') && !url.toLowerCase().startsWith('/hai_au_english')) {
            return BASE_PATH + url;
        }
        return url;
    };
    
    try {
        // Only fetch if we don't have data or need refresh
        if (allCoursesData.length === 0 || (!searchTerm && categoryFilter === 'all')) {
            const result = await adminService.getCourses();
            allCoursesData = result.courses || [];
        }
        
        // Filter courses by age_group (not category)
        let filteredCourses = [...allCoursesData];
        
        if (categoryFilter && categoryFilter !== 'all') {
            filteredCourses = filteredCourses.filter(c => c.age_group === categoryFilter);
        }
        
        if (searchTerm) {
            const term = searchTerm.toLowerCase();
            filteredCourses = filteredCourses.filter(c => 
                (c.name || '').toLowerCase().includes(term) ||
                (c.curriculum || '').toLowerCase().includes(term) ||
                (c.level || '').toLowerCase().includes(term) ||
                (c.description || '').toLowerCase().includes(term)
            );
        }
        
        if (!filteredCourses.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-gray-500">Không tìm thấy khóa học</td></tr>';
            return;
        }

        tbody.innerHTML = filteredCourses.map(c => {
            const cat = categoryLabels[c.age_group] || { text: c.age_group || '-', bg: '#f3f4f6', color: '#374151' };
            return `
            <tr>
                <td>
                    <img src="${getCourseImageUrl(c.image_url)}" alt="${escapeHtml(c.name)}" class="w-16 h-10 object-cover rounded" onerror="this.src='https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=80&h=50&fit=crop'">
                </td>
                <td>${c.name}</td>
                <td><span class="px-2 py-1 rounded text-xs font-medium whitespace-nowrap" style="background: ${cat.bg}; color: ${cat.color};">${cat.text}</span></td>
                <td>${c.curriculum || '-'}</td>
                <td>${c.duration || '-'}</td>
                <td>${formatMoney(c.price)}/tháng</td>
                <td>
                    <button class="admin-action-btn info view-course-classes-btn" data-id="${c.id}" data-name="${escapeHtml(c.name)}" title="Xem các lớp">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </button>
                    <button class="admin-action-btn secondary edit-course-btn" data-id="${c.id}" data-course='${JSON.stringify(c).replace(/'/g, "\\'")}'>Sửa</button>
                    <button class="admin-action-btn danger delete-course-btn" data-id="${c.id}">Xóa</button>
                </td>
            </tr>
        `}).join('');

        // View classes handler
        tbody.querySelectorAll('.view-course-classes-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const courseId = btn.dataset.id;
                const courseName = btn.dataset.name;
                await showCourseClassesModal(courseId, courseName);
            });
        });

        // Edit handlers
        tbody.querySelectorAll('.edit-course-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const course = JSON.parse(btn.dataset.course.replace(/\\'/g, "'"));
                showCourseModal(course);
            });
        });

        // Delete handlers
        tbody.querySelectorAll('.delete-course-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn có chắc muốn xóa khóa học này?')) return;
                try {
                    const result = await adminService.deleteCourse(btn.dataset.id);
                    if (result.success) {
                        showToast('Đã chuyển vào thùng rác!', 'success');
                        allCoursesData = []; // Clear cache
                        renderCourses();
                        updateTrashCount();
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-red-500">Lỗi tải dữ liệu</td></tr>';
    }
}

// ==================== CLASSES (LỚP HỌC) ====================
let allClasses = [];
let allCoursesForClasses = [];
let allTeachersForClasses = [];

async function renderClasses() {
    const tbody = document.getElementById('classes-tbody');
    if (!tbody) return;
    
    // Show loading
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8"><div class="spinner"></div></td></tr>';
    
    try {
        const [classesResult, coursesResult, teachersResult] = await Promise.all([
            adminService.getClasses(),
            adminService.getCourses(),
            adminService.getTeachers()
        ]);
        
        allClasses = classesResult.classes || [];
        allCoursesForClasses = coursesResult.courses || [];
        allTeachersForClasses = teachersResult.teachers || [];
        
        // Populate course filter
        const courseFilter = document.getElementById('filter-classes-course');
        if (courseFilter) {
            courseFilter.innerHTML = '<option value="all">Tất cả khóa học</option>' +
                allCoursesForClasses.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
        }
        
        renderClassesTable(allClasses);
    } catch (error) {
        console.error('renderClasses error:', error);
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-red-500 py-8">Lỗi tải dữ liệu: ' + error.message + '</td></tr>';
        }
    }
}

function renderClassesTable(classes) {
    const tbody = document.getElementById('classes-tbody');
    if (!classes?.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-gray-500">Chưa có lớp học</td></tr>';
        return;
    }
    
    const getStatusBadgeClass = (status) => {
        const badges = {
            'upcoming': 'pending',
            'active': 'active',
            'completed': 'completed',
            'cancelled': 'cancelled'
        };
        return badges[status] || 'pending';
    };
    
    const getStatusLabel = (status) => {
        const labels = {
            'upcoming': 'Sắp khai giảng',
            'active': 'Đang học',
            'completed': 'Đã kết thúc',
            'cancelled': 'Đã hủy'
        };
        return labels[status] || status;
    };

    tbody.innerHTML = classes.map(c => `
        <tr>
            <td>
                <div class="font-medium text-gray-900">${escapeHtml(c.name)}</div>
                ${c.room ? `<div class="text-sm text-gray-500">Phòng: ${escapeHtml(c.room)}</div>` : ''}
            </td>
            <td>${escapeHtml(c.course_name) || '-'}</td>
            <td>${escapeHtml(c.teacher_name) || '<span class="text-gray-400">Chưa phân công</span>'}</td>
            <td>${escapeHtml(c.schedule) || '-'}</td>
            <td>
                <span class="font-medium">${c.student_count || 0}</span>/${c.max_students || 20}
            </td>
            <td>
                <div class="text-sm">
                    ${c.start_date ? formatDate(c.start_date) : 'Chưa xác định'}
                    ${c.end_date ? ' - ' + formatDate(c.end_date) : ''}
                </div>
                ${c.academic_year ? `<div class="text-xs text-gray-500">${escapeHtml(c.academic_year)} ${c.semester ? '- ' + escapeHtml(c.semester) : ''}</div>` : ''}
            </td>
            <td><span class="status-badge ${getStatusBadgeClass(c.status)}">${getStatusLabel(c.status)}</span></td>
            <td>
                <div class="flex gap-1 flex-wrap">
                    <button class="admin-action-btn info view-class-btn" data-id="${c.id}" title="Xem danh sách học viên">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                    <button class="admin-action-btn secondary edit-class-btn" data-id="${c.id}">Sửa</button>
                    <button class="admin-action-btn danger delete-class-btn" data-id="${c.id}">Xóa</button>
                </div>
            </td>
        </tr>
    `).join('');

    // View class students handlers
    tbody.querySelectorAll('.view-class-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const classData = allClasses.find(c => c.id == btn.dataset.id);
            if (classData) showClassStudentsModal(classData);
        });
    });

    // Edit handlers
    tbody.querySelectorAll('.edit-class-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const classData = allClasses.find(c => c.id == btn.dataset.id);
            if (classData) showClassModal(classData);
        });
    });

    // Delete handlers
    tbody.querySelectorAll('.delete-class-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa lớp học này?')) return;
            const result = await adminService.deleteClass(btn.dataset.id);
            if (result.success) {
                showToast('Xóa lớp học thành công', 'success');
                renderClasses();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
}

function showClassModal(classData = null) {
    const isEdit = !!classData;
    const modal = document.getElementById('modal-container');
    const content = document.getElementById('modal-content');
    
    const coursesOptions = allCoursesForClasses.map(c => 
        `<option value="${c.id}" ${classData?.course_id == c.id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`
    ).join('');
    
    const teachersOptions = allTeachersForClasses.map(t => 
        `<option value="${t.id}" ${classData?.teacher_id == t.id ? 'selected' : ''}>${escapeHtml(t.name)}</option>`
    ).join('');

    content.innerHTML = `
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">${isEdit ? 'Sửa lớp học' : 'Thêm lớp học mới'}</h3>
            <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        ${isEdit ? `
        <!-- Tabs for Edit Mode -->
        <div class="flex border-b mb-4">
            <button id="tab-info" class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-medium">
                Thông tin lớp
            </button>
            <button id="tab-schedule" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                Thời khóa biểu
            </button>
        </div>
        ` : ''}
        
        <div id="content-info">
        <form id="class-form">
            <input type="hidden" name="id" value="${classData?.id || ''}">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="profile-form-group col-span-2">
                    <label class="profile-form-label">Tên lớp *</label>
                    <input type="text" name="name" class="profile-form-input" value="${escapeHtml(classData?.name || '')}" required placeholder="VD: IELTS 6.5 - Lớp A">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Khóa học *</label>
                    <select name="course_id" class="profile-form-input" required>
                        <option value="">Chọn khóa học</option>
                        ${coursesOptions}
                    </select>
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Giảng viên chính</label>
                    <select name="teacher_id" class="profile-form-input">
                        <option value="">Chưa phân công</option>
                        ${teachersOptions}
                    </select>
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Sĩ số tối đa</label>
                    <input type="number" name="max_students" class="profile-form-input" value="${classData?.max_students || 20}" min="1" max="100">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Phòng học mặc định</label>
                    <input type="text" name="room" class="profile-form-input" value="${escapeHtml(classData?.room || '')}" placeholder="VD: P201">
                </div>
                
                <div class="profile-form-group col-span-2">
                    <label class="profile-form-label">Lịch học tổng quan</label>
                    <input type="text" name="schedule" class="profile-form-input" value="${escapeHtml(classData?.schedule || '')}" placeholder="Tự động cập nhật từ thời khóa biểu" ${isEdit ? 'readonly' : ''}>
                    ${isEdit ? '<p class="text-xs text-gray-500 mt-1">* Lịch học sẽ tự động cập nhật khi thiết lập thời khóa biểu chi tiết</p>' : ''}
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Ngày bắt đầu</label>
                    <input type="date" name="start_date" class="profile-form-input" value="${classData?.start_date || ''}">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Ngày kết thúc</label>
                    <input type="date" name="end_date" class="profile-form-input" value="${classData?.end_date || ''}">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Năm học</label>
                    <input type="text" name="academic_year" class="profile-form-input" value="${escapeHtml(classData?.academic_year || '')}" placeholder="VD: 2025-2026">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Học kỳ</label>
                    <input type="text" name="semester" class="profile-form-input" value="${escapeHtml(classData?.semester || '')}" placeholder="VD: Học kỳ 1">
                </div>
                
                <div class="profile-form-group col-span-2">
                    <label class="profile-form-label">Trạng thái</label>
                    <select name="status" class="profile-form-input">
                        <option value="upcoming" ${classData?.status === 'upcoming' ? 'selected' : ''}>Sắp khai giảng</option>
                        <option value="active" ${classData?.status === 'active' ? 'selected' : ''}>Đang học</option>
                        <option value="completed" ${classData?.status === 'completed' ? 'selected' : ''}>Đã kết thúc</option>
                        <option value="cancelled" ${classData?.status === 'cancelled' ? 'selected' : ''}>Đã hủy</option>
                    </select>
                </div>
                
                <div class="profile-form-group col-span-2">
                    <label class="profile-form-label">Mô tả</label>
                    <textarea name="description" class="profile-form-input" rows="2" placeholder="Ghi chú về lớp học...">${escapeHtml(classData?.description || '')}</textarea>
                </div>
            </div>
            
            <div class="flex gap-2 justify-end mt-6">
                <button type="button" id="cancel-btn" class="admin-action-btn secondary">Hủy</button>
                <button type="submit" class="admin-action-btn primary">${isEdit ? 'Cập nhật' : 'Thêm'}</button>
            </div>
        </form>
        </div>
        
        ${isEdit ? `
        <div id="content-schedule" class="hidden">
            <div class="flex justify-between items-center mb-4">
                <p class="text-sm text-gray-600">Thiết lập thời khóa biểu chi tiết cho lớp <strong>${escapeHtml(classData.name)}</strong></p>
                <button id="add-schedule-btn" class="admin-action-btn primary text-sm">+ Thêm buổi học</button>
            </div>
            <div id="class-schedules-list">
                <div class="text-center py-4"><div class="spinner"></div></div>
            </div>
        </div>
        ` : ''}
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // Event handlers
    document.getElementById('close-modal').addEventListener('click', hideModal);
    document.getElementById('cancel-btn').addEventListener('click', hideModal);
    
    // Tab switching for edit mode
    if (isEdit) {
        const tabInfo = document.getElementById('tab-info');
        const tabSchedule = document.getElementById('tab-schedule');
        const contentInfo = document.getElementById('content-info');
        const contentSchedule = document.getElementById('content-schedule');
        
        tabInfo.addEventListener('click', () => {
            tabInfo.classList.add('border-b-2', 'border-blue-500', 'text-blue-600', 'font-medium');
            tabInfo.classList.remove('text-gray-500');
            tabSchedule.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600', 'font-medium');
            tabSchedule.classList.add('text-gray-500');
            contentInfo.classList.remove('hidden');
            contentSchedule.classList.add('hidden');
        });
        
        tabSchedule.addEventListener('click', async () => {
            tabSchedule.classList.add('border-b-2', 'border-blue-500', 'text-blue-600', 'font-medium');
            tabSchedule.classList.remove('text-gray-500');
            tabInfo.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600', 'font-medium');
            tabInfo.classList.add('text-gray-500');
            contentSchedule.classList.remove('hidden');
            contentInfo.classList.add('hidden');
            
            // Load schedules
            await loadClassSchedules(classData);
        });
        
        document.getElementById('add-schedule-btn').addEventListener('click', () => {
            showAddScheduleModal(classData);
        });
    }
    
    document.getElementById('class-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        
        const result = isEdit 
            ? await adminService.updateClass(data)
            : await adminService.createClass(data);
            
        if (result.success) {
            showToast(isEdit ? 'Cập nhật thành công' : 'Thêm lớp học thành công', 'success');
            hideModal();
            renderClasses();
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    });
}

// Day of week mapping
const dayOfWeekMap = {
    'monday': 'Thứ 2',
    'tuesday': 'Thứ 3',
    'wednesday': 'Thứ 4',
    'thursday': 'Thứ 5',
    'friday': 'Thứ 6',
    'saturday': 'Thứ 7',
    'sunday': 'Chủ nhật'
};

const dayColors = {
    'monday': '#1e40af',
    'tuesday': '#059669',
    'wednesday': '#dc2626',
    'thursday': '#7c3aed',
    'friday': '#ea580c',
    'saturday': '#0891b2',
    'sunday': '#be185d'
};

// Load and display class schedules
async function loadClassSchedules(classData) {
    const container = document.getElementById('class-schedules-list');
    
    const result = await adminService.getClassSchedules(classData.id);
    
    if (!result.success) {
        container.innerHTML = `<p class="text-red-500 text-center py-4">Lỗi: ${result.message || 'Không thể tải thời khóa biểu'}</p>`;
        return;
    }
    
    const schedules = result.schedules || [];
    
    if (schedules.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 bg-gray-50 rounded-lg">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-500">Chưa có thời khóa biểu</p>
                <p class="text-sm text-gray-400 mt-1">Nhấn "Thêm buổi học" để thiết lập</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <div class="overflow-x-auto">
            <table class="profile-table text-sm w-full">
                <thead>
                    <tr>
                        <th>Thứ</th>
                        <th>Giờ học</th>
                        <th>Môn/Nội dung</th>
                        <th>Phòng</th>
                        <th>Giảng viên</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    ${schedules.map(s => `
                        <tr>
                            <td>
                                <span class="px-2 py-1 rounded text-white text-xs" style="background-color: ${s.color || dayColors[s.day_of_week] || '#1e40af'}">
                                    ${dayOfWeekMap[s.day_of_week] || s.day_of_week}
                                </span>
                            </td>
                            <td class="font-mono">${s.start_time?.substring(0,5)} - ${s.end_time?.substring(0,5)}</td>
                            <td>${s.subject ? escapeHtml(s.subject) : '-'}</td>
                            <td>
                                ${s.is_online ? 
                                    `<span class="text-green-600"><i class="fas fa-video"></i> Online</span>` : 
                                    escapeHtml(s.room || '-')
                                }
                            </td>
                            <td>${s.teacher_name ? escapeHtml(s.teacher_name) : '<span class="text-gray-400">Chưa phân</span>'}</td>
                            <td>
                                <button class="admin-action-btn secondary text-xs edit-schedule-btn" data-schedule='${JSON.stringify(s).replace(/'/g, "&#39;")}'>Sửa</button>
                                <button class="admin-action-btn danger text-xs delete-schedule-btn" data-id="${s.id}">Xóa</button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            <i class="fas fa-info-circle"></i> Thời khóa biểu này sẽ tự động hiển thị cho học viên trong lớp và giảng viên được phân công
        </p>
    `;
    
    // Edit schedule handlers
    container.querySelectorAll('.edit-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const schedule = JSON.parse(btn.dataset.schedule);
            showEditScheduleModal(schedule, classData);
        });
    });
    
    // Delete schedule handlers
    container.querySelectorAll('.delete-schedule-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Xóa buổi học này?')) return;
            btn.disabled = true;
            const result = await adminService.deleteSchedule(btn.dataset.id);
            if (result.success) {
                showToast('Đã xóa buổi học', 'success');
                await loadClassSchedules(classData);
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
                btn.disabled = false;
            }
        });
    });
}

// Show add schedule modal
async function showAddScheduleModal(classData) {
    const modal = document.getElementById('modal-container');
    const content = document.getElementById('modal-content');
    
    const teachersOptions = allTeachersForClasses.map(t => 
        `<option value="${t.id}">${escapeHtml(t.name)}</option>`
    ).join('');
    
    content.innerHTML = `
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Thêm buổi học - ${escapeHtml(classData.name)}</h3>
            <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="schedule-form">
            <div class="grid grid-cols-2 gap-4">
                <div class="profile-form-group">
                    <label class="profile-form-label">Thứ *</label>
                    <select name="day_of_week" class="profile-form-input" required>
                        <option value="monday">Thứ 2</option>
                        <option value="tuesday">Thứ 3</option>
                        <option value="wednesday">Thứ 4</option>
                        <option value="thursday">Thứ 5</option>
                        <option value="friday">Thứ 6</option>
                        <option value="saturday">Thứ 7</option>
                        <option value="sunday">Chủ nhật</option>
                    </select>
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Môn/Nội dung</label>
                    <select name="subject" class="profile-form-input">
                        <option value="">-- Chọn môn --</option>
                        <option value="Speaking">Speaking</option>
                        <option value="Writing">Writing</option>
                        <option value="Reading">Reading</option>
                        <option value="Listening">Listening</option>
                        <option value="Grammar">Grammar</option>
                        <option value="Vocabulary">Vocabulary</option>
                        <option value="General">Tổng hợp</option>
                    </select>
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Giờ bắt đầu *</label>
                    <input type="time" name="start_time" class="profile-form-input" required value="18:00">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Giờ kết thúc *</label>
                    <input type="time" name="end_time" class="profile-form-input" required value="20:00">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Phòng học</label>
                    <input type="text" name="room" class="profile-form-input" value="${escapeHtml(classData.room || '')}" placeholder="VD: P201">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Giảng viên</label>
                    <select name="teacher_id" class="profile-form-input">
                        <option value="">-- Chưa phân --</option>
                        ${teachersOptions}
                    </select>
                </div>
                
                <div class="profile-form-group col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_online" id="is-online-check">
                        <span>Học online</span>
                    </label>
                </div>
                
                <div class="profile-form-group col-span-2 hidden" id="meeting-link-group">
                    <label class="profile-form-label">Link meeting (Zoom/Meet)</label>
                    <input type="url" name="meeting_link" class="profile-form-input" placeholder="https://zoom.us/j/...">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Màu hiển thị</label>
                    <input type="color" name="color" class="profile-form-input h-10" value="#1e40af">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Ghi chú</label>
                    <input type="text" name="notes" class="profile-form-input" placeholder="Ghi chú thêm...">
                </div>
            </div>
            
            <div class="flex gap-2 justify-end mt-6">
                <button type="button" id="back-btn" class="admin-action-btn secondary">Quay lại</button>
                <button type="submit" class="admin-action-btn primary">Thêm buổi học</button>
            </div>
        </form>
    `;
    
    // Toggle meeting link field
    document.getElementById('is-online-check').addEventListener('change', (e) => {
        document.getElementById('meeting-link-group').classList.toggle('hidden', !e.target.checked);
    });
    
    document.getElementById('close-modal').addEventListener('click', hideModal);
    document.getElementById('back-btn').addEventListener('click', () => showClassModal(classData));
    
    document.getElementById('schedule-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        data.class_id = classData.id;
        data.is_online = formData.has('is_online') ? 1 : 0;
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang thêm...';
        
        const result = await adminService.createSchedule(data);
        
        if (result.success) {
            showToast('Đã thêm buổi học thành công', 'success');
            showClassModal(classData);
            // Switch to schedule tab
            setTimeout(() => {
                document.getElementById('tab-schedule')?.click();
            }, 100);
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Thêm buổi học';
        }
    });
}

// Show edit schedule modal
async function showEditScheduleModal(schedule, classData) {
    const modal = document.getElementById('modal-container');
    const content = document.getElementById('modal-content');
    
    const teachersOptions = allTeachersForClasses.map(t => 
        `<option value="${t.id}" ${schedule.teacher_id == t.id ? 'selected' : ''}>${escapeHtml(t.name)}</option>`
    ).join('');
    
    const dayOptions = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].map(d =>
        `<option value="${d}" ${schedule.day_of_week === d ? 'selected' : ''}>${dayOfWeekMap[d]}</option>`
    ).join('');
    
    const subjectOptions = ['', 'Speaking', 'Writing', 'Reading', 'Listening', 'Grammar', 'Vocabulary', 'General'].map(s =>
        `<option value="${s}" ${schedule.subject === s ? 'selected' : ''}>${s || '-- Chọn môn --'}</option>`
    ).join('');
    
    content.innerHTML = `
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Sửa buổi học</h3>
            <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="schedule-form">
            <input type="hidden" name="id" value="${schedule.id}">
            <div class="grid grid-cols-2 gap-4">
                <div class="profile-form-group">
                    <label class="profile-form-label">Thứ *</label>
                    <select name="day_of_week" class="profile-form-input" required>
                        ${dayOptions}
                    </select>
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Môn/Nội dung</label>
                    <select name="subject" class="profile-form-input">
                        ${subjectOptions}
                    </select>
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Giờ bắt đầu *</label>
                    <input type="time" name="start_time" class="profile-form-input" required value="${schedule.start_time?.substring(0,5) || '18:00'}">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Giờ kết thúc *</label>
                    <input type="time" name="end_time" class="profile-form-input" required value="${schedule.end_time?.substring(0,5) || '20:00'}">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Phòng học</label>
                    <input type="text" name="room" class="profile-form-input" value="${escapeHtml(schedule.room || '')}">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Giảng viên</label>
                    <select name="teacher_id" class="profile-form-input">
                        <option value="">-- Chưa phân --</option>
                        ${teachersOptions}
                    </select>
                </div>
                
                <div class="profile-form-group col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_online" id="is-online-check" ${schedule.is_online ? 'checked' : ''}>
                        <span>Học online</span>
                    </label>
                </div>
                
                <div class="profile-form-group col-span-2 ${schedule.is_online ? '' : 'hidden'}" id="meeting-link-group">
                    <label class="profile-form-label">Link meeting (Zoom/Meet)</label>
                    <input type="url" name="meeting_link" class="profile-form-input" value="${escapeHtml(schedule.meeting_link || '')}">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Màu hiển thị</label>
                    <input type="color" name="color" class="profile-form-input h-10" value="${schedule.color || '#1e40af'}">
                </div>
                
                <div class="profile-form-group">
                    <label class="profile-form-label">Ghi chú</label>
                    <input type="text" name="notes" class="profile-form-input" value="${escapeHtml(schedule.notes || '')}">
                </div>
            </div>
            
            <div class="flex gap-2 justify-end mt-6">
                <button type="button" id="back-btn" class="admin-action-btn secondary">Quay lại</button>
                <button type="submit" class="admin-action-btn primary">Cập nhật</button>
            </div>
        </form>
    `;
    
    // Toggle meeting link field
    document.getElementById('is-online-check').addEventListener('change', (e) => {
        document.getElementById('meeting-link-group').classList.toggle('hidden', !e.target.checked);
    });
    
    document.getElementById('close-modal').addEventListener('click', hideModal);
    document.getElementById('back-btn').addEventListener('click', () => showClassModal(classData));
    
    document.getElementById('schedule-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        data.is_online = formData.has('is_online') ? 1 : 0;
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang cập nhật...';
        
        const result = await adminService.updateSchedule(data);
        
        if (result.success) {
            showToast('Đã cập nhật buổi học', 'success');
            showClassModal(classData);
            setTimeout(() => {
                document.getElementById('tab-schedule')?.click();
            }, 100);
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Cập nhật';
        }
    });
}

async function showClassStudentsModal(classData) {
    const modal = document.getElementById('modal-container');
    const content = document.getElementById('modal-content');
    
    content.innerHTML = `
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Danh sách học viên - ${escapeHtml(classData.name)}</h3>
            <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="text-center py-4"><div class="spinner"></div></div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('close-modal').addEventListener('click', hideModal);
    
    // Load students
    const result = await adminService.getClassStudents(classData.id);
    
    if (!result.success) {
        content.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Danh sách học viên - ${escapeHtml(classData.name)}</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <p class="text-red-500 text-center py-4">Lỗi tải dữ liệu: ${result.error || result.message || 'Không xác định'}</p>
            <div class="flex justify-end mt-4">
                <button type="button" id="cancel-btn" class="admin-action-btn secondary">Đóng</button>
            </div>
        `;
        document.getElementById('close-modal').addEventListener('click', hideModal);
        document.getElementById('cancel-btn').addEventListener('click', hideModal);
        return;
    }
    
    const students = result.students || [];
    const classInfo = result.class || classData;
    
    content.innerHTML = `
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-semibold">${escapeHtml(classInfo.name)}</h3>
                <p class="text-sm text-gray-500">${escapeHtml(classInfo.course_name || '')} ${classInfo.teacher_name ? '• GV: ' + escapeHtml(classInfo.teacher_name) : ''}</p>
            </div>
            <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="flex justify-between items-center mb-4 p-3 bg-blue-50 rounded-lg">
            <span class="text-sm text-blue-800">Sĩ số: <strong>${students.length}/${classInfo.max_students || 20}</strong></span>
            <button id="add-student-to-class-btn" class="admin-action-btn primary text-sm">+ Thêm học viên</button>
        </div>
        
        ${students.length > 0 ? `
            <div class="overflow-x-auto max-h-96">
                <table class="profile-table text-sm">
                    <thead class="sticky top-0 bg-white">
                        <tr>
                            <th>STT</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${students.map((s, i) => `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${escapeHtml(s.fullname)}</td>
                                <td>${escapeHtml(s.email)}</td>
                                <td>${escapeHtml(s.phone) || '-'}</td>
                                <td><span class="status-badge ${s.status}">${s.status === 'active' ? 'Đang học' : s.status === 'completed' ? 'Hoàn thành' : s.status}</span></td>
                                <td>
                                    <button class="admin-action-btn danger text-xs remove-from-class-btn" data-enrollment-id="${s.id}">Xóa khỏi lớp</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : `
            <p class="text-center text-gray-500 py-8">Chưa có học viên trong lớp này</p>
        `}
        
        <div class="flex justify-end mt-4">
            <button type="button" id="cancel-btn" class="admin-action-btn secondary">Đóng</button>
        </div>
    `;

    document.getElementById('close-modal').addEventListener('click', hideModal);
    document.getElementById('cancel-btn').addEventListener('click', hideModal);
    
    // Add student button
    document.getElementById('add-student-to-class-btn')?.addEventListener('click', () => {
        showAddStudentToClassModal(classData);
    });
    
    // Remove student handlers
    content.querySelectorAll('.remove-from-class-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Xóa học viên này khỏi lớp?')) return;
            btn.disabled = true;
            btn.textContent = 'Đang xóa...';
            const result = await adminService.removeStudentFromClass(btn.dataset.enrollmentId);
            if (result.success) {
                showToast('Đã xóa học viên khỏi lớp', 'success');
                showClassStudentsModal(classData); // Refresh
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
                btn.disabled = false;
                btn.textContent = 'Xóa khỏi lớp';
            }
        });
    });
}

async function showAddStudentToClassModal(classData) {
    const modal = document.getElementById('modal-container');
    const content = document.getElementById('modal-content');
    
    content.innerHTML = `
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Thêm học viên vào lớp ${escapeHtml(classData.name)}</h3>
            <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="text-center py-4"><div class="spinner"></div></div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('close-modal').addEventListener('click', hideModal);
    
    // Load available students (same course, not in class)
    const enrolledResult = await adminService.getAvailableStudentsForClass(classData.id);
    const enrolledStudents = enrolledResult.students || [];
    
    // Also load all users for direct add
    const allUsersResult = await adminService.getAllUsersForClass(classData.id, '');
    const allUsers = (allUsersResult.users || []).filter(u => !u.in_this_class);
    
    content.innerHTML = `
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Thêm học viên vào lớp ${escapeHtml(classData.name)}</h3>
            <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Tabs -->
        <div class="flex border-b mb-4">
            <button id="tab-enrolled" class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-medium">
                Đã đăng ký khóa học (${enrolledStudents.length})
            </button>
            <button id="tab-all" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                Tất cả học viên (${allUsers.length})
            </button>
        </div>
        
        <!-- Tab 1: Enrolled students not in class -->
        <div id="enrolled-content">
            ${enrolledStudents.length > 0 ? `
                <p class="text-sm text-gray-600 mb-4">Học viên đăng ký cùng khóa học nhưng chưa được phân lớp:</p>
                <div class="overflow-x-auto max-h-64">
                    <table class="profile-table text-sm">
                        <thead class="sticky top-0 bg-white">
                            <tr>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${enrolledStudents.map(s => `
                                <tr>
                                    <td>${escapeHtml(s.fullname)}</td>
                                    <td>${escapeHtml(s.email)}</td>
                                    <td>
                                        <button class="admin-action-btn primary text-xs add-enrolled-btn" data-enrollment-id="${s.id}">Thêm vào lớp</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : `
                <p class="text-center text-gray-500 py-8">Không có học viên nào đăng ký khóa học này chưa được phân lớp</p>
            `}
        </div>
        
        <!-- Tab 2: All users -->
        <div id="all-users-content" class="hidden">
            <div class="mb-4">
                <input type="text" id="search-all-users" placeholder="Tìm kiếm theo tên, email, SĐT..." 
                       class="w-full p-2 border rounded-lg text-sm">
            </div>
            <div id="all-users-list">
                ${allUsers.length > 0 ? `
                    <div class="overflow-x-auto max-h-64">
                        <table class="profile-table text-sm">
                            <thead class="sticky top-0 bg-white">
                                <tr>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Đã đăng ký</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="all-users-tbody">
                                ${allUsers.map(u => `
                                    <tr>
                                        <td>${escapeHtml(u.fullname)}</td>
                                        <td>${escapeHtml(u.email)}</td>
                                        <td class="text-xs text-gray-500">${u.enrolled_courses ? escapeHtml(u.enrolled_courses.substring(0, 30) + (u.enrolled_courses.length > 30 ? '...' : '')) : 'Chưa đăng ký khóa nào'}</td>
                                        <td>
                                            <button class="admin-action-btn primary text-xs add-any-user-btn" data-user-id="${u.id}">Thêm vào lớp</button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                ` : `
                    <p class="text-center text-gray-500 py-8">Không có học viên nào</p>
                `}
            </div>
            <p class="text-xs text-gray-500 mt-2">
                <i class="fas fa-info-circle"></i> Thêm học viên sẽ tự động đăng ký họ vào khóa học tương ứng
            </p>
        </div>
        
        <div class="flex justify-end mt-4">
            <button type="button" id="back-btn" class="admin-action-btn secondary">Quay lại</button>
        </div>
    `;

    document.getElementById('close-modal').addEventListener('click', hideModal);
    document.getElementById('back-btn')?.addEventListener('click', () => showClassStudentsModal(classData));
    
    // Tab switching
    const tabEnrolled = document.getElementById('tab-enrolled');
    const tabAll = document.getElementById('tab-all');
    const enrolledContent = document.getElementById('enrolled-content');
    const allUsersContent = document.getElementById('all-users-content');
    
    tabEnrolled.addEventListener('click', () => {
        tabEnrolled.classList.add('border-b-2', 'border-blue-500', 'text-blue-600', 'font-medium');
        tabEnrolled.classList.remove('text-gray-500');
        tabAll.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600', 'font-medium');
        tabAll.classList.add('text-gray-500');
        enrolledContent.classList.remove('hidden');
        allUsersContent.classList.add('hidden');
    });
    
    tabAll.addEventListener('click', () => {
        tabAll.classList.add('border-b-2', 'border-blue-500', 'text-blue-600', 'font-medium');
        tabAll.classList.remove('text-gray-500');
        tabEnrolled.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600', 'font-medium');
        tabEnrolled.classList.add('text-gray-500');
        allUsersContent.classList.remove('hidden');
        enrolledContent.classList.add('hidden');
    });
    
    // Search functionality
    let searchTimeout;
    document.getElementById('search-all-users')?.addEventListener('input', async (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            const search = e.target.value.trim();
            const result = await adminService.getAllUsersForClass(classData.id, search);
            const users = (result.users || []).filter(u => !u.in_this_class);
            
            const tbody = document.getElementById('all-users-tbody');
            if (tbody) {
                tbody.innerHTML = users.map(u => `
                    <tr>
                        <td>${escapeHtml(u.fullname)}</td>
                        <td>${escapeHtml(u.email)}</td>
                        <td class="text-xs text-gray-500">${u.enrolled_courses ? escapeHtml(u.enrolled_courses.substring(0, 30) + (u.enrolled_courses.length > 30 ? '...' : '')) : 'Chưa đăng ký'}</td>
                        <td>
                            <button class="admin-action-btn primary text-xs add-any-user-btn" data-user-id="${u.id}">Thêm vào lớp</button>
                        </td>
                    </tr>
                `).join('');
                
                // Re-attach event listeners
                tbody.querySelectorAll('.add-any-user-btn').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        btn.disabled = true;
                        btn.textContent = 'Đang thêm...';
                        const result = await adminService.addUserToClass(btn.dataset.userId, classData.id);
                        if (result.success) {
                            showToast(result.message || 'Đã thêm học viên vào lớp', 'success');
                            showClassStudentsModal(classData);
                        } else {
                            showToast(result.message || 'Có lỗi xảy ra', 'error');
                            btn.disabled = false;
                            btn.textContent = 'Thêm vào lớp';
                        }
                    });
                });
            }
        }, 300);
    });
    
    // Add enrolled student to class handlers
    content.querySelectorAll('.add-enrolled-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.textContent = 'Đang thêm...';
            const result = await adminService.assignStudentToClass(btn.dataset.enrollmentId, classData.id);
            if (result.success) {
                showToast('Đã thêm học viên vào lớp', 'success');
                showClassStudentsModal(classData);
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
                btn.disabled = false;
                btn.textContent = 'Thêm vào lớp';
            }
        });
    });
    
    // Add any user to class handlers
    content.querySelectorAll('.add-any-user-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.textContent = 'Đang thêm...';
            const result = await adminService.addUserToClass(btn.dataset.userId, classData.id);
            if (result.success) {
                showToast(result.message || 'Đã thêm học viên vào lớp', 'success');
                showClassStudentsModal(classData);
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
                btn.disabled = false;
                btn.textContent = 'Thêm vào lớp';
            }
        });
    });
}

// Render Teachers
async function renderTeachers() {
    const tbody = document.getElementById('teachers-tbody');
    try {
        const result = await adminService.getTeachers();
        if (!result.teachers?.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-gray-500">Chưa có giảng viên</td></tr>';
            return;
        }
        
        // Helper to get correct image URL
        const getTeacherImageUrl = (url) => {
            if (!url) return BASE_PATH + '/frontend/assets/images/default-avatar.svg';
            if (url.startsWith('http')) return url;
            if (url.startsWith('/') && !url.toLowerCase().startsWith('/hai_au_english')) {
                return BASE_PATH + url;
            }
            return url;
        };

        tbody.innerHTML = result.teachers.map(t => `
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <img src="${getTeacherImageUrl(t.image_url)}" alt="${escapeHtml(t.name)}" class="w-10 h-10 rounded-full object-cover" onerror="this.src='${BASE_PATH}/frontend/assets/images/default-avatar.svg'">
                        <span>${escapeHtml(t.name)}</span>
                    </div>
                </td>
                <td>${escapeHtml(t.title) || '-'}</td>
                <td>${t.experience_years ? t.experience_years + ' năm' : '-'}</td>
                <!-- TODO: Tạm ẩn IELTS - bật lại khi cần
                <td>${t.ielts_score || '-'}</td>
                -->
                <td>
                    <span class="status-badge ${t.is_active ? 'active' : 'cancelled'}">
                        ${t.is_active ? 'Hoạt động' : 'Ẩn'}
                    </span>
                </td>
                <td>
                    <button class="admin-action-btn secondary edit-teacher-btn" data-id="${t.id}">Sửa</button>
                    <button class="admin-action-btn danger delete-teacher-btn" data-id="${t.id}">Xóa</button>
                </td>
            </tr>
        `).join('');

        // Edit handlers
        tbody.querySelectorAll('.edit-teacher-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const teacher = result.teachers.find(t => t.id == btn.dataset.id);
                if (teacher) {
                    showTeacherModal(teacher);
                }
            });
        });

        // Delete handlers
        tbody.querySelectorAll('.delete-teacher-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn có chắc muốn xóa giảng viên này?')) return;
                try {
                    const result = await adminService.deleteTeacher(btn.dataset.id);
                    if (result.success) {
                        showToast('Đã chuyển vào thùng rác!', 'success');
                        renderTeachers();
                        updateTrashCount();
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-red-500">Lỗi tải dữ liệu</td></tr>';
    }
}

// Render Scores
async function renderScores() {
    const tbody = document.getElementById('scores-tbody');
    try {
        const result = await adminService.getScores();
        if (!result.scores?.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-gray-500">Chưa có điểm số</td></tr>';
            return;
        }

        tbody.innerHTML = result.scores.map(s => `
            <tr>
                <td>${s.fullname || 'N/A'}</td>
                <td>${formatDate(s.test_date)}</td>
                <td class="text-center">${s.listening}</td>
                <td class="text-center">${s.reading}</td>
                <td class="text-center">${s.writing}</td>
                <td class="text-center">${s.speaking}</td>
                <td class="text-center font-bold text-blue-600">${s.overall}</td>
                <td>
                    <button class="admin-action-btn secondary edit-score-btn" data-id="${s.id}">Sửa</button>
                    <button class="admin-action-btn danger delete-score-btn" data-id="${s.id}">Xóa</button>
                </td>
            </tr>
        `).join('');

        // Edit handlers
        tbody.querySelectorAll('.edit-score-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const score = result.scores.find(s => s.id == btn.dataset.id);
                if (score) {
                    showScoreModal(score);
                }
            });
        });

        // Delete handlers
        tbody.querySelectorAll('.delete-score-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn có chắc muốn xóa điểm này?')) return;
                try {
                    const result = await adminService.deleteScore(btn.dataset.id);
                    if (result.success) {
                        showToast('Đã chuyển vào thùng rác!', 'success');
                        renderScores();
                        updateTrashCount();
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-red-500">Lỗi tải dữ liệu</td></tr>';
    }
}

// Render Feedback
async function renderFeedback() {
    const container = document.getElementById('admin-feedback-container');
    try {
        const result = await adminService.getFeedback();
        if (!result.feedback?.length) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">Chưa có nhận xét</p>';
            return;
        }

        container.innerHTML = `
            <div class="space-y-4">
                ${result.feedback.map(f => `
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium">${f.student_name || 'Học viên'}</h4>
                                <p class="text-sm text-gray-500">${f.course_name || ''} ${f.teacher_name ? '- GV: ' + f.teacher_name : ''}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                ${f.rating ? `<span class="text-yellow-500">★ ${f.rating}</span>` : ''}
                                <button class="admin-action-btn danger delete-feedback-btn" data-id="${f.id}">Xóa</button>
                            </div>
                        </div>
                        <p class="mt-2 text-gray-700">${f.content}</p>
                        <p class="text-xs text-gray-400 mt-2">${formatDate(f.feedback_date)}</p>
                    </div>
                `).join('')}
            </div>
        `;

        // Delete handlers
        container.querySelectorAll('.delete-feedback-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn có chắc muốn xóa nhận xét này?')) return;
                try {
                    const result = await adminService.deleteFeedback(btn.dataset.id);
                    if (result.success) {
                        showToast('Đã chuyển vào thùng rác!', 'success');
                        renderFeedback();
                        updateTrashCount();
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });
    } catch (error) {
        container.innerHTML = '<p class="text-red-500 text-center py-8">Lỗi tải dữ liệu</p>';
    }
}

// ==================== ACHIEVEMENTS FUNCTIONS ====================

// Get base path for API
function getApiBasePath() {
    const path = window.location.pathname;
    const match = path.match(/\/Hai_Au_English/i);
    return match ? '/Hai_Au_English' : '';
}

// Render Achievements
async function renderAchievements() {
    const grid = document.getElementById('achievements-grid');
    try {
        const basePath = getApiBasePath();
        const response = await fetch(`${basePath}/backend/php/achievements.php`, {
            credentials: 'include'
        });
        const result = await response.json();
        
        if (!result.success || !result.data?.length) {
            grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">Chưa có thành tích nào</div>';
            updateAchievementStats([]);
            return;
        }

        const achievements = result.data;
        updateAchievementStats(achievements);

        grid.innerHTML = achievements.map(a => `
            <div class="achievement-admin-card bg-white rounded-lg shadow-md overflow-hidden border hover:shadow-lg transition-shadow">
                <div class="relative">
                    <img src="${fixAchievementImageUrl(a.image_url)}" 
                         alt="${escapeHtml(a.student_name)}" 
                         class="w-full h-40 object-cover"
                         onerror="this.src='../assets/images/default-achievement.jpg'">
                    ${a.is_featured ? '<span class="absolute top-2 right-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded">Nổi bật</span>' : ''}
                </div>
                <div class="p-3">
                    <h4 class="font-semibold text-gray-800 truncate">${escapeHtml(a.student_name)}</h4>
                    <p class="text-sm text-blue-600 font-medium">${escapeHtml(a.achievement_title)}</p>
                    ${a.score ? `<p class="text-xs text-green-600 font-bold mt-1">Điểm: ${escapeHtml(a.score)}</p>` : ''}
                    ${a.course_name ? `<p class="text-xs text-gray-500">${escapeHtml(a.course_name)}</p>` : ''}
                    <div class="flex gap-1 mt-2">
                        <button class="flex-1 text-xs bg-blue-500 text-white py-1 px-2 rounded hover:bg-blue-600 edit-achievement-btn" data-id="${a.id}">Sửa</button>
                        <button class="flex-1 text-xs bg-red-500 text-white py-1 px-2 rounded hover:bg-red-600 delete-achievement-btn" data-id="${a.id}">Xóa</button>
                    </div>
                </div>
            </div>
        `).join('');

        // Event handlers
        grid.querySelectorAll('.edit-achievement-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const achievement = achievements.find(a => a.id == btn.dataset.id);
                if (achievement) showAchievementModal(achievement);
            });
        });

        grid.querySelectorAll('.delete-achievement-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn có chắc muốn xóa thành tích này?')) return;
                try {
                    const basePath = getApiBasePath();
                    const response = await fetch(`${basePath}/backend/php/achievements.php?id=${btn.dataset.id}`, {
                        method: 'DELETE',
                        credentials: 'include'
                    });
                    const result = await response.json();
                    if (result.success) {
                        showToast('Đã xóa thành tích!', 'success');
                        renderAchievements();
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });
    } catch (error) {
        grid.innerHTML = '<div class="col-span-full text-center py-8 text-red-500">Lỗi tải dữ liệu</div>';
    }
}

// Fix achievement image URL for XAMPP
function fixAchievementImageUrl(url) {
    if (!url) return '../assets/images/default-achievement.jpg';
    const basePath = getApiBasePath();
    if (url.startsWith('/frontend')) {
        return basePath + url;
    }
    if (url.startsWith('frontend')) {
        return basePath + '/' + url;
    }
    return url;
}

// Update achievement stats
function updateAchievementStats(achievements) {
    document.getElementById('total-achievements').textContent = achievements.length;
    
    // Find highest IELTS score
    let highestIelts = 0;
    achievements.forEach(a => {
        if (a.score) {
            const match = a.score.match(/(\d+\.?\d*)/);
            if (match) {
                const score = parseFloat(match[1]);
                if (score <= 9 && score > highestIelts) highestIelts = score;
            }
        }
    });
    document.getElementById('highest-ielts').textContent = highestIelts > 0 ? highestIelts.toFixed(1) : '-';
    
    // Recent achievements (last 7 days)
    const weekAgo = new Date();
    weekAgo.setDate(weekAgo.getDate() - 7);
    const recent = achievements.filter(a => new Date(a.created_at) > weekAgo).length;
    document.getElementById('recent-achievements').textContent = recent;
}

// Show Achievement Modal
function showAchievementModal(achievement = null) {
    const isEdit = !!achievement;
    const modalContent = document.getElementById('modal-content');
    
    modalContent.innerHTML = `
        <div class="modal-header flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">${isEdit ? 'Chỉnh sửa thành tích' : 'Thêm thành tích mới'}</h3>
            <button class="modal-close text-gray-500 hover:text-gray-700" onclick="document.getElementById('modal-container').classList.add('hidden')">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="achievement-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên học viên *</label>
                <input type="text" name="student_name" required value="${escapeHtml(achievement?.student_name || '')}"
                    class="profile-form-input w-full" placeholder="Nguyễn Văn A">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề thành tích *</label>
                <input type="text" name="achievement_title" required value="${escapeHtml(achievement?.achievement_title || '')}"
                    class="profile-form-input w-full" placeholder="IELTS 8.0">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Điểm số</label>
                    <input type="text" name="score" value="${escapeHtml(achievement?.score || '')}"
                        class="profile-form-input w-full" placeholder="8.0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Khóa học</label>
                    <input type="text" name="course_name" value="${escapeHtml(achievement?.course_name || '')}"
                        class="profile-form-input w-full" placeholder="IELTS Foundation">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                <textarea name="description" rows="2" class="profile-form-input w-full" placeholder="Mô tả thành tích...">${escapeHtml(achievement?.description || '')}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh thành tích</label>
                <input type="file" name="image" accept="image/*" class="profile-form-input w-full">
                ${achievement?.image_url ? `<img src="${fixAchievementImageUrl(achievement.image_url)}" class="mt-2 h-24 rounded object-cover">` : ''}
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ngày đạt</label>
                    <input type="date" name="achievement_date" value="${achievement?.achievement_date || ''}"
                        class="profile-form-input w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự hiển thị</label>
                    <input type="number" name="display_order" value="${achievement?.display_order || 0}"
                        class="profile-form-input w-full" min="0">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" id="is_featured" ${achievement?.is_featured ? 'checked' : ''}>
                <label for="is_featured" class="text-sm text-gray-700">Đánh dấu nổi bật</label>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                    ${isEdit ? 'Cập nhật' : 'Thêm mới'}
                </button>
                <button type="button" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300" 
                    onclick="document.getElementById('modal-container').classList.add('hidden')">
                    Hủy
                </button>
            </div>
        </form>
    `;
    
    document.getElementById('modal-container').classList.remove('hidden');
    document.getElementById('modal-container').classList.add('flex');
    
    // Form submit handler
    document.getElementById('achievement-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            const basePath = getApiBasePath();
            let url = `${basePath}/backend/php/achievements.php`;
            let method = 'POST';
            
            if (isEdit) {
                url += `?id=${achievement.id}`;
                method = 'PUT';
                // For PUT, send as JSON
                const data = {
                    student_name: formData.get('student_name'),
                    achievement_title: formData.get('achievement_title'),
                    score: formData.get('score'),
                    course_name: formData.get('course_name'),
                    description: formData.get('description'),
                    achievement_date: formData.get('achievement_date'),
                    display_order: parseInt(formData.get('display_order') || 0),
                    is_featured: form.is_featured.checked ? 1 : 0
                };
                
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    showToast('Đã cập nhật thành tích!', 'success');
                    hideModal();
                    renderAchievements();
                } else {
                    showToast(result.message || 'Có lỗi xảy ra', 'error');
                }
            } else {
                // For POST with file upload, use FormData
                formData.set('is_featured', form.is_featured.checked ? '1' : '0');
                
                const response = await fetch(url, {
                    method: 'POST',
                    credentials: 'include',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    showToast('Đã thêm thành tích mới!', 'success');
                    hideModal();
                    renderAchievements();
                } else {
                    showToast(result.message || 'Có lỗi xảy ra', 'error');
                }
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

// ==================== TRASH FUNCTIONS ====================

// Update trash badge count
function updateTrashBadge(count) {
    const badge = document.getElementById('trash-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
}

// Update trash count
async function updateTrashCount() {
    try {
        const result = await adminService.getTrash();
        updateTrashBadge(result.trash?.length || 0);
    } catch (error) {
        console.error('Error updating trash count:', error);
    }
}

// ==================== SCHEDULE FUNCTIONS ====================

// Day labels
const dayLabels = {
    'monday': 'Thứ Hai',
    'tuesday': 'Thứ Ba',
    'wednesday': 'Thứ Tư',
    'thursday': 'Thứ Năm',
    'friday': 'Thứ Sáu',
    'saturday': 'Thứ Bảy',
    'sunday': 'Chủ Nhật'
};

const dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

// Session labels
const sessionLabels = {
    'morning': 'Buổi sáng',
    'afternoon': 'Buổi chiều',
    'evening': 'Buổi tối'
};

// Period time mapping
const periodTimes = {
    1: { start: '06:30', end: '07:15' },
    2: { start: '07:20', end: '08:05' },
    3: { start: '08:15', end: '09:00' },
    4: { start: '09:05', end: '09:50' },
    5: { start: '10:00', end: '10:45' },
    6: { start: '10:50', end: '11:35' },
    7: { start: '12:30', end: '13:15' },
    8: { start: '13:20', end: '14:05' },
    9: { start: '14:15', end: '15:00' },
    10: { start: '15:05', end: '15:50' },
    11: { start: '16:00', end: '16:45' },
    12: { start: '16:50', end: '17:35' },
    13: { start: '18:00', end: '18:45' },
    14: { start: '18:50', end: '19:35' },
    15: { start: '19:45', end: '20:30' }
};

// Current schedule state for admin
let adminCurrentScheduleWeek = new Date();
let adminAllSchedules = [];

// Format time
function formatTime(timeStr) {
    if (!timeStr) return '';
    return timeStr.substring(0, 5);
}

// Get week dates
function getWeekDates(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    const monday = new Date(d.setDate(diff));
    
    const dates = {};
    dayOrder.forEach((dayName, index) => {
        const dayDate = new Date(monday);
        dayDate.setDate(monday.getDate() + index);
        dates[dayName] = dayDate;
    });
    return dates;
}

// Format date short
function formatDateShort(date) {
    return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// Populate admin week selector
function populateAdminWeekSelector() {
    const weekSelect = document.getElementById('admin-schedule-week');
    if (!weekSelect) return;
    
    weekSelect.innerHTML = '';
    const today = new Date();
    for (let i = -10; i <= 10; i++) {
        const weekDate = new Date(today);
        weekDate.setDate(today.getDate() + (i * 7));
        
        const weekDates = getWeekDates(weekDate);
        const monday = weekDates.monday;
        const sunday = weekDates.sunday;
        
        const option = document.createElement('option');
        option.value = monday.toISOString().split('T')[0];
        option.textContent = `${formatDateShort(monday)} - ${formatDateShort(sunday)}`;
        
        if (i === 0) option.selected = true;
        weekSelect.appendChild(option);
    }
}

// Update admin date headers
function updateAdminDateHeaders() {
    const weekDates = getWeekDates(adminCurrentScheduleWeek);
    
    dayOrder.forEach(day => {
        const dateEl = document.getElementById(`admin-date-${day}`);
        if (dateEl) {
            dateEl.textContent = formatDateShort(weekDates[day]);
        }
        
        const th = document.querySelector(`#admin-schedule-timetable th[data-day="${day}"]`);
        const today = new Date();
        if (th) {
            if (weekDates[day].toDateString() === today.toDateString()) {
                th.classList.add('today');
            } else {
                th.classList.remove('today');
            }
        }
    });
}

// Get period from time
function getPeriodFromTime(timeStr) {
    if (!timeStr) return 1;
    const time = timeStr.substring(0, 5);
    
    for (const [period, times] of Object.entries(periodTimes)) {
        if (time >= times.start && time <= times.end) {
            return parseInt(period);
        }
    }
    
    const hour = parseInt(time.split(':')[0]);
    if (hour < 12) return Math.max(1, Math.min(6, hour - 5));
    if (hour < 17) return Math.max(7, Math.min(12, hour - 5));
    return Math.max(13, Math.min(15, hour - 5));
}

// Render admin timetable grid
function renderAdminTimetableGrid(schedules) {
    const tbody = document.getElementById('admin-schedule-tbody');
    if (!tbody) return;
    
    let html = '';
    for (let period = 1; period <= 15; period++) {
        html += `<tr data-period="${period}">
            <td class="period-cell">${period}</td>
            ${dayOrder.map(day => `<td class="schedule-cell" data-day="${day}" data-period="${period}"></td>`).join('')}
        </tr>`;
    }
    tbody.innerHTML = html;
    
    schedules.forEach(s => {
        const startPeriod = s.period || getPeriodFromTime(s.start_time);
        const periodCount = s.period_count || 1;
        
        const cell = tbody.querySelector(`td[data-day="${s.day_of_week}"][data-period="${startPeriod}"]`);
        if (!cell) return;
        
        const itemHeight = (periodCount * 50) - 2;
        
        cell.innerHTML = `
            <div class="schedule-cell-item" 
                 style="background: ${s.color || '#1e40af'}; height: ${itemHeight}px;"
                 data-id="${s.id}">
                <div class="item-title">${escapeHtml(s.title)}</div>
                ${s.course_code ? `<div class="item-code">(${escapeHtml(s.course_code)})</div>` : ''}
                <div class="item-session">${sessionLabels[s.session] || ''}</div>
                <div class="item-time">Giờ: ${formatTime(s.start_time)}-${formatTime(s.end_time)}</div>
                ${s.group_name ? `<div class="item-group">Nhóm: ${escapeHtml(s.group_name)}</div>` : ''}
                ${s.class_name ? `<div class="item-class">Lớp: ${escapeHtml(s.class_name)}</div>` : ''}
                <div class="item-room"><strong>Phòng:</strong> ${s.is_online ? 'Online' : escapeHtml(s.room || '-')}</div>
                ${s.teacher_name ? `<div class="item-teacher">GV: ${escapeHtml(s.teacher_name)}</div>` : ''}
                ${s.teacher_email ? `<div class="item-email">Email: ${escapeHtml(s.teacher_email)}</div>` : ''}
            </div>
        `;
        
        for (let i = 1; i < periodCount; i++) {
            const nextCell = tbody.querySelector(`td[data-day="${s.day_of_week}"][data-period="${startPeriod + i}"]`);
            if (nextCell) nextCell.classList.add('spanned');
        }
    });
    
    // Highlight today
    const today = new Date();
    const weekDates = getWeekDates(adminCurrentScheduleWeek);
    dayOrder.forEach(day => {
        if (weekDates[day].toDateString() === today.toDateString()) {
            tbody.querySelectorAll(`td[data-day="${day}"]`).forEach(td => {
                td.classList.add('today-col');
            });
        }
    });
    
    // Click handlers
    tbody.querySelectorAll('.schedule-cell-item').forEach(item => {
        item.addEventListener('click', () => {
            const schedule = schedules.find(s => s.id == item.dataset.id);
            if (schedule) showScheduleModal(schedule);
        });
    });
}

// Render Schedule List Table
function renderScheduleListTable(schedules) {
    const tbody = document.getElementById('schedule-list-tbody');
    if (!tbody) return;
    
    if (!schedules.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-gray-500 py-8">Chưa có lịch học</td></tr>';
        return;
    }
    
    tbody.innerHTML = schedules.map(s => `
        <tr>
            <td>${s.student_name || '-'}</td>
            <td>
                <span class="inline-block w-3 h-3 rounded mr-2" style="background: ${s.color || '#1e40af'}"></span>
                ${s.course_name || '-'}
            </td>
            <td class="font-medium">${s.title}</td>
            <td>${dayLabels[s.day_of_week] || s.day_of_week}</td>
            <td>${formatTime(s.start_time)} - ${formatTime(s.end_time)}</td>
            <td>${s.room || (s.is_online ? '🖥️ Online' : '-')}</td>
            <td>${s.teacher_name || '-'}</td>
            <td>
                <button class="admin-action-btn primary edit-schedule-btn" data-id="${s.id}">Sửa</button>
                <button class="admin-action-btn danger delete-schedule-btn" data-id="${s.id}">Xóa</button>
            </td>
        </tr>
    `).join('');
    
    // Edit handlers
    tbody.querySelectorAll('.edit-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const schedule = schedules.find(s => s.id == btn.dataset.id);
            if (schedule) showScheduleModal(schedule);
        });
    });
    
    // Delete handlers
    tbody.querySelectorAll('.delete-schedule-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa lịch học này?')) return;
            try {
                const result = await adminService.deleteSchedule(btn.dataset.id);
                if (result.success) {
                    showToast('Đã xóa lịch học!', 'success');
                    renderSchedule();
                    updateTrashCount();
                } else {
                    showToast(result.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        });
    });
}

// Render Schedule
async function renderSchedule(filterDay = '') {
    const tbody = document.getElementById('admin-schedule-tbody');
    
    try {
        // Initialize filters
        populateAdminWeekSelector();
        updateAdminDateHeaders();
        initAdminScheduleFilters();
        
        const result = await adminService.getSchedules();
        if (!result.success) {
            if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-red-500">Lỗi tải dữ liệu</td></tr>';
            return;
        }

        adminAllSchedules = result.schedules || [];
        
        // Apply filters
        const year = document.getElementById('admin-schedule-year')?.value || '';
        const semester = document.getElementById('admin-schedule-semester')?.value || '';
        const day = document.getElementById('admin-schedule-day')?.value || filterDay;
        
        let filteredSchedules = adminAllSchedules.filter(s => {
            const matchYear = !year || !s.academic_year || s.academic_year === year;
            const matchSemester = !semester || !s.semester || s.semester == semester;
            const matchDay = !day || s.day_of_week === day;
            return matchYear && matchSemester && matchDay;
        });
        
        renderAdminTimetableGrid(filteredSchedules);
        renderScheduleListTable(filteredSchedules);
        
    } catch (error) {
        console.error('Error loading schedule:', error);
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-red-500">Lỗi tải dữ liệu</td></tr>';
        }
    }
}

// Initialize admin schedule filters
function initAdminScheduleFilters() {
    // Only init once
    if (window.adminScheduleFiltersInitialized) return;
    window.adminScheduleFiltersInitialized = true;
    
    // Week navigation
    document.getElementById('admin-schedule-prev-week')?.addEventListener('click', () => {
        adminCurrentScheduleWeek.setDate(adminCurrentScheduleWeek.getDate() - 7);
        updateAdminDateHeaders();
        updateAdminWeekSelector();
    });
    
    document.getElementById('admin-schedule-next-week')?.addEventListener('click', () => {
        adminCurrentScheduleWeek.setDate(adminCurrentScheduleWeek.getDate() + 7);
        updateAdminDateHeaders();
        updateAdminWeekSelector();
    });
    
    document.getElementById('admin-schedule-current-week')?.addEventListener('click', () => {
        adminCurrentScheduleWeek = new Date();
        updateAdminDateHeaders();
        updateAdminWeekSelector();
    });
    
    // Week selector change
    document.getElementById('admin-schedule-week')?.addEventListener('change', (e) => {
        adminCurrentScheduleWeek = new Date(e.target.value);
        updateAdminDateHeaders();
    });
    
    // Filter changes
    document.getElementById('admin-schedule-year')?.addEventListener('change', () => renderSchedule());
    document.getElementById('admin-schedule-semester')?.addEventListener('change', () => renderSchedule());
    document.getElementById('admin-schedule-day')?.addEventListener('change', () => renderSchedule());
}

// Update admin week selector
function updateAdminWeekSelector() {
    const weekSelect = document.getElementById('admin-schedule-week');
    if (!weekSelect) return;
    
    const weekDates = getWeekDates(adminCurrentScheduleWeek);
    const mondayStr = weekDates.monday.toISOString().split('T')[0];
    
    for (const option of weekSelect.options) {
        if (option.value === mondayStr) {
            option.selected = true;
            break;
        }
    }
}

// Render Trash
async function renderTrash(table = null) {
    const tbody = document.getElementById('trash-tbody');
    try {
        const result = await adminService.getTrash(table);
        if (!result.trash?.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-gray-500 py-8">Thùng rác trống</td></tr>';
            updateTrashBadge(0);
            return;
        }

        updateTrashBadge(result.trash.length);

        tbody.innerHTML = result.trash.map(item => `
            <tr>
                <td>
                    <span class="px-2 py-1 rounded text-xs font-medium" style="background: #fee2e2; color: #b91c1c;">
                        ${getTableLabel(item.original_table)}
                    </span>
                </td>
                <td>${getTrashItemDescription(item)}</td>
                <td>Admin #${item.deleted_by}</td>
                <td>${formatDateTime(item.deleted_at)}</td>
                <td>
                    <span class="text-orange-600 text-sm">${formatDate(item.expires_at)}</span>
                </td>
                <td>
                    <button class="admin-action-btn primary restore-btn" data-id="${item.id}">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Khôi phục
                    </button>
                    <button class="admin-action-btn danger permanent-delete-btn" data-id="${item.id}">Xóa vĩnh viễn</button>
                </td>
            </tr>
        `).join('');

        // Restore handlers
        tbody.querySelectorAll('.restore-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                try {
                    const result = await adminService.restoreFromTrash(btn.dataset.id);
                    if (result.success) {
                        showToast('Khôi phục thành công!', 'success');
                        renderTrash(document.getElementById('trash-filter')?.value || null);
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });

        // Permanent delete handlers
        tbody.querySelectorAll('.permanent-delete-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('⚠️ CẢNH BÁO: Dữ liệu sẽ bị xóa VĨNH VIỄN và không thể khôi phục!\n\nBạn có chắc chắn muốn tiếp tục?')) return;
                try {
                    const result = await adminService.deletePermanent(btn.dataset.id);
                    if (result.success) {
                        showToast('Đã xóa vĩnh viễn!', 'success');
                        renderTrash(document.getElementById('trash-filter')?.value || null);
                    } else {
                        showToast(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi kết nối', 'error');
                }
            });
        });
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-red-500 text-center py-8">Lỗi tải dữ liệu</td></tr>';
    }
}

// ==================== NOTIFICATIONS FUNCTIONS ====================

let notificationsCurrentPage = 1;
let notificationsTotalPages = 1;

// Render Notifications
async function renderNotifications(type = '', page = 1) {
    const tbody = document.getElementById('notifications-tbody');
    if (!tbody) return;
    
    try {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8"><div class="spinner"></div></td></tr>';
        
        const result = await adminService.getNotifications({ type, page, limit: 20 });
        
        if (!result.success || !result.data?.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-gray-500 py-8">Không có thông báo nào</td></tr>';
            updateNotificationsBadge(0);
            updateNotificationsPagination(1, 1);
            return;
        }
        
        updateNotificationsBadge(result.unread_count || 0);
        notificationsCurrentPage = result.pagination?.page || 1;
        notificationsTotalPages = result.pagination?.total_pages || 1;
        updateNotificationsPagination(notificationsCurrentPage, notificationsTotalPages);
        
        const typeLabels = {
            'review': { label: 'Đánh giá', color: '#fef3c7', textColor: '#b45309' },
            'achievement': { label: 'Thành tích', color: '#d1fae5', textColor: '#047857' },
            'score': { label: 'Điểm số', color: '#dbeafe', textColor: '#1d4ed8' },
            'contact': { label: 'Liên hệ', color: '#fce7f3', textColor: '#be185d' },
            'user': { label: 'Người dùng', color: '#e0e7ff', textColor: '#4338ca' },
            'system': { label: 'Hệ thống', color: '#f3f4f6', textColor: '#374151' }
        };
        
        tbody.innerHTML = result.data.map((notif, index) => {
            const typeInfo = typeLabels[notif.type] || typeLabels['system'];
            const startIdx = ((result.pagination?.page || 1) - 1) * 20;
            
            return `
            <tr class="${notif.is_read == 0 ? 'bg-blue-50' : ''}">
                <td>${startIdx + index + 1}</td>
                <td>
                    <span class="px-2 py-1 rounded text-xs font-medium" style="background: ${typeInfo.color}; color: ${typeInfo.textColor};">
                        ${typeInfo.label}
                    </span>
                </td>
                <td class="font-medium">${escapeHtml(notif.title)}</td>
                <td class="max-w-xs truncate text-sm text-gray-600" title="${escapeHtml(notif.message)}">${escapeHtml(notif.message)}</td>
                <td>
                    ${notif.is_read == 0 
                        ? '<span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-700">Mới</span>'
                        : '<span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-500">Đã đọc</span>'
                    }
                </td>
                <td class="text-sm text-gray-500">${formatDateTime(notif.created_at)}</td>
                <td>
                    <div class="flex gap-1">
                        ${notif.is_read == 0 ? `
                            <button class="text-blue-600 hover:text-blue-800 mark-read-btn" data-id="${notif.id}" title="Đánh dấu đã đọc">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        ` : ''}
                        <button class="text-red-600 hover:text-red-800 delete-notif-btn" data-id="${notif.id}" title="Xóa">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `}).join('');
        
        // Bind mark as read handlers
        tbody.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const result = await adminService.markNotificationAsRead(btn.dataset.id);
                if (result.success) {
                    const filter = document.getElementById('notifications-type-filter')?.value || '';
                    renderNotifications(filter, notificationsCurrentPage);
                    loadHeaderNotifications();
                }
            });
        });
        
        // Bind delete handlers
        tbody.querySelectorAll('.delete-notif-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn có chắc muốn xóa thông báo này?')) return;
                const result = await adminService.deleteNotification(btn.dataset.id);
                if (result.success) {
                    showToast('Đã xóa thông báo', 'success');
                    const filter = document.getElementById('notifications-type-filter')?.value || '';
                    renderNotifications(filter, notificationsCurrentPage);
                    loadHeaderNotifications();
                }
            });
        });
        
    } catch (error) {
        console.error('Render notifications error:', error);
        tbody.innerHTML = '<tr><td colspan="7" class="text-red-500 text-center py-8">Lỗi tải dữ liệu</td></tr>';
    }
}

function updateNotificationsBadge(count) {
    const badge = document.getElementById('notifications-badge');
    const headerBadge = document.getElementById('notification-badge');
    
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
    
    if (headerBadge) {
        if (count > 0) {
            headerBadge.textContent = count > 99 ? '99+' : count;
            headerBadge.classList.remove('hidden');
        } else {
            headerBadge.classList.add('hidden');
        }
    }
}

function updateNotificationsPagination(page, totalPages) {
    const prevBtn = document.getElementById('notif-prev-page');
    const nextBtn = document.getElementById('notif-next-page');
    const pageInfo = document.getElementById('notif-page-info');
    
    if (pageInfo) pageInfo.textContent = `Trang ${page} / ${totalPages}`;
    if (prevBtn) prevBtn.disabled = page <= 1;
    if (nextBtn) nextBtn.disabled = page >= totalPages;
}

// Load header notification dropdown
async function loadHeaderNotifications() {
    const list = document.getElementById('notification-list');
    if (!list) return;
    
    try {
        const result = await adminService.getNotifications({ limit: 10, unread_only: false });
        
        if (!result.success || !result.data?.length) {
            list.innerHTML = '<div class="p-4 text-center text-gray-500">Không có thông báo mới</div>';
            updateNotificationsBadge(0);
            return;
        }
        
        updateNotificationsBadge(result.unread_count || 0);
        
        const typeIcons = {
            'review': '⭐',
            'achievement': '🏆',
            'score': '📊',
            'contact': '📧',
            'user': '👤',
            'system': '🔔'
        };
        
        list.innerHTML = result.data.map(notif => `
            <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 ${notif.is_read == 0 ? 'bg-blue-50' : ''}" 
                 onclick="window.markNotificationRead(${notif.id})">
                <div class="flex items-start gap-2">
                    <span class="text-lg">${typeIcons[notif.type] || '🔔'}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">${escapeHtml(notif.title)}</p>
                        <p class="text-xs text-gray-500 truncate">${escapeHtml(notif.message)}</p>
                        <p class="text-xs text-gray-400 mt-1">${formatTimeAgo(notif.created_at)}</p>
                    </div>
                    ${notif.is_read == 0 ? '<span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1"></span>' : ''}
                </div>
            </div>
        `).join('');
        
    } catch (error) {
        console.error('Load header notifications error:', error);
        list.innerHTML = '<div class="p-4 text-center text-red-500">Lỗi tải thông báo</div>';
    }
}

// Format time ago
function formatTimeAgo(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Vừa xong';
    if (diffMins < 60) return `${diffMins} phút trước`;
    if (diffHours < 24) return `${diffHours} giờ trước`;
    if (diffDays < 7) return `${diffDays} ngày trước`;
    return formatDate(dateStr);
}

// Global function for notification click
window.markNotificationRead = async function(id) {
    await adminService.markNotificationAsRead(id);
    loadHeaderNotifications();
    const filter = document.getElementById('notifications-type-filter')?.value || '';
    renderNotifications(filter, notificationsCurrentPage);
};

// ==================== MODAL FUNCTIONS ====================

function showModal(content) {
    const container = document.getElementById('modal-container');
    const modalContent = document.getElementById('modal-content');
    modalContent.innerHTML = content;
    container.classList.remove('hidden');
    container.classList.add('flex');
}

function hideModal() {
    const container = document.getElementById('modal-container');
    container.classList.add('hidden');
    container.classList.remove('flex');
}

// Show user profile modal with detailed info
async function showUserProfileModal(user) {
    // Show loading first
    showModal(`
        <div class="text-center py-8">
            <div class="spinner"></div>
            <p class="mt-4 text-gray-500">Đang tải thông tin học viên...</p>
        </div>
    `);
    
    // Fetch detailed user data using new API
    let enrollments = [];
    let scores = [];
    let feedback = [];
    let userDetails = user;
    
    try {
        const detailResult = await adminService.getStudentDetails(user.id);
        if (detailResult.success) {
            userDetails = detailResult.user || user;
            enrollments = detailResult.enrollments || [];
            scores = detailResult.scores || [];
            feedback = detailResult.feedback || [];
        } else {
            // Fallback to old method
            const enrollResult = await adminService.getEnrollments();
            if (enrollResult.success && enrollResult.enrollments) {
                enrollments = enrollResult.enrollments.filter(e => e.user_id == user.id);
            }
            
            const scoresResult = await adminService.getScores();
            if (scoresResult.success && scoresResult.scores) {
                scores = scoresResult.scores.filter(s => s.user_id == user.id);
            }
            
            const feedbackResult = await adminService.getFeedback();
            if (feedbackResult.success && feedbackResult.feedback) {
                feedback = feedbackResult.feedback.filter(f => f.user_id == user.id);
            }
        }
    } catch (error) {
        console.error('Error fetching user details:', error);
    }
    
    const avatarUrl = userDetails.avatar 
        ? (userDetails.avatar.startsWith('http') ? userDetails.avatar : BASE_PATH + userDetails.avatar)
        : null;
    
    showModal(`
        <div class="user-profile-modal" style="max-width: 900px;">
            <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Thông tin học viên
            </h3>
            
            <!-- User Info Header -->
            <div class="flex items-start gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold overflow-hidden flex-shrink-0">
                    ${avatarUrl 
                        ? `<img src="${avatarUrl}" alt="${escapeHtml(userDetails.fullname)}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='${(userDetails.fullname || 'U').charAt(0).toUpperCase()}'">` 
                        : (userDetails.fullname || 'U').charAt(0).toUpperCase()
                    }
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-bold text-gray-800">${escapeHtml(userDetails.fullname)}</h4>
                    <p class="text-gray-600">${escapeHtml(userDetails.email)}</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="status-badge ${userDetails.is_active ? 'active' : 'cancelled'}">
                            ${userDetails.is_active ? 'Hoạt động' : 'Bị khóa'}
                        </span>
                        <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">ID: ${userDetails.id}</span>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-3 bg-white border rounded-lg">
                    <p class="text-xs text-gray-500 uppercase mb-1">Số điện thoại</p>
                    <p class="font-medium">${escapeHtml(userDetails.phone) || 'Chưa cập nhật'}</p>
                </div>
                <div class="p-3 bg-white border rounded-lg">
                    <p class="text-xs text-gray-500 uppercase mb-1">Ngày tạo tài khoản</p>
                    <p class="font-medium">${formatDate(userDetails.created_at)}</p>
                </div>
            </div>
            
            <!-- Enrollments Section (with class info) -->
            <div class="mb-6">
                <h5 class="font-bold text-gray-700 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Khóa học & Lớp học (${enrollments.length})
                </h5>
                ${enrollments.length > 0 ? `
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left">Khóa học</th>
                                    <th class="px-3 py-2 text-left">Lớp học</th>
                                    <th class="px-3 py-2 text-left">Giảng viên</th>
                                    <th class="px-3 py-2 text-left">Lịch học</th>
                                    <th class="px-3 py-2 text-left">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${enrollments.map(e => `
                                    <tr class="border-t">
                                        <td class="px-3 py-2">
                                            <div class="font-medium">${escapeHtml(e.course_name)}</div>
                                            <div class="text-xs text-gray-500">${e.level || ''}</div>
                                        </td>
                                        <td class="px-3 py-2">
                                            ${e.class_name ? `
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">${escapeHtml(e.class_name)}</span>
                                            ` : `
                                                <span class="text-gray-400 text-xs">Chưa phân lớp</span>
                                            `}
                                        </td>
                                        <td class="px-3 py-2">${e.teacher_name ? escapeHtml(e.teacher_name) : '-'}</td>
                                        <td class="px-3 py-2">
                                            ${e.schedule ? `
                                                <div class="text-xs">${escapeHtml(e.schedule)}</div>
                                                ${e.start_date ? `<div class="text-xs text-gray-500">${formatDate(e.start_date)} - ${formatDate(e.end_date)}</div>` : ''}
                                            ` : '-'}
                                        </td>
                                        <td class="px-3 py-2">${getStatusBadge(e.status)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                ` : '<p class="text-gray-500 text-sm">Chưa đăng ký khóa học nào</p>'}
            </div>
            
            <!-- Scores Section -->
            <div class="mb-6">
                <h5 class="font-bold text-gray-700 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Điểm số (${scores.length})
                </h5>
                ${scores.length > 0 ? `
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border rounded-lg overflow-hidden">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left">Lớp/Khóa</th>
                                    <th class="px-3 py-2 text-left">Ngày thi</th>
                                    <th class="px-3 py-2 text-center">Listening</th>
                                    <th class="px-3 py-2 text-center">Reading</th>
                                    <th class="px-3 py-2 text-center">Writing</th>
                                    <th class="px-3 py-2 text-center">Speaking</th>
                                    <th class="px-3 py-2 text-center">Overall</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${scores.map(s => `
                                    <tr class="border-t">
                                        <td class="px-3 py-2">${s.class_name ? escapeHtml(s.class_name) : (s.course_name ? escapeHtml(s.course_name) : '-')}</td>
                                        <td class="px-3 py-2">${formatDate(s.test_date)}</td>
                                        <td class="px-3 py-2 text-center font-medium">${s.listening || '-'}</td>
                                        <td class="px-3 py-2 text-center font-medium">${s.reading || '-'}</td>
                                        <td class="px-3 py-2 text-center font-medium">${s.writing || '-'}</td>
                                        <td class="px-3 py-2 text-center font-medium">${s.speaking || '-'}</td>
                                        <td class="px-3 py-2 text-center font-bold text-blue-600">${s.overall || '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                ` : '<p class="text-gray-500 text-sm">Chưa có điểm số</p>'}
            </div>
            
            <!-- Feedback Section -->
            <div class="mb-4">
                <h5 class="font-bold text-gray-700 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Nhận xét từ giảng viên (${feedback.length})
                </h5>
                ${feedback.length > 0 ? `
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        ${feedback.map(f => `
                            <div class="p-3 bg-white border rounded-lg">
                                <p class="text-sm">${escapeHtml(f.content)}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    ${f.teacher_name ? escapeHtml(f.teacher_name) + ' • ' : ''}
                                    ${formatDate(f.created_at)}
                                </p>
                            </div>
                        `).join('')}
                    </div>
                ` : '<p class="text-gray-500 text-sm">Chưa có nhận xét</p>'}
            </div>
            
            <!-- Actions -->
            <div class="flex gap-2 justify-end pt-4 border-t">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Đóng</button>
                <button type="button" class="admin-action-btn primary" id="edit-from-profile-btn">Sửa thông tin</button>
            </div>
        </div>
    `);
    
    // Edit button handler
    document.getElementById('edit-from-profile-btn')?.addEventListener('click', () => {
        hideModal();
        showUserModal(user);
    });
}

function showUserModal(user = null) {
    const isEdit = !!user;
    showModal(`
        <h3 class="text-xl font-bold mb-4">${isEdit ? 'Sửa thông tin học viên' : 'Thêm học viên mới'}</h3>
        <form id="user-form" class="space-y-4">
            <input type="hidden" name="id" value="${user?.id || ''}">
            <div>
                <label class="profile-form-label">Họ và tên *</label>
                <input type="text" name="fullname" class="profile-form-input" value="${escapeHtml(user?.fullname || '')}" required>
            </div>
            <div>
                <label class="profile-form-label">Email *</label>
                <input type="email" name="email" class="profile-form-input" value="${escapeHtml(user?.email || '')}" ${isEdit ? 'readonly' : ''} required>
            </div>
            ${!isEdit ? `
            <div>
                <label class="profile-form-label">Mật khẩu *</label>
                <input type="password" name="password" class="profile-form-input" minlength="6" required>
                <p class="text-xs text-gray-500 mt-1">Tối thiểu 6 ký tự</p>
            </div>
            ` : ''}
            <div>
                <label class="profile-form-label">Số điện thoại</label>
                <input type="tel" name="phone" class="profile-form-input" value="${escapeHtml(user?.phone || '')}">
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" ${user?.is_active !== 0 ? 'checked' : ''}>
                    <span>Tài khoản hoạt động</span>
                </label>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                <button type="submit" class="admin-action-btn primary">${isEdit ? 'Cập nhật' : 'Thêm học viên'}</button>
            </div>
        </form>
    `);

    document.getElementById('user-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        data.is_active = formData.has('is_active') ? 1 : 0;

        try {
            const result = isEdit 
                ? await adminService.updateUser(data)
                : await adminService.createUser(data);
            
            if (result.success) {
                showToast(isEdit ? 'Cập nhật thành công!' : 'Thêm học viên thành công!', 'success');
                hideModal();
                renderUsers();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

function showCourseModal(course = null) {
    const isEdit = !!course;
    const featuresStr = Array.isArray(course?.features) ? course.features.join('\n') : (course?.features || '');
    const imageUrl = course?.image_url ? (course.image_url.startsWith('http') ? course.image_url : BASE_PATH + course.image_url) : '';
    showModal(`
        <h3 class="text-xl font-bold mb-4">${isEdit ? 'Sửa khóa học' : 'Thêm khóa học'}</h3>
        <form id="course-form" class="space-y-4" enctype="multipart/form-data">
            <input type="hidden" name="id" value="${course?.id || ''}">
            <input type="hidden" name="_method" value="${isEdit ? 'PUT' : 'POST'}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Tên khóa học *</label>
                    <input type="text" name="name" class="profile-form-input" value="${course?.name || ''}" required>
                </div>
                <div>
                    <label class="profile-form-label">Danh mục *</label>
                    <select name="age_group" class="profile-form-input" required>
                        <option value="tieuhoc" ${course?.age_group === 'tieuhoc' ? 'selected' : ''}>Tiểu học</option>
                        <option value="thcs" ${course?.age_group === 'thcs' ? 'selected' : ''}>THCS</option>
                        <option value="ielts" ${course?.age_group === 'ielts' ? 'selected' : ''}>IELTS</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Level (hiển thị trong bảng)</label>
                    <input type="text" name="level" class="profile-form-input" value="${course?.level || ''}" placeholder="VD: Pre-Starters, S1, IELTS 4.5-5.0">
                </div>
                <div>
                    <label class="profile-form-label">Giáo trình</label>
                    <input type="text" name="curriculum" class="profile-form-input" value="${course?.curriculum || ''}" placeholder="VD: F1, Prepare 2, Complete IELTS">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Thời lượng khóa học</label>
                    <input type="text" name="duration" class="profile-form-input" value="${course?.duration || ''}" placeholder="VD: 20 weeks = 5 months">
                </div>
                <div>
                    <label class="profile-form-label">Học phí/tháng (VNĐ)</label>
                    <input type="number" name="price" class="profile-form-input" value="${course?.price || 0}" placeholder="750000">
                </div>
            </div>
            <div>
                <label class="profile-form-label">Mô tả</label>
                <textarea name="description" class="profile-form-input" rows="2">${course?.description || ''}</textarea>
            </div>
            <div>
                <label class="profile-form-label">Đặc điểm khóa học (mỗi dòng 1 điểm)</label>
                <textarea name="features" class="profile-form-input" rows="3" placeholder="Giáo trình Cambridge&#10;Cam kết đầu ra&#10;Lớp học nhỏ 8-12 học viên">${featuresStr}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Hình ảnh khóa học</label>
                    <div class="space-y-2">
                        <input type="file" name="image" id="course-image-input" class="profile-form-input" accept="image/*">
                        <div id="course-image-preview" class="mt-2 ${imageUrl ? '' : 'hidden'}">
                            <img src="${imageUrl}" alt="Preview" class="w-32 h-20 object-cover rounded border" id="course-preview-img">
                            <p class="text-xs text-gray-500 mt-1">Ảnh hiện tại</p>
                        </div>
                        <input type="hidden" name="image_url" id="course-image-url" value="${course?.image_url || ''}">
                    </div>
                </div>
                <div>
                    <label class="profile-form-label">Badge</label>
                    <select name="badge" class="profile-form-input">
                        <option value="" ${!course?.badge ? 'selected' : ''}>Không có</option>
                        <option value="Hot" ${course?.badge === 'Hot' ? 'selected' : ''}>🔥 Hot</option>
                        <option value="Mới" ${course?.badge === 'Mới' ? 'selected' : ''}>✨ Mới</option>
                        <option value="Phổ biến" ${course?.badge === 'Phổ biến' ? 'selected' : ''}>⭐ Phổ biến</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" ${course?.is_active !== 0 ? 'checked' : ''}>
                    <span>Hoạt động</span>
                </label>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                <button type="submit" class="admin-action-btn primary">${isEdit ? 'Cập nhật' : 'Thêm mới'}</button>
            </div>
        </form>
    `);

    // Preview image on file select
    document.getElementById('course-image-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('course-preview-img').src = ev.target.result;
                document.getElementById('course-image-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('course-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        // Check if there's a file upload
        const imageFile = form.querySelector('input[name="image"]').files[0];
        
        if (imageFile) {
            // Use FormData for file upload
            formData.set('is_active', form.querySelector('input[name="is_active"]').checked ? 1 : 0);
            formData.set('price', parseInt(formData.get('price')) || 0);
            formData.set('price_unit', '/tháng');
            
            // Set badge_type
            const badge = formData.get('badge');
            if (badge === 'Hot') formData.set('badge_type', 'hot');
            else if (badge === 'Mới') formData.set('badge_type', 'new');
            else if (badge === 'Phổ biến') formData.set('badge_type', 'popular');
            else formData.set('badge_type', '');
            
            try {
                const result = isEdit 
                    ? await adminService.updateCourseWithFile(formData)
                    : await adminService.createCourseWithFile(formData);
                
                if (result.success) {
                    showToast(isEdit ? 'Cập nhật thành công!' : 'Thêm khóa học thành công!', 'success');
                    hideModal();
                    renderCourses();
                } else {
                    showToast(result.message || result.error || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối: ' + error.message, 'error');
            }
        } else {
            // No file - use JSON
            const data = Object.fromEntries(formData.entries());
            data.is_active = form.querySelector('input[name="is_active"]').checked ? 1 : 0;
            data.price = parseInt(data.price) || 0;
            data.price_unit = '/tháng';
            if (data.features) {
                data.features = data.features.split('\n').map(f => f.trim()).filter(f => f);
            }
            if (data.badge === 'Hot') data.badge_type = 'hot';
            else if (data.badge === 'Mới') data.badge_type = 'new';
            else if (data.badge === 'Phổ biến') data.badge_type = 'popular';
            else data.badge_type = '';

            try {
                const result = isEdit 
                    ? await adminService.updateCourse(data)
                    : await adminService.createCourse(data);
                
                if (result.success) {
                    showToast(isEdit ? 'Cập nhật thành công!' : 'Thêm khóa học thành công!', 'success');
                    hideModal();
                    renderCourses();
                } else {
                    showToast(result.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        }
    });
}

function showEnrollmentModal(enrollment) {
    showModal(`
        <h3 class="text-xl font-bold mb-4">Sửa đăng ký</h3>
        <form id="enrollment-form" class="space-y-4">
            <input type="hidden" name="id" value="${enrollment.id}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Năm học</label>
                    <input type="text" name="academic_year" class="profile-form-input" value="${enrollment.academic_year || ''}" placeholder="VD: 2024-2025">
                </div>
                <div>
                    <label class="profile-form-label">Học kỳ</label>
                    <input type="text" name="semester" class="profile-form-input" value="${enrollment.semester || ''}" placeholder="VD: HK1">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Ngày bắt đầu</label>
                    <input type="date" name="start_date" class="profile-form-input" value="${enrollment.start_date?.split('T')[0] || ''}">
                </div>
                <div>
                    <label class="profile-form-label">Ngày kết thúc</label>
                    <input type="date" name="end_date" class="profile-form-input" value="${enrollment.end_date?.split('T')[0] || ''}">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Tiến độ (%)</label>
                    <input type="number" name="progress" class="profile-form-input" value="${enrollment.progress || 0}" min="0" max="100">
                </div>
                <div>
                    <label class="profile-form-label">Trạng thái</label>
                    <select name="status" class="profile-form-input">
                        <option value="pending" ${enrollment.status === 'pending' ? 'selected' : ''}>Chờ xử lý</option>
                        <option value="active" ${enrollment.status === 'active' ? 'selected' : ''}>Đang học</option>
                        <option value="completed" ${enrollment.status === 'completed' ? 'selected' : ''}>Hoàn thành</option>
                        <option value="cancelled" ${enrollment.status === 'cancelled' ? 'selected' : ''}>Đã hủy</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                <button type="submit" class="admin-action-btn primary">Cập nhật</button>
            </div>
        </form>
    `);

    document.getElementById('enrollment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        data.progress = parseInt(data.progress) || 0;

        try {
            const result = await adminService.updateEnrollment(data);
            if (result.success) {
                showToast('Cập nhật thành công!', 'success');
                hideModal();
                renderEnrollments();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

// Make hideModal global
window.hideModal = hideModal;

// Variable to store selected teacher image file
let selectedTeacherImageFile = null;

// Show Teacher Modal
function showTeacherModal(teacher = null) {
    const isEdit = !!teacher;
    selectedTeacherImageFile = null;
    
    // Get base path for image display
    const basePath = typeof BASE_PATH !== 'undefined' ? BASE_PATH : '';
    const currentImageUrl = teacher?.image_url ? (teacher.image_url.startsWith('http') ? teacher.image_url : basePath + teacher.image_url) : '';
    
    showModal(`
        <h3 class="text-xl font-bold mb-4">${isEdit ? 'Sửa thông tin giảng viên' : 'Thêm giảng viên mới'}</h3>
        <form id="teacher-form" class="space-y-4" style="max-height: 70vh; overflow-y: auto; padding-right: 8px;">
            <input type="hidden" name="id" value="${teacher?.id || ''}">
            <input type="hidden" name="image_url" id="teacher-image-url" value="${escapeHtml(teacher?.image_url || '')}">
            <div>
                <label class="profile-form-label">Họ và tên *</label>
                <input type="text" name="name" class="profile-form-input" value="${escapeHtml(teacher?.name || '')}" required>
            </div>
            <div>
                <label class="profile-form-label">Chức danh</label>
                <input type="text" name="title" class="profile-form-input" value="${escapeHtml(teacher?.title || '')}" placeholder="VD: Giảng viên IELTS">
            </div>
            <div>
                <label class="profile-form-label">Học vị</label>
                <select name="degree" class="profile-form-input">
                    <option value="" ${!teacher?.degree ? 'selected' : ''}>-- Chọn học vị --</option>
                    <option value="Cử nhân" ${teacher?.degree === 'Cử nhân' ? 'selected' : ''}>Cử nhân</option>
                    <option value="Thạc sĩ" ${teacher?.degree === 'Thạc sĩ' ? 'selected' : ''}>Thạc sĩ</option>
                    <option value="Tiến sĩ" ${teacher?.degree === 'Tiến sĩ' ? 'selected' : ''}>Tiến sĩ</option>
                    <option value="Phó Giáo sư" ${teacher?.degree === 'Phó Giáo sư' ? 'selected' : ''}>Phó Giáo sư</option>
                    <option value="Giáo sư" ${teacher?.degree === 'Giáo sư' ? 'selected' : ''}>Giáo sư</option>
                </select>
            </div>
            <!-- TODO: Tạm ẩn nhập IELTS - bật lại khi cần
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Điểm IELTS</label>
                    <input type="number" name="ielts_score" class="profile-form-input" value="${teacher?.ielts_score || ''}" step="0.5" min="0" max="9">
                </div>
                <div>
                    <label class="profile-form-label">Kinh nghiệm (năm)</label>
                    <input type="number" name="experience_years" class="profile-form-input" value="${teacher?.experience_years || 0}" min="0">
                </div>
            </div>
            -->
            <div>
                <label class="profile-form-label">Kinh nghiệm (năm)</label>
                <input type="number" name="experience_years" class="profile-form-input" value="${teacher?.experience_years || 0}" min="0">
            </div>
            <!-- TODO: Tạm ẩn nhập IELTS - bật lại khi cần 
            <div>
                <label class="profile-form-label">Mô tả</label>
                <textarea name="description" class="profile-form-input" rows="3" placeholder="Thông tin về giảng viên...">${escapeHtml(teacher?.description || '')}</textarea> 
            </div>
            -->
            <div>
                <label class="profile-form-label">Ảnh đại diện</label>
                <div class="flex items-start gap-4">
                    <div id="teacher-image-preview" class="w-24 h-24 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300">
                        ${currentImageUrl 
                            ? `<img src="${currentImageUrl}" alt="Teacher" class="w-full h-full object-cover">`
                            : `<svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>`
                        }
                    </div>
                    <div class="flex-1">
                        <input type="file" id="teacher-image-input" accept="image/*" class="hidden">
                        <button type="button" id="teacher-image-btn" class="admin-action-btn secondary mb-2">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Chọn ảnh
                        </button>
                        <p class="text-xs text-gray-500">JPG, PNG, GIF, WEBP. Tối đa 5MB</p>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_featured" ${teacher?.is_featured ? 'checked' : ''}>
                        <span>Giảng viên nổi bật</span>
                    </label>
                </div>
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" ${teacher?.is_active !== 0 ? 'checked' : ''}>
                        <span>Đang hoạt động</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-2 justify-end pt-4 border-t">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                <button type="submit" class="admin-action-btn primary">${isEdit ? 'Cập nhật' : 'Thêm giảng viên'}</button>
            </div>
        </form>
    `);
    
    // Setup image upload
    const imageBtn = document.getElementById('teacher-image-btn');
    const imageInput = document.getElementById('teacher-image-input');
    const imagePreview = document.getElementById('teacher-image-preview');
    
    imageBtn?.addEventListener('click', () => imageInput?.click());
    
    imageInput?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validate
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showToast('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            showToast('File quá lớn. Tối đa 5MB', 'error');
            return;
        }
        
        selectedTeacherImageFile = file;
        
        // Preview
        const reader = new FileReader();
        reader.onload = (event) => {
            imagePreview.innerHTML = `<img src="${event.target.result}" alt="Preview" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('teacher-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        data.is_featured = formData.has('is_featured') ? 1 : 0;
        data.is_active = formData.has('is_active') ? 1 : 0;
        data.ielts_score = parseFloat(data.ielts_score) || null;
        data.experience_years = parseInt(data.experience_years) || 0;

        try {
            const result = isEdit 
                ? await adminService.updateTeacher(data, selectedTeacherImageFile)
                : await adminService.createTeacher(data, selectedTeacherImageFile);
            
            if (result.success) {
                showToast(isEdit ? 'Cập nhật thành công!' : 'Thêm giảng viên thành công!', 'success');
                hideModal();
                renderTeachers();
                selectedTeacherImageFile = null;
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

// Show New Enrollment Modal (Đăng ký khóa học cho học viên)
async function showNewEnrollmentModal() {
    // Load users, courses and classes for dropdown
    const [usersResult, coursesResult, classesResult] = await Promise.all([
        adminService.getUsers(),
        adminService.getCourses(),
        adminService.getClasses()
    ]);

    const users = usersResult.users || [];
    const courses = coursesResult.courses || [];
    const allClassesForEnroll = classesResult.classes || [];

    showModal(`
        <h3 class="text-xl font-bold mb-4">Đăng ký khóa học cho học viên</h3>
        <form id="new-enrollment-form" class="space-y-4">
            <div>
                <label class="profile-form-label">Học viên *</label>
                <select name="user_id" class="profile-form-input" required>
                    <option value="">-- Chọn học viên --</option>
                    ${users.filter(u => u.role !== 'admin').map(u => 
                        `<option value="${u.id}">${escapeHtml(u.fullname)} (${escapeHtml(u.email)})</option>`
                    ).join('')}
                </select>
            </div>
            <div>
                <label class="profile-form-label">Khóa học *</label>
                <select name="course_id" id="enroll-course-select" class="profile-form-input" required>
                    <option value="">-- Chọn khóa học --</option>
                    ${courses.filter(c => c.is_active).map(c => 
                        `<option value="${c.id}">${escapeHtml(c.name)} - ${formatMoney(c.price)}</option>`
                    ).join('')}
                </select>
            </div>
            <div>
                <label class="profile-form-label">Lớp học <span class="text-gray-400 text-sm">(tùy chọn)</span></label>
                <select name="class_id" id="enroll-class-select" class="profile-form-input">
                    <option value="">-- Chưa phân lớp --</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Chọn khóa học trước để xem danh sách lớp</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Năm học</label>
                    <input type="text" name="academic_year" class="profile-form-input" value="2025-2026" placeholder="VD: 2025-2026">
                </div>
                <div>
                    <label class="profile-form-label">Học kỳ</label>
                    <select name="semester" class="profile-form-input">
                        <option value="Học kỳ 1">Học kỳ 1</option>
                        <option value="Học kỳ 2" selected>Học kỳ 2</option>
                        <option value="Học kỳ hè">Học kỳ hè</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Ngày bắt đầu</label>
                    <input type="date" name="start_date" class="profile-form-input" value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div>
                    <label class="profile-form-label">Ngày kết thúc</label>
                    <input type="date" name="end_date" class="profile-form-input">
                </div>
            </div>
            <div>
                <label class="profile-form-label">Trạng thái</label>
                <select name="status" class="profile-form-input">
                    <option value="pending">Chờ xử lý</option>
                    <option value="active" selected>Đang học</option>
                </select>
            </div>
            <div class="flex gap-2 justify-end pt-4 border-t">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                <button type="submit" class="admin-action-btn primary">Đăng ký</button>
            </div>
        </form>
    `);

    // Update class dropdown when course changes
    document.getElementById('enroll-course-select').addEventListener('change', (e) => {
        const courseId = e.target.value;
        const classSelect = document.getElementById('enroll-class-select');
        const filteredClasses = allClassesForEnroll.filter(c => c.course_id == courseId && c.status !== 'completed' && c.status !== 'cancelled');
        
        classSelect.innerHTML = '<option value="">-- Chưa phân lớp --</option>' +
            filteredClasses.map(c => {
                const available = (c.max_students || 20) - (c.student_count || 0);
                return `<option value="${c.id}" ${available <= 0 ? 'disabled' : ''}>${escapeHtml(c.name)} - ${c.schedule || 'Chưa có lịch'} (còn ${available} chỗ)</option>`;
            }).join('');
    });

    document.getElementById('new-enrollment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        data.progress = 0;
        if (!data.class_id) delete data.class_id;

        try {
            const result = await adminService.createEnrollment(data);
            if (result.success) {
                showToast('Đăng ký khóa học thành công!', 'success');
                hideModal();
                renderEnrollments();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

// Show Score Modal (Nhập điểm cho học viên)
async function showScoreModal(score = null) {
    const isEdit = !!score;
    
    // Load enrollments for dropdown
    const enrollmentsResult = await adminService.getEnrollments();
    const enrollments = (enrollmentsResult.enrollments || []).filter(e => e.status === 'active' || e.status === 'completed');

    showModal(`
        <h3 class="text-xl font-bold mb-4">${isEdit ? 'Sửa điểm số' : 'Nhập điểm IELTS cho học viên'}</h3>
        <form id="score-form" class="space-y-4">
            <input type="hidden" name="id" value="${score?.id || ''}">
            <div>
                <label class="profile-form-label">Học viên - Khóa học *</label>
                <select name="enrollment_id" class="profile-form-input" required ${isEdit ? 'disabled' : ''}>
                    <option value="">-- Chọn học viên và khóa học --</option>
                    ${enrollments.map(e => 
                        `<option value="${e.id}" ${score?.enrollment_id == e.id ? 'selected' : ''}>${escapeHtml(e.fullname || 'N/A')} - ${escapeHtml(e.course_name || 'N/A')}</option>`
                    ).join('')}
                </select>
                ${isEdit ? `<input type="hidden" name="enrollment_id" value="${score?.enrollment_id}">` : ''}
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Loại bài thi</label>
                    <select name="test_type" class="profile-form-input">
                        <option value="placement" ${score?.test_type === 'placement' ? 'selected' : ''}>Đầu vào (Placement)</option>
                        <option value="midterm" ${score?.test_type === 'midterm' ? 'selected' : ''}>Giữa kỳ (Midterm)</option>
                        <option value="final" ${score?.test_type === 'final' ? 'selected' : ''}>Cuối kỳ (Final)</option>
                        <option value="mock" ${score?.test_type === 'mock' ? 'selected' : ''}>Thi thử (Mock)</option>
                    </select>
                </div>
                <div>
                    <label class="profile-form-label">Ngày thi</label>
                    <input type="date" name="test_date" class="profile-form-input" value="${score?.test_date?.split('T')[0] || new Date().toISOString().split('T')[0]}">
                </div>
            </div>
            <div class="grid grid-cols-4 gap-4">
                <div>
                    <label class="profile-form-label">Listening</label>
                    <input type="number" name="listening" class="profile-form-input" value="${score?.listening || ''}" step="0.5" min="0" max="9" placeholder="0-9">
                </div>
                <div>
                    <label class="profile-form-label">Reading</label>
                    <input type="number" name="reading" class="profile-form-input" value="${score?.reading || ''}" step="0.5" min="0" max="9" placeholder="0-9">
                </div>
                <div>
                    <label class="profile-form-label">Writing</label>
                    <input type="number" name="writing" class="profile-form-input" value="${score?.writing || ''}" step="0.5" min="0" max="9" placeholder="0-9">
                </div>
                <div>
                    <label class="profile-form-label">Speaking</label>
                    <input type="number" name="speaking" class="profile-form-input" value="${score?.speaking || ''}" step="0.5" min="0" max="9" placeholder="0-9">
                </div>
            </div>
            <div>
                <label class="profile-form-label">Overall (tự tính hoặc nhập)</label>
                <input type="number" name="overall" id="score-overall" class="profile-form-input" value="${score?.overall || ''}" step="0.5" min="0" max="9" placeholder="Điểm tổng">
                <button type="button" id="calc-overall-btn" class="text-blue-600 text-sm mt-1 hover:underline">Tính tự động</button>
            </div>
            <div>
                <label class="profile-form-label">Ghi chú</label>
                <textarea name="notes" class="profile-form-input" rows="2" placeholder="Nhận xét về bài thi...">${escapeHtml(score?.notes || '')}</textarea>
            </div>
            <div class="flex gap-2 justify-end pt-4 border-t">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                <button type="submit" class="admin-action-btn primary">${isEdit ? 'Cập nhật' : 'Lưu điểm'}</button>
            </div>
        </form>
    `);

    // Auto calculate overall
    document.getElementById('calc-overall-btn')?.addEventListener('click', () => {
        const l = parseFloat(document.querySelector('[name="listening"]').value) || 0;
        const r = parseFloat(document.querySelector('[name="reading"]').value) || 0;
        const w = parseFloat(document.querySelector('[name="writing"]').value) || 0;
        const s = parseFloat(document.querySelector('[name="speaking"]').value) || 0;
        const avg = (l + r + w + s) / 4;
        // Round to nearest 0.5
        const overall = Math.round(avg * 2) / 2;
        document.getElementById('score-overall').value = overall;
    });

    document.getElementById('score-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        // Get user_id from enrollment
        const selectedEnrollment = enrollments.find(en => en.id == data.enrollment_id);
        data.user_id = selectedEnrollment?.user_id;
        
        data.listening = parseFloat(data.listening) || null;
        data.reading = parseFloat(data.reading) || null;
        data.writing = parseFloat(data.writing) || null;
        data.speaking = parseFloat(data.speaking) || null;
        data.overall = parseFloat(data.overall) || null;

        try {
            const result = isEdit 
                ? await adminService.updateScore(data)
                : await adminService.createScore(data);
            
            if (result.success) {
                showToast(isEdit ? 'Cập nhật thành công!' : 'Lưu điểm thành công!', 'success');
                hideModal();
                renderScores();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

// Show Feedback Modal (Thêm nhận xét học viên)
async function showFeedbackModal() {
    // Load enrollments and teachers for dropdown
    const [enrollmentsResult, teachersResult] = await Promise.all([
        adminService.getEnrollments(),
        adminService.getTeachers()
    ]);

    const enrollments = (enrollmentsResult.enrollments || []).filter(e => e.status === 'active' || e.status === 'completed');
    const teachers = teachersResult.teachers || [];

    showModal(`
        <h3 class="text-xl font-bold mb-4">Thêm nhận xét học viên</h3>
        <form id="feedback-form" class="space-y-4">
            <div>
                <label class="profile-form-label">Học viên - Khóa học *</label>
                <select name="enrollment_id" id="feedback-enrollment" class="profile-form-input" required>
                    <option value="">-- Chọn học viên và khóa học --</option>
                    ${enrollments.map(e => 
                        `<option value="${e.id}" data-user-id="${e.user_id}">${escapeHtml(e.fullname || 'N/A')} - ${escapeHtml(e.course_name || 'N/A')}</option>`
                    ).join('')}
                </select>
            </div>
            <div>
                <label class="profile-form-label">Giảng viên *</label>
                <select name="teacher_id" class="profile-form-input" required>
                    <option value="">-- Chọn giảng viên --</option>
                    ${teachers.map(t => 
                        `<option value="${t.id}">${escapeHtml(t.name)} - ${escapeHtml(t.title || '')}</option>`
                    ).join('')}
                </select>
            </div>
            <div>
                <label class="profile-form-label">Đánh giá (1-5 sao)</label>
                <select name="rating" class="profile-form-input">
                    <option value="5">⭐⭐⭐⭐⭐ Xuất sắc</option>
                    <option value="4" selected>⭐⭐⭐⭐ Tốt</option>
                    <option value="3">⭐⭐⭐ Khá</option>
                    <option value="2">⭐⭐ Trung bình</option>
                    <option value="1">⭐ Cần cải thiện</option>
                </select>
            </div>
            <div>
                <label class="profile-form-label">Nội dung nhận xét *</label>
                <textarea name="content" class="profile-form-input" rows="4" 
                    placeholder="Nhập nhận xét về quá trình học tập, điểm mạnh, điểm cần cải thiện..." required></textarea>
            </div>
            <div>
                <label class="profile-form-label">Ngày nhận xét</label>
                <input type="date" name="feedback_date" class="profile-form-input" value="${new Date().toISOString().split('T')[0]}">
            </div>
            <div class="flex gap-2 justify-end pt-4 border-t">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                <button type="submit" class="admin-action-btn primary">Lưu nhận xét</button>
            </div>
        </form>
    `);

    document.getElementById('feedback-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        // Get user_id from enrollment
        const selectedOption = document.getElementById('feedback-enrollment').selectedOptions[0];
        data.user_id = selectedOption?.dataset.userId;

        try {
            const result = await adminService.createFeedback(data);
            if (result.success) {
                showToast('Đã thêm nhận xét!', 'success');
                hideModal();
                renderFeedback();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

// Show Schedule Modal (CLASS-BASED - thêm thời khóa biểu cho LỚP HỌC)
async function showScheduleModal(schedule = null) {
    // Load classes and teachers for dropdown
    const [classesResult, teachersResult] = await Promise.all([
        adminService.getClasses(),
        adminService.getTeachers()
    ]);

    const classes = classesResult.classes || [];
    const teachers = teachersResult.teachers || [];
    const activeClasses = classes.filter(c => c.status === 'active' || c.status === 'upcoming');

    showModal(`
        <h3 class="text-xl font-bold mb-4">${schedule ? 'Sửa lịch học' : 'Thêm lịch học mới'}</h3>
        <form id="schedule-form" class="space-y-4" style="max-height: 70vh; overflow-y: auto; padding-right: 8px;">
            ${schedule ? `<input type="hidden" name="id" value="${schedule.id}">` : ''}
            
            <div>
                <label class="profile-form-label">Lớp học *</label>
                <select name="class_id" class="profile-form-input" required>
                    <option value="">-- Chọn lớp học --</option>
                    ${activeClasses.map(c => `
                        <option value="${c.id}" ${schedule?.class_id == c.id ? 'selected' : ''}>
                            ${escapeHtml(c.name)} - ${escapeHtml(c.course_name || '')}
                        </option>
                    `).join('')}
                </select>
                <p class="text-xs text-gray-500 mt-1">* Học viên trong lớp sẽ tự động nhận thời khóa biểu này</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Ngày trong tuần *</label>
                    <select name="day_of_week" class="profile-form-input" required>
                        <option value="monday" ${schedule?.day_of_week === 'monday' ? 'selected' : ''}>Thứ Hai</option>
                        <option value="tuesday" ${schedule?.day_of_week === 'tuesday' ? 'selected' : ''}>Thứ Ba</option>
                        <option value="wednesday" ${schedule?.day_of_week === 'wednesday' ? 'selected' : ''}>Thứ Tư</option>
                        <option value="thursday" ${schedule?.day_of_week === 'thursday' ? 'selected' : ''}>Thứ Năm</option>
                        <option value="friday" ${schedule?.day_of_week === 'friday' ? 'selected' : ''}>Thứ Sáu</option>
                        <option value="saturday" ${schedule?.day_of_week === 'saturday' ? 'selected' : ''}>Thứ Bảy</option>
                        <option value="sunday" ${schedule?.day_of_week === 'sunday' ? 'selected' : ''}>Chủ Nhật</option>
                    </select>
                </div>
                <div>
                    <label class="profile-form-label">Môn/Nội dung</label>
                    <select name="subject" class="profile-form-input">
                        <option value="">-- Chọn môn --</option>
                        <option value="Speaking" ${schedule?.subject === 'Speaking' ? 'selected' : ''}>Speaking</option>
                        <option value="Writing" ${schedule?.subject === 'Writing' ? 'selected' : ''}>Writing</option>
                        <option value="Reading" ${schedule?.subject === 'Reading' ? 'selected' : ''}>Reading</option>
                        <option value="Listening" ${schedule?.subject === 'Listening' ? 'selected' : ''}>Listening</option>
                        <option value="Grammar" ${schedule?.subject === 'Grammar' ? 'selected' : ''}>Grammar</option>
                        <option value="Vocabulary" ${schedule?.subject === 'Vocabulary' ? 'selected' : ''}>Vocabulary</option>
                        <option value="General" ${schedule?.subject === 'General' ? 'selected' : ''}>Tổng hợp</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Giờ bắt đầu *</label>
                    <input type="time" name="start_time" class="profile-form-input" value="${schedule?.start_time?.substring(0, 5) || '18:00'}" required>
                </div>
                <div>
                    <label class="profile-form-label">Giờ kết thúc *</label>
                    <input type="time" name="end_time" class="profile-form-input" value="${schedule?.end_time?.substring(0, 5) || '20:00'}" required>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Phòng học</label>
                    <input type="text" name="room" class="profile-form-input" value="${schedule?.room || ''}" placeholder="VD: Phòng A1">
                </div>
                <div>
                    <label class="profile-form-label">Giảng viên</label>
                    <select name="teacher_id" class="profile-form-input">
                        <option value="">-- Chọn --</option>
                        ${teachers.map(t => `
                            <option value="${t.id}" ${schedule?.teacher_id == t.id ? 'selected' : ''}>${escapeHtml(t.name)}</option>
                        `).join('')}
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_online" id="is_online" ${schedule?.is_online ? 'checked' : ''} class="w-4 h-4">
                        <span>Học trực tuyến (Online)</span>
                    </label>
                </div>
                <div>
                    <label class="profile-form-label">Màu sắc</label>
                    <input type="color" name="color" class="profile-form-input h-10" value="${schedule?.color || '#1e40af'}">
                </div>
            </div>
            
            <div id="meeting-link-field" class="${schedule?.is_online ? '' : 'hidden'}">
                <label class="profile-form-label">Link Meeting (Zoom/Meet)</label>
                <input type="url" name="meeting_link" class="profile-form-input" value="${schedule?.meeting_link || ''}" placeholder="https://zoom.us/...">
            </div>
            
            <div>
                <label class="profile-form-label">Ghi chú</label>
                <textarea name="notes" class="profile-form-input" rows="2" placeholder="Ghi chú thêm...">${schedule?.notes || ''}</textarea>
            </div>
            
            <div class="flex gap-2 justify-end pt-4 border-t">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                <button type="submit" class="admin-action-btn primary">${schedule ? 'Cập nhật' : 'Thêm mới'}</button>
            </div>
        </form>
    `);

    // Toggle meeting link field
    document.getElementById('is_online')?.addEventListener('change', (e) => {
        document.getElementById('meeting-link-field')?.classList.toggle('hidden', !e.target.checked);
    });

    document.getElementById('schedule-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        data.is_online = formData.get('is_online') ? 1 : 0;

        try {
            const result = schedule 
                ? await adminService.updateSchedule(data)
                : await adminService.createSchedule(data);
            
            if (result.success) {
                showToast(schedule ? 'Cập nhật thành công!' : 'Thêm lịch học thành công!', 'success');
                hideModal();
                renderSchedule();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

// ==================== INIT FUNCTIONS ====================

// Helper functions for sidebar
function openSidebar() {
    document.getElementById('sidebar')?.classList.add('open');
    document.getElementById('sidebar-overlay')?.classList.add('active');
    document.getElementById('sidebar-toggle')?.classList.add('sidebar-open');
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    document.getElementById('sidebar')?.classList.remove('open');
    document.getElementById('sidebar-overlay')?.classList.remove('active');
    document.getElementById('sidebar-toggle')?.classList.remove('sidebar-open');
    document.body.style.overflow = '';
}

// Sidebar navigation
function initSidebar() {
    const menuItems = document.querySelectorAll('.sidebar-menu-item');
    const sections = document.querySelectorAll('.content-section');

    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            const sectionId = item.dataset.section;
            
            // Update active menu
            menuItems.forEach(m => m.classList.remove('active'));
            item.classList.add('active');
            
            // Show section
            sections.forEach(s => s.classList.remove('active'));
            document.getElementById(`section-${sectionId}`)?.classList.add('active');

            // Load data based on section
            loadSectionData(sectionId);

            // Close mobile sidebar
            closeSidebar();
        });
    });

    // Mobile toggle
    document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
        openSidebar();
    });
    
    // Close sidebar button
    document.getElementById('sidebar-close')?.addEventListener('click', () => {
        closeSidebar();
    });
    
    // Overlay click to close
    document.getElementById('sidebar-overlay')?.addEventListener('click', () => {
        closeSidebar();
    });
}

// Load section data
function loadSectionData(section) {
    switch(section) {
        case 'dashboard':
            renderDashboard();
            break;
        case 'users':
            renderUsers();
            break;
        case 'enrollments':
            // Load with default filter set to 'open' courses
            const courseStatusFilter = document.getElementById('enrollment-course-status-filter')?.value || 'open';
            const categoryFilter = document.getElementById('enrollment-category-filter')?.value || '';
            renderEnrollments(courseStatusFilter, categoryFilter);
            break;
        case 'courses':
            renderCourses();
            break;
        case 'teachers':
            renderTeachers();
            break;
        case 'classes':
            renderClasses();
            break;
        case 'scores':
            renderScores();
            break;
        case 'feedback':
            renderFeedback();
            break;
        case 'achievements':
            renderAchievements();
            break;
        case 'schedule':
            renderSchedule();
            break;
        case 'trash':
            renderTrash();
            break;
        case 'notifications':
            renderNotifications();
            break;
        case 'reviews':
            renderReviews();
            break;
        case 'content':
            loadSiteContent();
            break;
        case 'course-fees':
            loadCourseFees();
            break;
        case 'teacher-reviews':
            loadTeacherReviews();
            break;
    }
}

// Init filters
function initFilters() {
    // Enrollment filters (new - course-based)
    const enrollmentCourseStatusFilter = document.getElementById('enrollment-course-status-filter');
    const enrollmentCategoryFilter = document.getElementById('enrollment-category-filter');
    
    enrollmentCourseStatusFilter?.addEventListener('change', () => {
        renderEnrollments(
            enrollmentCourseStatusFilter.value || '',
            enrollmentCategoryFilter?.value || ''
        );
    });
    
    enrollmentCategoryFilter?.addEventListener('change', () => {
        renderEnrollments(
            enrollmentCourseStatusFilter?.value || '',
            enrollmentCategoryFilter.value || ''
        );
    });

    // Trash filter
    const trashFilter = document.getElementById('trash-filter');
    trashFilter?.addEventListener('change', () => {
        renderTrash(trashFilter.value || null);
    });

    // Schedule filter by day
    const scheduleFilter = document.getElementById('schedule-filter-day');
    scheduleFilter?.addEventListener('change', () => {
        renderSchedule(scheduleFilter.value || '');
    });

    // Reviews filter
    const reviewsFilter = document.getElementById('reviews-filter');
    reviewsFilter?.addEventListener('change', () => {
        renderReviews(reviewsFilter.value || '');
    });
    
    // Notifications type filter
    const notificationsTypeFilter = document.getElementById('notifications-type-filter');
    notificationsTypeFilter?.addEventListener('change', () => {
        renderNotifications(notificationsTypeFilter.value || '', 1);
    });
    
    // Notifications pagination
    document.getElementById('notif-prev-page')?.addEventListener('click', () => {
        if (notificationsCurrentPage > 1) {
            const filter = document.getElementById('notifications-type-filter')?.value || '';
            renderNotifications(filter, notificationsCurrentPage - 1);
        }
    });
    
    document.getElementById('notif-next-page')?.addEventListener('click', () => {
        if (notificationsCurrentPage < notificationsTotalPages) {
            const filter = document.getElementById('notifications-type-filter')?.value || '';
            renderNotifications(filter, notificationsCurrentPage + 1);
        }
    });
    
    // Mark all notifications as read
    document.getElementById('mark-all-notifications-read')?.addEventListener('click', async () => {
        const result = await adminService.markAllNotificationsAsRead();
        if (result.success) {
            showToast('Đã đánh dấu tất cả đã đọc', 'success');
            const filter = document.getElementById('notifications-type-filter')?.value || '';
            renderNotifications(filter, 1);
            loadHeaderNotifications();
        }
    });
    
    // Header notification bell toggle
    const notificationBtn = document.getElementById('notification-btn');
    const notificationDropdown = document.getElementById('notification-dropdown');
    
    notificationBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        notificationDropdown?.classList.toggle('hidden');
        if (!notificationDropdown?.classList.contains('hidden')) {
            loadHeaderNotifications();
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#notification-container')) {
            notificationDropdown?.classList.add('hidden');
        }
    });
    
    // Mark all read from dropdown
    document.getElementById('mark-all-read-btn')?.addEventListener('click', async () => {
        const result = await adminService.markAllNotificationsAsRead();
        if (result.success) {
            loadHeaderNotifications();
            const filter = document.getElementById('notifications-type-filter')?.value || '';
            renderNotifications(filter, 1);
        }
    });
    
    // View all notifications - switch to notifications section
    document.getElementById('view-all-notifications')?.addEventListener('click', () => {
        notificationDropdown?.classList.add('hidden');
        // Switch to notifications section
        document.querySelectorAll('.sidebar-menu-item').forEach(item => item.classList.remove('active'));
        document.querySelector('[data-section="notifications"]')?.classList.add('active');
        document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
        document.getElementById('section-notifications')?.classList.add('active');
        renderNotifications();
    });
    
    // Load notifications on startup
    loadHeaderNotifications();
}

// Init add buttons
function initAddButtons() {
    document.getElementById('add-user-btn')?.addEventListener('click', () => showUserModal());
    document.getElementById('add-course-btn')?.addEventListener('click', () => showCourseModal());
    document.getElementById('add-schedule-btn')?.addEventListener('click', () => showScheduleModal());
    document.getElementById('add-teacher-btn')?.addEventListener('click', () => showTeacherModal());
    // Removed: add-enrollment-btn - students now self-register
    document.getElementById('add-score-btn')?.addEventListener('click', () => showScoreModal());
    document.getElementById('add-feedback-btn')?.addEventListener('click', () => showFeedbackModal());
    document.getElementById('add-achievement-btn')?.addEventListener('click', () => showAchievementModal());
    document.getElementById('add-class-btn')?.addEventListener('click', () => showClassModal());
    
    // === Classes Search and Filter ===
    const searchClassesInput = document.getElementById('search-classes');
    const filterClassesStatus = document.getElementById('filter-classes-status');
    const filterClassesCourse = document.getElementById('filter-classes-course');
    
    let classSearchTimeout;
    
    const filterClasses = () => {
        const searchVal = searchClassesInput?.value?.trim().toLowerCase() || '';
        const statusVal = filterClassesStatus?.value || 'all';
        const courseVal = filterClassesCourse?.value || 'all';
        
        let filtered = allClasses;
        
        if (searchVal) {
            filtered = filtered.filter(c => 
                c.name?.toLowerCase().includes(searchVal) ||
                c.teacher_name?.toLowerCase().includes(searchVal) ||
                c.room?.toLowerCase().includes(searchVal)
            );
        }
        
        if (statusVal !== 'all') {
            filtered = filtered.filter(c => c.status === statusVal);
        }
        
        if (courseVal !== 'all') {
            filtered = filtered.filter(c => c.course_id == courseVal);
        }
        
        renderClassesTable(filtered);
    };
    
    searchClassesInput?.addEventListener('input', () => {
        clearTimeout(classSearchTimeout);
        classSearchTimeout = setTimeout(filterClasses, 300);
    });
    
    filterClassesStatus?.addEventListener('change', filterClasses);
    filterClassesCourse?.addEventListener('change', filterClasses);
    
    // === Courses Search and Filter ===
    const searchCoursesInput = document.getElementById('search-courses');
    const clearSearchCoursesBtn = document.getElementById('clear-search-courses');
    const filterCoursesCategory = document.getElementById('filter-courses-category');
    
    let courseSearchTimeout;
    
    searchCoursesInput?.addEventListener('input', (e) => {
        clearTimeout(courseSearchTimeout);
        const value = e.target.value.trim();
        clearSearchCoursesBtn?.classList.toggle('hidden', !value);
        
        courseSearchTimeout = setTimeout(() => {
            const categoryVal = filterCoursesCategory?.value || 'all';
            allCoursesData = []; // Reset cache to refetch
            renderCourses(value, categoryVal);
        }, 300);
    });
    
    clearSearchCoursesBtn?.addEventListener('click', () => {
        searchCoursesInput.value = '';
        clearSearchCoursesBtn.classList.add('hidden');
        const categoryVal = filterCoursesCategory?.value || 'all';
        allCoursesData = [];
        renderCourses('', categoryVal);
    });
    
    filterCoursesCategory?.addEventListener('change', () => {
        const searchVal = searchCoursesInput?.value?.trim() || '';
        allCoursesData = [];
        renderCourses(searchVal, filterCoursesCategory.value);
    });
    
    // Empty trash button
    document.getElementById('empty-trash-btn')?.addEventListener('click', async () => {
        const filter = document.getElementById('trash-filter')?.value || null;
        const msg = filter 
            ? `Bạn có chắc muốn xóa vĩnh viễn TẤT CẢ ${getTableLabel(filter)} trong thùng rác?`
            : 'Bạn có chắc muốn xóa vĩnh viễn TẤT CẢ dữ liệu trong thùng rác?';
        
        if (!confirm('⚠️ CẢNH BÁO: ' + msg + '\n\nDữ liệu sẽ KHÔNG THỂ khôi phục!')) return;
        
        try {
            const result = await adminService.emptyTrash(filter);
            if (result.success) {
                showToast('Đã dọn sạch thùng rác!', 'success');
                renderTrash(filter);
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

// Logout
function initLogout() {
    const logoutBtns = document.querySelectorAll('#logout-btn, #sidebar-logout');
    
    logoutBtns.forEach(btn => {
        btn?.addEventListener('click', async () => {
            try {
                await fetch(getApiBasePath() + '/backend/php/auth.php?action=logout', {
                    credentials: 'include'
                });
            } catch (error) {
                // Ignore error
            }
            window.location.replace(getUrl('/DangNhap'));
        });
    });
}

// Click outside modal to close
function initModal() {
    const container = document.getElementById('modal-container');
    container?.addEventListener('click', (e) => {
        if (e.target === container) {
            hideModal();
        }
    });
}

// ==================== REVIEWS MANAGEMENT ====================
async function renderReviews(filter = '') {
    const tbody = document.getElementById('reviews-tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8"><div class="spinner"></div></td></tr>';
    
    const result = await adminService.getReviews(filter);
    
    if (!result.success || !result.data?.length) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-gray-500">Không có đánh giá nào</td></tr>';
        return;
    }
    
    tbody.innerHTML = result.data.map(review => `
        <tr>
            <td>${review.id}</td>
            <td>${escapeHtml(review.fullname || review.user_name)}</td>
            <td>
                ${review.user_avatar 
                    ? `<img src="${review.user_avatar}" alt="Avatar" class="w-10 h-10 rounded-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"><div class="w-10 h-10 rounded-full bg-blue-500 items-center justify-center text-white font-bold" style="display:none">${(review.fullname || review.user_name || 'U').charAt(0).toUpperCase()}</div>`
                    : `<div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">${(review.fullname || review.user_name || 'U').charAt(0).toUpperCase()}</div>`
                }
            </td>
            <td>
                <div class="flex items-center gap-1">
                    ${renderStars(review.rating)}
                </div>
            </td>
            <td class="max-w-xs truncate" title="${escapeHtml(review.comment)}">${escapeHtml(review.comment?.substring(0, 50) + (review.comment?.length > 50 ? '...' : ''))}</td>
            <td>
                ${review.image_url 
                    ? `<img src="${review.image_url}" alt="Review" class="w-16 h-12 object-cover rounded cursor-pointer" onclick="window.open('${review.image_url}', '_blank')" onerror="this.style.display='none'">`
                    : '-'
                }
            </td>
            <td>
                <span class="status-badge ${review.is_approved ? 'active' : 'pending'}">
                    ${review.is_approved ? 'Đã duyệt' : 'Chờ duyệt'}
                </span>
            </td>
            <td>${formatDate(review.created_at)}</td>
            <td>
                <div class="flex gap-2">
                    ${review.is_approved 
                        ? `<button onclick="handleReviewApprove(${review.id}, false)" class="text-orange-600 hover:text-orange-800" title="Ẩn">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                           </button>`
                        : `<button onclick="handleReviewApprove(${review.id}, true)" class="text-green-600 hover:text-green-800" title="Duyệt">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                           </button>`
                    }
                    <button onclick="handleReviewDelete(${review.id})" class="text-red-600 hover:text-red-800" title="Xóa">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Render stars for rating
function renderStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
        } else {
            stars += '<svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
        }
    }
    return stars;
}

// Handle review approve/hide
window.handleReviewApprove = async function(reviewId, isApproved) {
    const result = await adminService.approveReview(reviewId, isApproved);
    if (result.success) {
        showToast(result.message, 'success');
        renderReviews(document.getElementById('reviews-filter')?.value || '');
    } else {
        showToast(result.message || 'Có lỗi xảy ra', 'error');
    }
};

// Handle review delete
window.handleReviewDelete = async function(reviewId) {
    if (!confirm('Bạn có chắc muốn xóa đánh giá này? Thao tác này không thể hoàn tác.')) return;
    
    const result = await adminService.deleteReview(reviewId);
    if (result.success) {
        showToast(result.message, 'success');
        renderReviews(document.getElementById('reviews-filter')?.value || '');
    } else {
        showToast(result.message || 'Có lỗi xảy ra', 'error');
    }
};

// ==================== SETTINGS (AVATAR & PASSWORD) ====================
async function initSettings() {
    // Kiểm tra đã xác thực admin chưa
    if (!isAdminVerified) {
        console.log('Admin not verified, skipping initSettings');
        return;
    }
    
    // Load admin profile
    const result = await adminService.getProfile();
    if (result.success && result.data) {
        const admin = result.data;
        
        // Update sidebar info
        const sidebarName = document.getElementById('sidebar-name');
        const headerUsername = document.getElementById('header-username');
        const headerAvatar = document.getElementById('header-avatar');
        
        if (sidebarName) sidebarName.textContent = admin.fullname || 'Admin';
        if (headerUsername) headerUsername.textContent = admin.fullname || 'Admin';
        
        // Update avatar preview
        const avatarPreview = document.getElementById('admin-avatar-preview');
        const sidebarAvatar = document.querySelector('.sidebar-avatar-container');
        
        if (admin.avatar) {
            if (avatarPreview) {
                avatarPreview.innerHTML = `<img src="${admin.avatar}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            }
            if (sidebarAvatar) {
                sidebarAvatar.innerHTML = `<img src="${admin.avatar}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            }
            if (headerAvatar) {
                headerAvatar.innerHTML = `<img src="${admin.avatar}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            }
        }
    }
    
    // Avatar upload
    const avatarUploadBtn = document.getElementById('admin-avatar-upload-btn');
    const avatarInput = document.getElementById('admin-avatar-input');
    const avatarPreview = document.getElementById('admin-avatar-preview');
    
    avatarUploadBtn?.addEventListener('click', () => {
        avatarInput?.click();
    });
    
    avatarInput?.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showToast('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)', 'error');
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showToast('File quá lớn. Tối đa 5MB', 'error');
            return;
        }
        
        // Preview immediately
        const reader = new FileReader();
        reader.onload = (event) => {
            if (avatarPreview) {
                avatarPreview.innerHTML = `<img src="${event.target.result}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            }
        };
        reader.readAsDataURL(file);
        
        // Upload to server
        const result = await adminService.uploadAvatar(file);
        
        if (result.success) {
            showToast('Cập nhật ảnh đại diện thành công', 'success');
            
            // Update sidebar avatar
            const sidebarAvatar = document.querySelector('.sidebar-avatar-container');
            if (sidebarAvatar && result.avatar) {
                sidebarAvatar.innerHTML = `<img src="${result.avatar}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            }
            
            // Update header avatar
            const headerAvatar = document.getElementById('header-avatar');
            if (headerAvatar && result.avatar) {
                headerAvatar.innerHTML = `<img src="${result.avatar}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            }
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    });
    
    // Change password
    const changePasswordBtn = document.getElementById('admin-change-password-btn');
    
    changePasswordBtn?.addEventListener('click', async () => {
        const currentPassword = document.getElementById('admin-current-password')?.value;
        const newPassword = document.getElementById('admin-new-password')?.value;
        const confirmPassword = document.getElementById('admin-confirm-password')?.value;
        
        if (!currentPassword || !newPassword || !confirmPassword) {
            showToast('Vui lòng nhập đầy đủ thông tin', 'error');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            showToast('Mật khẩu mới không khớp', 'error');
            return;
        }
        
        if (newPassword.length < 6) {
            showToast('Mật khẩu mới phải có ít nhất 6 ký tự', 'error');
            return;
        }
        
        const result = await adminService.changePassword(currentPassword, newPassword);
        
        if (result.success) {
            showToast('Đổi mật khẩu thành công', 'success');
            // Clear fields
            document.getElementById('admin-current-password').value = '';
            document.getElementById('admin-new-password').value = '';
            document.getElementById('admin-confirm-password').value = '';
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    });
    
    // Load site settings
    await loadSiteSettings();
    
    // Save settings button
    const saveSettingsBtn = document.getElementById('save-settings-btn');
    saveSettingsBtn?.addEventListener('click', saveSiteSettings);
}

// Load site settings from API
async function loadSiteSettings() {
    try {
        const result = await adminService.getSiteSettings();
        if (!result.success) return;
        
        // Use raw array data
        const settings = result.raw || [];
        
        // Fill form fields (supports both input and textarea)
        settings.forEach(setting => {
            const input = document.querySelector(`.setting-input[data-key="${setting.setting_key}"]`);
            if (input) {
                if (input.tagName === 'TEXTAREA') {
                    input.value = setting.setting_value || '';
                } else {
                    input.value = setting.setting_value || '';
                }
            }
        });
    } catch (error) {
        console.error('Error loading settings:', error);
    }
}

// Save site settings
async function saveSiteSettings() {
    const saveBtn = document.getElementById('save-settings-btn');
    const originalText = saveBtn?.innerHTML;
    
    try {
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Đang lưu...';
        }
        
        // Collect all settings
        const items = [];
        document.querySelectorAll('.setting-input').forEach(input => {
            const key = input.dataset.key;
            const value = input.value.trim();
            if (key) {
                items.push({
                    setting_key: key,
                    setting_value: value
                });
            }
        });
        
        if (items.length === 0) {
            showToast('Không có dữ liệu để lưu', 'warning');
            return;
        }
        
        const result = await adminService.bulkUpdateSiteSettings(items);
        
        if (result.success) {
            showToast('Lưu cài đặt thành công!', 'success');
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    } catch (error) {
        showToast('Lỗi kết nối', 'error');
    } finally {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    }
}

// ==================== CONTENT MANAGEMENT ====================
async function initContentManagement() {
    // Kiểm tra đã xác thực admin chưa
    if (!isAdminVerified) {
        console.log('Admin not verified, skipping initContentManagement');
        return;
    }
    
    // Tab switching
    const tabBtns = document.querySelectorAll('.content-tab-btn');
    const pageEditors = document.querySelectorAll('.content-page-editor');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const page = btn.dataset.page;
            
            // Update active tab
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Show corresponding editor
            pageEditors.forEach(editor => {
                if (editor.dataset.page === page) {
                    editor.classList.remove('hidden');
                } else {
                    editor.classList.add('hidden');
                }
            });
        });
    });
    
    // Load content from API
    await loadSiteContent();
    
    // Initialize content image upload buttons
    initContentImageUpload();
    
    // Save content button
    const saveBtn = document.getElementById('save-content-btn');
    saveBtn?.addEventListener('click', saveSiteContent);
}

// Initialize content image upload functionality
function initContentImageUpload() {
    const uploadBtns = document.querySelectorAll('.upload-content-image-btn');
    
    uploadBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const container = btn.closest('.content-image-upload');
            const fileInput = container.querySelector('.content-image-input');
            fileInput?.click();
        });
    });
    
    // Handle file selection
    const fileInputs = document.querySelectorAll('.content-image-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            
            const page = input.dataset.page;
            const section = input.dataset.section;
            const key = input.dataset.key;
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showToast('Vui lòng chọn file hình ảnh', 'error');
                return;
            }
            
            // Validate file size (max 10MB)
            if (file.size > 10 * 1024 * 1024) {
                showToast('File quá lớn. Tối đa 10MB', 'error');
                return;
            }
            
            // Preview immediately
            const previewImg = document.getElementById(`preview-${page}-${section}-${key}`);
            const placeholder = document.getElementById(`placeholder-${page}-${section}-${key}`);
            
            const reader = new FileReader();
            reader.onload = (event) => {
                if (previewImg) {
                    previewImg.src = event.target.result;
                    previewImg.classList.remove('hidden');
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            };
            reader.readAsDataURL(file);
            
            // Upload to server
            const btn = input.closest('.content-image-upload').querySelector('.upload-content-image-btn');
            const originalText = btn?.innerHTML;
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Đang upload...';
            }
            
            try {
                const result = await adminService.uploadContentImage(file, page, section, key);
                
                if (result.success) {
                    showToast('Upload hình ảnh thành công!', 'success');
                    // Update preview with server URL
                    if (previewImg && result.image_url) {
                        previewImg.src = BASE_PATH + result.image_url;
                    }
                } else {
                    showToast(result.message || 'Có lỗi xảy ra khi upload', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            }
        });
    });
    
    // Handle delete image buttons
    const deleteBtns = document.querySelectorAll('.delete-content-image-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            const page = btn.dataset.page;
            const section = btn.dataset.section;
            const key = btn.dataset.key;
            
            if (!confirm('Bạn có chắc muốn xóa hình ảnh này?')) return;
            
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Đang xóa...';
            
            try {
                const result = await adminService.deleteContentImage(page, section, key);
                
                if (result.success) {
                    showToast('Xóa hình ảnh thành công!', 'success');
                    
                    // Reset preview
                    const previewImg = document.getElementById(`preview-${page}-${section}-${key}`);
                    const placeholder = document.getElementById(`placeholder-${page}-${section}-${key}`);
                    
                    if (previewImg) {
                        previewImg.src = '';
                        previewImg.classList.add('hidden');
                    }
                    if (placeholder) {
                        placeholder.classList.remove('hidden');
                    }
                    
                    // Clear file input
                    const container = btn.closest('.content-image-upload');
                    const fileInput = container?.querySelector('.content-image-input');
                    if (fileInput) fileInput.value = '';
                } else {
                    showToast(result.message || 'Có lỗi xảy ra khi xóa', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });
}

async function loadSiteContent() {
    try {
        const result = await adminService.getSiteContent();
        if (!result.success) return;
        
        const data = result.data || {};
        
        // Fill form fields
        document.querySelectorAll('.content-input').forEach(input => {
            const page = input.dataset.page;
            const section = input.dataset.section;
            const key = input.dataset.key;
            
            if (data[page]?.[section]?.[key]) {
                input.value = data[page][section][key].value || '';
            }
        });
        
        // Load images into previews
        document.querySelectorAll('.content-image-input').forEach(input => {
            const page = input.dataset.page;
            const section = input.dataset.section;
            const key = input.dataset.key;
            
            if (data[page]?.[section]?.[key]?.value) {
                const imageUrl = data[page][section][key].value;
                const previewImg = document.getElementById(`preview-${page}-${section}-${key}`);
                const placeholder = document.getElementById(`placeholder-${page}-${section}-${key}`);
                
                if (previewImg && imageUrl) {
                    previewImg.src = BASE_PATH + imageUrl;
                    previewImg.classList.remove('hidden');
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error loading site content:', error);
    }
}

async function saveSiteContent() {
    const saveBtn = document.getElementById('save-content-btn');
    const originalText = saveBtn?.innerHTML;
    
    // Show loading
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Đang lưu...';
    }
    
    try {
        const items = [];
        
        document.querySelectorAll('.content-input').forEach(input => {
            const page = input.dataset.page;
            const section = input.dataset.section;
            const key = input.dataset.key;
            const value = input.value.trim();
            
            if (value) {
                items.push({
                    page,
                    section,
                    content_key: key,
                    content_value: value,
                    content_type: 'text'
                });
            }
        });
        
        if (items.length === 0) {
            showToast('Không có dữ liệu để lưu', 'warning');
            return;
        }
        
        const result = await adminService.bulkUpdateSiteContent(items);
        
        if (result.success) {
            showToast('Lưu nội dung thành công!', 'success');
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    } catch (error) {
        showToast('Lỗi kết nối', 'error');
    } finally {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    }
}

// Initialize page
async function initPage() {
    const dashboard = await checkAdmin();
    if (!dashboard) return;

    // Chỉ khởi tạo sau khi đã xác thực admin
    initSidebar();
    initFilters();
    initAddButtons();
    initLogout();
    initModal();
    initSearchBars(); // Initialize search functionality
    
    // Load dashboard (sử dụng cached data)
    renderDashboard();
    
    // Load settings sau cùng
    await initSettings();
    await initContentManagement();
    
    // Init new sections
    await initTeacherReviewsSection();
    await initCourseFeesSection();
}

// ==================== SEARCH FUNCTIONALITY ====================
function initSearchBars() {
    // Setup search for each section
    setupSearch('users', searchUsers, renderUsers);
    setupSearch('enrollments', searchEnrollments, () => renderEnrollments());
    setupSearch('teachers', searchTeachers, renderTeachers);
    setupSearch('scores', searchScores, renderScores);
    setupSearch('feedback', searchFeedback, renderFeedback);
    setupSearch('achievements', searchAchievements, renderAchievements);
    setupSearch('reviews', searchReviews, () => renderReviews());
    setupSearch('schedules', searchSchedules, () => renderSchedule());
}

// Generic search setup function
function setupSearch(section, searchFn, renderAllFn) {
    const searchInput = document.getElementById(`search-${section}`);
    const clearBtn = document.getElementById(`clear-search-${section}`);
    
    if (!searchInput) return;
    
    let debounceTimer;
    
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        
        // Show/hide clear button
        if (clearBtn) {
            clearBtn.classList.toggle('hidden', !query);
        }
        
        // Debounce search
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            if (query.length >= 1) {
                await searchFn(query);
            } else {
                renderAllFn();
            }
        }, 300);
    });
    
    // Clear button handler
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            clearBtn.classList.add('hidden');
            renderAllFn();
        });
    }
}

// Search Users
async function searchUsers(query) {
    const tbody = document.getElementById('users-tbody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner"></div></td></tr>';
    
    try {
        const result = await adminService.searchUsers(query);
        if (!result.success || !result.users?.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-gray-500 py-4">Không tìm thấy kết quả cho "${escapeHtml(query)}"</td></tr>`;
            return;
        }
        
        renderUsersTable(result.users);
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-red-500 py-4">Lỗi tìm kiếm</td></tr>';
    }
}

// Search Enrollments
async function searchEnrollments(query) {
    const tbody = document.getElementById('enrollments-tbody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner"></div></td></tr>';
    
    const status = document.getElementById('enrollment-status-filter')?.value || '';
    
    try {
        const result = await adminService.searchEnrollments(query, status);
        if (!result.success || !result.enrollments?.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-gray-500 py-4">Không tìm thấy kết quả cho "${escapeHtml(query)}"</td></tr>`;
            return;
        }
        
        renderEnrollmentsTable(result.enrollments);
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-red-500 py-4">Lỗi tìm kiếm</td></tr>';
    }
}

// Search Teachers
async function searchTeachers(query) {
    const tbody = document.getElementById('teachers-tbody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner"></div></td></tr>';
    
    try {
        const result = await adminService.searchTeachers(query);
        if (!result.success || !result.teachers?.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-gray-500 py-4">Không tìm thấy kết quả cho "${escapeHtml(query)}"</td></tr>`;
            return;
        }
        
        renderTeachersTable(result.teachers);
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-red-500 py-4">Lỗi tìm kiếm</td></tr>';
    }
}

// Search Scores
async function searchScores(query) {
    const tbody = document.getElementById('scores-tbody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner"></div></td></tr>';
    
    try {
        const result = await adminService.searchScores(query);
        if (!result.success || !result.scores?.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-gray-500 py-4">Không tìm thấy kết quả cho "${escapeHtml(query)}"</td></tr>`;
            return;
        }
        
        renderScoresTable(result.scores);
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-red-500 py-4">Lỗi tìm kiếm</td></tr>';
    }
}

// Search Feedback
async function searchFeedback(query) {
    const container = document.getElementById('admin-feedback-container');
    container.innerHTML = '<div class="spinner text-center py-4"></div>';
    
    try {
        const result = await adminService.searchFeedback(query);
        if (!result.success || !result.feedback?.length) {
            container.innerHTML = `<p class="text-gray-500 text-center py-8">Không tìm thấy kết quả cho "${escapeHtml(query)}"</p>`;
            return;
        }
        
        renderFeedbackList(result.feedback);
    } catch (error) {
        container.innerHTML = '<p class="text-red-500 text-center py-8">Lỗi tìm kiếm</p>';
    }
}

// Search Achievements
async function searchAchievements(query) {
    const grid = document.getElementById('achievements-grid');
    grid.innerHTML = '<div class="col-span-full text-center py-8"><div class="spinner"></div></div>';
    
    try {
        const result = await adminService.searchAchievements(query);
        if (!result.success || !result.achievements?.length) {
            grid.innerHTML = `<div class="col-span-full text-center text-gray-500 py-8">Không tìm thấy kết quả cho "${escapeHtml(query)}"</div>`;
            return;
        }
        
        renderAchievementsGrid(result.achievements);
    } catch (error) {
        grid.innerHTML = '<div class="col-span-full text-red-500 py-8">Lỗi tìm kiếm</div>';
    }
}

// Search Reviews
async function searchReviews(query) {
    const tbody = document.getElementById('reviews-tbody');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner"></div></td></tr>';
    
    const filter = document.getElementById('reviews-filter')?.value || '';
    
    try {
        const result = await adminService.searchReviews(query, filter);
        if (!result.success || !result.reviews?.length) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center text-gray-500 py-4">Không tìm thấy kết quả cho "${escapeHtml(query)}"</td></tr>`;
            return;
        }
        
        renderReviewsTable(result.reviews);
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-red-500 py-4">Lỗi tìm kiếm</td></tr>';
    }
}

// Search Schedules
async function searchSchedules(query) {
    const tbody = document.getElementById('schedule-list-tbody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner"></div></td></tr>';
    
    try {
        const result = await adminService.searchSchedules(query);
        if (!result.success || !result.schedules?.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-gray-500 py-4">Không tìm thấy kết quả cho "${escapeHtml(query)}"</td></tr>`;
            return;
        }
        
        renderScheduleListTable(result.schedules);
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-red-500 py-4">Lỗi tìm kiếm</td></tr>';
    }
}

// Helper render functions for search results (reuse existing table structure)
function renderUsersTable(users) {
    const tbody = document.getElementById('users-tbody');
    tbody.innerHTML = users.map(u => `
        <tr>
            <td>${u.id}</td>
            <td>${escapeHtml(u.fullname)}</td>
            <td>${escapeHtml(u.email)}</td>
            <td>${escapeHtml(u.phone) || '-'}</td>
            <td>
                <span class="status-badge ${u.is_active ? 'active' : 'cancelled'}">
                    ${u.is_active ? 'Hoạt động' : 'Bị khóa'}
                </span>
            </td>
            <td>${formatDate(u.created_at)}</td>
            <td>
                <button class="admin-action-btn info view-user-enrollments-btn" data-id="${u.id}" data-name="${escapeHtml(u.fullname)}" title="Xem đăng ký">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </button>
                ${u.role !== 'admin' ? `
                    <button class="admin-action-btn secondary edit-user-btn" 
                            data-user='${JSON.stringify(u).replace(/'/g, "&#39;")}'>Sửa</button>
                ` : ''}
                <button class="admin-action-btn ${u.is_active ? 'warning' : 'primary'} toggle-user-btn" 
                        data-id="${u.id}" data-active="${u.is_active ? '0' : '1'}">
                    ${u.is_active ? 'Khóa' : 'Mở khóa'}
                </button>
                ${u.role !== 'admin' ? `
                    <button class="admin-action-btn danger delete-user-btn" data-id="${u.id}">Xóa</button>
                ` : ''}
            </td>
        </tr>
    `).join('');
    
    // Rebind event handlers
    bindUserTableEvents(users);
}

function renderEnrollmentsTable(enrollments) {
    const tbody = document.getElementById('enrollments-tbody');
    tbody.innerHTML = enrollments.map(e => `
        <tr>
            <td>
                <div class="font-medium">${escapeHtml(e.fullname)}</div>
                <div class="text-xs text-gray-500">${escapeHtml(e.email || '')}</div>
            </td>
            <td>
                <div>${escapeHtml(e.course_name)}</div>
                ${e.class_name ? `<div class="text-xs text-blue-600">📚 ${escapeHtml(e.class_name)}</div>` : '<div class="text-xs text-gray-400">Chưa phân lớp</div>'}
            </td>
            <td>${e.academic_year || '-'}</td>
            <td>${e.semester || '-'}</td>
            <td>
                <div class="flex items-center gap-2">
                    <div class="progress-bar-container flex-1" style="height: 8px; background: #e5e7eb; border-radius: 4px;">
                        <div class="progress-bar-fill" style="width: ${e.progress || 0}%; height: 100%; background: #10b981; border-radius: 4px;"></div>
                    </div>
                    <span class="text-sm">${e.progress || 0}%</span>
                </div>
            </td>
            <td>${getStatusBadge(e.status)}</td>
            <td>
                <div class="flex gap-1 flex-wrap">
                    ${!e.class_id ? `<button class="admin-action-btn info assign-class-btn" data-id="${e.id}" data-courseid="${e.course_id}" data-user="${escapeHtml(e.fullname)}" title="Phân lớp">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </button>` : ''}
                    <button class="admin-action-btn secondary edit-enrollment-btn" data-id="${e.id}" data-enrollment='${JSON.stringify(e)}'>Sửa</button>
                    <button class="admin-action-btn danger delete-enrollment-btn" data-id="${e.id}">Xóa</button>
                </div>
            </td>
        </tr>
    `).join('');
    
    bindEnrollmentTableEvents(enrollments);
}

function renderTeachersTable(teachers) {
    const tbody = document.getElementById('teachers-tbody');
    const getTeacherImageUrl = (url) => {
        if (!url) return BASE_PATH + '/frontend/assets/images/default-avatar.svg';
        if (url.startsWith('http')) return url;
        if (url.startsWith('/') && !url.toLowerCase().startsWith('/hai_au_english')) {
            return BASE_PATH + url;
        }
        return url;
    };
    
    tbody.innerHTML = teachers.map(t => `
        <tr>
            <td>
                <div class="flex items-center gap-3">
                    <img src="${getTeacherImageUrl(t.image_url)}" alt="${escapeHtml(t.name)}" class="w-10 h-10 rounded-full object-cover" onerror="this.src='${BASE_PATH}/frontend/assets/images/default-avatar.svg'">
                    <span>${escapeHtml(t.name)}</span>
                </div>
            </td>
            <td>${escapeHtml(t.title) || '-'}</td>
            <td>${t.experience_years ? t.experience_years + ' năm' : '-'}</td>
            <td>${t.ielts_score || '-'}</td>
            <td>
                <span class="status-badge ${t.is_active ? 'active' : 'cancelled'}">
                    ${t.is_active ? 'Hoạt động' : 'Ẩn'}
                </span>
            </td>
            <td>
                <div class="flex gap-1 flex-wrap">
                    <button class="admin-action-btn info view-teacher-classes-btn" data-id="${t.id}" data-name="${escapeHtml(t.name)}" title="Xem các lớp đang dạy">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </button>
                    <button class="admin-action-btn secondary edit-teacher-btn" data-id="${t.id}">Sửa</button>
                    <button class="admin-action-btn danger delete-teacher-btn" data-id="${t.id}">Xóa</button>
                </div>
            </td>
        </tr>
    `).join('');
    
    bindTeacherTableEvents(teachers);
}

function renderScoresTable(scores) {
    const tbody = document.getElementById('scores-tbody');
    tbody.innerHTML = scores.map(s => `
        <tr>
            <td>${s.fullname || 'N/A'}</td>
            <td>${formatDate(s.test_date)}</td>
            <td class="text-center">${s.listening}</td>
            <td class="text-center">${s.reading}</td>
            <td class="text-center">${s.writing}</td>
            <td class="text-center">${s.speaking}</td>
            <td class="text-center font-bold text-blue-600">${s.overall}</td>
            <td>
                <button class="admin-action-btn secondary edit-score-btn" data-id="${s.id}">Sửa</button>
                <button class="admin-action-btn danger delete-score-btn" data-id="${s.id}">Xóa</button>
            </td>
        </tr>
    `).join('');
    
    bindScoreTableEvents(scores);
}

function renderFeedbackList(feedback) {
    const container = document.getElementById('admin-feedback-container');
    container.innerHTML = `
        <div class="space-y-4">
            ${feedback.map(f => `
                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-medium">${f.student_name || 'Học viên'}</h4>
                            <p class="text-sm text-gray-500">${f.course_name || ''} ${f.teacher_name ? '- GV: ' + f.teacher_name : ''}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            ${f.rating ? `<span class="text-yellow-500">★ ${f.rating}</span>` : ''}
                            <button class="admin-action-btn danger delete-feedback-btn" data-id="${f.id}">Xóa</button>
                        </div>
                    </div>
                    <p class="mt-2 text-gray-700">${f.content}</p>
                    <p class="text-xs text-gray-400 mt-2">${formatDate(f.feedback_date)}</p>
                </div>
            `).join('')}
        </div>
    `;
    
    bindFeedbackEvents();
}

function renderAchievementsGrid(achievements) {
    const grid = document.getElementById('achievements-grid');
    updateAchievementStats(achievements);
    
    grid.innerHTML = achievements.map(a => `
        <div class="achievement-admin-card bg-white rounded-lg shadow-md overflow-hidden border hover:shadow-lg transition-shadow">
            <div class="relative">
                <img src="${fixAchievementImageUrl(a.image_url)}" 
                     alt="${escapeHtml(a.student_name)}" 
                     class="w-full h-32 object-cover"
                     onerror="this.src='${BASE_PATH}/frontend/assets/images/placeholder.jpg'">
                ${a.is_featured ? '<span class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 text-xs px-2 py-1 rounded-full">⭐ Nổi bật</span>' : ''}
            </div>
            <div class="p-3">
                <h4 class="font-medium text-sm truncate">${escapeHtml(a.student_name)}</h4>
                <p class="text-xs text-gray-500 truncate">${escapeHtml(a.achievement_title)}</p>
                ${a.score ? `<p class="text-lg font-bold text-blue-600 mt-1">IELTS ${a.score}</p>` : ''}
                <div class="flex gap-1 mt-2">
                    <button class="admin-action-btn secondary text-xs flex-1 edit-achievement-btn" data-id="${a.id}">Sửa</button>
                    <button class="admin-action-btn danger text-xs flex-1 delete-achievement-btn" data-id="${a.id}">Xóa</button>
                </div>
            </div>
        </div>
    `).join('');
    
    bindAchievementEvents(achievements);
}

function renderReviewsTable(reviews) {
    const tbody = document.getElementById('reviews-tbody');
    tbody.innerHTML = reviews.map(r => `
        <tr>
            <td>${r.id}</td>
            <td>${escapeHtml(r.user_name)}</td>
            <td>
                ${r.user_avatar 
                    ? `<img src="${BASE_PATH}${r.user_avatar}" alt="Avatar" class="w-8 h-8 rounded-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"><div class="w-8 h-8 rounded-full bg-blue-500 items-center justify-center text-white font-bold text-sm" style="display:none">${(r.user_name || 'U').charAt(0).toUpperCase()}</div>` 
                    : `<div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">${(r.user_name || 'U').charAt(0).toUpperCase()}</div>`
                }
            </td>
            <td class="text-yellow-500">${'★'.repeat(r.rating)}</td>
            <td class="max-w-xs truncate">${escapeHtml(r.comment)}</td>
            <td>${r.image_url ? `<img src="${BASE_PATH}${r.image_url}" alt="Review" class="w-12 h-12 object-cover rounded" onerror="this.style.display='none'">` : '-'}</td>
            <td>
                <span class="status-badge ${r.is_approved ? 'active' : 'pending'}">
                    ${r.is_approved ? 'Đã duyệt' : 'Chờ duyệt'}
                </span>
            </td>
            <td>${formatDate(r.created_at)}</td>
            <td>
                <button class="admin-action-btn ${r.is_approved ? 'warning' : 'primary'} toggle-review-btn" 
                        data-id="${r.id}" data-approved="${r.is_approved ? '0' : '1'}">
                    ${r.is_approved ? 'Ẩn' : 'Duyệt'}
                </button>
                <button class="admin-action-btn danger delete-review-btn" data-id="${r.id}">Xóa</button>
            </td>
        </tr>
    `).join('');
    
    bindReviewTableEvents();
}

// Bind event handlers for tables (to be called after rendering)
function bindUserTableEvents(users) {
    const tbody = document.getElementById('users-tbody');
    
    tbody.querySelectorAll('.edit-user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const user = JSON.parse(btn.dataset.user);
            showUserModal(user);
        });
    });
    
    tbody.querySelectorAll('.toggle-user-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const userId = btn.dataset.id;
            const isActive = btn.dataset.active === '1' ? 1 : 0;
            const result = await adminService.updateUserStatus(userId, isActive);
            if (result.success) {
                showToast('Cập nhật thành công!', 'success');
                renderUsers();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
    
    tbody.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa học viên này?')) return;
            const result = await adminService.deleteUser(btn.dataset.id);
            if (result.success) {
                showToast('Đã chuyển vào thùng rác!', 'success');
                renderUsers();
                updateTrashCount();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
    
    // View user enrollments
    tbody.querySelectorAll('.view-user-enrollments-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const userId = btn.dataset.id;
            const userName = btn.dataset.name;
            await showUserEnrollmentsModal(userId, userName);
        });
    });
}

// Show user enrollments modal
async function showUserEnrollmentsModal(userId, userName) {
    const result = await adminService.getEnrollments();
    const enrollments = (result.enrollments || []).filter(e => e.user_id == userId);
    
    showModal(`
        <h3 class="text-xl font-bold mb-4">Khóa học đăng ký của ${escapeHtml(userName)}</h3>
        ${enrollments.length > 0 ? `
            <div class="overflow-x-auto">
                <table class="profile-table text-sm">
                    <thead>
                        <tr>
                            <th>Khóa học</th>
                            <th>Lớp</th>
                            <th>Năm học</th>
                            <th>Tiến độ</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${enrollments.map(e => `
                            <tr>
                                <td>${escapeHtml(e.course_name)}</td>
                                <td>${e.class_name ? escapeHtml(e.class_name) : '<span class="text-gray-400">Chưa phân lớp</span>'}</td>
                                <td>${e.academic_year || '-'}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div style="width: 60px; height: 6px; background: #e5e7eb; border-radius: 3px;">
                                            <div style="width: ${e.progress || 0}%; height: 100%; background: #10b981; border-radius: 3px;"></div>
                                        </div>
                                        <span class="text-xs">${e.progress || 0}%</span>
                                    </div>
                                </td>
                                <td>${getStatusBadge(e.status)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<p class="text-center text-gray-500 py-8">Học viên chưa đăng ký khóa học nào</p>'}
        <div class="flex justify-between mt-4 pt-4 border-t">
            <button type="button" class="admin-action-btn primary" onclick="showNewEnrollmentModalForUser('${userId}', '${escapeHtml(userName)}')">+ Đăng ký khóa học</button>
            <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Đóng</button>
        </div>
    `);
}

// Quick enroll for a specific user
window.showNewEnrollmentModalForUser = async function(userId, userName) {
    hideModal();
    
    // Fetch courses and classes
    const [coursesResult, classesResult] = await Promise.all([
        adminService.getCourses(),
        adminService.getClasses()
    ]);
    
    const courses = coursesResult.courses || [];
    const classes = classesResult.classes || [];
    
    showModal(`
        <h3 class="text-xl font-bold mb-4">Đăng ký khóa học cho ${escapeHtml(userName)}</h3>
        <form id="quick-enrollment-form">
            <input type="hidden" name="user_id" value="${userId}">
            
            <div class="mb-4">
                <label class="input-label">Khóa học <span class="text-red-500">*</span></label>
                <select name="course_id" id="quick-enroll-course" required class="admin-input">
                    <option value="">-- Chọn khóa học --</option>
                    ${courses.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('')}
                </select>
            </div>
            
            <div class="mb-4">
                <label class="input-label">Lớp học (không bắt buộc)</label>
                <select name="class_id" id="quick-enroll-class" class="admin-input">
                    <option value="">-- Chọn sau khi chọn khóa học --</option>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="input-label">Năm học</label>
                    <input type="text" name="academic_year" value="${new Date().getFullYear()}-${new Date().getFullYear() + 1}" class="admin-input">
                </div>
                <div>
                    <label class="input-label">Học kỳ</label>
                    <select name="semester" class="admin-input">
                        <option value="1">Học kỳ 1</option>
                        <option value="2">Học kỳ 2</option>
                        <option value="Hè">Hè</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t">
                <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                <button type="submit" class="admin-action-btn primary">Đăng ký</button>
            </div>
        </form>
    `);
    
    // Course change handler - populate classes
    const courseSelect = document.getElementById('quick-enroll-course');
    const classSelect = document.getElementById('quick-enroll-class');
    
    courseSelect.addEventListener('change', () => {
        const selectedCourseId = courseSelect.value;
        const availableClasses = classes.filter(c => 
            c.course_id == selectedCourseId && 
            c.status !== 'completed' &&
            (c.student_count || 0) < (c.max_students || 20)
        );
        
        classSelect.innerHTML = `
            <option value="">-- Không chọn lớp --</option>
            ${availableClasses.map(c => `
                <option value="${c.id}">${escapeHtml(c.name)} - ${c.schedule || 'Chưa có lịch'} (${c.student_count || 0}/${c.max_students} học viên)</option>
            `).join('')}
        `;
    });
    
    // Form submit
    document.getElementById('quick-enrollment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        const data = {
            user_id: formData.get('user_id'),
            course_id: formData.get('course_id'),
            class_id: formData.get('class_id') || null,
            academic_year: formData.get('academic_year'),
            semester: formData.get('semester'),
            status: 'active',
            progress: 0
        };
        
        const result = await adminService.createEnrollment(data);
        if (result.success) {
            showToast('Đăng ký thành công!', 'success');
            hideModal();
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    });
}

function bindEnrollmentTableEvents(enrollments) {
    const tbody = document.getElementById('enrollments-tbody');
    
    tbody.querySelectorAll('.edit-enrollment-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const enrollment = JSON.parse(btn.dataset.enrollment);
            showEnrollmentModal(enrollment);
        });
    });
    
    tbody.querySelectorAll('.delete-enrollment-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa đăng ký này?')) return;
            const result = await adminService.deleteEnrollment(btn.dataset.id);
            if (result.success) {
                showToast('Đã chuyển vào thùng rác!', 'success');
                renderEnrollments();
                updateTrashCount();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
    
    // Assign class to enrollment
    tbody.querySelectorAll('.assign-class-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const enrollmentId = btn.dataset.id;
            const courseId = btn.dataset.courseid;
            const userName = btn.dataset.user;
            await showAssignClassModal(enrollmentId, courseId, userName);
        });
    });
}

// Show modal to assign class to an enrollment
async function showAssignClassModal(enrollmentId, courseId, userName) {
    const result = await adminService.getClasses();
    const allClasses = result.classes || [];
    // Filter classes by course and status
    const availableClasses = allClasses.filter(c => 
        c.course_id == courseId && 
        c.status !== 'completed' &&
        (c.student_count || 0) < (c.max_students || 20)
    );
    
    showModal(`
        <h3 class="text-xl font-bold mb-4">Phân lớp cho ${escapeHtml(userName)}</h3>
        <form id="assign-class-form">
            <input type="hidden" name="enrollment_id" value="${enrollmentId}">
            ${availableClasses.length > 0 ? `
                <div class="mb-4">
                    <label class="input-label">Chọn lớp</label>
                    <select name="class_id" required class="admin-input">
                        <option value="">-- Chọn lớp --</option>
                        ${availableClasses.map(c => `
                            <option value="${c.id}">
                                ${escapeHtml(c.name)} - ${c.schedule || 'Chưa có lịch'} (${c.student_count || 0}/${c.max_students || 20} học viên)
                            </option>
                        `).join('')}
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t">
                    <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Hủy</button>
                    <button type="submit" class="admin-action-btn primary">Xác nhận</button>
                </div>
            ` : `
                <p class="text-center text-gray-500 py-8">Không có lớp nào khả dụng cho khóa học này</p>
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t">
                    <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Đóng</button>
                    <button type="button" class="admin-action-btn primary" onclick="document.querySelector('[data-section=\\'classes\\']').click(); hideModal();">Tạo lớp mới</button>
                </div>
            `}
        </form>
    `);
    
    const form = document.getElementById('assign-class-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const classId = formData.get('class_id');
            
            if (!classId) {
                showToast('Vui lòng chọn lớp', 'error');
                return;
            }
            
            const result = await adminService.updateEnrollment({
                id: enrollmentId,
                class_id: classId
            });
            
            if (result.success) {
                showToast('Đã phân lớp thành công!', 'success');
                hideModal();
                renderEnrollments();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    }
}

function bindTeacherTableEvents(teachers) {
    const tbody = document.getElementById('teachers-tbody');
    
    tbody.querySelectorAll('.edit-teacher-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const teacher = teachers.find(t => t.id == btn.dataset.id);
            if (teacher) showTeacherModal(teacher);
        });
    });
    
    tbody.querySelectorAll('.delete-teacher-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa giảng viên này?')) return;
            const result = await adminService.deleteTeacher(btn.dataset.id);
            if (result.success) {
                showToast('Đã chuyển vào thùng rác!', 'success');
                renderTeachers();
                updateTrashCount();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
    
    // View teacher's classes
    tbody.querySelectorAll('.view-teacher-classes-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const teacherId = btn.dataset.id;
            const teacherName = btn.dataset.name;
            await showTeacherClassesModal(teacherId, teacherName);
        });
    });
}

// Show modal for teacher's classes
async function showTeacherClassesModal(teacherId, teacherName) {
    const result = await adminService.getClasses();
    const classes = (result.classes || []).filter(c => c.teacher_id == teacherId);
    
    showModal(`
        <h3 class="text-xl font-bold mb-4">Các lớp của ${escapeHtml(teacherName)}</h3>
        ${classes.length > 0 ? `
            <div class="overflow-x-auto">
                <table class="profile-table text-sm">
                    <thead>
                        <tr>
                            <th>Tên lớp</th>
                            <th>Khóa học</th>
                            <th>Lịch học</th>
                            <th>Sĩ số</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${classes.map(c => `
                            <tr>
                                <td>${escapeHtml(c.name)}</td>
                                <td>${escapeHtml(c.course_name) || '-'}</td>
                                <td>${escapeHtml(c.schedule) || '-'}</td>
                                <td>${c.student_count || 0}/${c.max_students || 20}</td>
                                <td><span class="status-badge ${c.status === 'active' ? 'active' : c.status === 'completed' ? 'completed' : 'pending'}">${c.status === 'active' ? 'Đang học' : c.status === 'completed' ? 'Hoàn thành' : c.status === 'upcoming' ? 'Sắp mở' : c.status}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<p class="text-center text-gray-500 py-8">Giảng viên chưa được phân công lớp nào</p>'}
        <div class="flex justify-end mt-4 pt-4 border-t">
            <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Đóng</button>
        </div>
    `);
}

// Show modal for course's classes
async function showCourseClassesModal(courseId, courseName) {
    const result = await adminService.getClasses();
    const classes = (result.classes || []).filter(c => c.course_id == courseId);
    
    showModal(`
        <h3 class="text-xl font-bold mb-4">Các lớp của khóa "${escapeHtml(courseName)}"</h3>
        ${classes.length > 0 ? `
            <div class="overflow-x-auto">
                <table class="profile-table text-sm">
                    <thead>
                        <tr>
                            <th>Tên lớp</th>
                            <th>Giảng viên</th>
                            <th>Lịch học</th>
                            <th>Sĩ số</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${classes.map(c => `
                            <tr>
                                <td>${escapeHtml(c.name)}</td>
                                <td>${escapeHtml(c.teacher_name) || '-'}</td>
                                <td>${escapeHtml(c.schedule) || '-'}</td>
                                <td>${c.student_count || 0}/${c.max_students || 20}</td>
                                <td><span class="status-badge ${c.status === 'active' ? 'active' : c.status === 'completed' ? 'completed' : 'pending'}">${c.status === 'active' ? 'Đang học' : c.status === 'completed' ? 'Hoàn thành' : c.status === 'upcoming' ? 'Sắp mở' : c.status}</span></td>
                                <td>
                                    <button class="admin-action-btn info text-xs view-class-students-modal-btn" data-id="${c.id}" data-name="${escapeHtml(c.name)}">Học viên</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<p class="text-center text-gray-500 py-8">Khóa học chưa có lớp nào</p>'}
        <div class="flex justify-between mt-4 pt-4 border-t">
            <button type="button" class="admin-action-btn primary" onclick="document.querySelector('[data-section=\\'classes\\']').click(); hideModal();">Quản lý lớp học</button>
            <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Đóng</button>
        </div>
    `);
    
    // Bind view students buttons
    document.querySelectorAll('.view-class-students-modal-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const classId = btn.dataset.id;
            const className = btn.dataset.name;
            await showClassStudentsModalFromCourse(classId, className);
        });
    });
}

// Show students in a class (from course classes modal)
async function showClassStudentsModalFromCourse(classId, className) {
    const result = await adminService.getClassStudents(classId);
    const students = result.students || [];
    
    showModal(`
        <h3 class="text-xl font-bold mb-4">Học viên lớp "${escapeHtml(className)}"</h3>
        ${students.length > 0 ? `
            <div class="overflow-x-auto">
                <table class="profile-table text-sm">
                    <thead>
                        <tr>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Ngày đăng ký</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${students.map(s => `
                            <tr>
                                <td>${escapeHtml(s.full_name || s.name)}</td>
                                <td>${escapeHtml(s.email) || '-'}</td>
                                <td>${escapeHtml(s.phone) || '-'}</td>
                                <td>${s.enrolled_at ? new Date(s.enrolled_at).toLocaleDateString('vi-VN') : '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<p class="text-center text-gray-500 py-8">Lớp chưa có học viên</p>'}
        <div class="flex justify-end mt-4 pt-4 border-t">
            <button type="button" class="admin-action-btn secondary" onclick="hideModal()">Đóng</button>
        </div>
    `);
}

function bindScoreTableEvents(scores) {
    const tbody = document.getElementById('scores-tbody');
    
    tbody.querySelectorAll('.edit-score-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const score = scores.find(s => s.id == btn.dataset.id);
            if (score) showScoreModal(score);
        });
    });
    
    tbody.querySelectorAll('.delete-score-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa điểm này?')) return;
            const result = await adminService.deleteScore(btn.dataset.id);
            if (result.success) {
                showToast('Đã chuyển vào thùng rác!', 'success');
                renderScores();
                updateTrashCount();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
}

function bindFeedbackEvents() {
    const container = document.getElementById('admin-feedback-container');
    container.querySelectorAll('.delete-feedback-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa nhận xét này?')) return;
            const result = await adminService.deleteFeedback(btn.dataset.id);
            if (result.success) {
                showToast('Đã chuyển vào thùng rác!', 'success');
                renderFeedback();
                updateTrashCount();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
}

function bindAchievementEvents(achievements) {
    const grid = document.getElementById('achievements-grid');
    
    grid.querySelectorAll('.edit-achievement-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const achievement = achievements.find(a => a.id == btn.dataset.id);
            if (achievement) showAchievementModal(achievement);
        });
    });
    
    grid.querySelectorAll('.delete-achievement-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa thành tích này?')) return;
            const basePath = getApiBasePath();
            try {
                const response = await fetch(`${basePath}/backend/php/achievements.php?id=${btn.dataset.id}`, {
                    method: 'DELETE',
                    credentials: 'include'
                });
                const result = await response.json();
                if (result.success) {
                    showToast('Đã xóa thành tích!', 'success');
                    renderAchievements();
                } else {
                    showToast(result.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        });
    });
}

function bindReviewTableEvents() {
    const tbody = document.getElementById('reviews-tbody');
    
    tbody.querySelectorAll('.toggle-review-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const reviewId = btn.dataset.id;
            const isApproved = btn.dataset.approved === '1';
            const result = await adminService.approveReview(reviewId, isApproved);
            if (result.success) {
                showToast(isApproved ? 'Đã duyệt đánh giá!' : 'Đã ẩn đánh giá!', 'success');
                renderReviews();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
    
    tbody.querySelectorAll('.delete-review-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa đánh giá này?')) return;
            const result = await adminService.deleteReview(btn.dataset.id);
            if (result.success) {
                showToast('Đã xóa đánh giá!', 'success');
                renderReviews();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
}

function bindScheduleTableEvents(schedules) {
    const tbody = document.getElementById('schedule-list-tbody');
    
    tbody.querySelectorAll('.edit-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const schedule = schedules.find(s => s.id == btn.dataset.id);
            if (schedule) showScheduleModal(schedule);
        });
    });
    
    tbody.querySelectorAll('.delete-schedule-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa lịch học này?')) return;
            const result = await adminService.deleteSchedule(btn.dataset.id);
            if (result.success) {
                showToast('Đã xóa lịch học!', 'success');
                renderSchedule();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
}

// ==================== TEACHER REVIEWS MANAGEMENT ====================
let teacherReviewsData = [];

let teacherReviewsInitialized = false;

async function initTeacherReviewsSection() {
    if (teacherReviewsInitialized) return;
    teacherReviewsInitialized = true;
    
    await loadTeacherReviews();
    
    // Add review button
    document.getElementById('add-teacher-review-btn')?.addEventListener('click', () => {
        showTeacherReviewModal();
    });
}

async function loadTeacherReviews() {
    const tbody = document.getElementById('teacher-reviews-tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div></td></tr>';
    
    const result = await adminService.getTeacherReviews();
    
    if (result.success && result.data) {
        teacherReviewsData = result.data;
        renderTeacherReviewsTable(result.data);
    } else {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-gray-500">Không có dữ liệu</td></tr>';
    }
}

function renderTeacherReviewsTable(reviews) {
    const tbody = document.getElementById('teacher-reviews-tbody');
    if (!tbody) return;
    
    if (!reviews || reviews.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-gray-500">Chưa có đánh giá nào</td></tr>';
        return;
    }
    
    tbody.innerHTML = reviews.map((review, index) => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">${index + 1}</td>
            <td class="px-4 py-3 font-medium">${escapeHtml(review.reviewer_name)}</td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white text-sm font-medium">
                    ${escapeHtml(review.reviewer_avatar || review.reviewer_name.substring(0, 2).toUpperCase())}
                </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">${escapeHtml(review.reviewer_info || '-')}</td>
            <td class="px-4 py-3">${'⭐'.repeat(review.rating || 5)}</td>
            <td class="px-4 py-3 max-w-xs truncate" title="${escapeHtml(review.comment || '')}">${escapeHtml((review.comment || '').substring(0, 50))}${(review.comment || '').length > 50 ? '...' : ''}</td>
            <td class="px-4 py-3 text-center">
                <span class="px-2 py-1 rounded text-xs ${review.is_approved == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}">
                    ${review.is_approved == 1 ? 'Hiển thị' : 'Ẩn'}
                </span>
            </td>
            <td class="px-4 py-3 text-center">
                <button class="admin-action-btn secondary edit-teacher-review-btn" data-id="${review.id}" title="Sửa">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>
                <button class="admin-action-btn danger delete-teacher-review-btn" data-id="${review.id}" title="Xóa">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </td>
        </tr>
    `).join('');
    
    bindTeacherReviewEvents();
}

function bindTeacherReviewEvents() {
    document.querySelectorAll('.edit-teacher-review-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const review = teacherReviewsData.find(r => r.id == btn.dataset.id);
            if (review) showTeacherReviewModal(review);
        });
    });
    
    document.querySelectorAll('.delete-teacher-review-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa đánh giá này?')) return;
            const result = await adminService.deleteTeacherReview(btn.dataset.id);
            if (result.success) {
                showToast('Đã xóa đánh giá!', 'success');
                loadTeacherReviews();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
}

function showTeacherReviewModal(review = null) {
    const isEdit = review !== null;
    const modalHtml = `
        <div id="teacher-review-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">${isEdit ? 'Sửa đánh giá' : 'Thêm đánh giá mới'}</h3>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form id="teacher-review-form" class="p-4 space-y-4">
                    <input type="hidden" name="id" value="${review?.id || ''}">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tên người đánh giá *</label>
                            <input type="text" name="reviewer_name" value="${review?.reviewer_name || ''}" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Avatar (2 ký tự)</label>
                            <input type="text" name="reviewer_avatar" value="${review?.reviewer_avatar || ''}" maxlength="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="VD: NH">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thông tin</label>
                        <input type="text" name="reviewer_info" value="${review?.reviewer_info || ''}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="VD: Học viên lớp Speaking">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số sao (1-5)</label>
                        <select name="rating" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            ${[5,4,3,2,1].map(n => `<option value="${n}" ${review?.rating == n ? 'selected' : ''}>${'⭐'.repeat(n)}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung đánh giá *</label>
                        <textarea name="comment" required rows="4" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Nhập nội dung đánh giá...">${review?.comment || ''}</textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_approved" id="review-approved" ${review?.is_approved != 0 ? 'checked' : ''}>
                        <label for="review-approved" class="text-sm text-gray-700">Hiển thị trên website</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button type="button" class="close-modal px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Hủy</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">${isEdit ? 'Cập nhật' : 'Thêm'}</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = document.getElementById('teacher-review-modal');
    modal.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => modal.remove());
    });
    
    document.getElementById('teacher-review-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            id: formData.get('id') || null,
            reviewer_name: formData.get('reviewer_name'),
            reviewer_avatar: formData.get('reviewer_avatar'),
            reviewer_info: formData.get('reviewer_info'),
            rating: parseInt(formData.get('rating')),
            comment: formData.get('comment'),
            is_approved: formData.get('is_approved') ? 1 : 0
        };
        
        const result = isEdit 
            ? await adminService.updateTeacherReview(data)
            : await adminService.createTeacherReview(data);
        
        if (result.success) {
            showToast(isEdit ? 'Đã cập nhật!' : 'Đã thêm đánh giá!', 'success');
            modal.remove();
            loadTeacherReviews();
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    });
}

// ==================== COURSE FEES MANAGEMENT ====================
let courseFeesData = { tieuhoc: [], thcs: [], ielts: [] };
let currentFeeCategory = 'tieuhoc';

let courseFeesInitialized = false;

async function initCourseFeesSection() {
    if (courseFeesInitialized) return;
    courseFeesInitialized = true;
    
    await loadCourseFees();
    
    // Category filter buttons
    document.querySelectorAll('.course-fee-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Update button styles
            document.querySelectorAll('.course-fee-filter-btn').forEach(b => {
                b.classList.remove('bg-blue-500', 'text-white');
                b.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
            });
            btn.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
            btn.classList.add('bg-blue-500', 'text-white');
            
            currentFeeCategory = btn.dataset.category;
            renderCourseFeesTable();
        });
    });
    
    // Add button
    document.getElementById('add-course-fee-btn')?.addEventListener('click', () => {
        showCourseFeeModal();
    });
}

async function loadCourseFees() {
    const tbody = document.getElementById('course-fees-tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div></td></tr>';
    
    const result = await adminService.getCourseFees();
    
    if (result.success && result.data) {
        courseFeesData = result.data.grouped || { tieuhoc: [], thcs: [], ielts: [] };
        renderCourseFeesTable();
    } else {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500">Không có dữ liệu</td></tr>';
    }
}

function renderCourseFeesTable() {
    const tbody = document.getElementById('course-fees-tbody');
    if (!tbody) return;
    
    const items = courseFeesData[currentFeeCategory] || [];
    
    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500">Chưa có dữ liệu học phí cho danh mục này</td></tr>';
        return;
    }
    
    tbody.innerHTML = items.map((item, index) => `
        <tr class="hover:bg-gray-50 ${item.is_highlight == 1 ? 'bg-yellow-50' : ''}">
            <td class="px-4 py-3">${index + 1}</td>
            <td class="px-4 py-3 font-medium">${escapeHtml(item.level)}</td>
            <td class="px-4 py-3">${escapeHtml(item.curriculum || '-')}</td>
            <td class="px-4 py-3">${escapeHtml(item.duration || '-')}</td>
            <td class="px-4 py-3 font-semibold text-green-600">${escapeHtml(item.fee || '-')}</td>
            <td class="px-4 py-3 text-center">
                ${item.is_highlight == 1 ? '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">Nổi bật</span>' : '-'}
            </td>
            <td class="px-4 py-3 text-center">
                <button class="admin-action-btn secondary edit-course-fee-btn" data-id="${item.id}" title="Sửa">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>
                <button class="admin-action-btn danger delete-course-fee-btn" data-id="${item.id}" title="Xóa">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </td>
        </tr>
    `).join('');
    
    bindCourseFeeEvents();
}

function bindCourseFeeEvents() {
    document.querySelectorAll('.edit-course-fee-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const items = courseFeesData[currentFeeCategory] || [];
            const item = items.find(i => i.id == btn.dataset.id);
            if (item) showCourseFeeModal(item);
        });
    });
    
    document.querySelectorAll('.delete-course-fee-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Bạn có chắc muốn xóa mục này?')) return;
            const result = await adminService.deleteCourseFee(btn.dataset.id);
            if (result.success) {
                showToast('Đã xóa!', 'success');
                loadCourseFees();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        });
    });
}

function showCourseFeeModal(item = null) {
    const isEdit = item !== null;
    const categoryLabels = { tieuhoc: 'Tiểu học', thcs: 'THCS', ielts: 'IELTS' };
    
    const modalHtml = `
        <div id="course-fee-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">${isEdit ? 'Sửa mục học phí' : 'Thêm mục học phí mới'}</h3>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form id="course-fee-form" class="p-4 space-y-4">
                    <input type="hidden" name="id" value="${item?.id || ''}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                        <select name="category" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" ${isEdit ? 'disabled' : ''}>
                            ${Object.entries(categoryLabels).map(([val, label]) => 
                                `<option value="${val}" ${(item?.category || currentFeeCategory) === val ? 'selected' : ''}>${label}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Level / Tên khóa *</label>
                        <input type="text" name="level" value="${item?.level || ''}" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="VD: Lớp 1 hoặc IELTS Foundation">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giáo trình</label>
                        <input type="text" name="curriculum" value="${item?.curriculum || ''}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="VD: Family and Friends 1">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Thời lượng</label>
                            <input type="text" name="duration" value="${item?.duration || ''}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="VD: 12 tháng">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Học phí</label>
                            <input type="text" name="fee" value="${item?.fee || ''}" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="VD: 850.000">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_highlight" id="fee-highlight" ${item?.is_highlight == 1 ? 'checked' : ''}>
                        <label for="fee-highlight" class="text-sm text-gray-700">Đánh dấu nổi bật (VD: Luyện thi)</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button type="button" class="close-modal px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Hủy</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">${isEdit ? 'Cập nhật' : 'Thêm'}</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = document.getElementById('course-fee-modal');
    modal.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => modal.remove());
    });
    
    document.getElementById('course-fee-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {
            id: formData.get('id') || null,
            category: isEdit ? item.category : formData.get('category'),
            level: formData.get('level'),
            curriculum: formData.get('curriculum'),
            duration: formData.get('duration'),
            fee: formData.get('fee'),
            is_highlight: formData.get('is_highlight') ? 1 : 0,
            is_active: 1
        };
        
        const result = isEdit 
            ? await adminService.updateCourseFee(data)
            : await adminService.createCourseFee(data);
        
        if (result.success) {
            showToast(isEdit ? 'Đã cập nhật!' : 'Đã thêm!', 'success');
            modal.remove();
            loadCourseFees();
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    });
}

// ==================== START ====================
document.addEventListener('DOMContentLoaded', () => {
    initPage();
});


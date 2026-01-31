/**
 * Admin Controller - Điều khiển trang admin dashboard
 * Bao gồm CRUD cho tất cả các bảng và quản lý thùng rác
 */

import { adminService } from '../services/adminService.js';
import { showToast } from '../ui/toast.js';

// ==================== HELPER FUNCTIONS ====================

// Escape HTML special characters
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Kiểm tra quyền admin
async function checkAdmin() {
    try {
        const result = await adminService.getDashboard();
        if (result.error) {
            window.location.href = 'login.html';
            return null;
        }
        return result;
    } catch (error) {
        window.location.href = 'login.html';
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
        const result = await adminService.getDashboard();
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
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

// Render Users
async function renderUsers() {
    const tbody = document.getElementById('users-tbody');
    try {
        const result = await adminService.getUsers();
        if (!result.success || !result.users?.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-gray-500">Chưa có học viên</td></tr>';
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
        tbody.innerHTML = '<tr><td colspan="7" class="text-red-500">Lỗi tải dữ liệu</td></tr>';
    }
}

// Render Enrollments
async function renderEnrollments(status = null) {
    const tbody = document.getElementById('enrollments-tbody');
    try {
        const result = await adminService.getEnrollments(status);
        if (!result.success || !result.enrollments?.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-gray-500">Không có dữ liệu</td></tr>';
            return;
        }

        tbody.innerHTML = result.enrollments.map(e => `
            <tr>
                <td>${e.fullname}</td>
                <td>${e.course_name}</td>
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
                    <button class="admin-action-btn secondary edit-enrollment-btn" data-id="${e.id}" data-enrollment='${JSON.stringify(e)}'>Sửa</button>
                    <button class="admin-action-btn danger delete-enrollment-btn" data-id="${e.id}">Xóa</button>
                </td>
            </tr>
        `).join('');

        // Edit handlers
        tbody.querySelectorAll('.edit-enrollment-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const enrollment = JSON.parse(btn.dataset.enrollment);
                showEnrollmentModal(enrollment);
            });
        });

        // Delete handlers
        tbody.querySelectorAll('.delete-enrollment-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn có chắc muốn xóa đăng ký này?')) return;
                try {
                    const result = await adminService.deleteEnrollment(btn.dataset.id);
                    if (result.success) {
                        showToast('Đã chuyển vào thùng rác!', 'success');
                        renderEnrollments();
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

// Render Courses
async function renderCourses() {
    const tbody = document.getElementById('courses-tbody');
    try {
        const result = await adminService.getCourses();
        if (!result.courses?.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-gray-500">Chưa có khóa học</td></tr>';
            return;
        }

        tbody.innerHTML = result.courses.map(c => `
            <tr>
                <td>${c.name}</td>
                <td><span class="px-2 py-1 rounded text-xs font-medium" style="background: #dbeafe; color: #1e40af;">${c.level}</span></td>
                <td>${c.duration || '-'}</td>
                <td>${formatMoney(c.price)}</td>
                <td>
                    <span class="status-badge ${c.is_active ? 'active' : 'cancelled'}">
                        ${c.is_active ? 'Hoạt động' : 'Ẩn'}
                    </span>
                </td>
                <td>
                    <button class="admin-action-btn secondary edit-course-btn" data-id="${c.id}" data-course='${JSON.stringify(c).replace(/'/g, "\\'")}'>Sửa</button>
                    <button class="admin-action-btn danger delete-course-btn" data-id="${c.id}">Xóa</button>
                </td>
            </tr>
        `).join('');

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
        tbody.innerHTML = '<tr><td colspan="6" class="text-red-500">Lỗi tải dữ liệu</td></tr>';
    }
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

        tbody.innerHTML = result.teachers.map(t => `
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <img src="${t.image_url || '../assets/images/default-avatar.png'}" alt="${t.name}" class="w-10 h-10 rounded-full object-cover">
                        <span>${t.name}</span>
                    </div>
                </td>
                <td>${t.title || '-'}</td>
                <td>${t.experience_years ? t.experience_years + ' năm' : '-'}</td>
                <td>${t.ielts_score || '-'}</td>
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
    showModal(`
        <h3 class="text-xl font-bold mb-4">${isEdit ? 'Sửa khóa học' : 'Thêm khóa học'}</h3>
        <form id="course-form" class="space-y-4">
            <input type="hidden" name="id" value="${course?.id || ''}">
            <div>
                <label class="profile-form-label">Tên khóa học *</label>
                <input type="text" name="name" class="profile-form-input" value="${course?.name || ''}" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Cấp độ</label>
                    <select name="level" class="profile-form-input">
                        <option value="beginner" ${course?.level === 'beginner' ? 'selected' : ''}>Beginner</option>
                        <option value="intermediate" ${course?.level === 'intermediate' ? 'selected' : ''}>Intermediate</option>
                        <option value="advanced" ${course?.level === 'advanced' ? 'selected' : ''}>Advanced</option>
                    </select>
                </div>
                <div>
                    <label class="profile-form-label">Thời lượng</label>
                    <input type="text" name="duration" class="profile-form-input" value="${course?.duration || ''}" placeholder="VD: 3 tháng">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Học phí</label>
                    <input type="number" name="price" class="profile-form-input" value="${course?.price || 0}">
                </div>
                <div>
                    <label class="profile-form-label">Số buổi</label>
                    <input type="number" name="total_sessions" class="profile-form-input" value="${course?.total_sessions || 0}">
                </div>
            </div>
            <div>
                <label class="profile-form-label">Mô tả</label>
                <textarea name="description" class="profile-form-input" rows="3">${course?.description || ''}</textarea>
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

    document.getElementById('course-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        data.is_active = formData.has('is_active') ? 1 : 0;
        data.price = parseInt(data.price) || 0;
        data.total_sessions = parseInt(data.total_sessions) || 0;

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

// Show Schedule Modal
async function showScheduleModal(schedule = null) {
    // Load enrollments and teachers for dropdown
    const [enrollmentsResult, teachersResult] = await Promise.all([
        adminService.getEnrollments(),
        adminService.getTeachers()
    ]);

    const enrollments = enrollmentsResult.enrollments || [];
    const teachers = teachersResult.teachers || [];

    showModal(`
        <h3 class="text-xl font-bold mb-4">${schedule ? 'Sửa lịch học' : 'Thêm lịch học mới'}</h3>
        <form id="schedule-form" class="space-y-4" style="max-height: 70vh; overflow-y: auto; padding-right: 8px;">
            ${schedule ? `<input type="hidden" name="id" value="${schedule.id}">` : ''}
            
            <div>
                <label class="profile-form-label">Đăng ký khóa học *</label>
                <select name="enrollment_id" class="profile-form-input" required>
                    <option value="">-- Chọn đăng ký --</option>
                    ${enrollments.filter(e => e.status === 'active').map(e => `
                        <option value="${e.id}" ${schedule?.enrollment_id == e.id ? 'selected' : ''}>
                            ${e.fullname} - ${e.course_name}
                        </option>
                    `).join('')}
                </select>
            </div>
            
            <div>
                <label class="profile-form-label">Tiêu đề buổi học *</label>
                <input type="text" name="title" class="profile-form-input" value="${schedule?.title || ''}" required placeholder="VD: IELTS Writing Task 2">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Mã khóa học</label>
                    <input type="text" name="course_code" class="profile-form-input" value="${schedule?.course_code || ''}" placeholder="VD: IELTS1001">
                </div>
                <div>
                    <label class="profile-form-label">Tên lớp</label>
                    <input type="text" name="class_name" class="profile-form-input" value="${schedule?.class_name || ''}" placeholder="VD: IELTS.INT.A">
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="profile-form-label">Năm học</label>
                    <select name="academic_year" class="profile-form-input">
                        <option value="2025-2026" ${schedule?.academic_year === '2025-2026' ? 'selected' : ''}>2025-2026</option>
                        <option value="2024-2025" ${schedule?.academic_year === '2024-2025' ? 'selected' : ''}>2024-2025</option>
                        <option value="2023-2024" ${schedule?.academic_year === '2023-2024' ? 'selected' : ''}>2023-2024</option>
                    </select>
                </div>
                <div>
                    <label class="profile-form-label">Học kỳ</label>
                    <select name="semester" class="profile-form-input">
                        <option value="1" ${schedule?.semester == 1 ? 'selected' : ''}>Học kỳ 1</option>
                        <option value="2" ${schedule?.semester == 2 ? 'selected' : ''}>Học kỳ 2</option>
                        <option value="3" ${schedule?.semester == 3 ? 'selected' : ''}>Học kỳ 3 (Hè)</option>
                    </select>
                </div>
                <div>
                    <label class="profile-form-label">Nhóm</label>
                    <input type="text" name="group_name" class="profile-form-input" value="${schedule?.group_name || ''}" placeholder="VD: 01">
                </div>
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
                    <label class="profile-form-label">Buổi học</label>
                    <select name="session" class="profile-form-input">
                        <option value="morning" ${schedule?.session === 'morning' ? 'selected' : ''}>Buổi sáng</option>
                        <option value="afternoon" ${schedule?.session === 'afternoon' ? 'selected' : ''}>Buổi chiều</option>
                        <option value="evening" ${schedule?.session === 'evening' ? 'selected' : ''}>Buổi tối</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="profile-form-label">Tiết bắt đầu</label>
                    <select name="period" class="profile-form-input">
                        ${[...Array(15)].map((_, i) => `
                            <option value="${i+1}" ${schedule?.period == i+1 ? 'selected' : ''}>Tiết ${i+1}</option>
                        `).join('')}
                    </select>
                </div>
                <div>
                    <label class="profile-form-label">Số tiết</label>
                    <select name="period_count" class="profile-form-input">
                        ${[1,2,3,4,5].map(n => `
                            <option value="${n}" ${schedule?.period_count == n ? 'selected' : ''}>${n} tiết</option>
                        `).join('')}
                    </select>
                </div>
                <div>
                    <label class="profile-form-label">Giảng viên</label>
                    <select name="teacher_id" class="profile-form-input">
                        <option value="">-- Chọn --</option>
                        ${teachers.map(t => `
                            <option value="${t.id}" ${schedule?.teacher_id == t.id ? 'selected' : ''}>${t.name}</option>
                        `).join('')}
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
                    <label class="profile-form-label">Email giảng viên</label>
                    <input type="email" name="teacher_email" class="profile-form-input" value="${schedule?.teacher_email || ''}" placeholder="teacher@haiau.edu.vn">
                </div>
                <div>
                    <label class="profile-form-label">Màu sắc</label>
                    <input type="color" name="color" class="profile-form-input h-10" value="${schedule?.color || '#1e40af'}">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Phòng học</label>
                    <input type="text" name="room" class="profile-form-input" value="${schedule?.room || ''}" placeholder="VD: Phòng A1">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_online" id="is_online" ${schedule?.is_online ? 'checked' : ''} class="w-4 h-4">
                        <span>Học trực tuyến (Online)</span>
                    </label>
                </div>
            </div>
            
            <div id="meeting-link-field" class="${schedule?.is_online ? '' : 'hidden'}">
                <label class="profile-form-label">Link Meeting</label>
                <input type="url" name="meeting_link" class="profile-form-input" value="${schedule?.meeting_link || ''}" placeholder="https://zoom.us/...">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="profile-form-label">Ngày bắt đầu hiệu lực</label>
                    <input type="date" name="start_date" class="profile-form-input" value="${schedule?.start_date || ''}">
                </div>
                <div>
                    <label class="profile-form-label">Ngày kết thúc</label>
                    <input type="date" name="end_date" class="profile-form-input" value="${schedule?.end_date || ''}">
                </div>
            </div>
            
            <div>
                <label class="profile-form-label">Mô tả</label>
                <textarea name="description" class="profile-form-input" rows="2" placeholder="Nội dung buổi học...">${schedule?.description || ''}</textarea>
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
            document.getElementById('sidebar').classList.remove('open');
        });
    });

    // Mobile toggle
    document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('open');
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
            renderEnrollments();
            break;
        case 'courses':
            renderCourses();
            break;
        case 'teachers':
            renderTeachers();
            break;
        case 'scores':
            renderScores();
            break;
        case 'feedback':
            renderFeedback();
            break;
        case 'schedule':
            renderSchedule();
            break;
        case 'trash':
            renderTrash();
            break;
    }
}

// Init filters
function initFilters() {
    // Enrollment filter
    const enrollmentFilter = document.getElementById('enrollment-status-filter');
    enrollmentFilter?.addEventListener('change', () => {
        renderEnrollments(enrollmentFilter.value || null);
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
}

// Init add buttons
function initAddButtons() {
    document.getElementById('add-user-btn')?.addEventListener('click', () => showUserModal());
    document.getElementById('add-course-btn')?.addEventListener('click', () => showCourseModal());
    document.getElementById('add-schedule-btn')?.addEventListener('click', () => showScheduleModal());
    
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
                await fetch('../../backend/php/auth.php?action=logout', {
                    credentials: 'include'
                });
            } catch (error) {
                // Ignore error
            }
            window.location.href = 'login.html';
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

// Initialize page
async function initPage() {
    const dashboard = await checkAdmin();
    if (!dashboard) return;

    // Load dashboard
    renderDashboard();
}

// ==================== START ====================
document.addEventListener('DOMContentLoaded', () => {
    initPage();
    initSidebar();
    initFilters();
    initAddButtons();
    initLogout();
    initModal();
});

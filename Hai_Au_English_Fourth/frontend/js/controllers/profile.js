// profile.js - Controller cho trang profile học viên
import { profileService } from '../services/profileService.js';
import { showToast } from '../ui/toast.js';

// Kiểm tra đăng nhập - truy vấn từ database
async function checkAuth() {
    try {
        const result = await profileService.getProfile();
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

// Cập nhật sidebar user info
function updateSidebarInfo(profile) {
    const sidebarName = document.getElementById('sidebar-name');
    const sidebarEmail = document.getElementById('sidebar-email');
    const headerUsername = document.getElementById('header-username');
    const welcomeName = document.getElementById('welcome-name');
    
    if (sidebarName) sidebarName.textContent = profile.fullname || 'Học viên';
    if (sidebarEmail) sidebarEmail.textContent = profile.email || '';
    if (headerUsername) headerUsername.textContent = profile.fullname || 'Học viên';
    if (welcomeName) welcomeName.textContent = profile.fullname?.split(' ').pop() || 'Bạn';
}

// Điền thông tin profile form
function fillProfileForm(profile) {
    document.getElementById('profile-fullname').value = profile.fullname || '';
    document.getElementById('profile-email').value = profile.email || '';
    document.getElementById('profile-phone').value = profile.phone || '';
    document.getElementById('profile-dob').value = profile.date_of_birth || '';
    document.getElementById('profile-gender').value = profile.gender || '';
    document.getElementById('profile-address').value = profile.address || '';
}

// Escape HTML special characters
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Format điểm số badge
function getScoreBadge(score) {
    let className = 'low';
    if (score >= 7.5) className = 'excellent';
    else if (score >= 6.5) className = 'good';
    else if (score >= 5.5) className = 'average';
    
    return `<span class="score-badge ${className}">${score}</span>`;
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

// Format ngày
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN');
}

// Render dashboard
async function renderDashboard() {
    try {
        const result = await profileService.getDashboard();
        if (!result.success) return;

        const { stats, recent_scores } = result;
        
        // Stats
        document.getElementById('stat-courses').textContent = stats.active_courses || 0;
        document.getElementById('stat-completed').textContent = stats.completed_courses || 0;
        document.getElementById('stat-score').textContent = stats.highest_score || '0.0';
        document.getElementById('stat-progress').textContent = `${stats.avg_progress || 0}%`;

        // Recent scores
        const container = document.getElementById('recent-scores-container');
        if (recent_scores && recent_scores.length > 0) {
            container.innerHTML = `
                <table class="profile-table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>L</th>
                            <th>R</th>
                            <th>W</th>
                            <th>S</th>
                            <th>Overall</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${recent_scores.slice(0, 5).map(score => `
                            <tr>
                                <td>${formatDate(score.test_date)}</td>
                                <td>${getScoreBadge(score.listening)}</td>
                                <td>${getScoreBadge(score.reading)}</td>
                                <td>${getScoreBadge(score.writing)}</td>
                                <td>${getScoreBadge(score.speaking)}</td>
                                <td>${getScoreBadge(score.overall)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h3 class="empty-state-title">Chưa có điểm số</h3>
                    <p class="empty-state-text">Điểm số IELTS của bạn sẽ hiển thị ở đây</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

// Render khóa học đã đăng ký
async function renderCourses() {
    const container = document.getElementById('courses-container');
    try {
        const result = await profileService.getEnrollments();
        if (!result.success || !result.enrollments?.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <h3 class="empty-state-title">Chưa đăng ký khóa học</h3>
                    <p class="empty-state-text">Hãy đăng ký một khóa học để bắt đầu học IELTS</p>
                    <a href="courses.html" class="admin-action-btn primary mt-4 inline-block">Xem khóa học</a>
                </div>
            `;
            return;
        }

        container.innerHTML = result.enrollments.map(e => `
            <div class="enrollment-card">
                <img src="${e.image_url || '../assets/images/course-default.jpg'}" alt="${e.course_name}" class="enrollment-image">
                <div class="enrollment-info">
                    <h3 class="enrollment-title">${e.course_name}</h3>
                    <div class="enrollment-meta">
                        <span>📅 ${formatDate(e.start_date)} - ${formatDate(e.end_date)}</span>
                        <span>📚 ${e.academic_year} - ${e.semester}</span>
                    </div>
                    <div class="flex items-center gap-4">
                        ${getStatusBadge(e.status)}
                        <div class="flex-1">
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: ${e.progress || 0}%"></div>
                            </div>
                        </div>
                        <span class="text-sm text-gray-600">${e.progress || 0}%</span>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = '<p class="text-red-500">Lỗi tải dữ liệu</p>';
    }
}

// Render điểm số
async function renderScores() {
    const tbody = document.getElementById('scores-tbody');
    try {
        const result = await profileService.getScores();
        if (!result.success || !result.scores?.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <p class="text-gray-500">Chưa có điểm số IELTS</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = result.scores.map(s => `
            <tr>
                <td>${formatDate(s.test_date)}</td>
                <td><span class="text-xs text-gray-500">${s.test_type || 'Practice'}</span></td>
                <td>${getScoreBadge(s.listening)}</td>
                <td>${getScoreBadge(s.reading)}</td>
                <td>${getScoreBadge(s.writing)}</td>
                <td>${getScoreBadge(s.speaking)}</td>
                <td>${getScoreBadge(s.overall)}</td>
            </tr>
        `).join('');
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-red-500">Lỗi tải dữ liệu</td></tr>';
    }
}

// Render scores charts
let lineChart = null;
let pieChart = null;

async function renderScoresCharts() {
    try {
        const result = await profileService.getScoresChart();
        if (!result.success) return;

        const { timeline, averages } = result;

        // Line/Bar Chart - Tiến trình điểm
        const lineCtx = document.getElementById('scores-line-chart')?.getContext('2d');
        const lineContainer = document.getElementById('scores-line-chart')?.parentElement;
        
        if (lineCtx) {
            // Destroy existing chart
            if (lineChart) lineChart.destroy();
            
            if (!timeline || timeline.length === 0) {
                // Show empty state
                if (lineContainer) {
                    lineContainer.innerHTML = `
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Tiến trình điểm số</h3>
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <p class="text-center">Chưa có dữ liệu điểm số</p>
                            <p class="text-sm text-gray-300 mt-1">Điểm số sẽ hiển thị khi có kết quả thi</p>
                        </div>
                    `;
                }
            } else {
                const labels = timeline.map(s => formatDate(s.test_date));
            
            lineChart = new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Overall',
                            data: timeline.map(s => parseFloat(s.overall) || 0),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3
                        },
                        {
                            label: 'Listening',
                            data: timeline.map(s => parseFloat(s.listening) || 0),
                            borderColor: '#10b981',
                            backgroundColor: 'transparent',
                            tension: 0.4,
                            borderWidth: 2
                        },
                        {
                            label: 'Reading',
                            data: timeline.map(s => parseFloat(s.reading) || 0),
                            borderColor: '#f59e0b',
                            backgroundColor: 'transparent',
                            tension: 0.4,
                            borderWidth: 2
                        },
                        {
                            label: 'Writing',
                            data: timeline.map(s => parseFloat(s.writing) || 0),
                            borderColor: '#8b5cf6',
                            backgroundColor: 'transparent',
                            tension: 0.4,
                            borderWidth: 2
                        },
                        {
                            label: 'Speaking',
                            data: timeline.map(s => parseFloat(s.speaking) || 0),
                            borderColor: '#ec4899',
                            backgroundColor: 'transparent',
                            tension: 0.4,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 9,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
            }
        }

        // Pie Chart - Phân bổ điểm trung bình
        const pieCtx = document.getElementById('scores-pie-chart')?.getContext('2d');
        const pieContainer = document.getElementById('scores-pie-chart')?.parentElement;
        
        if (pieCtx) {
            // Destroy existing chart
            if (pieChart) pieChart.destroy();
            
            const hasData = averages && (
                parseFloat(averages.avg_listening) > 0 ||
                parseFloat(averages.avg_reading) > 0 ||
                parseFloat(averages.avg_writing) > 0 ||
                parseFloat(averages.avg_speaking) > 0
            );
            
            if (!hasData) {
                // Show empty state
                if (pieContainer) {
                    pieContainer.innerHTML = `
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Phân bổ điểm trung bình</h3>
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                            <p class="text-center">Chưa có dữ liệu</p>
                            <p class="text-sm text-gray-300 mt-1">Biểu đồ sẽ hiển thị khi có điểm</p>
                        </div>
                    `;
                }
            } else {
                const avgData = [
                    parseFloat(averages.avg_listening) || 0,
                    parseFloat(averages.avg_reading) || 0,
                    parseFloat(averages.avg_writing) || 0,
                    parseFloat(averages.avg_speaking) || 0
                ];

            pieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Listening', 'Reading', 'Writing', 'Speaking'],
                    datasets: [{
                        data: avgData,
                        backgroundColor: [
                            '#10b981',
                            '#f59e0b',
                            '#8b5cf6',
                            '#ec4899'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw.toFixed(1)}`;
                                }
                            }
                        }
                    }
                }
            });
            }
        }
    } catch (error) {
        console.error('Error rendering charts:', error);
    }
}

// Render tiến độ
async function renderProgress() {
    const container = document.getElementById('progress-container');
    try {
        const result = await profileService.getProgress();
        if (!result.success || !result.enrollments?.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <p class="text-gray-500">Chưa có dữ liệu tiến độ</p>
                </div>
            `;
            return;
        }

        container.innerHTML = result.enrollments.map(e => `
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-semibold text-gray-800">${e.course_name}</h4>
                    <span class="text-blue-600 font-bold">${e.progress || 0}%</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: ${e.progress || 0}%"></div>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Trạng thái: ${getStatusBadge(e.status)}
                </p>
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = '<p class="text-red-500">Lỗi tải dữ liệu</p>';
    }
}

// Render nhận xét
async function renderFeedback() {
    const container = document.getElementById('feedback-container');
    try {
        const result = await profileService.getFeedback();
        if (!result.success || !result.feedback?.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="empty-state-title">Chưa có nhận xét</h3>
                    <p class="empty-state-text">Nhận xét từ giảng viên sẽ hiển thị ở đây</p>
                </div>
            `;
            return;
        }

        container.innerHTML = result.feedback.map(f => `
            <div class="feedback-card">
                <div class="feedback-header">
                    <img src="${f.teacher_avatar || '../assets/images/default-avatar.png'}" alt="${f.teacher_name}" class="feedback-avatar">
                    <div>
                        <p class="feedback-teacher-name">${f.teacher_name}</p>
                        <p class="feedback-date">${formatDate(f.feedback_date)} • ${f.course_name}</p>
                    </div>
                </div>
                <p class="feedback-content">${f.content}</p>
                <div class="feedback-rating">
                    ${[1,2,3,4,5].map(i => 
                        `<span class="feedback-star ${i <= f.rating ? '' : 'empty'}">★</span>`
                    ).join('')}
                </div>
            </div>
        `).join('');
    } catch (error) {
        container.innerHTML = '<p class="text-red-500">Lỗi tải dữ liệu</p>';
    }
}

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

// Period time mapping (Tiết học)
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

// Current schedule state
let currentScheduleWeek = new Date();
let allSchedules = [];

// Format time
function formatTime(timeStr) {
    if (!timeStr) return '';
    return timeStr.substring(0, 5);
}

// Get week dates
function getWeekDates(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Monday
    const monday = new Date(d.setDate(diff));
    
    const dates = {};
    dayOrder.forEach((dayName, index) => {
        const dayDate = new Date(monday);
        dayDate.setDate(monday.getDate() + index);
        dates[dayName] = dayDate;
    });
    return dates;
}

// Format date for display
function formatDateShort(date) {
    return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// Populate week selector
function populateWeekSelector() {
    const weekSelect = document.getElementById('schedule-week');
    if (!weekSelect) return;
    
    weekSelect.innerHTML = '';
    
    // Generate 20 weeks (10 before, current, 9 after)
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

// Update date headers
function updateDateHeaders() {
    const weekDates = getWeekDates(currentScheduleWeek);
    
    dayOrder.forEach(day => {
        const dateEl = document.getElementById(`date-${day}`);
        if (dateEl) {
            dateEl.textContent = formatDateShort(weekDates[day]);
        }
        
        // Highlight today
        const th = document.querySelector(`th[data-day="${day}"]`);
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

// Render timetable grid
function renderTimetableGrid(schedules) {
    const tbody = document.getElementById('schedule-tbody');
    if (!tbody) return;
    
    // Create period rows (15 periods)
    let html = '';
    for (let period = 1; period <= 15; period++) {
        html += `<tr data-period="${period}">
            <td class="period-cell">${period}</td>
            ${dayOrder.map(day => `<td class="schedule-cell" data-day="${day}" data-period="${period}"></td>`).join('')}
        </tr>`;
    }
    tbody.innerHTML = html;
    
    // Place schedule items
    schedules.forEach(s => {
        const startPeriod = s.period || getPeriodFromTime(s.start_time);
        const periodCount = s.period_count || 1;
        
        const cell = tbody.querySelector(`td[data-day="${s.day_of_week}"][data-period="${startPeriod}"]`);
        if (!cell) return;
        
        // Calculate height based on period count (50px per period row)
        const itemHeight = (periodCount * 50) - 2;
        
        // Escape special characters for JSON
        const safeSchedule = JSON.stringify(s).replace(/\\/g, '\\\\').replace(/"/g, '&quot;');
        
        cell.innerHTML = `
            <div class="schedule-cell-item" 
                 style="background: ${s.color || '#1e40af'}; height: ${itemHeight}px;"
                 onclick="showScheduleDetail(${safeSchedule})">
                <div class="item-title">${escapeHtml(s.title)}</div>
                ${s.course_code ? `<div class="item-code">(${escapeHtml(s.course_code)})</div>` : ''}
                <div class="item-session">${sessionLabels[s.session] || ''}</div>
                <div class="item-time">Giờ: ${formatTime(s.start_time)}-${formatTime(s.end_time)}</div>
                ${s.group_name ? `<div class="item-group">Nhóm: ${escapeHtml(s.group_name)}</div>` : ''}
                ${s.class_name ? `<div class="item-class">Lớp: ${escapeHtml(s.class_name)}</div>` : ''}
                <div class="item-room"><strong>Phòng:</strong> ${s.is_online ? 'Online' : escapeHtml(s.room || '-')}</div>
                ${s.teacher_name ? `<div class="item-teacher">GV: ${escapeHtml(s.teacher_name)}</div>` : ''}
                ${s.teacher_email ? `<div class="item-email">Email: ${escapeHtml(s.teacher_email)}</div>` : ''}
                ${s.is_online && s.meeting_link ? `<a href="${s.meeting_link}" target="_blank" class="item-link" onclick="event.stopPropagation()">Link học trực tuyến</a>` : ''}
            </div>
        `;
        
        // Mark spanned cells (for visual merging effect)
        for (let i = 1; i < periodCount; i++) {
            const nextCell = tbody.querySelector(`td[data-day="${s.day_of_week}"][data-period="${startPeriod + i}"]`);
            if (nextCell) {
                nextCell.classList.add('spanned');
            }
        }
    });
    
    // Highlight today column
    const today = new Date();
    const weekDates = getWeekDates(currentScheduleWeek);
    dayOrder.forEach(day => {
        if (weekDates[day].toDateString() === today.toDateString()) {
            tbody.querySelectorAll(`td[data-day="${day}"]`).forEach(td => {
                td.classList.add('today-col');
            });
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
    
    // Find closest period
    const hour = parseInt(time.split(':')[0]);
    if (hour < 12) return Math.max(1, Math.min(6, hour - 5));
    if (hour < 17) return Math.max(7, Math.min(12, hour - 5));
    return Math.max(13, Math.min(15, hour - 5));
}

// Show schedule detail modal
window.showScheduleDetail = function(schedule) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4';
    modal.id = 'schedule-detail-modal';
    modal.onclick = (e) => {
        if (e.target === modal) modal.remove();
    };
    
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md schedule-detail-modal animate-in">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-blue-800">${schedule.title}</h3>
                    <button onclick="this.closest('#schedule-detail-modal').remove()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-3">
                    ${schedule.course_code ? `<div class="schedule-detail-row"><span class="schedule-detail-label">Mã môn:</span><span class="schedule-detail-value font-semibold">${schedule.course_code}</span></div>` : ''}
                    <div class="schedule-detail-row"><span class="schedule-detail-label">Ngày:</span><span class="schedule-detail-value">${dayLabels[schedule.day_of_week]}</span></div>
                    <div class="schedule-detail-row"><span class="schedule-detail-label">Buổi:</span><span class="schedule-detail-value">${sessionLabels[schedule.session] || '-'}</span></div>
                    <div class="schedule-detail-row"><span class="schedule-detail-label">Giờ học:</span><span class="schedule-detail-value">${formatTime(schedule.start_time)} - ${formatTime(schedule.end_time)}</span></div>
                    ${schedule.group_name ? `<div class="schedule-detail-row"><span class="schedule-detail-label">Nhóm:</span><span class="schedule-detail-value">${schedule.group_name}</span></div>` : ''}
                    ${schedule.class_name ? `<div class="schedule-detail-row"><span class="schedule-detail-label">Lớp:</span><span class="schedule-detail-value">${schedule.class_name}</span></div>` : ''}
                    <div class="schedule-detail-row"><span class="schedule-detail-label">Phòng:</span><span class="schedule-detail-value font-semibold">${schedule.is_online ? 'Online' : (schedule.room || '-')}</span></div>
                    ${schedule.teacher_name ? `<div class="schedule-detail-row"><span class="schedule-detail-label">Giảng viên:</span><span class="schedule-detail-value">${schedule.teacher_name}</span></div>` : ''}
                    ${schedule.teacher_email ? `<div class="schedule-detail-row"><span class="schedule-detail-label">Email GV:</span><span class="schedule-detail-value text-blue-600">${schedule.teacher_email}</span></div>` : ''}
                    ${schedule.is_online && schedule.meeting_link ? `
                        <div class="schedule-detail-row">
                            <span class="schedule-detail-label">Link học:</span>
                            <span class="schedule-detail-value">
                                <a href="${schedule.meeting_link}" target="_blank" class="text-blue-600 hover:underline">Tham gia lớp học</a>
                            </span>
                        </div>
                    ` : ''}
                </div>
                
                <div class="mt-6 pt-4 border-t flex justify-end">
                    <button onclick="this.closest('#schedule-detail-modal').remove()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
};

// Render schedule (thời khóa biểu)
async function renderSchedule() {
    const tbody = document.getElementById('schedule-tbody');
    
    try {
        // Initialize filters
        populateWeekSelector();
        updateDateHeaders();
        initScheduleFilters();
        
        const result = await profileService.getSchedule();
        if (!result.success || !result.schedules?.length) {
            // Show empty timetable
            renderTimetableGrid([]);
            return;
        }

        allSchedules = result.schedules;
        
        // Filter by academic year and semester
        const year = document.getElementById('schedule-year')?.value || '2025-2026';
        const semester = document.getElementById('schedule-semester')?.value || '2';
        
        const filteredSchedules = allSchedules.filter(s => {
            const matchYear = !s.academic_year || s.academic_year === year;
            const matchSemester = !s.semester || s.semester == semester;
            return matchYear && matchSemester;
        });
        
        renderTimetableGrid(filteredSchedules);
        
    } catch (error) {
        console.error('Error loading schedule:', error);
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-red-500">Lỗi tải thời khóa biểu</td></tr>';
        }
    }
}

// Initialize schedule filters
function initScheduleFilters() {
    // Week navigation
    document.getElementById('schedule-prev-week')?.addEventListener('click', () => {
        currentScheduleWeek.setDate(currentScheduleWeek.getDate() - 7);
        updateDateHeaders();
        updateWeekSelector();
    });
    
    document.getElementById('schedule-next-week')?.addEventListener('click', () => {
        currentScheduleWeek.setDate(currentScheduleWeek.getDate() + 7);
        updateDateHeaders();
        updateWeekSelector();
    });
    
    document.getElementById('schedule-current-week')?.addEventListener('click', () => {
        currentScheduleWeek = new Date();
        updateDateHeaders();
        updateWeekSelector();
    });
    
    // Week selector change
    document.getElementById('schedule-week')?.addEventListener('change', (e) => {
        currentScheduleWeek = new Date(e.target.value);
        updateDateHeaders();
    });
    
    // Year/Semester filter change
    document.getElementById('schedule-year')?.addEventListener('change', renderSchedule);
    document.getElementById('schedule-semester')?.addEventListener('change', renderSchedule);
    
    // Print button
    document.getElementById('schedule-print-btn')?.addEventListener('click', () => {
        window.print();
    });
    
    // Detail button
    document.getElementById('schedule-detail-btn')?.addEventListener('click', () => {
        showAllSchedulesList();
    });
}

// Update week selector to match current week
function updateWeekSelector() {
    const weekSelect = document.getElementById('schedule-week');
    if (!weekSelect) return;
    
    const weekDates = getWeekDates(currentScheduleWeek);
    const mondayStr = weekDates.monday.toISOString().split('T')[0];
    
    // Find and select matching option
    for (const option of weekSelect.options) {
        if (option.value === mondayStr) {
            option.selected = true;
            break;
        }
    }
}

// Show all schedules in a list modal
function showAllSchedulesList() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4';
    modal.id = 'schedule-list-modal';
    modal.onclick = (e) => {
        if (e.target === modal) modal.remove();
    };
    
    const schedulesList = allSchedules.map(s => `
        <tr class="hover:bg-gray-50 cursor-pointer" onclick="document.getElementById('schedule-list-modal').remove(); showScheduleDetail(${JSON.stringify(s).replace(/"/g, '&quot;')})">
            <td class="px-4 py-3">
                <span class="inline-block w-3 h-3 rounded mr-2" style="background: ${s.color || '#1e40af'}"></span>
                ${dayLabels[s.day_of_week]}
            </td>
            <td class="px-4 py-3">${formatTime(s.start_time)} - ${formatTime(s.end_time)}</td>
            <td class="px-4 py-3 font-medium">${s.title}</td>
            <td class="px-4 py-3">${s.course_code || '-'}</td>
            <td class="px-4 py-3">${s.room || (s.is_online ? '🖥️ Online' : '-')}</td>
            <td class="px-4 py-3">${s.teacher_name || '-'}</td>
        </tr>
    `).join('');
    
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[80vh] overflow-hidden">
            <div class="p-4 bg-blue-800 text-white flex items-center justify-between">
                <h3 class="text-lg font-bold">Thời khóa biểu chi tiết</h3>
                <button onclick="this.closest('#schedule-list-modal').remove()" class="text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="overflow-auto max-h-[calc(80vh-80px)]">
                <table class="w-full">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Ngày</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Giờ</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Nội dung</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Mã môn</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Phòng</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Giảng viên</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        ${schedulesList || '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Chưa có lịch học</td></tr>'}
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
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
        case 'courses':
            renderCourses();
            break;
        case 'scores':
            renderScores();
            renderScoresCharts();
            break;
        case 'progress':
            renderProgress();
            break;
        case 'schedule':
            renderSchedule();
            break;
        case 'feedback':
            renderFeedback();
            break;
    }
}

// Edit profile
function initProfileEdit() {
    const form = document.getElementById('profile-form');
    const editBtn = document.getElementById('edit-profile-btn');
    const cancelBtn = document.getElementById('cancel-edit-btn');
    const actions = document.getElementById('profile-actions');
    const inputs = form.querySelectorAll('input, select');

    editBtn?.addEventListener('click', () => {
        inputs.forEach(input => {
            if (input.id !== 'profile-email') {
                input.disabled = false;
            }
        });
        actions.classList.remove('hidden');
        editBtn.classList.add('hidden');
    });

    cancelBtn?.addEventListener('click', () => {
        inputs.forEach(input => input.disabled = true);
        actions.classList.add('hidden');
        editBtn.classList.remove('hidden');
        // Reload profile data
        initPage();
    });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        try {
            const result = await profileService.updateProfile({
                fullname: document.getElementById('profile-fullname').value,
                phone: document.getElementById('profile-phone').value,
                date_of_birth: document.getElementById('profile-dob').value,
                gender: document.getElementById('profile-gender').value,
                address: document.getElementById('profile-address').value
            });

            if (result.success) {
                showToast('Cập nhật thành công!', 'success');
                inputs.forEach(input => input.disabled = true);
                actions.classList.add('hidden');
                editBtn.classList.remove('hidden');
                
                // Update sidebar
                const profile = await profileService.getProfile();
                if (profile.success) {
                    updateSidebarInfo(profile.profile);
                }
            } else {
                showToast(result.error || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            showToast('Lỗi kết nối', 'error');
        }
    });
}

// Change password
function initPasswordForm() {
    const form = document.getElementById('password-form');
    
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const current = document.getElementById('current-password').value;
        const newPass = document.getElementById('new-password').value;
        const confirm = document.getElementById('confirm-password').value;

        if (newPass !== confirm) {
            showToast('Mật khẩu xác nhận không khớp', 'error');
            return;
        }

        if (newPass.length < 6) {
            showToast('Mật khẩu phải có ít nhất 6 ký tự', 'error');
            return;
        }

        try {
            const result = await profileService.changePassword(current, newPass);
            if (result.success) {
                showToast('Đổi mật khẩu thành công!', 'success');
                form.reset();
            } else {
                showToast(result.error || 'Có lỗi xảy ra', 'error');
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
                await fetch('/hai_au_english/backend/php/auth.php?action=logout', {
                    credentials: 'include'
                });
            } catch (error) {
                // Ignore error
            }
            window.location.href = 'login.html';
        });
    });
}

// Initialize page
async function initPage() {
    const result = await checkAuth();
    if (!result || !result.user) return;

    const profile = result.user;

    // Nếu là admin, chuyển hướng sang trang admin
    if (profile.role === 'admin') {
        window.location.href = 'admin.html';
        return;
    }

    updateSidebarInfo(profile);
    fillProfileForm(profile);
    
    // Load dashboard
    renderDashboard();
}

// Start
document.addEventListener('DOMContentLoaded', () => {
    initPage();
    initSidebar();
    initProfileEdit();
    initPasswordForm();
    initLogout();
});

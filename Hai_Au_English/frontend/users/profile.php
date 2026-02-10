<?php
$pageTitle = 'Hồ sơ học viên - Hải Âu English';
$currentPage = 'profile';
$additionalCss = ['/frontend/css/pages/profile.css'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" href="/frontend/assets/images/favicon.jpg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/frontend/css/styles.css">
    <link rel="stylesheet" href="/frontend/css/pages/profile.css">
</head>
<body class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="sticky top-0 left-0 right-0 bg-white/95 backdrop-blur-sm shadow-sm z-50">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="/TrangChu" class="inline-block">
                        <img src="/frontend/assets/images/logo.png" alt="logo" class="h-20 object-contain hover:opacity-80 transition-opacity">
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <a href="/TrangChu" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                        Trang chủ
                    </a>
                    <a href="/KhoaHoc" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                        Khóa học
                    </a>
                    <a href="/LienHe" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                        Liên hệ
                    </a>
                </nav>

                <!-- User Info -->
                <div class="flex items-center gap-4">
                    <div id="header-avatar" class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold overflow-hidden">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <span id="header-username" class="text-gray-700 font-medium hidden md:block">Học viên</span>
                    <button id="logout-btn" class="text-red-600 hover:text-red-700 font-medium transition-colors">
                        Đăng xuất
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Sidebar -->
    <aside class="profile-sidebar" id="sidebar">
        <!-- Close Button (Mobile) -->
        <button class="sidebar-close-btn" id="sidebar-close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- User Info -->
        <div class="sidebar-user-info">
            <div id="sidebar-avatar" class="sidebar-avatar-container">
                <svg class="sidebar-avatar-icon" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <div class="sidebar-user-text">
                <h3 id="sidebar-name" class="sidebar-username">Học viên</h3>
                <p id="sidebar-email" class="sidebar-role">email@example.com</p>
            </div>
        </div>

        <!-- Menu -->
        <nav class="sidebar-menu">
            <p class="sidebar-section-title">Tổng quan</p>
            
            <div class="sidebar-menu-item active" data-section="dashboard">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Trang chính</span>
            </div>

            <p class="sidebar-section-title">Học tập</p>

            <div class="sidebar-menu-item" data-section="courses">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span>Khóa học đã đăng ký</span>
            </div>

            <div class="sidebar-menu-item" data-section="scores">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Điểm số IELTS</span>
            </div>

            <div class="sidebar-menu-item" data-section="progress">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>Tiến độ học tập</span>
            </div>

            <div class="sidebar-menu-item" data-section="schedule">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Thời khóa biểu</span>
            </div>

            <div class="sidebar-menu-item" data-section="feedback">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span>Nhận xét từ giảng viên</span>
            </div>

            <p class="sidebar-section-title">Tài khoản</p>

            <div class="sidebar-menu-item" data-section="profile">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Thông tin cá nhân</span>
            </div>

            <div class="sidebar-menu-item" data-section="password">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Đổi mật khẩu</span>
            </div>
        </nav>

        <!-- Logout -->
        <button class="sidebar-logout" id="sidebar-logout">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span>Đăng xuất</span>
        </button>
    </aside>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebar-toggle">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <!-- Main Content -->
    <main class="profile-content pt-16">
        <!-- Dashboard Section -->
        <section id="section-dashboard" class="content-section active">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Chào mừng trở lại, <span id="welcome-name">Học viên</span>!</h2>
                </div>
                
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div class="stat-card-value" id="stat-courses">0</div>
                        <div class="stat-card-label">Khóa học đang học</div>
                    </div>
                    
                    <div class="stat-card green">
                        <div class="stat-card-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <div class="stat-card-value" id="stat-completed">0</div>
                        <div class="stat-card-label">Khóa học hoàn thành</div>
                    </div>
                    
                    <div class="stat-card purple">
                        <div class="stat-card-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <div class="stat-card-value" id="stat-score">0.0</div>
                        <div class="stat-card-label">Điểm IELTS cao nhất</div>
                    </div>
                    
                    <div class="stat-card orange">
                        <div class="stat-card-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div class="stat-card-value" id="stat-progress">0%</div>
                        <div class="stat-card-label">Tiến độ trung bình</div>
                    </div>
                </div>

                <!-- Recent Scores -->
                <div class="profile-card-header" style="margin-top: 24px;">
                    <h3 class="profile-card-title text-lg">Điểm số gần đây</h3>
                </div>
                <div id="recent-scores-container">
                    <div class="spinner"></div>
                </div>
            </div>
        </section>

        <!-- Courses Section -->
        <section id="section-courses" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Khóa học đã đăng ký</h2>
                </div>
                <div id="courses-container">
                    <div class="spinner"></div>
                </div>
            </div>
        </section>

        <!-- Scores Section -->
        <section id="section-scores" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Điểm số IELTS</h2>
                </div>
                
                <!-- Charts Row -->
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <!-- Line/Bar Chart - Tiến trình điểm theo thời gian -->
                    <div class="chart-container bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Tiến trình điểm số</h3>
                        <div style="position: relative; height: 220px;">
                            <canvas id="scores-line-chart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Pie Chart - Phân bổ điểm các kỹ năng -->
                    <div class="chart-container bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Phân bổ điểm trung bình</h3>
                        <div style="position: relative; height: 220px;">
                            <canvas id="scores-pie-chart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Scores Table -->
                <div class="overflow-x-auto">
                    <table class="profile-table" id="scores-table">
                        <thead>
                            <tr>
                                <th>Ngày thi</th>
                                <th>Loại</th>
                                <th>Listening</th>
                                <th>Reading</th>
                                <th>Writing</th>
                                <th>Speaking</th>
                                <th>Overall</th>
                            </tr>
                        </thead>
                        <tbody id="scores-tbody">
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Progress Section -->
        <section id="section-progress" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Tiến độ học tập</h2>
                </div>
                <div id="progress-container">
                    <div class="spinner"></div>
                </div>
            </div>
        </section>

        <!-- Schedule Section (Thời khóa biểu) -->
        <section id="section-schedule" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header flex flex-wrap items-center justify-between gap-4">
                    <h2 class="profile-card-title flex items-center gap-2">
                        <span class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        Thời khóa biểu
                    </h2>
                </div>
                
                <!-- Schedule Filters -->
                <div class="schedule-filters bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Năm học -->
                        <div class="filter-group">
                            <label class="text-xs text-gray-500 block mb-1">Năm học</label>
                            <select id="schedule-year" class="schedule-filter-select">
                                <option value="2025-2026" selected>2025-2026</option>
                                <option value="2024-2025">2024-2025</option>
                                <option value="2023-2024">2023-2024</option>
                            </select>
                        </div>
                        
                        <!-- Học kỳ -->
                        <div class="filter-group">
                            <label class="text-xs text-gray-500 block mb-1">Học kỳ</label>
                            <select id="schedule-semester" class="schedule-filter-select">
                                <option value="1">Học kỳ 1</option>
                                <option value="2" selected>Học kỳ 2</option>
                                <option value="3">Học kỳ 3 (Hè)</option>
                            </select>
                        </div>
                        
                        <!-- Tuần -->
                        <div class="filter-group flex-1 min-w-[200px]">
                            <label class="text-xs text-gray-500 block mb-1">Tuần</label>
                            <select id="schedule-week" class="schedule-filter-select w-full">
                                <!-- Populated by JS -->
                            </select>
                        </div>
                        
                        <!-- Navigation Buttons -->
                        <div class="flex items-end gap-2">
                            <button id="schedule-prev-week" class="schedule-nav-btn" title="Tuần trước">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button id="schedule-current-week" class="schedule-nav-btn primary" title="Tuần hiện tại">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                Hiện tại
                            </button>
                            <button id="schedule-next-week" class="schedule-nav-btn" title="Tuần sau">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200">
                        <button id="schedule-detail-btn" class="schedule-action-btn">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Xem chi tiết
                        </button>
                        <button id="schedule-print-btn" class="schedule-action-btn">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            In lịch
                        </button>
                    </div>
                </div>
                
                <!-- Timetable Grid -->
                <div class="schedule-timetable-wrapper overflow-x-auto">
                    <table class="schedule-timetable" id="schedule-timetable">
                        <thead>
                            <tr>
                                <th class="period-col">Tiết</th>
                                <th class="day-col" data-day="monday">
                                    <div class="day-name">Thứ 2</div>
                                    <div class="day-date" id="date-monday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="tuesday">
                                    <div class="day-name">Thứ 3</div>
                                    <div class="day-date" id="date-tuesday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="wednesday">
                                    <div class="day-name">Thứ 4</div>
                                    <div class="day-date" id="date-wednesday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="thursday">
                                    <div class="day-name">Thứ 5</div>
                                    <div class="day-date" id="date-thursday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="friday">
                                    <div class="day-name">Thứ 6</div>
                                    <div class="day-date" id="date-friday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="saturday">
                                    <div class="day-name">Thứ 7</div>
                                    <div class="day-date" id="date-saturday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="sunday">
                                    <div class="day-name">CN</div>
                                    <div class="day-date" id="date-sunday">--/--/----</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="schedule-tbody">
                            <!-- Rows generated by JS -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Legend -->
                <div class="schedule-legend mt-4 flex flex-wrap gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded" style="background: #1e40af"></span>
                        <span>Lớp học offline</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded" style="background: #059669"></span>
                        <span>Lớp học online</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feedback Section -->
        <section id="section-feedback" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Nhận xét từ giảng viên</h2>
                </div>
                <div id="feedback-container">
                    <div class="spinner"></div>
                </div>
            </div>
        </section>

        <!-- Profile Section -->
        <section id="section-profile" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Thông tin cá nhân</h2>
                    <button id="edit-profile-btn" class="admin-action-btn primary">Chỉnh sửa</button>
                </div>
                
                <!-- Avatar Upload Section -->
                <div class="avatar-upload-section">
                    <div class="avatar-preview" id="avatar-preview">
                        <svg class="avatar-placeholder-icon" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <div class="avatar-upload-controls">
                        <input type="file" id="avatar-input" accept="image/*" hidden>
                        <button type="button" id="avatar-upload-btn" class="avatar-btn primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Thay đổi ảnh đại diện
                        </button>
                        <p class="avatar-hint">JPG, PNG, GIF hoặc WebP. Tối đa 5MB.</p>
                    </div>
                </div>
                
                <form id="profile-form">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="profile-form-group">
                            <label class="profile-form-label">Họ và tên</label>
                            <input type="text" id="profile-fullname" class="profile-form-input" disabled>
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Email</label>
                            <input type="email" id="profile-email" class="profile-form-input" disabled>
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Số điện thoại</label>
                            <input type="tel" id="profile-phone" class="profile-form-input" disabled>
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Ngày sinh</label>
                            <input type="date" id="profile-dob" class="profile-form-input" disabled>
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Giới tính</label>
                            <select id="profile-gender" class="profile-form-input" disabled>
                                <option value="">-- Chọn --</option>
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Địa chỉ</label>
                            <input type="text" id="profile-address" class="profile-form-input" disabled>
                        </div>
                    </div>
                    <div id="profile-actions" class="hidden mt-6 flex gap-4">
                        <button type="submit" class="admin-action-btn primary">Lưu thay đổi</button>
                        <button type="button" id="cancel-edit-btn" class="admin-action-btn secondary">Hủy</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Password Section -->
        <section id="section-password" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Đổi mật khẩu</h2>
                </div>
                <form id="password-form" class="max-w-md">
                    <div class="profile-form-group">
                        <label class="profile-form-label">Mật khẩu hiện tại <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="current-password" class="profile-form-input pr-10" required>
                            <button type="button" class="toggle-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" data-target="current-password">
                                <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="profile-form-group">
                        <label class="profile-form-label">Mật khẩu mới <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="new-password" class="profile-form-input pr-10" required minlength="6">
                            <button type="button" class="toggle-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" data-target="new-password">
                                <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Password strength indicator -->
                        <div id="password-strength" class="mt-2 hidden">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div id="strength-bar" class="h-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <span id="strength-text" class="text-xs font-medium"></span>
                            </div>
                            <ul id="password-requirements" class="mt-2 text-xs space-y-1">
                                <li id="req-length" class="flex items-center gap-1 text-gray-500">
                                    <svg class="w-4 h-4 check-icon hidden text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <svg class="w-4 h-4 x-icon text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    Ít nhất 6 ký tự
                                </li>
                                <li id="req-uppercase" class="flex items-center gap-1 text-gray-500">
                                    <svg class="w-4 h-4 check-icon hidden text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <svg class="w-4 h-4 x-icon text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    Có chữ hoa (A-Z)
                                </li>
                                <li id="req-number" class="flex items-center gap-1 text-gray-500">
                                    <svg class="w-4 h-4 check-icon hidden text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <svg class="w-4 h-4 x-icon text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    Có số (0-9)
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="profile-form-group">
                        <label class="profile-form-label">Xác nhận mật khẩu mới <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" id="confirm-password" class="profile-form-input pr-10" required>
                            <button type="button" class="toggle-password absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" data-target="confirm-password">
                                <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p id="password-match-error" class="text-red-500 text-sm mt-1 hidden">
                            <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            Mật khẩu xác nhận không khớp!
                        </p>
                        <p id="password-match-success" class="text-green-500 text-sm mt-1 hidden">
                            <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Mật khẩu khớp!
                        </p>
                    </div>
                    <button type="submit" id="change-password-btn" class="admin-action-btn primary">
                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Đổi mật khẩu
                    </button>
                </form>
            </div>
        </section>
    </main>

    <!-- Password Change Confirmation Modal -->
    <div id="password-confirm-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4 shadow-2xl transform transition-all">
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Xác nhận đổi mật khẩu</h3>
                <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn đổi mật khẩu? Sau khi đổi, bạn sẽ cần sử dụng mật khẩu mới để đăng nhập.</p>
                <div class="flex gap-3 justify-center">
                    <button id="cancel-password-change" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Hủy bỏ
                    </button>
                    <button id="confirm-password-change" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>

    <script src="/frontend/js/ui/toast.js"></script>
    <script type="module" src="/frontend/js/controllers/profile.js"></script>
</body>
</html>

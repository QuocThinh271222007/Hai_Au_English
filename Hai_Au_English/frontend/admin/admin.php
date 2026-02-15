<?php
require_once __DIR__ . '/../components/base_config.php';
require_once __DIR__ . '/../../backend/php/session_config.php';

// Check if user is logged in and is admin
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Redirect to login if not admin
if (!$isLoggedIn || !$isAdmin) {
    header('Location: ' . $basePath . '/DangNhap');
    exit;
}

$pageTitle = 'Admin Dashboard - Hải Âu English';
$currentPage = 'admin';
$additionalCss = ['/frontend/css/pages/profile.css'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" href="<?php echo $assetsPath; ?>/assets/images/favicon.jpg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <link rel="stylesheet" href="<?php echo $assetsPath; ?>/css/styles.css">
    <link rel="stylesheet" href="<?php echo $assetsPath; ?>/css/pages/profile.css">
</head>

<body class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white/95 backdrop-blur-sm shadow-sm z-50">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="<?php echo $paths['home']; ?>" class="inline-block">
                        <img src="<?php echo $assetsPath; ?>/assets/images/logo.png" alt="logo"
                            class="h-20 object-contain hover:opacity-80 transition-opacity">
                    </a>
                </div>

                <!-- Title -->
                <h1 class="text-xl font-bold text-blue-600">Admin Dashboard</h1>

                <!-- User Info -->
                <div class="flex items-center gap-4">
                    <!-- Notification Bell -->
                    <div class="relative" id="notification-container">
                        <button id="notification-btn"
                            class="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-full transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span id="notification-badge"
                                class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">0</span>
                        </button>

                        <!-- Notification Dropdown -->
                        <div id="notification-dropdown"
                            class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-96 overflow-hidden">
                            <div class="p-3 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                <h3 class="font-semibold text-gray-800">Thông báo</h3>
                                <button id="mark-all-read-btn"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">Đánh dấu đã
                                    đọc</button>
                            </div>
                            <div id="notification-list" class="overflow-y-auto max-h-72">
                                <div class="p-4 text-center text-gray-500">Đang tải...</div>
                            </div>
                            <div class="p-2 border-t border-gray-200 bg-gray-50 text-center">
                                <button id="view-all-notifications"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">Xem tất cả thông
                                    báo</button>
                            </div>
                        </div>
                    </div>

                    <div id="header-avatar"
                        class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold overflow-hidden">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                    </div>
                    <span id="header-username" class="text-gray-700 font-medium hidden md:block">Admin</span>
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
    <aside class="profile-sidebar" id="sidebar"
        style="background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #334155 100%);">
        <!-- Close Button (Mobile) -->
        <button class="sidebar-close-btn" id="sidebar-close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Admin Info -->
        <div class="sidebar-user-info">
            <div class="sidebar-avatar-container">
                <svg class="sidebar-avatar-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                </svg>
            </div>
            <div class="sidebar-user-text">
                <h3 id="sidebar-name" class="sidebar-username">Admin</h3>
                <p class="sidebar-role">Quản trị viên</p>
            </div>
        </div>

        <!-- Menu -->
        <nav class="sidebar-menu">
            <p class="sidebar-section-title">Tổng quan</p>

            <div class="sidebar-menu-item active" data-section="dashboard">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
                <span>Dashboard</span>
            </div>

            <p class="sidebar-section-title">Quản lý</p>

            <div class="sidebar-menu-item" data-section="users">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Quản lý học viên</span>
            </div>

            <div class="sidebar-menu-item" data-section="enrollments">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>Đăng ký khóa học</span>
            </div>

            <div class="sidebar-menu-item" data-section="courses">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Quản lý khóa học</span>
            </div>

            <div class="sidebar-menu-item" data-section="classes">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Quản lý lớp học</span>
            </div>

            <div class="sidebar-menu-item" data-section="teachers">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <span>Quản lý giảng viên</span>
            </div>

            <div class="sidebar-menu-item" data-section="scores">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Quản lý điểm số</span>
            </div>

            <div class="sidebar-menu-item" data-section="feedback">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span>Nhận xét học viên</span>
            </div>

            <div class="sidebar-menu-item" data-section="achievements">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                <span>Thành tích học viên</span>
            </div>

            <div class="sidebar-menu-item" data-section="reviews">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span>Quản lý đánh giá</span>
            </div>

            <div class="sidebar-menu-item" data-section="teacher-reviews">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
                <span>Đánh giá giảng viên</span>
            </div>

            <div class="sidebar-menu-item" data-section="course-fees">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Bảng học phí</span>
            </div>

            <div class="sidebar-menu-item" data-section="schedule">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Thời khóa biểu</span>
            </div>

            <p class="sidebar-section-title">Hệ thống</p>

            <div class="sidebar-menu-item" data-section="notifications">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span>Thông báo</span>
                <span id="notifications-badge" class="trash-badge hidden">0</span>
            </div>

            <div class="sidebar-menu-item" data-section="trash">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Thùng rác</span>
                <span id="trash-badge" class="trash-badge hidden">0</span>
            </div>

            <p class="sidebar-section-title">Nội dung Website</p>

            <div class="sidebar-menu-item" data-section="content">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Quản lý nội dung</span>
            </div>

            <div class="sidebar-menu-item" data-section="recruitment">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Tuyển dụng</span>
            </div>

            <p class="sidebar-section-title">Cài đặt</p>

            <div class="sidebar-menu-item" data-section="settings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Cài đặt hệ thống</span>
            </div>
        </nav>

        <!-- Logout -->
        <button class="sidebar-logout" id="sidebar-logout">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Đăng xuất</span>
        </button>
    </aside>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebar-toggle">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Main Content -->
    <main class="profile-content pt-16">
        <!-- Dashboard Section -->
        <section id="section-dashboard" class="content-section active">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Thống kê tổng quan</h2>
                </div>

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="stat-card-value" id="stat-users">0</div>
                        <div class="stat-card-label">Tổng học viên</div>
                    </div>

                    <div class="stat-card green">
                        <div class="stat-card-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div class="stat-card-value" id="stat-enrollments">0</div>
                        <div class="stat-card-label">Đăng ký đang học</div>
                    </div>

                    <div class="stat-card purple">
                        <div class="stat-card-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="stat-card-value" id="stat-courses">0</div>
                        <div class="stat-card-label">Khóa học</div>
                    </div>

                    <div class="stat-card orange">
                        <div class="stat-card-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                        </div>
                        <div class="stat-card-value" id="stat-teachers">0</div>
                        <div class="stat-card-label">Giảng viên</div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="profile-card-header"
                    style="margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 24px;">
                    <h3 class="profile-card-title text-lg">Biểu đồ phân tích</h3>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
                    <!-- Enrollment Status Pie Chart -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Trạng thái đăng ký</h4>
                        <div class="h-64">
                            <canvas id="enrollmentPieChart"></canvas>
                        </div>
                    </div>

                    <!-- Course Distribution Pie Chart -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Phân bổ khóa học</h4>
                        <div class="h-64">
                            <canvas id="coursePieChart"></canvas>
                        </div>
                    </div>

                    <!-- Monthly Enrollments Line + Bar Chart -->
                    <div class="bg-white rounded-lg shadow p-4 lg:col-span-2">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Đăng ký theo tháng</h4>
                        <div class="h-80">
                            <canvas id="monthlyEnrollmentChart"></canvas>
                        </div>
                    </div>

                    <!-- Score Distribution Bar Chart -->
                    <div class="bg-white rounded-lg shadow p-4 lg:col-span-2">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Phân bổ điểm số học viên</h4>
                        <div class="h-80">
                            <canvas id="scoreDistributionChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Enrollments -->
                <div class="profile-card-header"
                    style="margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 24px;">
                    <h3 class="profile-card-title text-lg">Đăng ký gần đây</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>Học viên</th>
                                <th>Email</th>
                                <th>Khóa học</th>
                                <th>Trạng thái</th>
                                <th>Ngày đăng ký</th>
                            </tr>
                        </thead>
                        <tbody id="recent-enrollments-tbody">
                            <tr>
                                <td colspan="5" class="text-center py-8">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Users Section -->
        <section id="section-users" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Quản lý học viên</h2>
                    <button id="add-user-btn" class="admin-action-btn primary">+ Thêm học viên</button>
                </div>
                <!-- Search Bar -->
                <div class="search-bar-container mb-4">
                    <div class="relative">
                        <input type="text" id="search-users" class="profile-form-input pl-10"
                            placeholder="Tìm kiếm theo tên, email, SĐT hoặc mã số học viên...">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button id="clear-search-users"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th style="min-width: 200px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="users-tbody">
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

        <!-- Enrollments Section -->
        <section id="section-enrollments" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Quản lý đăng ký khóa học</h2>
                </div>

                <!-- Info Banner -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-blue-700 text-sm">
                            Học viên tự đăng ký khóa học từ trang Khóa học. Click vào từng khóa để xem danh sách học
                            viên đã đăng ký.
                        </p>
                    </div>
                </div>

                <!-- Filter -->
                <div class="flex flex-wrap gap-4 mb-4">
                    <select id="enrollment-course-status-filter" class="profile-form-input" style="max-width: 200px;">
                        <option value="">Tất cả khóa học</option>
                        <option value="open" selected>Đang mở đăng ký</option>
                        <option value="closed">Đã đóng</option>
                    </select>
                    <select id="enrollment-category-filter" class="profile-form-input" style="max-width: 200px;">
                        <option value="">Tất cả danh mục</option>
                        <option value="tieuhoc">Tiểu học</option>
                        <option value="thcs">THCS</option>
                        <option value="ielts">IELTS</option>
                    </select>
                </div>

                <!-- Courses Grid -->
                <div id="enrollment-courses-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="col-span-full text-center py-8">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>

            <!-- Enrolled Students Modal -->
            <div id="enrolled-students-modal" class="admin-modal hidden">
                <div class="admin-modal-content" style="max-width: 800px;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800" id="enrolled-modal-title">Danh sách học viên đăng ký
                        </h3>
                        <button class="close-enrolled-modal text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Course Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-4">
                            <img id="enrolled-course-image" src="" alt="" class="w-20 h-14 object-cover rounded">
                            <div>
                                <h4 id="enrolled-course-name" class="font-semibold text-gray-800"></h4>
                                <p id="enrolled-course-info" class="text-sm text-gray-600"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Students Table -->
                    <div class="overflow-x-auto max-h-96">
                        <table class="profile-table w-full">
                            <thead class="sticky top-0 bg-white">
                                <tr>
                                    <th>STT</th>
                                    <th>Học viên</th>
                                    <th>Email</th>
                                    <th>Ngày đăng ký</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="enrolled-students-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Courses Section -->
        <section id="section-courses" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Quản lý khóa học</h2>
                    <button id="add-course-btn" class="admin-action-btn primary">+ Thêm khóa học</button>
                </div>
                <!-- Search Bar and Filter -->
                <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <div class="relative flex-1">
                        <input type="text" id="search-courses" class="profile-form-input pl-10"
                            placeholder="Tìm kiếm theo tên khóa học, giáo trình...">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button id="clear-search-courses"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <select id="filter-courses-category" class="profile-form-input w-auto">
                            <option value="all">Tất cả danh mục</option>
                            <option value="tieuhoc">Tiểu học</option>
                            <option value="thcs">THCS</option>
                            <option value="ielts">IELTS</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>Hình ảnh</th>
                                <th>Tên khóa học</th>
                                <th>Danh mục</th>
                                <th>Giáo trình</th>
                                <th>Thời lượng</th>
                                <th>Học phí</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="courses-tbody">
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

        <!-- Classes Section -->
        <section id="section-classes" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Quản lý lớp học</h2>
                    <button id="add-class-btn" class="admin-action-btn primary">+ Thêm lớp học</button>
                </div>
                <!-- Search Bar and Filter -->
                <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <div class="relative flex-1">
                        <input type="text" id="search-classes" class="profile-form-input pl-10"
                            placeholder="Tìm kiếm theo tên lớp, giảng viên...">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button id="clear-search-classes"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <select id="filter-classes-status" class="profile-form-input w-auto">
                            <option value="all">Tất cả trạng thái</option>
                            <option value="upcoming">Sắp khai giảng</option>
                            <option value="active">Đang học</option>
                            <option value="completed">Đã kết thúc</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                        <select id="filter-classes-course" class="profile-form-input w-auto">
                            <option value="all">Tất cả khóa học</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>Tên lớp</th>
                                <th>Khóa học</th>
                                <th>Giảng viên</th>
                                <th>Lịch học</th>
                                <th>Sĩ số</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="classes-tbody">
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Teachers Section -->
        <section id="section-teachers" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Quản lý giảng viên</h2>
                    <button id="add-teacher-btn" class="admin-action-btn primary">+ Thêm giảng viên</button>
                </div>
                <!-- Search Bar -->
                <div class="search-bar-container mb-4">
                    <div class="relative">
                        <input type="text" id="search-teachers" class="profile-form-input pl-10"
                            placeholder="Tìm kiếm theo tên, chuyên môn hoặc mã số giảng viên...">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button id="clear-search-teachers"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>Họ tên</th>
                                <th>Chuyên môn</th>
                                <th>Kinh nghiệm</th>
                                <!-- TODO: Tạm ẩn IELTS - bật lại khi cần
                                <th>IELTS</th>
                                -->
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="teachers-tbody">
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Scores Section -->
        <section id="section-scores" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Quản lý điểm số</h2>
                    <button id="add-score-btn" class="admin-action-btn primary">+ Thêm điểm</button>
                </div>
                <!-- Search Bar -->
                <div class="search-bar-container mb-4">
                    <div class="relative">
                        <input type="text" id="search-scores" class="profile-form-input pl-10"
                            placeholder="Tìm kiếm theo tên hoặc mã số học viên...">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button id="clear-search-scores"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>Học viên</th>
                                <th>Ngày thi</th>
                                <th>Listening</th>
                                <th>Reading</th>
                                <th>Writing</th>
                                <th>Speaking</th>
                                <th>Overall</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="scores-tbody">
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Feedback Section -->
        <section id="section-feedback" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Nhận xét học viên</h2>
                    <button id="add-feedback-btn" class="admin-action-btn primary">+ Thêm nhận xét</button>
                </div>
                <!-- Search Bar -->
                <div class="search-bar-container mb-4">
                    <div class="relative">
                        <input type="text" id="search-feedback" class="profile-form-input pl-10"
                            placeholder="Tìm kiếm theo tên, mã số học viên hoặc nội dung...">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button id="clear-search-feedback"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="admin-feedback-container">
                    <div class="spinner"></div>
                </div>
            </div>
        </section>

        <!-- Achievements Section (Thành tích học viên) -->
        <section id="section-achievements" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title flex items-center gap-2">
                        <span class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </span>
                        Quản lý thành tích học viên
                    </h2>
                    <button id="add-achievement-btn" class="admin-action-btn primary">+ Thêm thành tích</button>
                </div>

                <!-- Search Bar -->
                <div class="search-bar-container mb-4">
                    <div class="relative">
                        <input type="text" id="search-achievements" class="profile-form-input pl-10"
                            placeholder="Tìm kiếm theo tên học viên, tiêu đề thành tích hoặc mã số...">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button id="clear-search-achievements"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Achievement Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-lg border border-yellow-200">
                        <p class="text-sm text-yellow-700">Tổng thành tích</p>
                        <p id="total-achievements" class="text-2xl font-bold text-yellow-600">0</p>
                    </div>
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg border border-green-200">
                        <p class="text-sm text-green-700">Điểm IELTS cao nhất</p>
                        <p id="highest-ielts" class="text-2xl font-bold text-green-600">0</p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-200">
                        <p class="text-sm text-blue-700">Thêm gần đây</p>
                        <p id="recent-achievements" class="text-2xl font-bold text-blue-600">0</p>
                    </div>
                </div>

                <!-- Achievement Grid -->
                <div id="achievements-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <div class="col-span-full text-center py-8">
                        <div class="spinner"></div>
                    </div>
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
                                <path fill-rule="evenodd"
                                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                        Quản lý thời khóa biểu
                    </h2>
                    <button id="add-schedule-btn" class="admin-action-btn primary">+ Thêm lịch học</button>
                </div>

                <!-- Schedule Filters -->
                <div class="schedule-filters bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Năm học -->
                        <div class="filter-group">
                            <label class="text-xs text-gray-500 block mb-1">Năm học</label>
                            <select id="admin-schedule-year" class="schedule-filter-select">
                                <option value="">Tất cả</option>
                                <option value="2025-2026" selected>2025-2026</option>
                                <option value="2024-2025">2024-2025</option>
                            </select>
                        </div>

                        <!-- Học kỳ -->
                        <div class="filter-group">
                            <label class="text-xs text-gray-500 block mb-1">Học kỳ</label>
                            <select id="admin-schedule-semester" class="schedule-filter-select">
                                <option value="">Tất cả</option>
                                <option value="1">Học kỳ 1</option>
                                <option value="2" selected>Học kỳ 2</option>
                                <option value="3">Học kỳ 3</option>
                            </select>
                        </div>

                        <!-- Ngày -->
                        <div class="filter-group">
                            <label class="text-xs text-gray-500 block mb-1">Ngày</label>
                            <select id="admin-schedule-day" class="schedule-filter-select">
                                <option value="">Tất cả các ngày</option>
                                <option value="monday">Thứ Hai</option>
                                <option value="tuesday">Thứ Ba</option>
                                <option value="wednesday">Thứ Tư</option>
                                <option value="thursday">Thứ Năm</option>
                                <option value="friday">Thứ Sáu</option>
                                <option value="saturday">Thứ Bảy</option>
                                <option value="sunday">Chủ Nhật</option>
                            </select>
                        </div>

                        <!-- Tuần -->
                        <div class="filter-group flex-1 min-w-[200px]">
                            <label class="text-xs text-gray-500 block mb-1">Tuần</label>
                            <select id="admin-schedule-week" class="schedule-filter-select w-full">
                                <!-- Populated by JS -->
                            </select>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex items-end gap-2">
                            <button id="admin-schedule-prev-week" class="schedule-nav-btn" title="Tuần trước">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button id="admin-schedule-current-week" class="schedule-nav-btn primary"
                                title="Tuần hiện tại">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                Hiện tại
                            </button>
                            <button id="admin-schedule-next-week" class="schedule-nav-btn" title="Tuần sau">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Timetable Grid -->
                <div class="schedule-timetable-wrapper overflow-x-auto">
                    <table class="schedule-timetable" id="admin-schedule-timetable">
                        <thead>
                            <tr>
                                <th class="period-col">Tiết</th>
                                <th class="day-col" data-day="monday">
                                    <div class="day-name">Thứ 2</div>
                                    <div class="day-date" id="admin-date-monday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="tuesday">
                                    <div class="day-name">Thứ 3</div>
                                    <div class="day-date" id="admin-date-tuesday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="wednesday">
                                    <div class="day-name">Thứ 4</div>
                                    <div class="day-date" id="admin-date-wednesday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="thursday">
                                    <div class="day-name">Thứ 5</div>
                                    <div class="day-date" id="admin-date-thursday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="friday">
                                    <div class="day-name">Thứ 6</div>
                                    <div class="day-date" id="admin-date-friday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="saturday">
                                    <div class="day-name">Thứ 7</div>
                                    <div class="day-date" id="admin-date-saturday">--/--/----</div>
                                </th>
                                <th class="day-col" data-day="sunday">
                                    <div class="day-name">CN</div>
                                    <div class="day-date" id="admin-date-sunday">--/--/----</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="admin-schedule-tbody">
                            <!-- Rows generated by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Schedule List Table -->
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Danh sách lịch học</h3>
                    <!-- Search Bar -->
                    <div class="search-bar-container mb-4">
                        <div class="relative">
                            <input type="text" id="search-schedules" class="profile-form-input pl-10"
                                placeholder="Tìm kiếm theo tên học viên, khóa học, giáo viên hoặc mã số...">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button id="clear-search-schedules"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="profile-table" id="schedule-list-table">
                            <thead>
                                <tr>
                                    <th>Học viên</th>
                                    <th>Khóa học</th>
                                    <th>Nội dung</th>
                                    <th>Ngày</th>
                                    <th>Giờ</th>
                                    <th>Phòng</th>
                                    <th>Giảng viên</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="schedule-list-tbody">
                                <tr>
                                    <td colspan="8" class="text-center py-8">
                                        <div class="spinner"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Legend -->
                <div class="schedule-legend mt-4 flex flex-wrap gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded" style="background: #1e40af"></span>
                        <span>Offline</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded" style="background: #059669"></span>
                        <span>Online</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reviews Management Section -->
        <section id="section-reviews" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Quản lý đánh giá</h2>
                    <div class="flex gap-2">
                        <select id="reviews-filter" class="profile-form-input" style="max-width: 200px;">
                            <option value="">Tất cả</option>
                            <option value="approved">Đã duyệt</option>
                            <option value="pending">Chờ duyệt</option>
                        </select>
                    </div>
                </div>
                <!-- Search Bar -->
                <div class="search-bar-container mb-4">
                    <div class="relative">
                        <input type="text" id="search-reviews" class="profile-form-input pl-10"
                            placeholder="Tìm kiếm theo tên học viên, nội dung đánh giá hoặc mã số...">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button id="clear-search-reviews"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Học viên</th>
                                <th>Avatar</th>
                                <th>Đánh giá</th>
                                <th>Nhận xét</th>
                                <th>Ảnh</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="reviews-tbody">
                            <tr>
                                <td colspan="9" class="text-center py-8">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Settings Section -->
        <section id="section-settings" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Cài đặt hệ thống</h2>
                </div>

                <!-- Avatar Upload Section -->
                <div class="profile-form-group mb-6">
                    <label class="profile-form-label">Ảnh đại diện Admin</label>
                    <div class="avatar-upload-section">
                        <div class="avatar-preview" id="admin-avatar-preview">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
                            </svg>
                        </div>
                        <div class="avatar-upload-controls">
                            <input type="file" id="admin-avatar-input" accept="image/*" hidden>
                            <button type="button" class="avatar-btn primary" id="admin-avatar-upload-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Đổi ảnh đại diện
                            </button>
                            <p class="avatar-hint">JPG, PNG, GIF hoặc WebP. Tối đa 5MB.</p>
                        </div>
                    </div>
                </div>

                <!-- Password Change Section -->
                <div class="profile-form-group mb-6" style="padding-top: 20px; border-top: 1px solid #e5e7eb;">
                    <label class="profile-form-label">Đổi mật khẩu Admin</label>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm text-gray-500">Mật khẩu hiện tại</label>
                            <input type="password" id="admin-current-password" class="profile-form-input"
                                placeholder="••••••••">
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Mật khẩu mới</label>
                            <input type="password" id="admin-new-password" class="profile-form-input"
                                placeholder="••••••••">
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Xác nhận mật khẩu mới</label>
                            <input type="password" id="admin-confirm-password" class="profile-form-input"
                                placeholder="••••••••">
                        </div>
                    </div>
                    <button type="button" class="admin-action-btn primary mt-4" id="admin-change-password-btn">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Đổi mật khẩu
                    </button>
                </div>

                <!-- System Settings -->
                <div style="padding-top: 20px; border-top: 1px solid #e5e7eb;">
                    <label class="profile-form-label mb-4">Thông tin cơ bản</label>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="profile-form-group">
                            <label class="profile-form-label">Tên trung tâm</label>
                            <input type="text" class="profile-form-input setting-input" data-key="site_name"
                                placeholder="VD: Trung tâm Ngoại ngữ Giáo dục Anh văn Hải Âu">
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Slogan</label>
                            <input type="text" class="profile-form-input setting-input" data-key="site_slogan"
                                placeholder="VD: Chinh phục IELTS cùng Hải Âu">
                        </div>
                        <div class="profile-form-group md:col-span-2">
                            <label class="profile-form-label">Mô tả trung tâm</label>
                            <textarea class="profile-form-input content-input" data-page="home" data-section="hero"
                                data-key="description" rows="3" placeholder="Mô tả ngắn về trung tâm..."></textarea>

                        </div>
                    </div>

                    <label class="profile-form-label mb-4">Thông tin liên hệ</label>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="profile-form-group">
                            <label class="profile-form-label">Email liên hệ</label>
                            <input type="email" class="profile-form-input setting-input" data-key="contact_email"
                                placeholder="VD: haiauenglish@gmail.com">
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Số điện thoại hotline</label>
                            <input type="tel" class="profile-form-input setting-input" data-key="contact_phone"
                                placeholder="VD: 0931 828 960">
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Số Zalo</label>
                            <input type="tel" class="profile-form-input setting-input" data-key="zalo_phone"
                                placeholder="VD: 0931828960 (không có dấu cách)">
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Giờ làm việc</label>
                            <input type="text" class="profile-form-input setting-input" data-key="working_hours"
                                placeholder="VD: Thứ 2 - Chủ nhật: 8:00 - 21:00">
                        </div>
                        <div class="profile-form-group md:col-span-2">
                            <label class="profile-form-label">Địa chỉ trung tâm</label>
                            <input type="text" class="profile-form-input setting-input" data-key="contact_address"
                                placeholder="VD: 14/2A Trương Phước Phan, Phường Bình Trị Đông, TP.HCM">
                        </div>
                    </div>

                    <label class="profile-form-label mb-4">Mạng xã hội</label>
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="profile-form-group">
                            <label class="profile-form-label">Link Facebook</label>
                            <input type="url" class="profile-form-input setting-input" data-key="facebook_url"
                                placeholder="VD: https://www.facebook.com/AnhNguHaiAu">
                        </div>
                        <div class="profile-form-group">
                            <label class="profile-form-label">Năm học hiện tại</label>
                            <input type="text" class="profile-form-input setting-input" data-key="academic_year"
                                placeholder="VD: 2025-2026">
                        </div>
                        <div class="profile-form-group md:col-span-2">
                            <label class="profile-form-label">Google Maps Embed URL</label>
                            <input type="text" class="profile-form-input setting-input" data-key="map_embed"
                                placeholder="VD: https://www.google.com/maps/embed?pb=...">
                            <p class="text-xs text-gray-500 mt-1">Lấy từ Google Maps → Share → Embed a map</p>
                        </div>
                    </div>
                </div>
                <button class="admin-action-btn primary mt-4" id="save-settings-btn">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Lưu cài đặt
                </button>
            </div>
        </section>

        <!-- Content Management Section (Quản lý nội dung) -->
        <section id="section-content" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title flex items-center gap-2">
                        <span class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </span>
                        Quản lý nội dung Website
                    </h2>
                </div>

                <div class="alert-info mb-4"
                    style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 8px;">
                    <p class="text-sm text-blue-800">
                        <strong>💡 Hướng dẫn:</strong> Chỉnh sửa nội dung hiển thị trên các trang website. Thay đổi sẽ
                        được áp dụng ngay sau khi lưu.
                    </p>
                </div>

                <!-- Page Tabs -->
                <div class="content-tabs mb-6">
                    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-2">
                        <button class="content-tab-btn active" data-page="home">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Trang chủ
                        </button>
                        <button class="content-tab-btn" data-page="about">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Giới thiệu
                        </button>
                        <button class="content-tab-btn" data-page="courses">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Khóa học
                        </button>
                        <button class="content-tab-btn" data-page="teachers">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            Giảng viên
                        </button>
                        <button class="content-tab-btn" data-page="contact">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Liên hệ
                        </button>
                    </div>
                </div>

                <!-- Content Editor Area -->
                <div id="content-editor-area">
                    <!-- Home Page Content -->
                    <div class="content-page-editor" data-page="home">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📌 Hero Section (Banner
                            chính)</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề chính</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="hero" data-key="title" placeholder="VD: Chinh phục IELTS">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề highlight (màu xanh)</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="hero" data-key="title_highlight" placeholder="VD: 8.0+">
                            </div>
                            <div class="profile-form-group md:col-span-2">
                                <label class="profile-form-label">Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="home" data-section="hero"
                                    data-key="description" rows="3" placeholder="Mô tả ngắn về trung tâm..."></textarea>
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Nút CTA chính</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="hero" data-key="cta_primary"
                                    placeholder="VD: Đăng ký học thử miễn phí">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Nút CTA phụ</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="hero" data-key="cta_secondary" placeholder="VD: Xem khóa học">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Stat box - Số</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="hero" data-key="stat_number" placeholder="VD: 1000+">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Stat box - Mô tả</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="hero" data-key="stat_label" placeholder="VD: Học viên đạt 7.0+">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">🏠 Section Về chúng tôi</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="about" data-key="title" placeholder="VD: Về Hải Âu English">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="about" data-key="description"
                                    placeholder="VD: Trung tâm đào tạo IELTS hàng đầu...">
                            </div>
                        </div>

                        <!-- Home Page Images Section -->
                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">🖼️ Hình ảnh Hero trang chủ
                        </h3>
                        <div class="grid md:grid-cols-1 gap-4 mb-6">
                            <!-- Hero Image -->
                            <div class="profile-form-group">
                                <label class="profile-form-label">Hình ảnh Hero (hiển thị ở banner đầu trang)</label>
                                <div class="content-image-upload" data-page="home" data-section="hero"
                                    data-key="image1">
                                    <div class="image-preview-container mb-2">
                                        <img id="preview-home-hero-image1" src="" alt="Preview"
                                            class="content-image-preview hidden w-full h-32 object-cover rounded-lg border">
                                        <div id="placeholder-home-hero-image1"
                                            class="w-full h-32 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-sm">Chưa có hình ảnh</span>
                                        </div>
                                    </div>
                                    <input type="file" class="content-image-input hidden" data-page="home"
                                        data-section="hero" data-key="image1" accept="image/*">
                                    <div class="flex gap-2">
                                        <button type="button"
                                            class="admin-action-btn secondary upload-content-image-btn flex-1">Chọn
                                            ảnh</button>
                                        <button type="button"
                                            class="admin-action-btn danger delete-content-image-btn flex-1"
                                            data-page="home" data-section="hero" data-key="image1">Xóa ảnh</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📊 Thống kê (Stats)</h3>
                        <div class="grid md:grid-cols-4 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Số liệu 1 (số)</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="stats" data-key="stat1_number" placeholder="VD: 5000+">
                                <label class="profile-form-label mt-2">Số liệu 1 (mô tả)</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="stats" data-key="stat1_label" placeholder="VD: Học viên đã tin tưởng">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Số liệu 2 (số)</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="stats" data-key="stat2_number" placeholder="VD: 98%">
                                <label class="profile-form-label mt-2">Số liệu 2 (mô tả)</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="stats" data-key="stat2_label" placeholder="VD: Tỷ lệ đạt mục tiêu">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Số liệu 3 (số)</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="stats" data-key="stat3_number" placeholder="VD: 50+">
                                <label class="profile-form-label mt-2">Số liệu 3 (mô tả)</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="stats" data-key="stat3_label" placeholder="VD: Giảng viên 8.0+">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Số liệu 4 (số)</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="stats" data-key="stat4_number" placeholder="VD: 10+">
                                <label class="profile-form-label mt-2">Số liệu 4 (mô tả)</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="stats" data-key="stat4_label" placeholder="VD: Năm kinh nghiệm">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">❓ Tại sao chọn chúng tôi</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề section</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="why_choose" data-key="title" placeholder="VD: Vì sao chọn chúng tôi?">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả section</label>
                                <input type="text" class="profile-form-input content-input" data-page="home"
                                    data-section="why_choose" data-key="subtitle"
                                    placeholder="VD: Những lợi ích vượt trội khi học tại Hải Âu English">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Lý do 1 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="home"
                                    data-section="why_choose" data-key="item1_title"
                                    placeholder="VD: Giáo trình độc quyền">
                                <label class="profile-form-label">Lý do 1 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="home"
                                    data-section="why_choose" data-key="item1_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Lý do 2 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="home"
                                    data-section="why_choose" data-key="item2_title" placeholder="VD: Lớp học nhỏ">
                                <label class="profile-form-label">Lý do 2 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="home"
                                    data-section="why_choose" data-key="item2_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Lý do 3 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="home"
                                    data-section="why_choose" data-key="item3_title" placeholder="VD: Cam kết đầu ra">
                                <label class="profile-form-label">Lý do 3 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="home"
                                    data-section="why_choose" data-key="item3_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Lý do 4 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="home"
                                    data-section="why_choose" data-key="item4_title"
                                    placeholder="VD: Lộ trình cá nhân hóa">
                                <label class="profile-form-label">Lý do 4 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="home"
                                    data-section="why_choose" data-key="item4_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Lý do 5 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="home"
                                    data-section="why_choose" data-key="item5_title" placeholder="VD: Học liệu đa dạng">
                                <label class="profile-form-label">Lý do 5 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="home"
                                    data-section="why_choose" data-key="item5_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Lý do 6 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="home"
                                    data-section="why_choose" data-key="item6_title" placeholder="VD: Hỗ trợ 24/7">
                                <label class="profile-form-label">Lý do 6 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="home"
                                    data-section="why_choose" data-key="item6_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- About Page Content -->
                    <div class="content-page-editor hidden" data-page="about">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">� Hero Section</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề trang</label>
                                <input type="text" class="profile-form-input content-input" data-page="about"
                                    data-section="hero" data-key="title" placeholder="VD: Về Hải Âu English">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả</label>
                                <input type="text" class="profile-form-input content-input" data-page="about"
                                    data-section="hero" data-key="subtitle"
                                    placeholder="VD: Trung tâm đào tạo IELTS hàng đầu với hơn 10 năm kinh nghiệm">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📖 Câu chuyện của chúng tôi
                        </h3>
                        <div class="grid md:grid-cols-1 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề section</label>
                                <input type="text" class="profile-form-input content-input" data-page="about"
                                    data-section="story" data-key="title" placeholder="VD: Câu chuyện của chúng tôi">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Đoạn văn 1</label>
                                <textarea class="profile-form-input content-input" data-page="about"
                                    data-section="story" data-key="paragraph1" rows="3"
                                    placeholder="Nội dung giới thiệu..."></textarea>
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Đoạn văn 2</label>
                                <textarea class="profile-form-input content-input" data-page="about"
                                    data-section="story" data-key="paragraph2" rows="3"
                                    placeholder="Nội dung giới thiệu..."></textarea>
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Đoạn văn 3</label>
                                <textarea class="profile-form-input content-input" data-page="about"
                                    data-section="story" data-key="paragraph3" rows="3"
                                    placeholder="Nội dung giới thiệu..."></textarea>
                            </div>
                            <!-- Story Image -->
                            <div class="profile-form-group">
                                <label class="profile-form-label">Hình ảnh minh họa (Story)</label>
                                <div class="content-image-upload" data-page="about" data-section="story"
                                    data-key="image">
                                    <div class="image-preview-container mb-2">
                                        <img id="preview-about-story-image" src="" alt="Preview"
                                            class="content-image-preview hidden w-full h-32 object-cover rounded-lg border">
                                        <div id="placeholder-about-story-image"
                                            class="w-full h-32 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-sm">Chưa có hình ảnh</span>
                                        </div>
                                    </div>
                                    <input type="file" class="content-image-input hidden" data-page="about"
                                        data-section="story" data-key="image" accept="image/*">
                                    <div class="flex gap-2">
                                        <button type="button"
                                            class="admin-action-btn secondary upload-content-image-btn flex-1">Chọn
                                            ảnh</button>
                                        <button type="button"
                                            class="admin-action-btn danger delete-content-image-btn flex-1"
                                            data-page="about" data-section="story" data-key="image">Xóa ảnh</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">🎯 Sứ mệnh & Tầm nhìn</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Tiêu đề Sứ mệnh</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="about"
                                    data-section="mission" data-key="title" placeholder="VD: Sứ mệnh">
                                <label class="profile-form-label">Nội dung Sứ mệnh</label>
                                <textarea class="profile-form-input content-input" data-page="about"
                                    data-section="mission" data-key="description" rows="4"
                                    placeholder="Nội dung..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Tiêu đề Tầm nhìn</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="about"
                                    data-section="vision" data-key="title" placeholder="VD: Tầm nhìn">
                                <label class="profile-form-label">Nội dung Tầm nhìn</label>
                                <textarea class="profile-form-input content-input" data-page="about"
                                    data-section="vision" data-key="description" rows="4"
                                    placeholder="Nội dung..."></textarea>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">🏢 Cơ sở vật chất</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input" data-page="about"
                                    data-section="facilities" data-key="title" placeholder="VD: Cơ sở vật chất">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả</label>
                                <input type="text" class="profile-form-input content-input" data-page="about"
                                    data-section="facilities" data-key="subtitle"
                                    placeholder="VD: Không gian học tập hiện đại và thoải mái">
                            </div>
                        </div>

                        <!-- Facilities Gallery Images -->
                        <h4 class="text-md font-medium mb-3 text-gray-600">🖼️ Hình ảnh cơ sở vật chất (Gallery)</h4>
                        <div class="grid md:grid-cols-4 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Ảnh 1</label>
                                <div class="content-image-upload" data-page="about" data-section="facilities"
                                    data-key="image1">
                                    <div class="image-preview-container mb-2">
                                        <img id="preview-about-facilities-image1" src="" alt="Preview"
                                            class="content-image-preview hidden w-full h-24 object-cover rounded-lg border">
                                        <div id="placeholder-about-facilities-image1"
                                            class="w-full h-24 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Chưa có ảnh</span>
                                        </div>
                                    </div>
                                    <input type="file" class="content-image-input hidden" data-page="about"
                                        data-section="facilities" data-key="image1" accept="image/*">
                                    <div class="flex flex-col gap-1">
                                        <button type="button"
                                            class="admin-action-btn secondary upload-content-image-btn text-xs py-1">Chọn
                                            ảnh</button>
                                        <button type="button"
                                            class="admin-action-btn danger delete-content-image-btn text-xs py-1"
                                            data-page="about" data-section="facilities" data-key="image1">Xóa
                                            ảnh</button>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Ảnh 2</label>
                                <div class="content-image-upload" data-page="about" data-section="facilities"
                                    data-key="image2">
                                    <div class="image-preview-container mb-2">
                                        <img id="preview-about-facilities-image2" src="" alt="Preview"
                                            class="content-image-preview hidden w-full h-24 object-cover rounded-lg border">
                                        <div id="placeholder-about-facilities-image2"
                                            class="w-full h-24 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Chưa có ảnh</span>
                                        </div>
                                    </div>
                                    <input type="file" class="content-image-input hidden" data-page="about"
                                        data-section="facilities" data-key="image2" accept="image/*">
                                    <div class="flex flex-col gap-1">
                                        <button type="button"
                                            class="admin-action-btn secondary upload-content-image-btn text-xs py-1">Chọn
                                            ảnh</button>
                                        <button type="button"
                                            class="admin-action-btn danger delete-content-image-btn text-xs py-1"
                                            data-page="about" data-section="facilities" data-key="image2">Xóa
                                            ảnh</button>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Ảnh 3</label>
                                <div class="content-image-upload" data-page="about" data-section="facilities"
                                    data-key="image3">
                                    <div class="image-preview-container mb-2">
                                        <img id="preview-about-facilities-image3" src="" alt="Preview"
                                            class="content-image-preview hidden w-full h-24 object-cover rounded-lg border">
                                        <div id="placeholder-about-facilities-image3"
                                            class="w-full h-24 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Chưa có ảnh</span>
                                        </div>
                                    </div>
                                    <input type="file" class="content-image-input hidden" data-page="about"
                                        data-section="facilities" data-key="image3" accept="image/*">
                                    <div class="flex flex-col gap-1">
                                        <button type="button"
                                            class="admin-action-btn secondary upload-content-image-btn text-xs py-1">Chọn
                                            ảnh</button>
                                        <button type="button"
                                            class="admin-action-btn danger delete-content-image-btn text-xs py-1"
                                            data-page="about" data-section="facilities" data-key="image3">Xóa
                                            ảnh</button>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Ảnh 4</label>
                                <div class="content-image-upload" data-page="about" data-section="facilities"
                                    data-key="image4">
                                    <div class="image-preview-container mb-2">
                                        <img id="preview-about-facilities-image4" src="" alt="Preview"
                                            class="content-image-preview hidden w-full h-24 object-cover rounded-lg border">
                                        <div id="placeholder-about-facilities-image4"
                                            class="w-full h-24 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Chưa có ảnh</span>
                                        </div>
                                    </div>
                                    <input type="file" class="content-image-input hidden" data-page="about"
                                        data-section="facilities" data-key="image4" accept="image/*">
                                    <div class="flex flex-col gap-1">
                                        <button type="button"
                                            class="admin-action-btn secondary upload-content-image-btn text-xs py-1">Chọn
                                            ảnh</button>
                                        <button type="button"
                                            class="admin-action-btn danger delete-content-image-btn text-xs py-1"
                                            data-page="about" data-section="facilities" data-key="image4">Xóa
                                            ảnh</button>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Ảnh 5</label>
                                <div class="content-image-upload" data-page="about" data-section="facilities"
                                    data-key="image5">
                                    <div class="image-preview-container mb-2">
                                        <img id="preview-about-facilities-image5" src="" alt="Preview"
                                            class="content-image-preview hidden w-full h-24 object-cover rounded-lg border">
                                        <div id="placeholder-about-facilities-image5"
                                            class="w-full h-24 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Chưa có ảnh</span>
                                        </div>
                                    </div>
                                    <input type="file" class="content-image-input hidden" data-page="about"
                                        data-section="facilities" data-key="image5" accept="image/*">
                                    <div class="flex flex-col gap-1">
                                        <button type="button"
                                            class="admin-action-btn secondary upload-content-image-btn text-xs py-1">Chọn
                                            ảnh</button>
                                        <button type="button"
                                            class="admin-action-btn danger delete-content-image-btn text-xs py-1"
                                            data-page="about" data-section="facilities" data-key="image5">Xóa
                                            ảnh</button>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Ảnh 6</label>
                                <div class="content-image-upload" data-page="about" data-section="facilities"
                                    data-key="image6">
                                    <div class="image-preview-container mb-2">
                                        <img id="preview-about-facilities-image6" src="" alt="Preview"
                                            class="content-image-preview hidden w-full h-24 object-cover rounded-lg border">
                                        <div id="placeholder-about-facilities-image6"
                                            class="w-full h-24 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Chưa có ảnh</span>
                                        </div>
                                    </div>
                                    <input type="file" class="content-image-input hidden" data-page="about"
                                        data-section="facilities" data-key="image6" accept="image/*">
                                    <div class="flex flex-col gap-1">
                                        <button type="button"
                                            class="admin-action-btn secondary upload-content-image-btn text-xs py-1">Chọn
                                            ảnh</button>
                                        <button type="button"
                                            class="admin-action-btn danger delete-content-image-btn text-xs py-1"
                                            data-page="about" data-section="facilities" data-key="image6">Xóa
                                            ảnh</button>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-form-group md:col-span-2">
                                <label class="profile-form-label">Ảnh 7 (Rộng)</label>
                                <div class="content-image-upload" data-page="about" data-section="facilities"
                                    data-key="image7">
                                    <div class="image-preview-container mb-2">
                                        <img id="preview-about-facilities-image7" src="" alt="Preview"
                                            class="content-image-preview hidden w-full h-24 object-cover rounded-lg border">
                                        <div id="placeholder-about-facilities-image7"
                                            class="w-full h-24 bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Chưa có ảnh</span>
                                        </div>
                                    </div>
                                    <input type="file" class="content-image-input hidden" data-page="about"
                                        data-section="facilities" data-key="image7" accept="image/*">
                                    <div class="flex gap-2">
                                        <button type="button"
                                            class="admin-action-btn secondary upload-content-image-btn flex-1 text-xs py-1">Chọn
                                            ảnh</button>
                                        <button type="button"
                                            class="admin-action-btn danger delete-content-image-btn flex-1 text-xs py-1"
                                            data-page="about" data-section="facilities" data-key="image7">Xóa
                                            ảnh</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📅 Hành trình phát triển
                            (Timeline)</h3>
                        <div class="space-y-4 mb-6">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Năm 1</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="year1" placeholder="VD: 2016">
                                    </div>
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Tiêu đề 1</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="title1" placeholder="VD: Thành lập">
                                    </div>
                                    <div class="profile-form-group md:col-span-1">
                                        <label class="profile-form-label">Mô tả 1</label>
                                        <textarea class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="desc1" rows="2"
                                            placeholder="Mô tả sự kiện..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Năm 2</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="year2" placeholder="VD: 2018">
                                    </div>
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Tiêu đề 2</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="title2" placeholder="VD: Mở rộng quy mô">
                                    </div>
                                    <div class="profile-form-group md:col-span-1">
                                        <label class="profile-form-label">Mô tả 2</label>
                                        <textarea class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="desc2" rows="2"
                                            placeholder="Mô tả sự kiện..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Năm 3</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="year3" placeholder="VD: 2020">
                                    </div>
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Tiêu đề 3</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="title3" placeholder="VD: Chuyển đổi số">
                                    </div>
                                    <div class="profile-form-group md:col-span-1">
                                        <label class="profile-form-label">Mô tả 3</label>
                                        <textarea class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="desc3" rows="2"
                                            placeholder="Mô tả sự kiện..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Năm 4</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="year4" placeholder="VD: 2023">
                                    </div>
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Tiêu đề 4</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="title4"
                                            placeholder="VD: Đạt mốc 5000 học viên">
                                    </div>
                                    <div class="profile-form-group md:col-span-1">
                                        <label class="profile-form-label">Mô tả 4</label>
                                        <textarea class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="desc4" rows="2"
                                            placeholder="Mô tả sự kiện..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Năm 5</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="year5" placeholder="VD: 2026">
                                    </div>
                                    <div class="profile-form-group">
                                        <label class="profile-form-label">Tiêu đề 5</label>
                                        <input type="text" class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="title5" placeholder="VD: Mở rộng quốc tế">
                                    </div>
                                    <div class="profile-form-group md:col-span-1">
                                        <label class="profile-form-label">Mô tả 5</label>
                                        <textarea class="profile-form-input content-input" data-page="about"
                                            data-section="timeline" data-key="desc5" rows="2"
                                            placeholder="Mô tả sự kiện..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Page Content -->
                    <div class="content-page-editor hidden" data-page="contact">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📌 Hero Section</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề trang</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="hero" data-key="title" placeholder="VD: Liên hệ với chúng tôi">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="hero" data-key="subtitle"
                                    placeholder="VD: Chúng tôi sẵn sàng tư vấn và hỗ trợ bạn 24/7">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📧 Form liên hệ</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề form</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="form" data-key="title" placeholder="VD: ĐĂNG KÝ HỌC/TƯ VẤN">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả form</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="form" data-key="subtitle" placeholder="Mô tả ngắn...">
                            </div>
                        </div>

                        <!-- Danh sách khóa học - Collapsible -->
                        <div class="collapsible-section mb-6">
                            <div class="flex items-center justify-between cursor-pointer p-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                                onclick="toggleSection('courses-dropdown-section')">
                                <h3 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
                                    📚 Danh sách khóa học (Dropdown trong form)
                                    <span class="text-sm font-normal text-gray-500" id="courses-count">(0 khóa
                                        học)</span>
                                </h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-blue-600" id="courses-toggle-text">Mở rộng</span>
                                    <svg id="courses-toggle-icon" class="w-5 h-5 text-gray-500 transition-transform"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <div id="courses-dropdown-section" class="hidden mt-4">
                                <div class="alert-info mb-4"
                                    style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 8px;">
                                    <p class="text-sm text-amber-800">
                                        <strong>💡 Hướng dẫn:</strong> Mỗi khóa học gồm 2 phần: <strong>Giá trị
                                            (value)</strong> - dùng trong hệ thống, và <strong>Tên hiển thị</strong> -
                                        hiển thị cho người dùng. Bấm nút <strong>+ Thêm khóa học</strong> để thêm mới,
                                        bấm <strong>🗑️</strong> để xóa (chỉ xóa nội dung, không xóa ô).
                                    </p>
                                </div>
                                <div id="courses-list" class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div class="p-4 bg-gray-50 rounded-lg relative course-item" data-index="1">
                                        <button type="button" onclick="clearCourseItem(1)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Khóa học 1 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="courses" data-key="course1_value"
                                            placeholder="VD: foundation">
                                        <label class="profile-form-label">Khóa học 1 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="courses" data-key="course1_label"
                                            placeholder="VD: IELTS Foundation">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-lg relative course-item" data-index="2">
                                        <button type="button" onclick="clearCourseItem(2)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Khóa học 2 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="courses" data-key="course2_value"
                                            placeholder="VD: intermediate">
                                        <label class="profile-form-label">Khóa học 2 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="courses" data-key="course2_label"
                                            placeholder="VD: IELTS Intermediate">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-lg relative course-item" data-index="3">
                                        <button type="button" onclick="clearCourseItem(3)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Khóa học 3 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="courses" data-key="course3_value"
                                            placeholder="VD: advanced">
                                        <label class="profile-form-label">Khóa học 3 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="courses" data-key="course3_label"
                                            placeholder="VD: IELTS Advanced">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-lg relative course-item" data-index="4">
                                        <button type="button" onclick="clearCourseItem(4)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Khóa học 4 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="courses" data-key="course4_value"
                                            placeholder="VD: 1on1">
                                        <label class="profile-form-label">Khóa học 4 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="courses" data-key="course4_label"
                                            placeholder="VD: IELTS 1-1 Cá nhân">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-lg relative course-item" data-index="5">
                                        <button type="button" onclick="clearCourseItem(5)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Khóa học 5 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="courses" data-key="course5_value"
                                            placeholder="VD: online">
                                        <label class="profile-form-label">Khóa học 5 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="courses" data-key="course5_label"
                                            placeholder="VD: IELTS Online">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-lg relative course-item" data-index="6">
                                        <button type="button" onclick="clearCourseItem(6)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Khóa học 6 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="courses" data-key="course6_value"
                                            placeholder="VD: weekend">
                                        <label class="profile-form-label">Khóa học 6 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="courses" data-key="course6_label"
                                            placeholder="VD: IELTS Weekend">
                                    </div>
                                </div>
                                <button type="button" onclick="addCourseItem()"
                                    class="w-full py-3 border-2 border-dashed border-blue-300 rounded-lg text-blue-600 hover:bg-blue-50 hover:border-blue-400 transition flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Thêm khóa học mới
                                </button>
                            </div>
                        </div>

                        <!-- Danh sách trình độ - Collapsible -->
                        <div class="collapsible-section mb-6">
                            <div class="flex items-center justify-between cursor-pointer p-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                                onclick="toggleSection('levels-dropdown-section')">
                                <h3 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
                                    📋 Trình độ (Dropdown trong form)
                                    <span class="text-sm font-normal text-gray-500" id="levels-count">(0 trình
                                        độ)</span>
                                </h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-blue-600" id="levels-toggle-text">Mở rộng</span>
                                    <svg id="levels-toggle-icon" class="w-5 h-5 text-gray-500 transition-transform"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <div id="levels-dropdown-section" class="hidden mt-4">
                                <div id="levels-list" class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div class="p-4 bg-gray-50 rounded-lg relative level-item" data-index="1">
                                        <button type="button" onclick="clearLevelItem(1)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Trình độ 1 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="levels" data-key="level1_value"
                                            placeholder="VD: beginner">
                                        <label class="profile-form-label">Trình độ 1 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="levels" data-key="level1_label" placeholder="VD: Mới bắt đầu">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-lg relative level-item" data-index="2">
                                        <button type="button" onclick="clearLevelItem(2)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Trình độ 2 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="levels" data-key="level2_value"
                                            placeholder="VD: elementary">
                                        <label class="profile-form-label">Trình độ 2 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="levels" data-key="level2_label"
                                            placeholder="VD: Sơ cấp (A1-A2)">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-lg relative level-item" data-index="3">
                                        <button type="button" onclick="clearLevelItem(3)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Trình độ 3 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="levels" data-key="level3_value"
                                            placeholder="VD: intermediate">
                                        <label class="profile-form-label">Trình độ 3 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="levels" data-key="level3_label"
                                            placeholder="VD: Trung cấp (B1-B2)">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-lg relative level-item" data-index="4">
                                        <button type="button" onclick="clearLevelItem(4)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Trình độ 4 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="levels" data-key="level4_value"
                                            placeholder="VD: advanced">
                                        <label class="profile-form-label">Trình độ 4 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="levels" data-key="level4_label"
                                            placeholder="VD: Cao cấp (C1-C2)">
                                    </div>
                                    <div class="p-4 bg-gray-50 rounded-lg relative level-item" data-index="5">
                                        <button type="button" onclick="clearLevelItem(5)"
                                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1"
                                            title="Xóa nội dung">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <label class="profile-form-label">Trình độ 5 - Giá trị</label>
                                        <input type="text" class="profile-form-input content-input mb-2"
                                            data-page="contact" data-section="levels" data-key="level5_value"
                                            placeholder="VD: unknown">
                                        <label class="profile-form-label">Trình độ 5 - Tên hiển thị</label>
                                        <input type="text" class="profile-form-input content-input" data-page="contact"
                                            data-section="levels" data-key="level5_label"
                                            placeholder="VD: Chưa xác định">
                                    </div>
                                </div>
                                <button type="button" onclick="addLevelItem()"
                                    class="w-full py-3 border-2 border-dashed border-green-300 rounded-lg text-green-600 hover:bg-green-50 hover:border-green-400 transition flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Thêm trình độ mới
                                </button>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📍 Thông tin liên hệ</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề section</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="info" data-key="section_title" placeholder="VD: Thông tin liên hệ">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Địa chỉ</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="info" data-key="address"
                                    placeholder="VD: 14/2A Trương Phước Phan, Phường Bình Trị Đông, TP.HCM">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Số điện thoại</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="info" data-key="phone" placeholder="VD: Mobile: 0931 828 960">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Email</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="info" data-key="email" placeholder="VD: haiauenglish@gmail.com">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Giờ làm việc (dòng 1)</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="info" data-key="working_hours1"
                                    placeholder="VD: Thứ 2 - Thứ 6: 08h30 - 10h30, 14h00 - 16h00, 17h45 - 21h00">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Giờ làm việc (dòng 2)</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="info" data-key="working_hours2"
                                    placeholder="VD: Thứ 7 - Chủ nhật: 07h30 - 11h45, 13h00 - 17h00">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">❓ Câu hỏi thường gặp (FAQ)
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề section</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="faq" data-key="title" placeholder="VD: Câu hỏi thường gặp">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả section</label>
                                <input type="text" class="profile-form-input content-input" data-page="contact"
                                    data-section="faq" data-key="subtitle"
                                    placeholder="VD: Giải đáp những thắc mắc phổ biến">
                            </div>
                        </div>
                        <div class="space-y-4 mb-6">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Câu hỏi 1</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="contact"
                                    data-section="faq" data-key="q1" placeholder="VD: Làm thế nào để đăng ký học?">
                                <label class="profile-form-label">Trả lời 1</label>
                                <textarea class="profile-form-input content-input" data-page="contact"
                                    data-section="faq" data-key="a1" rows="2"
                                    placeholder="Nội dung trả lời..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Câu hỏi 2</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="contact"
                                    data-section="faq" data-key="q2" placeholder="VD: Có được học thử miễn phí không?">
                                <label class="profile-form-label">Trả lời 2</label>
                                <textarea class="profile-form-input content-input" data-page="contact"
                                    data-section="faq" data-key="a2" rows="2"
                                    placeholder="Nội dung trả lời..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Câu hỏi 3</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="contact"
                                    data-section="faq" data-key="q3" placeholder="VD: Trung tâm có cơ sở nào khác?">
                                <label class="profile-form-label">Trả lời 3</label>
                                <textarea class="profile-form-input content-input" data-page="contact"
                                    data-section="faq" data-key="a3" rows="2"
                                    placeholder="Nội dung trả lời..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Câu hỏi 4</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="contact"
                                    data-section="faq" data-key="q4"
                                    placeholder="VD: Thời gian phản hồi sau khi gửi form?">
                                <label class="profile-form-label">Trả lời 4</label>
                                <textarea class="profile-form-input content-input" data-page="contact"
                                    data-section="faq" data-key="a4" rows="2"
                                    placeholder="Nội dung trả lời..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Page Content -->
                    <div class="content-page-editor hidden" data-page="courses">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📚 Hero Section</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề trang</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="hero" data-key="title" placeholder="VD: Chương trình đào tạo">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="hero" data-key="subtitle"
                                    placeholder="VD: Lựa chọn khóa học phù hợp với độ tuổi và trình độ của bạn">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">🔍 Bộ lọc khóa học</h3>
                        <div class="grid md:grid-cols-4 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Nút "Tất cả"</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="filter" data-key="all" placeholder="VD: Tất cả khóa học">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Nút "Tiểu học"</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="filter" data-key="tieuhoc" placeholder="VD: Tiểu học">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Nút "THCS"</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="filter" data-key="thcs" placeholder="VD: THCS">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Nút "IELTS"</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="filter" data-key="ielts" placeholder="VD: IELTS">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📖 Tiêu đề các chương trình
                        </h3>
                        <div class="grid md:grid-cols-1 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề chương trình Tiểu học</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="sections" data-key="tieuhoc_title"
                                    placeholder="VD: 📚 CHƯƠNG TRÌNH TIẾNG ANH CẤP TIỂU HỌC">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề chương trình THCS</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="sections" data-key="thcs_title"
                                    placeholder="VD: 📖 CHƯƠNG TRÌNH TIẾNG ANH CẤP THCS">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề chương trình IELTS</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="sections" data-key="ielts_title"
                                    placeholder="VD: 🎯 CHƯƠNG TRÌNH IELTS VÀ LT IELTS">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📊 Bảng học phí (Header)</h3>
                        <div class="grid md:grid-cols-4 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Cột 1 (Level)</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="table" data-key="col1" placeholder="VD: Level">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Cột 2 (Giáo trình)</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="table" data-key="col2" placeholder="VD: Giáo trình">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Cột 3 (Thời lượng)</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="table" data-key="col3" placeholder="VD: Course length">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Cột 4 (Học phí)</label>
                                <input type="text" class="profile-form-input content-input" data-page="courses"
                                    data-section="table" data-key="col4" placeholder="VD: Fee/month">
                            </div>
                        </div>

                        <div class="alert-info"
                            style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 8px;">
                            <p class="text-sm text-blue-800">
                                <strong>💡 Lưu ý:</strong> Chi tiết từng khóa học được quản lý trong mục <strong>"Quản
                                    lý khóa học"</strong> ở sidebar.
                            </p>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 mt-6 text-gray-700 border-b pb-2">❓ Câu hỏi thường gặp
                            (FAQ trang Khóa học)</h3>
                        <div class="space-y-4 mb-6">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Câu hỏi 1</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="courses"
                                    data-section="faq" data-key="q1"
                                    placeholder="VD: Làm thế nào để biết mình phù hợp với khóa học nào?">
                                <label class="profile-form-label">Trả lời 1</label>
                                <textarea class="profile-form-input content-input" data-page="courses"
                                    data-section="faq" data-key="a1" rows="2"
                                    placeholder="Nội dung trả lời..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Câu hỏi 2</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="courses"
                                    data-section="faq" data-key="q2"
                                    placeholder="VD: Có thể học thử trước khi đăng ký không?">
                                <label class="profile-form-label">Trả lời 2</label>
                                <textarea class="profile-form-input content-input" data-page="courses"
                                    data-section="faq" data-key="a2" rows="2"
                                    placeholder="Nội dung trả lời..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Câu hỏi 3</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="courses"
                                    data-section="faq" data-key="q3"
                                    placeholder="VD: Học phí có bao gồm tài liệu không?">
                                <label class="profile-form-label">Trả lời 3</label>
                                <textarea class="profile-form-input content-input" data-page="courses"
                                    data-section="faq" data-key="a3" rows="2"
                                    placeholder="Nội dung trả lời..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Câu hỏi 4</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="courses"
                                    data-section="faq" data-key="q4"
                                    placeholder="VD: Chính sách hoàn học phí như thế nào?">
                                <label class="profile-form-label">Trả lời 4</label>
                                <textarea class="profile-form-input content-input" data-page="courses"
                                    data-section="faq" data-key="a4" rows="2"
                                    placeholder="Nội dung trả lời..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Teachers Page Content -->
                    <div class="content-page-editor hidden" data-page="teachers">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">👩‍🏫 Hero Section</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề trang</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="hero" data-key="title" placeholder="VD: Đội ngũ giảng viên">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="hero" data-key="subtitle"
                                    placeholder="VD: Giảng viên chứng chỉ 8.0+ với nhiều năm kinh nghiệm giảng dạy">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📊 Thống kê đội ngũ</h3>
                        <div class="grid md:grid-cols-4 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Số liệu 1 (số)</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="stats" data-key="stat1_number" placeholder="VD: 50+">
                                <label class="profile-form-label mt-2">Số liệu 1 (mô tả)</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="stats" data-key="stat1_label" placeholder="VD: Giảng viên">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Số liệu 2 (số)</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="stats" data-key="stat2_number" placeholder="VD: 8.5+">
                                <label class="profile-form-label mt-2">Số liệu 2 (mô tả)</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="stats" data-key="stat2_label" placeholder="VD: Điểm TB IELTS">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Số liệu 3 (số)</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="stats" data-key="stat3_number" placeholder="VD: 10+">
                                <label class="profile-form-label mt-2">Số liệu 3 (mô tả)</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="stats" data-key="stat3_label" placeholder="VD: Năm kinh nghiệm">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Số liệu 4 (số)</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="stats" data-key="stat4_number" placeholder="VD: 100%">
                                <label class="profile-form-label mt-2">Số liệu 4 (mô tả)</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="stats" data-key="stat4_label" placeholder="VD: Được đào tạo">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">⭐ Giảng viên nổi bật</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề section</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="featured" data-key="title" placeholder="VD: Giảng viên nổi bật">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả section</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="featured" data-key="subtitle"
                                    placeholder="VD: Những giảng viên xuất sắc của Hải Âu English">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">📋 Tiêu chuẩn giảng viên</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề section</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="qualifications" data-key="title"
                                    placeholder="VD: Tiêu chuẩn giảng viên">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả section</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="qualifications" data-key="subtitle"
                                    placeholder="VD: Chúng tôi đặt ra những tiêu chuẩn cao cho đội ngũ giảng viên">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Tiêu chuẩn 1 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="qualifications" data-key="qual1_title"
                                    placeholder="VD: Chứng chỉ IELTS 8.0+">
                                <label class="profile-form-label">Tiêu chuẩn 1 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="teachers"
                                    data-section="qualifications" data-key="qual1_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Tiêu chuẩn 2 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="qualifications" data-key="qual2_title"
                                    placeholder="VD: Kinh nghiệm giảng dạy">
                                <label class="profile-form-label">Tiêu chuẩn 2 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="teachers"
                                    data-section="qualifications" data-key="qual2_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Tiêu chuẩn 3 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="qualifications" data-key="qual3_title"
                                    placeholder="VD: Đào tạo chuyên sâu">
                                <label class="profile-form-label">Tiêu chuẩn 3 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="teachers"
                                    data-section="qualifications" data-key="qual3_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Tiêu chuẩn 4 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="qualifications" data-key="qual4_title"
                                    placeholder="VD: Kỹ năng giao tiếp">
                                <label class="profile-form-label">Tiêu chuẩn 4 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="teachers"
                                    data-section="qualifications" data-key="qual4_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Tiêu chuẩn 5 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="qualifications" data-key="qual5_title"
                                    placeholder="VD: Cập nhật liên tục">
                                <label class="profile-form-label">Tiêu chuẩn 5 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="teachers"
                                    data-section="qualifications" data-key="qual5_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Tiêu chuẩn 6 - Tiêu đề</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="qualifications" data-key="qual6_title"
                                    placeholder="VD: Tâm huyết với nghề">
                                <label class="profile-form-label">Tiêu chuẩn 6 - Mô tả</label>
                                <textarea class="profile-form-input content-input" data-page="teachers"
                                    data-section="qualifications" data-key="qual6_desc" rows="2"
                                    placeholder="Mô tả..."></textarea>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">💬 Testimonials (Đánh giá)
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề section</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="testimonials" data-key="title"
                                    placeholder="VD: Học viên nói gì về giảng viên">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả section</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="testimonials" data-key="subtitle"
                                    placeholder="VD: Đánh giá chân thực từ học viên về chất lượng giảng dạy">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-3 gap-4 mb-6">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Đánh giá 1 - Nội dung</label>
                                <textarea class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="testimonials" data-key="review1_text" rows="3"
                                    placeholder="Nội dung đánh giá..."></textarea>
                                <label class="profile-form-label">Avatar (ký tự viết tắt)</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="testimonials" data-key="review1_avatar" placeholder="VD: NH">
                                <label class="profile-form-label">Tên</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="testimonials" data-key="review1_name" placeholder="VD: Nguyễn Hoàng">
                                <label class="profile-form-label">Thông tin</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="testimonials" data-key="review1_info"
                                    placeholder="VD: Học viên lớp Speaking">
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Đánh giá 2 - Nội dung</label>
                                <textarea class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="testimonials" data-key="review2_text" rows="3"
                                    placeholder="Nội dung đánh giá..."></textarea>
                                <label class="profile-form-label">Avatar (ký tự viết tắt)</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="testimonials" data-key="review2_avatar" placeholder="VD: TL">
                                <label class="profile-form-label">Tên</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="testimonials" data-key="review2_name" placeholder="VD: Trần Linh">
                                <label class="profile-form-label">Thông tin</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="testimonials" data-key="review2_info"
                                    placeholder="VD: Học viên lớp Advanced">
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label class="profile-form-label">Đánh giá 3 - Nội dung</label>
                                <textarea class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="testimonials" data-key="review3_text" rows="3"
                                    placeholder="Nội dung đánh giá..."></textarea>
                                <label class="profile-form-label">Avatar (ký tự viết tắt)</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="testimonials" data-key="review3_avatar" placeholder="VD: PA">
                                <label class="profile-form-label">Tên</label>
                                <input type="text" class="profile-form-input content-input mb-2" data-page="teachers"
                                    data-section="testimonials" data-key="review3_name" placeholder="VD: Phạm Anh">
                                <label class="profile-form-label">Thông tin</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="testimonials" data-key="review3_info"
                                    placeholder="VD: Học viên lớp Intermediate">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">🎯 CTA Section</h3>
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="profile-form-group">
                                <label class="profile-form-label">Tiêu đề CTA</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="cta" data-key="title"
                                    placeholder="VD: Học với đội ngũ giảng viên xuất sắc">
                            </div>
                            <div class="profile-form-group">
                                <label class="profile-form-label">Mô tả CTA</label>
                                <input type="text" class="profile-form-input content-input" data-page="teachers"
                                    data-section="cta" data-key="subtitle"
                                    placeholder="VD: Đăng ký ngay để được tư vấn và sắp xếp lớp học phù hợp">
                            </div>
                        </div>

                        <div class="alert-info"
                            style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 8px;">
                            <p class="text-sm text-blue-800">
                                <strong>💡 Lưu ý:</strong> Chi tiết từng giảng viên được quản lý trong mục <strong>"Quản
                                    lý giảng viên"</strong> ở sidebar.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <button id="save-content-btn" class="admin-action-btn primary">
                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Lưu tất cả thay đổi
                    </button>
                </div>
            </div>
        </section>

        <!-- Teacher Reviews Section (Đánh giá giảng viên) -->
        <section id="section-teacher-reviews" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title flex items-center gap-2">
                        <span class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </span>
                        Đánh giá giảng viên (Testimonials)
                    </h2>
                    <button class="admin-action-btn primary" id="add-teacher-review-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm đánh giá
                    </button>
                </div>

                <div class="alert-info mb-4"
                    style="background: #ccfbf1; border-left: 4px solid #14b8a6; padding: 12px 16px; border-radius: 8px;">
                    <p class="text-sm text-teal-800">
                        <strong>💡 Hướng dẫn:</strong> Quản lý các đánh giá từ học viên về giảng viên. Hiển thị ở trang
                        "Giảng viên" - mục "Học viên nói gì về giảng viên".
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="admin-table w-full" id="teacher-reviews-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left">STT</th>
                                <th class="px-4 py-3 text-left">Người đánh giá</th>
                                <th class="px-4 py-3 text-left">Avatar</th>
                                <th class="px-4 py-3 text-left">Thông tin</th>
                                <th class="px-4 py-3 text-left">Đánh giá</th>
                                <th class="px-4 py-3 text-left">Nội dung</th>
                                <th class="px-4 py-3 text-center">Trạng thái</th>
                                <th class="px-4 py-3 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="teacher-reviews-tbody">
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Course Fees Section (Bảng học phí) -->
        <section id="section-course-fees" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title flex items-center gap-2">
                        <span class="w-8 h-8 bg-amber-600 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </span>
                        Bảng học phí
                    </h2>
                    <button class="admin-action-btn primary" id="add-course-fee-btn" style="white-space: nowrap;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Thêm dòng</span>
                    </button>
                </div>

                <div class="alert-info mb-4"
                    style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 8px;">
                    <p class="text-sm text-amber-800">
                        <strong>💡 Hướng dẫn:</strong> Quản lý nội dung bảng học phí hiển thị ở trang "Khóa học". Mỗi
                        danh mục (Tiểu học, THCS, IELTS) có bảng riêng.
                    </p>
                </div>

                <!-- Category Filter -->
                <div class="mb-4 flex gap-2">
                    <button
                        class="course-fee-filter-btn px-4 py-2 rounded-lg font-medium transition-all bg-blue-500 text-white"
                        data-category="tieuhoc">Tiểu học</button>
                    <button
                        class="course-fee-filter-btn px-4 py-2 rounded-lg font-medium transition-all bg-gray-200 text-gray-700 hover:bg-gray-300"
                        data-category="thcs">THCS</button>
                    <button
                        class="course-fee-filter-btn px-4 py-2 rounded-lg font-medium transition-all bg-gray-200 text-gray-700 hover:bg-gray-300"
                        data-category="ielts">IELTS</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="admin-table w-full" id="course-fees-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left">STT</th>
                                <th class="px-4 py-3 text-left">Level/Tên khóa</th>
                                <th class="px-4 py-3 text-left">Giáo trình</th>
                                <th class="px-4 py-3 text-left">Thời lượng</th>
                                <th class="px-4 py-3 text-left">Học phí</th>
                                <th class="px-4 py-3 text-center">Nổi bật</th>
                                <th class="px-4 py-3 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="course-fees-tbody">
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">Đang tải...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Notifications Section (Thông báo) -->
        <section id="section-notifications" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Thông báo hệ thống</h2>
                    <div class="flex gap-2">
                        <select id="notifications-type-filter" class="profile-form-input" style="max-width: 180px;">
                            <option value="">Tất cả loại</option>
                            <option value="review">Đánh giá</option>
                            <option value="achievement">Thành tích</option>
                            <option value="score">Điểm số</option>
                            <option value="contact">Liên hệ</option>
                            <option value="user">Người dùng</option>
                            <option value="system">Hệ thống</option>
                        </select>
                        <button id="mark-all-notifications-read" class="admin-action-btn secondary">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Đánh dấu tất cả đã đọc
                        </button>
                    </div>
                </div>

                <div class="alert-info mb-4"
                    style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 8px;">
                    <p class="text-sm text-blue-800">
                        <strong>ℹ️ Thông tin:</strong> Thông báo giúp bạn theo dõi các hoạt động mới như đánh giá, điểm
                        số, liên hệ từ học viên.
                        <br>
                        <span class="text-xs mt-1 block">• Giới hạn hiển thị: <strong
                                id="max-reviews-display">20</strong> đánh giá | <strong
                                id="max-achievements-display">20</strong> thành tích</span>
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">STT</th>
                                <th style="width: 100px;">Loại</th>
                                <th style="width: 200px;">Tiêu đề</th>
                                <th>Nội dung</th>
                                <th style="width: 80px;">Trạng thái</th>
                                <th style="width: 150px;">Thời gian</th>
                                <th style="width: 100px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="notifications-tbody">
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="notifications-pagination" class="flex justify-center items-center gap-4 mt-4">
                    <button id="notif-prev-page" class="admin-action-btn secondary" disabled>← Trước</button>
                    <span id="notif-page-info" class="text-gray-600">Trang 1 / 1</span>
                    <button id="notif-next-page" class="admin-action-btn secondary" disabled>Tiếp →</button>
                </div>
            </div>
        </section>

        <!-- Trash Section (Thùng rác) -->
        <section id="section-trash" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title">Thùng rác</h2>
                    <div class="flex gap-2">
                        <select id="trash-filter" class="profile-form-input" style="max-width: 200px;">
                            <option value="">Tất cả bảng</option>
                            <option value="users">Học viên</option>
                            <option value="courses">Khóa học</option>
                            <option value="enrollments">Đăng ký</option>
                            <option value="teachers">Giảng viên</option>
                            <option value="scores">Điểm số</option>
                            <option value="feedback">Nhận xét</option>
                        </select>
                        <button id="empty-trash-btn" class="admin-action-btn danger">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Dọn sạch thùng rác
                        </button>
                    </div>
                </div>

                <div class="alert-info mb-4"
                    style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 8px;">
                    <p class="text-sm text-yellow-800">
                        <strong>⚠️ Lưu ý:</strong> Dữ liệu trong thùng rác sẽ tự động bị xóa vĩnh viễn sau <strong>3
                            tháng</strong> kể từ ngày xóa.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>Loại</th>
                                <th>Dữ liệu</th>
                                <th>Người xóa</th>
                                <th>Ngày xóa</th>
                                <th>Hết hạn</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="trash-tbody">
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Recruitment Management Section -->
        <section id="section-recruitment" class="content-section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-card-title flex items-center gap-2">
                        <span class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                        Quản lý Tuyển dụng
                    </h2>
                    <button class="admin-action-btn primary" id="add-recruitment-btn">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Thêm tin tuyển dụng
                    </button>
                </div>

                <div class="alert-info mb-4"
                    style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 8px;">
                    <p class="text-sm text-blue-800">
                        <strong>💡 Gợi ý:</strong> Quản lý các tin tuyển dụng hiển thị trên trang <a
                            href="<?php echo $basePath; ?>/TuyenDung" target="_blank" class="underline">Tuyển dụng</a>.
                        Có thể bật/tắt hiển thị và đánh dấu tin nổi bật.
                    </p>
                </div>

                <!-- Recruitments Table -->
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Vị trí</th>
                                <th>Phòng ban</th>
                                <th>Loại hình</th>
                                <th>Mức lương</th>
                                <th>Hạn nộp</th>
                                <th>Trạng thái</th>
                                <th>Nổi bật</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="recruitment-tbody">
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <div class="spinner"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal Container -->
    <div id="modal-container" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div id="modal-content"></div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>

    <script src="<?php echo $assetsPath; ?>/js/ui/toast.js"></script>

    <!-- Course/Level Dropdown Management Scripts -->
    <script>
    // Toggle section collapse/expand
    function toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        const icon = document.getElementById(sectionId.replace('-section', '') + '-toggle-icon');
        const text = document.getElementById(sectionId.replace('-section', '') + '-toggle-text');

        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
            text.textContent = 'Thu gọn';
        } else {
            section.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
            text.textContent = 'Mở rộng';
        }
    }

    // Count filled courses/levels
    function updateCounts() {
        // Count courses
        let courseCount = 0;
        document.querySelectorAll('.course-item').forEach(item => {
            const valueInput = item.querySelector('input[data-key$="_value"]');
            const labelInput = item.querySelector('input[data-key$="_label"]');
            if (valueInput && labelInput && valueInput.value.trim() && labelInput.value.trim()) {
                courseCount++;
            }
        });
        const coursesCountEl = document.getElementById('courses-count');
        if (coursesCountEl) coursesCountEl.textContent = `(${courseCount} khóa học)`;

        // Count levels
        let levelCount = 0;
        document.querySelectorAll('.level-item').forEach(item => {
            const valueInput = item.querySelector('input[data-key$="_value"]');
            const labelInput = item.querySelector('input[data-key$="_label"]');
            if (valueInput && labelInput && valueInput.value.trim() && labelInput.value.trim()) {
                levelCount++;
            }
        });
        const levelsCountEl = document.getElementById('levels-count');
        if (levelsCountEl) levelsCountEl.textContent = `(${levelCount} trình độ)`;
    }

    // Clear course item content
    function clearCourseItem(index) {
        if (!confirm('Bạn có chắc muốn xóa nội dung khóa học này?')) return;

        const valueInput = document.querySelector(`input[data-key="course${index}_value"]`);
        const labelInput = document.querySelector(`input[data-key="course${index}_label"]`);

        if (valueInput) valueInput.value = '';
        if (labelInput) labelInput.value = '';

        updateCounts();
        showToast('Đã xóa nội dung khóa học', 'success');
    }

    // Clear level item content
    function clearLevelItem(index) {
        if (!confirm('Bạn có chắc muốn xóa nội dung trình độ này?')) return;

        const valueInput = document.querySelector(`input[data-key="level${index}_value"]`);
        const labelInput = document.querySelector(`input[data-key="level${index}_label"]`);

        if (valueInput) valueInput.value = '';
        if (labelInput) labelInput.value = '';

        updateCounts();
        showToast('Đã xóa nội dung trình độ', 'success');
    }

    // Add new course item
    let courseCounter = 6; // Start from 7 since we have 6 default items
    function addCourseItem() {
        courseCounter++;
        const coursesList = document.getElementById('courses-list');

        const newItem = document.createElement('div');
        newItem.className = 'p-4 bg-blue-50 rounded-lg relative course-item border-2 border-blue-200';
        newItem.dataset.index = courseCounter;
        newItem.innerHTML = `
                <button type="button" onclick="removeCourseItem(this)" class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1" title="Xóa item này">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <span class="absolute top-2 left-4 text-xs text-blue-600 font-medium">Mới thêm</span>
                <label class="profile-form-label mt-4">Khóa học ${courseCounter} - Giá trị</label>
                <input type="text" class="profile-form-input content-input mb-2" data-page="contact" data-section="courses" data-key="course${courseCounter}_value" placeholder="VD: course_name">
                <label class="profile-form-label">Khóa học ${courseCounter} - Tên hiển thị</label>
                <input type="text" class="profile-form-input content-input" data-page="contact" data-section="courses" data-key="course${courseCounter}_label" placeholder="VD: Tên khóa học hiển thị">
            `;

        coursesList.appendChild(newItem);
        newItem.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        // Focus on first input
        setTimeout(() => {
            newItem.querySelector('input').focus();
        }, 100);

        updateCounts();
        showToast('Đã thêm khóa học mới', 'success');
    }

    // Remove newly added course item
    function removeCourseItem(btn) {
        if (!confirm('Bạn có chắc muốn xóa item này?')) return;
        const item = btn.closest('.course-item');
        item.remove();
        updateCounts();
        showToast('Đã xóa khóa học', 'success');
    }

    // Add new level item
    let levelCounter = 5; // Start from 6 since we have 5 default items
    function addLevelItem() {
        levelCounter++;
        const levelsList = document.getElementById('levels-list');

        const newItem = document.createElement('div');
        newItem.className = 'p-4 bg-green-50 rounded-lg relative level-item border-2 border-green-200';
        newItem.dataset.index = levelCounter;
        newItem.innerHTML = `
                <button type="button" onclick="removeLevelItem(this)" class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1" title="Xóa item này">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <span class="absolute top-2 left-4 text-xs text-green-600 font-medium">Mới thêm</span>
                <label class="profile-form-label mt-4">Trình độ ${levelCounter} - Giá trị</label>
                <input type="text" class="profile-form-input content-input mb-2" data-page="contact" data-section="levels" data-key="level${levelCounter}_value" placeholder="VD: level_value">
                <label class="profile-form-label">Trình độ ${levelCounter} - Tên hiển thị</label>
                <input type="text" class="profile-form-input content-input" data-page="contact" data-section="levels" data-key="level${levelCounter}_label" placeholder="VD: Tên trình độ hiển thị">
            `;

        levelsList.appendChild(newItem);
        newItem.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        // Focus on first input
        setTimeout(() => {
            newItem.querySelector('input').focus();
        }, 100);

        updateCounts();
        showToast('Đã thêm trình độ mới', 'success');
    }

    // Remove newly added level item
    function removeLevelItem(btn) {
        if (!confirm('Bạn có chắc muốn xóa item này?')) return;
        const item = btn.closest('.level-item');
        item.remove();
        updateCounts();
        showToast('Đã xóa trình độ', 'success');
    }

    // Simple toast function (fallback if toast.js not loaded)
    function showToast(message, type = 'info') {
        if (window.Toast && typeof window.Toast.show === 'function') {
            window.Toast.show(message, type);
            return;
        }

        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className =
            `px-4 py-3 rounded-lg shadow-lg mb-2 ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'} text-white`;
        toast.textContent = message;
        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Update counts when content is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Wait a bit for content to load from database
        setTimeout(updateCounts, 1500);

        // Also listen for input changes
        document.addEventListener('input', function(e) {
            if (e.target.matches(
                    '.content-input[data-section="courses"], .content-input[data-section="levels"]')) {
                updateCounts();
            }
        });
    });
    </script>

    <!-- Recruitment Management Script -->
    <script>
    (function() {
        const basePath = '<?php echo $basePath; ?>';
        const RECRUITMENT_API = basePath + '/backend/php/recruitment.php';

        // Employment type labels
        const employmentTypes = {
            'full-time': 'Toàn thời gian',
            'part-time': 'Bán thời gian',
            'contract': 'Hợp đồng',
            'intern': 'Thực tập'
        };

        // Load recruitments when section is shown
        document.addEventListener('DOMContentLoaded', function() {
            const recruitmentSection = document.getElementById('section-recruitment');
            if (recruitmentSection) {
                loadRecruitments();
            }

            // Add recruitment button
            const addBtn = document.getElementById('add-recruitment-btn');
            if (addBtn) {
                addBtn.addEventListener('click', () => showRecruitmentModal());
            }
        });

        // Load recruitments list
        async function loadRecruitments() {
            const tbody = document.getElementById('recruitment-tbody');
            if (!tbody) return;

            try {
                const res = await fetch(RECRUITMENT_API + '?action=admin_list', {
                    credentials: 'include'
                });
                const data = await res.json();

                if (data.success && data.data.length > 0) {
                    tbody.innerHTML = data.data.map(job => `
                        <tr>
                            <td>
                                <div class="font-medium">${escapeHtml(job.title)}</div>
                                <div class="text-xs text-gray-500">${job.location}</div>
                            </td>
                            <td>${job.department || '-'}</td>
                            <td><span class="badge-${job.employment_type.replace('-', '')}">${job.employment_type_label}</span></td>
                            <td>${job.salary_range || '-'}</td>
                            <td>${job.deadline ? new Date(job.deadline).toLocaleDateString('vi-VN') : 'Không giới hạn'}</td>
                            <td>
                                <button onclick="toggleRecruitmentStatus(${job.id}, 'is_active')" class="px-2 py-1 rounded text-xs font-medium ${job.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}">
                                    ${job.is_active ? 'Đang tuyển' : 'Tạm dừng'}
                                </button>
                            </td>
                            <td>
                                <button onclick="toggleRecruitmentStatus(${job.id}, 'is_featured')" class="px-2 py-1 rounded text-xs font-medium ${job.is_featured ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600'}">
                                    ${job.is_featured ? '⭐ Nổi bật' : 'Thường'}
                                </button>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button onclick="editRecruitment(${job.id})" class="admin-action-btn text-blue-600 hover:bg-blue-50 p-1" title="Sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <button onclick="deleteRecruitment(${job.id}, '${escapeHtml(job.title)}')" class="admin-action-btn text-red-600 hover:bg-red-50 p-1" title="Xóa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML =
                        '<tr><td colspan="8" class="text-center py-8 text-gray-500">Chưa có tin tuyển dụng nào</td></tr>';
                }
            } catch (err) {
                console.error('Error loading recruitments:', err);
                tbody.innerHTML =
                    '<tr><td colspan="8" class="text-center py-8 text-red-500">Lỗi tải dữ liệu</td></tr>';
            }
        }

        // Show recruitment modal (add/edit)
        window.showRecruitmentModal = async function(jobId = null) {
            let job = null;

            if (jobId) {
                try {
                    const res = await fetch(RECRUITMENT_API + '?action=detail&id=' + jobId, {
                        credentials: 'include'
                    });
                    const data = await res.json();
                    if (data.success) job = data.data;
                } catch (err) {
                    showToast('Lỗi tải dữ liệu', 'error');
                    return;
                }
            }

            const modal = document.getElementById('modal-container');
            const modalContent = document.getElementById('modal-content');

            modalContent.innerHTML = `
                <div class="flex justify-between items-center mb-4 pb-4 border-b">
                    <h3 class="text-xl font-bold">${job ? 'Sửa tin tuyển dụng' : 'Thêm tin tuyển dụng'}</h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form id="recruitment-form" class="space-y-4">
                    <input type="hidden" name="id" value="${job ? job.id : ''}">
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Tiêu đề vị trí *</label>
                            <input type="text" name="title" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" value="${job ? escapeHtml(job.title) : ''}" placeholder="VD: Giảng viên Tiếng Anh IELTS">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Phòng ban</label>
                            <input type="text" name="department" class="w-full px-3 py-2 border rounded-lg" value="${job ? escapeHtml(job.department || '') : ''}" placeholder="VD: Giảng dạy">
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Địa điểm</label>
                            <input type="text" name="location" class="w-full px-3 py-2 border rounded-lg" value="${job ? escapeHtml(job.location) : 'TP. Hồ Chí Minh'}" placeholder="TP. Hồ Chí Minh">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Loại hình</label>
                            <select name="employment_type" class="w-full px-3 py-2 border rounded-lg">
                                <option value="full-time" ${job && job.employment_type === 'full-time' ? 'selected' : ''}>Toàn thời gian</option>
                                <option value="part-time" ${job && job.employment_type === 'part-time' ? 'selected' : ''}>Bán thời gian</option>
                                <option value="contract" ${job && job.employment_type === 'contract' ? 'selected' : ''}>Hợp đồng</option>
                                <option value="intern" ${job && job.employment_type === 'intern' ? 'selected' : ''}>Thực tập</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Mức lương</label>
                            <input type="text" name="salary_range" class="w-full px-3 py-2 border rounded-lg" value="${job ? escapeHtml(job.salary_range || '') : ''}" placeholder="VD: 15-25 triệu">
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Yêu cầu kinh nghiệm</label>
                            <input type="text" name="experience" class="w-full px-3 py-2 border rounded-lg" value="${job ? escapeHtml(job.experience || '') : ''}" placeholder="VD: Tối thiểu 2 năm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Hạn nộp hồ sơ</label>
                            <input type="date" name="deadline" class="w-full px-3 py-2 border rounded-lg" value="${job && job.deadline ? job.deadline.split(' ')[0] : ''}">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Mô tả công việc (có thể dùng HTML)</label>
                        <textarea name="description" rows="4" class="w-full px-3 py-2 border rounded-lg" placeholder="<h4>Mô tả:</h4><ul><li>Nội dung...</li></ul>">${job ? job.description || '' : ''}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Yêu cầu ứng viên (có thể dùng HTML)</label>
                        <textarea name="requirements" rows="4" class="w-full px-3 py-2 border rounded-lg">${job ? job.requirements || '' : ''}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Quyền lợi (có thể dùng HTML)</label>
                        <textarea name="benefits" rows="4" class="w-full px-3 py-2 border rounded-lg">${job ? job.benefits || '' : ''}</textarea>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Email liên hệ</label>
                            <input type="email" name="contact_email" class="w-full px-3 py-2 border rounded-lg" value="${job ? escapeHtml(job.contact_email) : 'haiauenglish@gmail.com'}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Số điện thoại liên hệ</label>
                            <input type="text" name="contact_phone" class="w-full px-3 py-2 border rounded-lg" value="${job ? escapeHtml(job.contact_phone) : '0931 828 960'}">
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" ${!job || job.is_active ? 'checked' : ''} class="w-4 h-4">
                            <span>Đang tuyển</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_featured" ${job && job.is_featured ? 'checked' : ''} class="w-4 h-4">
                            <span>⭐ Tin nổi bật</span>
                        </label>
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            ${job ? 'Cập nhật' : 'Tạo tin'}
                        </button>
                    </div>
                </form>
            `;

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Handle form submit
            document.getElementById('recruitment-form').onsubmit = async function(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                data.is_active = form.is_active.checked ? 1 : 0;
                data.is_featured = form.is_featured.checked ? 1 : 0;

                const action = data.id ? 'update' : 'create';

                try {
                    const res = await fetch(RECRUITMENT_API + '?action=' + action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'include',
                        body: JSON.stringify(data)
                    });
                    const result = await res.json();

                    if (result.success) {
                        showToast(result.message, 'success');
                        closeModal();
                        loadRecruitments();
                    } else {
                        showToast(result.error || 'Có lỗi xảy ra', 'error');
                    }
                } catch (err) {
                    showToast('Lỗi kết nối', 'error');
                }
            };
        };

        // Edit recruitment
        window.editRecruitment = function(id) {
            showRecruitmentModal(id);
        };

        // Delete recruitment
        window.deleteRecruitment = async function(id, title) {
            if (!confirm(`Bạn có chắc muốn xóa tin "${title}"?`)) return;

            try {
                const res = await fetch(RECRUITMENT_API + '?action=delete&id=' + id, {
                    method: 'GET',
                    credentials: 'include'
                });
                const data = await res.json();

                if (data.success) {
                    showToast('Đã xóa tin tuyển dụng', 'success');
                    loadRecruitments();
                } else {
                    showToast(data.error || 'Lỗi xóa', 'error');
                }
            } catch (err) {
                showToast('Lỗi kết nối', 'error');
            }
        };

        // Toggle status
        window.toggleRecruitmentStatus = async function(id, field) {
            try {
                const res = await fetch(RECRUITMENT_API + '?action=toggle_status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        id,
                        field
                    })
                });
                const data = await res.json();

                if (data.success) {
                    loadRecruitments();
                } else {
                    showToast(data.error || 'Lỗi cập nhật', 'error');
                }
            } catch (err) {
                showToast('Lỗi kết nối', 'error');
            }
        };

        // Close modal
        window.closeModal = function() {
            const modal = document.getElementById('modal-container');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        // Escape HTML
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Expose loadRecruitments globally
        window.loadRecruitments = loadRecruitments;
    })();
    </script>

    <script type="module" src="<?php echo $assetsPath; ?>/js/controllers/admin.js"></script>
    <script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
    </script>
</body>

</html>
<?php
// Ensure base config is loaded
if (!isset($basePath)) {
    require_once __DIR__ . '/base_config.php';
}
?>
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white/95 backdrop-blur-sm shadow-sm z-50">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="header-container flex items-center justify-around md:justify-around h-20 md:h-24">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="<?php echo $paths['home']; ?>" class="inline-block ">
                        <img src="<?php echo $assetsPath; ?>/assets/images/logo.png" alt="logo" class="logo_img_index object-contain hover:opacity-80 transition-opacity">
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <a href="<?php echo $paths['home']; ?>" class="<?php echo ($currentPage == 'home') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-600'; ?> font-medium transition-colors">
                        Trang chủ
                    </a>
                    <a href="<?php echo $paths['about']; ?>" class="<?php echo ($currentPage == 'about') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-600'; ?> font-medium transition-colors">
                        Giới thiệu
                    </a>
                    <a href="<?php echo $paths['courses']; ?>" class="<?php echo ($currentPage == 'courses') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-600'; ?> font-medium transition-colors">
                        Khóa học
                    </a>
                    <a href="<?php echo $paths['teachers']; ?>" class="<?php echo ($currentPage == 'teachers') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-600'; ?> font-medium transition-colors">
                        Giảng viên
                    </a>
                    <a href="<?php echo $paths['recruitment']; ?>" class="<?php echo ($currentPage == 'recruitment') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-600'; ?> font-medium transition-colors">
                        Tuyển dụng
                    </a>
                    <a href="<?php echo $paths['contact']; ?>" class="<?php echo ($currentPage == 'contact') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-600'; ?> font-medium transition-colors">
                        Liên hệ
                    </a>
                </nav>

                <!-- Auth & CTA Buttons -->
                <div id="auth-buttons" class="hidden md:hidden items-center gap-4">
                    <a href="<?php echo $paths['login']; ?>" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                        Đăng nhập
                    </a>
                    <a href="<?php echo $paths['signup']; ?>" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Đăng ký ngay
                    </a>
                </div>

                <!-- User Menu (khi đã đăng nhập) -->
                <div id="user-menu" class="hidden md:hidden items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div id="user-avatar" class="w-9 h-9 bg-blue-500 rounded-full flex items-center justify-center overflow-hidden">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <a href="<?php echo $paths['profile']; ?>" id="user-name" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                            Học viên
                        </a>
                    </div>
                    <button class="logout-btn text-red-600 hover:text-red-700 font-medium transition-colors">
                        Đăng xuất
                    </button>
                </div>

                <!-- Mobile CTA Button -->
                <div class="mobile-right-group flex items-center gap-3 md:hidden">
                    <a href="<?php echo $paths['contact']; ?>" class="mobile-cta-btn">Đăng ký học</a>
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="p-2 rounded-lg hover:bg-gray-100">
                        <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t">
            <nav class="px-4 py-4 space-y-3 bg-white">
                <a href="<?php echo $paths['home']; ?>" class="block py-2 <?php echo ($currentPage == 'home') ? 'text-blue-600 bg-blue-50 px-3 rounded' : 'text-gray-700 hover:text-blue-600'; ?> font-medium">
                    Trang chủ
                </a>
                <a href="<?php echo $paths['about']; ?>" class="block py-2 <?php echo ($currentPage == 'about') ? 'text-blue-600 bg-blue-50 px-3 rounded' : 'text-gray-700 hover:text-blue-600'; ?> font-medium">
                    Giới thiệu
                </a>
                <a href="<?php echo $paths['courses']; ?>" class="block py-2 <?php echo ($currentPage == 'courses') ? 'text-blue-600 bg-blue-50 px-3 rounded' : 'text-gray-700 hover:text-blue-600'; ?> font-medium">
                    Khóa học
                </a>
                <a href="<?php echo $paths['teachers']; ?>" class="block py-2 <?php echo ($currentPage == 'teachers') ? 'text-blue-600 bg-blue-50 px-3 rounded' : 'text-gray-700 hover:text-blue-600'; ?> font-medium">
                    Giảng viên
                </a>
                <a href="<?php echo $paths['recruitment']; ?>" class="block py-2 <?php echo ($currentPage == 'recruitment') ? 'text-blue-600 bg-blue-50 px-3 rounded' : 'text-gray-700 hover:text-blue-600'; ?> font-medium">
                    Tuyển dụng
                </a>
                <a href="<?php echo $paths['contact']; ?>" class="block py-2 <?php echo ($currentPage == 'contact') ? 'text-blue-600 bg-blue-50 px-3 rounded' : 'text-gray-700 hover:text-blue-600'; ?> font-medium">
                    Liên hệ
                </a>
                <div id="mobile-auth">
                    <a href="<?php echo $paths['login']; ?>" class="block py-2 text-gray-700 hover:text-blue-600 font-medium">
                        Đăng nhập
                    </a>
                    <a href="<?php echo $paths['signup']; ?>" class="block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors text-center">
                        Đăng ký ngay
                    </a>
                </div>
            </nav>
            
            <!-- Mobile User Menu (khi đã đăng nhập) -->
            <div id="mobile-user" class="hidden px-4 py-4 bg-white border-t">
                <div class="flex items-center gap-3 mb-3">
                    <div id="mobile-user-avatar" class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center overflow-hidden">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <div>
                        <a href="<?php echo $paths['profile']; ?>" id="mobile-user-name" class="font-medium text-gray-900 hover:text-blue-600">Học viên</a>
                        <p class="text-sm text-gray-500">Xem hồ sơ</p>
                    </div>
                </div>
                <button class="logout-btn w-full text-center py-2 text-red-600 hover:text-red-700 font-medium border-t">
                    Đăng xuất
                </button>
            </div>
        </div>
    </header>

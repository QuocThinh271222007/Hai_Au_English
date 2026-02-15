<?php
// Ensure base config is loaded
if (!isset($basePath)) {
    require_once __DIR__ . '/base_config.php';
}

// Load content helper if not already loaded
if (!function_exists('s')) {
    require_once __DIR__ . '/content_helper.php';
}
?>
    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-white text-lg font-bold mb-4"><?php echo s('site_name', 'Trung tâm Ngoại ngữ Giáo dục Anh văn Hải Âu'); ?></h3>
                    <p class="text-sm mb-4">
                        <?php echo s('site_description', 'Trung tâm đào tạo IELTS uy tín với đội ngũ giảng viên 8.0+ và phương pháp giảng dạy hiệu quả được chứng minh.'); ?>
                    </p>
                    <div class="flex gap-3">
                        <a href="<?php echo s('facebook_url', 'https://www.facebook.com/AnhNguHaiAu'); ?>" class="fb-icon p-2 rounded-lg transition-colors" title="Facebook">
                            <img src="<?php echo $assetsPath; ?>/assets/images/logo_fb.png" alt="Facebook" class="w-5 h-5 object-contain">
                        </a>
                        <a href="https://zalo.me/<?php echo s('zalo_phone', '0931828960'); ?>" class="zalo-icon p-2 rounded-lg transition-colors" title="Zalo">
                            <img src="<?php echo $assetsPath; ?>/assets/images/logo_zalo.png" alt="Zalo" class="w-5 h-5 object-contain">
                        </a>
                        <a href="tel:<?php echo s('contact_phone', '0931828960'); ?>" class="phone-icon p-2 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-white text-lg font-bold mb-4">Liên kết</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo $paths['home']; ?>" class="hover:text-white transition-colors">Trang chủ</a></li>
                        <li><a href="<?php echo $paths['about']; ?>" class="hover:text-white transition-colors">Giới thiệu</a></li>
                        <li><a href="<?php echo $paths['courses']; ?>" class="hover:text-white transition-colors">Khóa học</a></li>
                        <li><a href="<?php echo $paths['teachers']; ?>" class="hover:text-white transition-colors">Giảng viên</a></li>
                        <li><a href="<?php echo $paths['recruitment']; ?>" class="hover:text-white transition-colors">Tuyển dụng</a></li>
                        <li><a href="<?php echo $paths['contact']; ?>" class="hover:text-white transition-colors">Liên hệ</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-white text-lg font-bold mb-4">Khóa học</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo $paths['courses']; ?>" class="hover:text-white transition-colors">Tiếng Anh Tiểu học</a></li>
                        <li><a href="<?php echo $paths['courses']; ?>" class="hover:text-white transition-colors">Tiếng Anh THCS</a></li>
                        <li><a href="<?php echo $paths['courses']; ?>" class="hover:text-white transition-colors">Luyện thi Cambridge</a></li>
                        <li><a href="<?php echo $paths['courses']; ?>" class="hover:text-white transition-colors">IELTS</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-white text-lg font-bold mb-4">Liên hệ</h3>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span><?php echo s('contact_address', '14/2A Trương Phước Phan, Phường Bình Trị Đông, TP.HCM'); ?></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span><?php echo s('contact_phone', '0931 828 960'); ?></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span><?php echo s('contact_email', 'haiauenglish@gmail.com'); ?></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><?php echo s('working_hours', 'Thứ 2 - Chủ nhật: 8:00 - 21:00'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
                <p>&copy; <?php echo date('Y'); ?> <?php echo s('site_name', 'Hải Âu English'); ?>. All rights reserved.</p>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-white transition-colors">Chính sách bảo mật</a>
                    <a href="#" class="hover:text-white transition-colors">Điều khoản sử dụng</a>
                </div>
            </div>
        </div>
    </footer>

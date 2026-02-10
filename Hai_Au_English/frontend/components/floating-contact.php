<?php
// Ensure content helper is loaded
if (!function_exists('s')) {
    require_once __DIR__ . '/content_helper.php';
}
$phone = s('contact_phone', '0931828960');
$phoneClean = preg_replace('/\s+/', '', $phone); // Remove spaces for tel: link
$zaloPhone = s('zalo_phone', $phoneClean);
?>
    <!-- Floating Contact Sidebar -->
    <div class="floating-contact">
        <div class="floating-contact-icons">
            <!-- Phone -->
            <a href="tel:<?php echo $phoneClean; ?>" class="fc-phone" title="Gọi điện">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/>
                </svg>
            </a>
            <!-- Zalo -->
            <a href="https://zalo.me/<?php echo $zaloPhone; ?>" class="fc-zalo" title="Zalo" target="_blank">
                <img src="/frontend/assets/images/logo_zalo.png" alt="Zalo">
            </a>
        </div>
        <!-- Toggle Button (mobile only) -->
        <button class="floating-toggle-btn" id="floating-toggle-btn" title="Liên hệ">
            <svg class="icon-chat" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
            </svg>
            <svg class="icon-close" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
        </button>
    </div>

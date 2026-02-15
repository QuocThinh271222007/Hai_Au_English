<?php
$pageTitle = 'Đăng ký - Hải Âu English';
$currentPage = 'signup';
// Include base config for dynamic paths
require_once __DIR__ . '/../components/base_config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" href="<?php echo $assetsPath; ?>/assets/images/favicon.jpg" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo $assetsPath; ?>/css/styles.css">
    <!-- reCAPTCHA v3 - Will be loaded dynamically if enabled -->
    <script id="recaptcha-script"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex justify-center px-4">
        <div class="max-w-md w-full">
            <!-- Logo -->
            <div class="text-center">
                <a href="<?php echo $paths['home']; ?>" class="inline-block ">
                    <img src="<?php echo $assetsPath; ?>/assets/images/logo.png" alt="logo" class="logo_img_login_signup object-contain hover:opacity-80 transition-opacity">
                </a>
            </div>

            <!-- Sign Up Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Đăng ký</h2>
                <form id="signup-form" class="space-y-5">
                    <!-- Full Name -->
                    <div>
                        <label for="fullname" class="block text-sm font-medium text-gray-700 mb-2">
                            Họ và tên
                        </label>
                        <input 
                            type="text" 
                            id="fullname" 
                            name="fullname"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="Nguyễn Văn A"
                        >
                        <span class="text-red-500 text-sm hidden" id="fullname-error"></span>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="your@email.com"
                        >
                        <span class="text-red-500 text-sm hidden" id="email-error"></span>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Số điện thoại <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone"
                            required
                            pattern="(03|05|07|08|09)[0-9]{8}"
                            maxlength="10"
                            inputmode="numeric"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="0912345678"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                        >
                        <span class="text-gray-500 text-xs mt-1 block">Nhập đúng 10 số, bắt đầu bằng 03, 05, 07, 08 hoặc 09</span>
                        <span class="text-red-500 text-sm hidden" id="phone-error"></span>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Mật khẩu
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password"
                                required
                                minlength="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="••••••••"
                            >
                            <button 
                                type="button" 
                                id="toggle-password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Tối thiểu 6 ký tự</p>
                        <span class="text-red-500 text-sm hidden" id="password-error"></span>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
                            Xác nhận mật khẩu
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="confirm-password" 
                                name="confirm-password"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="••••••••"
                            >
                            <button 
                                type="button" 
                                id="toggle-confirm-password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <svg id="confirm-eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="confirm-eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <span class="text-red-500 text-sm hidden" id="confirm-password-error"></span>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start">
                        <input 
                            type="checkbox" 
                            id="terms" 
                            required
                            class="w-4 h-4 mt-1 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        >
                        <label for="terms" class="ml-2 text-sm text-gray-600">
                            Tôi đồng ý với 
                            <a href="#" class="text-blue-600 hover:text-blue-700">Điều khoản sử dụng</a> 
                            và 
                            <a href="#" class="text-blue-600 hover:text-blue-700">Chính sách bảo mật</a>
                        </label>
                    </div>

                    <!-- reCAPTCHA Badge Notice -->
                    <div id="recaptcha-notice" class="hidden text-xs text-gray-500 text-center">
                        Trang này được bảo vệ bởi reCAPTCHA
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        id="signup-submit-btn"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Đăng ký
                    </button>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500">Hoặc đăng ký với</span>
                        </div>
                    </div>

                    <!-- Social Login -->
                    <div class="grid grid-cols-2 gap-4">
                        <button 
                            type="button"
                            id="google-login-btn"
                            onclick="loginWithGoogle()"
                            class="flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Google</span>
                        </button>
                        <button 
                            type="button"
                            id="facebook-login-btn"
                            onclick="loginWithFacebook()"
                            class="flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Facebook</span>
                        </button>
                    </div>
                </form>

                <!-- Login Link -->
                <p class="mt-6 text-center text-sm text-gray-600">
                    Đã có tài khoản? 
                    <a href="<?php echo $paths['login']; ?>" class="text-blue-600 hover:text-blue-700 font-medium">
                        Đăng nhập
                    </a>
                </p>
            </div>

            <!-- Back to Home -->
            <div class="mt-6 text-center">
                <a href="<?php echo $paths['home']; ?>" class="text-sm text-gray-600 hover:text-gray-900">
                    ← Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>

    <script src="<?php echo $assetsPath; ?>/js/ui/toast.js"></script>
    <script type="module" src="<?php echo $assetsPath; ?>/js/animations/uiAnimations.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/auth.js"></script>
    
    <!-- OAuth Handler Script -->
    <script>
        // OAuth Configuration (loaded from backend)
        let oauthConfig = null;
        let recaptchaSiteKey = null;

        // Initialize OAuth and reCAPTCHA on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadOAuthConfig();
        });

        // Load OAuth configuration from backend
        async function loadOAuthConfig() {
            try {
                const API_BASE = window.API_BASE || '<?php echo $apiBase; ?>';
                const response = await fetch(`${API_BASE}/auth.php?action=oauth_config`, {
                    credentials: 'include'
                });
                const data = await response.json();
                
                if (data.success) {
                    oauthConfig = data.config;
                    
                    // Setup reCAPTCHA if enabled
                    if (oauthConfig.recaptcha && oauthConfig.recaptcha.enabled) {
                        recaptchaSiteKey = oauthConfig.recaptcha.site_key;
                        loadRecaptchaScript(recaptchaSiteKey);
                        document.getElementById('recaptcha-notice').classList.remove('hidden');
                    }
                    
                    // Disable OAuth buttons if not configured
                    if (!oauthConfig.google || !oauthConfig.google.enabled) {
                        const googleBtn = document.getElementById('google-login-btn');
                        googleBtn.disabled = true;
                        googleBtn.title = 'Google login chưa được cấu hình';
                    }
                    
                    if (!oauthConfig.facebook || !oauthConfig.facebook.enabled) {
                        const fbBtn = document.getElementById('facebook-login-btn');
                        fbBtn.disabled = true;
                        fbBtn.title = 'Facebook login chưa được cấu hình';
                    }
                }
            } catch (error) {
                console.error('Failed to load OAuth config:', error);
            }
        }

        // Load reCAPTCHA v3 script
        function loadRecaptchaScript(siteKey) {
            const script = document.getElementById('recaptcha-script');
            script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey}`;
        }

        // Get reCAPTCHA token
        async function getRecaptchaToken(action) {
            if (!recaptchaSiteKey || !window.grecaptcha) {
                return null;
            }
            
            return new Promise((resolve) => {
                grecaptcha.ready(function() {
                    grecaptcha.execute(recaptchaSiteKey, { action: action })
                        .then(function(token) {
                            resolve(token);
                        })
                        .catch(function() {
                            resolve(null);
                        });
                });
            });
        }

        // Google OAuth Login
        function loginWithGoogle() {
            if (!oauthConfig || !oauthConfig.google || !oauthConfig.google.enabled) {
                popup('error', 'Lỗi', 'Google OAuth chưa được cấu hình');
                return;
            }
            window.location.href = oauthConfig.google.url;
        }

        // Facebook OAuth Login
        function loginWithFacebook() {
            if (!oauthConfig || !oauthConfig.facebook || !oauthConfig.facebook.enabled) {
                popup('error', 'Lỗi', 'Facebook OAuth chưa được cấu hình');
                return;
            }
            window.location.href = oauthConfig.facebook.url;
        }

        // Expose getRecaptchaToken for auth.js
        window.getRecaptchaToken = getRecaptchaToken;
    </script>
</body>
</html>

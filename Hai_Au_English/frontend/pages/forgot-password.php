<?php
$pageTitle = 'Quên mật khẩu - Hải Âu English';
$currentPage = 'forgot-password';
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
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex justify-center px-4">
        <div class="max-w-md w-full">
            <!-- Logo -->
            <div class="text-center">
                <a href="<?php echo $paths['home']; ?>" class="inline-block">
                    <img src="<?php echo $assetsPath; ?>/assets/images/logo.png" alt="logo" class="logo_img_login_signup object-contain hover:opacity-80 transition-opacity">
                </a>
            </div>

            <!-- Step 1: Request Reset -->
            <div id="step-request" class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2 text-center">Quên mật khẩu?</h2>
                <p class="text-gray-600 text-center mb-6 text-sm">Nhập email của bạn để nhận link đặt lại mật khẩu</p>
                
                <!-- Message Box -->
                <div id="request-message" class="hidden mb-4 p-4 rounded-lg text-sm"></div>
                
                <form id="request-form" class="space-y-6">
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
                            placeholder="example@email.com"
                        >
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        id="request-btn"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Gửi link đặt lại mật khẩu
                    </button>
                </form>

                <!-- Back to Login -->
                <p class="mt-6 text-center text-sm text-gray-600">
                    <a href="<?php echo $paths['login']; ?>" class="text-blue-600 hover:text-blue-700 font-medium">
                        ← Quay lại đăng nhập
                    </a>
                </p>
            </div>

            <!-- Step 2: Reset Password -->
            <div id="step-reset" class="bg-white rounded-2xl shadow-xl p-8" style="display: none;">
                <h2 class="text-2xl font-bold text-gray-900 mb-2 text-center">Đặt mật khẩu mới</h2>
                <p class="text-gray-600 text-center mb-2 text-sm">Tạo mật khẩu mới cho tài khoản</p>
                <p class="text-blue-600 text-center mb-4 text-sm font-medium" id="reset-email"></p>
                
                <!-- Timer -->
                <div id="timer" class="text-center text-gray-500 text-sm mb-4"></div>
                
                <!-- Message Box -->
                <div id="reset-message" class="hidden mb-4 p-4 rounded-lg text-sm"></div>
                
                <form id="reset-form" class="space-y-6">
                    <input type="hidden" id="reset-token" name="token">
                    
                    <!-- New Password -->
                    <div>
                        <label for="new-password" class="block text-sm font-medium text-gray-700 mb-2">
                            Mật khẩu mới
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="new-password" 
                                name="password"
                                required
                                minlength="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="••••••••"
                            >
                            <button 
                                type="button" 
                                id="toggle-new-password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Tối thiểu 6 ký tự</p>
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
                                name="confirm_password"
                                required
                                minlength="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                placeholder="••••••••"
                            >
                            <button 
                                type="button" 
                                id="toggle-confirm-password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        id="reset-btn"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Đặt lại mật khẩu
                    </button>
                </form>

                <!-- Back to Login -->
                <p class="mt-6 text-center text-sm text-gray-600">
                    <a href="<?php echo $paths['login']; ?>" class="text-blue-600 hover:text-blue-700 font-medium">
                        ← Quay lại đăng nhập
                    </a>
                </p>
            </div>

            <!-- Step 3: Success -->
            <div id="step-success" class="bg-white rounded-2xl shadow-xl p-8" style="display: none;">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Thành công!</h2>
                    <p class="text-gray-600 mb-6 text-sm">Mật khẩu của bạn đã được đặt lại thành công.</p>
                    <a href="<?php echo $paths['login']; ?>" class="block w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium text-center">
                        Đăng nhập ngay
                    </a>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="mt-6 text-center">
                <a href="<?php echo $paths['home']; ?>" class="text-sm text-gray-600 hover:text-gray-900">
                    ← Quay lại trang chủ
                </a>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var basePath = '<?php echo $basePath; ?>';
        var API = basePath + '/backend/php/auth.php';
        var timerInterval = null;

        // Get token from URL
        var urlParams = new URLSearchParams(window.location.search);
        var token = urlParams.get('token');

        if (token) {
            verifyToken(token);
        }

        // Toggle password visibility
        document.querySelectorAll('[id^="toggle-"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var inputId = this.id.replace('toggle-', '');
                var input = document.getElementById(inputId);
                if (input.type === 'password') {
                    input.type = 'text';
                } else {
                    input.type = 'password';
                }
            });
        });

        // Request form
        document.getElementById('request-form').onsubmit = async function(e) {
            e.preventDefault();
            var btn = document.getElementById('request-btn');
            var email = document.getElementById('email').value;
            
            btn.disabled = true;
            btn.textContent = 'Đang gửi...';
            hideMessage('request-message');

            try {
                var res = await fetch(API + '?action=forgot_password', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({email: email})
                });
                var data = await res.json();
                
                if (data.success) {
                    showMessage('request-message', 'success', data.message);
                    document.getElementById('email').value = '';
                } else {
                    showMessage('request-message', 'error', data.error);
                }
            } catch(err) {
                showMessage('request-message', 'error', 'Lỗi kết nối. Vui lòng thử lại.');
            }
            
            btn.disabled = false;
            btn.textContent = 'Gửi link đặt lại mật khẩu';
        };

        // Reset form
        document.getElementById('reset-form').onsubmit = async function(e) {
            e.preventDefault();
            var btn = document.getElementById('reset-btn');
            var password = document.getElementById('new-password').value;
            var confirmPassword = document.getElementById('confirm-password').value;
            
            if (password !== confirmPassword) {
                showMessage('reset-message', 'error', 'Mật khẩu xác nhận không khớp');
                return;
            }
            
            btn.disabled = true;
            btn.textContent = 'Đang xử lý...';
            hideMessage('reset-message');

            try {
                var res = await fetch(API + '?action=reset_password', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        token: document.getElementById('reset-token').value,
                        password: password,
                        confirm_password: confirmPassword
                    })
                });
                var data = await res.json();
                
                if (data.success) {
                    document.getElementById('step-reset').style.display = 'none';
                    document.getElementById('step-success').style.display = 'block';
                    if (timerInterval) clearInterval(timerInterval);
                } else {
                    showMessage('reset-message', 'error', data.error);
                }
            } catch(err) {
                showMessage('reset-message', 'error', 'Lỗi kết nối. Vui lòng thử lại.');
            }
            
            btn.disabled = false;
            btn.textContent = 'Đặt lại mật khẩu';
        };

        async function verifyToken(token) {
            try {
                var res = await fetch(API + '?action=verify_reset_token', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({token: token})
                });
                var data = await res.json();
                
                if (data.success) {
                    document.getElementById('step-request').style.display = 'none';
                    document.getElementById('step-reset').style.display = 'block';
                    document.getElementById('reset-email').textContent = data.email;
                    document.getElementById('reset-token').value = token;
                    startTimer(15 * 60);
                } else {
                    showMessage('request-message', 'error', data.error);
                }
            } catch(err) {
                showMessage('request-message', 'error', 'Token không hợp lệ hoặc đã hết hạn.');
            }
        }

        function startTimer(seconds) {
            var timerEl = document.getElementById('timer');
            
            function updateTimer() {
                if (seconds <= 0) {
                    timerEl.textContent = '⚠️ Link đã hết hạn!';
                    timerEl.classList.add('text-red-500');
                    document.getElementById('reset-btn').disabled = true;
                    clearInterval(timerInterval);
                    return;
                }
                
                var mins = Math.floor(seconds / 60);
                var secs = seconds % 60;
                timerEl.textContent = 'Thời gian còn lại: ' + mins + ':' + (secs < 10 ? '0' : '') + secs;
                seconds--;
            }
            
            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
        }

        function showMessage(id, type, text) {
            var el = document.getElementById(id);
            el.textContent = text;
            el.className = 'mb-4 p-4 rounded-lg text-sm ' + 
                (type === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200');
        }

        function hideMessage(id) {
            document.getElementById(id).className = 'hidden mb-4 p-4 rounded-lg text-sm';
        }
    })();
    </script>
</body>
</html>

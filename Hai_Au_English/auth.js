// Authentication JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Login Form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        // Toggle password visibility for login
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');

        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon.classList.toggle('hidden');
                eyeOffIcon.classList.toggle('hidden');
            });
        }

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            // Validation
            let isValid = true;
            
            // Email validation
            if (!validateEmail(email)) {
                showError('email', 'Email không hợp lệ');
                isValid = false;
            } else {
                hideError('email');
            }
            
            // Password validation
            if (password.length < 6) {
                showError('password', 'Mật khẩu phải có ít nhất 6 ký tự');
                isValid = false;
            } else {
                hideError('password');
            }
            
            if (isValid) {
                // Show loading state
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Đang đăng nhập...';
                submitBtn.disabled = true;
                
                // Simulate API call
                setTimeout(() => {
                    // Store user session (in real app, this would be handled by backend)
                    const user = {
                        email: email,
                        loginTime: new Date().toISOString()
                    };
                    
                    if (remember) {
                        localStorage.setItem('user', JSON.stringify(user));
                    } else {
                        sessionStorage.setItem('user', JSON.stringify(user));
                    }
                    
                    // Show success message
                    showToast('Đăng nhập thành công!', 'success');
                    
                    // Redirect to home page
                    setTimeout(() => {
                        window.location.href = 'index.html';
                    }, 1500);
                }, 1500);
            }
        });
    }
    
    // Signup Form
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        // Toggle password visibility for signup
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');

        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon.classList.toggle('hidden');
                eyeOffIcon.classList.toggle('hidden');
            });
        }

        // Toggle confirm password visibility
        const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        const confirmEyeIcon = document.getElementById('confirm-eye-icon');
        const confirmEyeOffIcon = document.getElementById('confirm-eye-off-icon');

        if (toggleConfirmPassword) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                confirmEyeIcon.classList.toggle('hidden');
                confirmEyeOffIcon.classList.toggle('hidden');
            });
        }

        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const fullname = document.getElementById('fullname').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const terms = document.getElementById('terms').checked;
            
            // Validation
            let isValid = true;
            
            // Full name validation
            if (fullname.trim().length < 2) {
                showError('fullname', 'Họ tên phải có ít nhất 2 ký tự');
                isValid = false;
            } else {
                hideError('fullname');
            }
            
            // Email validation
            if (!validateEmail(email)) {
                showError('email', 'Email không hợp lệ');
                isValid = false;
            } else {
                hideError('email');
            }
            
            // Phone validation
            if (!validatePhone(phone)) {
                showError('phone', 'Số điện thoại không hợp lệ');
                isValid = false;
            } else {
                hideError('phone');
            }
            
            // Password validation
            if (password.length < 8) {
                showError('password', 'Mật khẩu phải có ít nhất 8 ký tự');
                isValid = false;
            } else if (!validatePasswordStrength(password)) {
                showError('password', 'Mật khẩu phải chứa chữ hoa, chữ thường và số');
                isValid = false;
            } else {
                hideError('password');
            }
            
            // Confirm password validation
            if (password !== confirmPassword) {
                showError('confirm-password', 'Mật khẩu không khớp');
                isValid = false;
            } else {
                hideError('confirm-password');
            }
            
            // Terms validation
            if (!terms) {
                showToast('Vui lòng đồng ý với điều khoản sử dụng', 'error');
                isValid = false;
            }
            
            if (isValid) {
                // Show loading state
                const submitBtn = signupForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Đang đăng ký...';
                submitBtn.disabled = true;
                
                // Simulate API call
                setTimeout(() => {
                    // Store user data (in real app, this would be handled by backend)
                    const user = {
                        fullname: fullname,
                        email: email,
                        phone: phone,
                        registerTime: new Date().toISOString()
                    };
                    
                    localStorage.setItem('user', JSON.stringify(user));
                    
                    // Show success message
                    showToast('Đăng ký thành công! Đang chuyển hướng...', 'success');
                    
                    // Redirect to login page
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 1500);
                }, 1500);
            }
        });
    }
});

// Helper Functions
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^(0|\+84)[0-9]{9,10}$/;
    return re.test(phone.replace(/\s/g, ''));
}

function validatePasswordStrength(password) {
    // At least one uppercase, one lowercase, and one number
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
    return re.test(password);
}

function showError(fieldId, message) {
    const input = document.getElementById(fieldId);
    const errorSpan = document.getElementById(`${fieldId}-error`);
    
    if (input) {
        input.classList.add('error-input');
        input.classList.add('border-red-500');
    }
    
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.classList.remove('hidden');
    }
}

function hideError(fieldId) {
    const input = document.getElementById(fieldId);
    const errorSpan = document.getElementById(`${fieldId}-error`);
    
    if (input) {
        input.classList.remove('error-input');
        input.classList.remove('border-red-500');
    }
    
    if (errorSpan) {
        errorSpan.textContent = '';
        errorSpan.classList.add('hidden');
    }
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="flex items-center gap-3">
            ${type === 'success' ? `
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            ` : type === 'error' ? `
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            ` : `
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            `}
            <p class="text-gray-800">${message}</p>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Check if user is logged in
function checkAuth() {
    const user = localStorage.getItem('user') || sessionStorage.getItem('user');
    return user !== null;
}

// Logout function
function logout() {
    localStorage.removeItem('user');
    sessionStorage.removeItem('user');
    showToast('Đăng xuất thành công', 'success');
    setTimeout(() => {
        window.location.href = 'index.html';
    }, 1000);
}

// Frontend auth controller (login / signup) - logic only
document.addEventListener('DOMContentLoaded', function() {
    // Login Form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function(e) {
                e.preventDefault();
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon?.classList.toggle('hidden');
                eyeOffIcon?.classList.toggle('hidden');
            });
        }

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember')?.checked;

            let isValid = true;
            if (!validateEmail(email)) { showError('email', 'Email không hợp lệ'); isValid = false; } else { hideError('email'); }
            if (password.length < 6) { showError('password', 'Mật khẩu phải có ít nhất 6 ký tự'); isValid = false; } else { hideError('password'); }

            if (isValid) {
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Đang đăng nhập...';
                submitBtn.disabled = true;

                // TODO: Call backend authService.login
                setTimeout(() => {
                    const user = { email, loginTime: new Date().toISOString() };
                    if (remember) localStorage.setItem('user', JSON.stringify(user)); else sessionStorage.setItem('user', JSON.stringify(user));
                    window.showToast?.('Đăng nhập thành công!', 'success');
                    setTimeout(() => window.location.href = 'index.html', 1200);
                }, 800);
            }
        });
    }

    // Signup Form
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function(e) {
                e.preventDefault();
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon?.classList.toggle('hidden');
                eyeOffIcon?.classList.toggle('hidden');
            });
        }

        const toggleConfirm = document.getElementById('toggle-confirm-password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        const confirmEyeIcon = document.getElementById('confirm-eye-icon');
        const confirmEyeOffIcon = document.getElementById('confirm-eye-off-icon');
        if (toggleConfirm && confirmPasswordInput) {
            toggleConfirm.addEventListener('click', (e) => {
                e.preventDefault();
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                confirmEyeIcon?.classList.toggle('hidden');
                confirmEyeOffIcon?.classList.toggle('hidden');
            });
        }

        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const fullname = document.getElementById('fullname').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const terms = document.getElementById('terms')?.checked;

            let isValid = true;
            if (fullname.trim().length < 2) { showError('fullname', 'Họ tên phải có ít nhất 2 ký tự'); isValid = false; } else { hideError('fullname'); }
            if (!validateEmail(email)) { showError('email', 'Email không hợp lệ'); isValid = false; } else { hideError('email'); }
            if (!validatePhone(phone)) { showError('phone', 'Số điện thoại không hợp lệ'); isValid = false; } else { hideError('phone'); }
            if (password.length < 8) { showError('password', 'Mật khẩu phải có ít nhất 8 ký tự'); isValid = false; } else if (!validatePasswordStrength(password)) { showError('password', 'Mật khẩu phải chứa chữ hoa, chữ thường và số'); isValid = false; } else { hideError('password'); }
            if (password !== confirmPassword) { showError('confirm-password', 'Mật khẩu không khớp'); isValid = false; } else { hideError('confirm-password'); }
            if (!terms) { window.showToast?.('Vui lòng đồng ý với điều khoản sử dụng', 'error'); isValid = false; }

            if (isValid) {
                const submitBtn = signupForm.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Đang đăng ký...';
                submitBtn.disabled = true;
                setTimeout(() => {
                    const user = { fullname, email, phone, registerTime: new Date().toISOString() };
                    localStorage.setItem('user', JSON.stringify(user));
                    window.showToast?.('Đăng ký thành công! Đang chuyển hướng...', 'success');
                    setTimeout(() => window.location.href = 'login.html', 1200);
                }, 1000);
            }
        });
    }
});

// Helpers
function validateEmail(email) { const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; return re.test(email); }
function validatePhone(phone) { const re = /^(0|\+84)[0-9]{9,10}$/; return re.test(phone.replace(/\s/g, '')); }
function validatePasswordStrength(password) { const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/; return re.test(password); }
function showError(fieldId, message) {
    const input = document.getElementById(fieldId);
    const errorSpan = document.getElementById(`${fieldId}-error`);
    if (input) input.classList.add('error-input');
    if (errorSpan) { errorSpan.textContent = message; errorSpan.classList.remove('hidden'); }
}
function hideError(fieldId) {
    const input = document.getElementById(fieldId);
    const errorSpan = document.getElementById(`${fieldId}-error`);
    if (input) input.classList.remove('error-input');
    if (errorSpan) { errorSpan.textContent = ''; errorSpan.classList.add('hidden'); }
}

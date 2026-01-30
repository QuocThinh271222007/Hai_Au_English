// auth.js - Xử lý form đăng ký/đăng nhập với backend PHP

// Toggle password visibility - chạy ngay khi DOM ready
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    // Toggle password
    const toggleBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeOffIcon = document.getElementById('eye-off-icon');
    
    if (toggleBtn && passwordInput) {
      toggleBtn.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        
        if (eyeIcon && eyeOffIcon) {
          eyeIcon.classList.toggle('hidden', isPassword);
          eyeOffIcon.classList.toggle('hidden', !isPassword);
        }
      });
    }

    // Toggle confirm password (signup page)
    const toggleConfirmBtn = document.getElementById('toggle-confirm-password');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const eyeIconConfirm = document.getElementById('confirm-eye-icon');
    const eyeOffIconConfirm = document.getElementById('confirm-eye-off-icon');
    
    if (toggleConfirmBtn && confirmPasswordInput) {
      toggleConfirmBtn.addEventListener('click', function() {
        const isPassword = confirmPasswordInput.type === 'password';
        confirmPasswordInput.type = isPassword ? 'text' : 'password';
        
        if (eyeIconConfirm && eyeOffIconConfirm) {
          eyeIconConfirm.classList.toggle('hidden', isPassword);
          eyeOffIconConfirm.classList.toggle('hidden', !isPassword);
        }
      });
    }
  });
})();

// Form handling với async import
document.addEventListener('DOMContentLoaded', async function() {
  const loginForm = document.getElementById('login-form');
  const signupForm = document.getElementById('signup-form');
  const formMessage = document.getElementById('form-message');

  let authService = null;
  try {
    const module = await import('../services/authService.js');
    authService = module.default || module.authService;
  } catch (e) {
    console.error('Failed to load authService:', e);
  }

  if (loginForm) {
    loginForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      if (!authService) {
        showFormMessage('error', 'Service not available');
        return;
      }
      const email = loginForm.email.value;
      const password = loginForm.password.value;
      try {
        const result = await authService.login({ email, password });
        showFormMessage('success', 'Đăng nhập thành công!');
      } catch (err) {
        showFormMessage('error', err.message);
      }
    });
  }

  if (signupForm) {
    signupForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      if (!authService) {
        showFormMessage('error', 'Service not available');
        return;
      }
      const fullname = signupForm.fullname.value;
      const email = signupForm.email.value;
      const password = signupForm.password.value;
      try {
        const result = await authService.register({ fullname, email, password });
        showFormMessage('success', 'Đăng ký thành công!');
        signupForm.reset();
      } catch (err) {
        showFormMessage('error', err.message);
      }
    });
  }

  function showFormMessage(type, message) {
    if (!formMessage) return;
    formMessage.textContent = message;
    formMessage.className = type;
    formMessage.classList.remove('hidden');
    setTimeout(() => formMessage.classList.add('hidden'), 5000);
  }
});
export default null;

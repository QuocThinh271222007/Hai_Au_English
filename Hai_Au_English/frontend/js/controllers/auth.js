(function() {
  // Auto-detect base path for XAMPP
  function getBasePath() {
    var path = window.location.pathname.toLowerCase();
    // Check for hai_au_english folder (case insensitive)
    if (path.includes('/hai_au_english')) {
      return '/hai_au_english';
    }
    return '';
  }
  var basePath = getBasePath();
  var API = basePath + '/backend/php/auth.php';

  function popup(type, title, msg, cb) {
    var old = document.getElementById('auth-popup');
    if (old) old.parentNode.removeChild(old);

    var ok = type === 'success';
    var bg = ok ? '#dcfce7' : '#fee2e2';
    var clr = ok ? '#22c55e' : '#ef4444';
    var icon = ok ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12';
    var cbCalled = false;

    var div = document.createElement('div');
    div.id = 'auth-popup';
    div.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:99999';

    div.innerHTML = '<div style="background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;text-align:center"><div style="width:80px;height:80px;margin:0 auto 16px;border-radius:50%;background:'+bg+';display:flex;align-items:center;justify-content:center"><svg width="40" height="40" fill="none" stroke="'+clr+'" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="'+icon+'"/></svg></div><h3 style="font-size:24px;font-weight:bold;margin:0 0 8px;color:'+clr+'">'+title+'</h3><p style="color:#6b7280;margin:0 0 24px;font-size:16px">'+msg+'</p><button id="popup-btn" style="padding:12px 32px;border-radius:8px;font-weight:600;color:#fff;border:none;cursor:pointer;background:'+clr+'">'+(ok?'OK':'Dong')+'</button></div>';

    document.body.appendChild(div);

    function runCallback() {
      if (!cbCalled && cb) {
        cbCalled = true;
        cb();
      }
    }

    document.getElementById('popup-btn').onclick = function() {
      var p = document.getElementById('auth-popup');
      if (p) p.parentNode.removeChild(p);
      runCallback();
    };

    if (ok) {
      setTimeout(function() {
        var p = document.getElementById('auth-popup');
        if (p) p.parentNode.removeChild(p);
        runCallback();
      }, 1500);
    }
  }

  // Expose popup globally for other scripts
  window.popup = popup;

  function api(action, data, cb) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', API + '?action=' + action);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.withCredentials = true;
    xhr.onload = function() {
      try {
        cb(null, JSON.parse(xhr.responseText));
      } catch(e) {
        cb(e, null);
      }
    };
    xhr.onerror = function() { cb(new Error('Network error'), null); };
    xhr.send(JSON.stringify(data));
  }

  // Async version of api call with reCAPTCHA support
  async function apiWithRecaptcha(action, data) {
    // Try to get reCAPTCHA token if available
    if (window.getRecaptchaToken) {
      try {
        var token = await window.getRecaptchaToken(action);
        if (token) {
          data.recaptcha_token = token;
        }
      } catch(e) {
        console.warn('reCAPTCHA token failed:', e);
      }
    }
    
    return new Promise(function(resolve, reject) {
      api(action, data, function(err, res) {
        if (err) reject(err);
        else resolve(res);
      });
    });
  }

  function togglePassword(inputId, eyeId, eyeOffId) {
    var input = document.getElementById(inputId);
    var eye = document.getElementById(eyeId);
    var eyeOff = document.getElementById(eyeOffId);

    if (!input) return;

    if (input.type === 'password') {
      input.type = 'text';
      if (eye) eye.classList.add('hidden');
      if (eyeOff) eyeOff.classList.remove('hidden');
    } else {
      input.type = 'password';
      if (eye) eye.classList.remove('hidden');
      if (eyeOff) eyeOff.classList.add('hidden');
    }
  }

  // Set button loading state
  function setButtonLoading(btn, isLoading) {
    if (!btn) return;
    btn.disabled = isLoading;
    if (isLoading) {
      btn.dataset.originalText = btn.textContent;
      btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Đang xử lý...';
    } else {
      btn.textContent = btn.dataset.originalText || 'Submit';
    }
  }

  window.onload = function() {
    console.log('Auth loaded, basePath:', basePath);

    // Login form
    var lf = document.getElementById('login-form');
    if (lf) {
      lf.onsubmit = async function(e) {
        e.preventDefault();
        var submitBtn = document.getElementById('login-submit-btn') || lf.querySelector('button[type="submit"]');
        
        setButtonLoading(submitBtn, true);
        
        try {
          var data = {
            email: lf.email.value, 
            password: lf.password.value
          };
          
          var res = await apiWithRecaptcha('login', data);
          
          if (res.success && res.user) {
            popup('success', 'Thành công!', 'Chào mừng ' + res.user.fullname, function() {
              // Redirect admin về admin, user về profile
              if (res.user.role === 'admin') {
                window.location.href = basePath + '/QuanTri';
              } else {
                window.location.href = basePath + '/HocVien';
              }
            });
          } else {
            popup('error', 'Thất bại!', res.error || 'Sai thông tin');
          }
        } catch(err) {
          popup('error', 'Lỗi!', 'Không kết nối được máy chủ');
        } finally {
          setButtonLoading(submitBtn, false);
        }
      };
    }

    // Signup form
    var sf = document.getElementById('signup-form');
    if (sf) {
      sf.onsubmit = async function(e) {
        e.preventDefault();
        var submitBtn = document.getElementById('signup-submit-btn') || sf.querySelector('button[type="submit"]');
        
        // Lấy giá trị
        var fullname = sf.fullname.value.trim();
        var email = sf.email.value.trim();
        var password = sf.password.value;
        var phone = sf.phone ? sf.phone.value.trim().replace(/[\s\-]/g, '') : '';
        
        // === VALIDATION PHÍA CLIENT ===
        
        // Kiểm tra họ tên
        if (!fullname) {
          popup('error', 'Lỗi!', 'Vui lòng nhập họ và tên');
          return;
        }
        
        // Kiểm tra email
        if (!email) {
          popup('error', 'Lỗi!', 'Vui lòng nhập email');
          return;
        }
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
          popup('error', 'Lỗi!', 'Email không hợp lệ');
          return;
        }
        
        // Kiểm tra mật khẩu
        if (!password || password.length < 6) {
          popup('error', 'Lỗi!', 'Mật khẩu phải có ít nhất 6 ký tự');
          return;
        }
        
        // Kiểm tra số điện thoại
        if (!phone) {
          popup('error', 'Lỗi!', 'Vui lòng nhập số điện thoại');
          return;
        }
        
        // Chỉ chứa số
        if (!/^[0-9]+$/.test(phone)) {
          popup('error', 'Lỗi!', 'Số điện thoại chỉ được chứa chữ số');
          return;
        }
        
        // Đúng 10 số
        if (phone.length !== 10) {
          popup('error', 'Lỗi!', 'Số điện thoại phải có đúng 10 chữ số');
          return;
        }
        
        // Đầu số hợp lệ (Việt Nam: 03, 05, 07, 08, 09)
        if (!/^(03|05|07|08|09)[0-9]{8}$/.test(phone)) {
          popup('error', 'Lỗi!', 'Số điện thoại không hợp lệ (phải bắt đầu bằng 03, 05, 07, 08 hoặc 09)');
          return;
        }
        
        // === GỬI REQUEST ===
        setButtonLoading(submitBtn, true);
        
        try {
          var data = {
            fullname: fullname,
            email: email,
            password: password,
            phone: phone
          };
          
          var res = await apiWithRecaptcha('register', data);
          
          if (res.success && res.user) {
            popup('success', 'Thành công!', 'Đăng ký thành công!', function() {
              window.location.href = basePath + '/HocVien';
            });
          } else {
            popup('error', 'Thất bại!', res.error || 'Có lỗi xảy ra');
          }
        } catch(err) {
          popup('error', 'Lỗi!', 'Không kết nối được máy chủ');
        } finally {
          setButtonLoading(submitBtn, false);
        }
      };
    }

    // Toggle password - main
    var tb = document.getElementById('toggle-password');
    if (tb) {
      tb.onclick = function() {
        togglePassword('password', 'eye-icon', 'eye-off-icon');
      };
    }

    // Toggle confirm password
    var tcb = document.getElementById('toggle-confirm-password');
    if (tcb) {
      tcb.onclick = function() {
        togglePassword('confirm-password', 'confirm-eye-icon', 'confirm-eye-off-icon');
      };
    }
  };
})();
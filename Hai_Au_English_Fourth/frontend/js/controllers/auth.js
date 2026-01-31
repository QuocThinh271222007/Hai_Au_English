(function() {
  var API = '/hai_au_english/backend/php/auth.php';

  function popup(type, title, msg, cb) {
    var old = document.getElementById('auth-popup');
    if (old) old.parentNode.removeChild(old);

    var ok = type === 'success';
    var bg = ok ? '#dcfce7' : '#fee2e2';
    var clr = ok ? '#22c55e' : '#ef4444';
    var icon = ok ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12';

    var div = document.createElement('div');
    div.id = 'auth-popup';
    div.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:99999';

    div.innerHTML = '<div style="background:#fff;border-radius:16px;padding:32px;max-width:400px;width:90%;text-align:center"><div style="width:80px;height:80px;margin:0 auto 16px;border-radius:50%;background:'+bg+';display:flex;align-items:center;justify-content:center"><svg width="40" height="40" fill="none" stroke="'+clr+'" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="'+icon+'"/></svg></div><h3 style="font-size:24px;font-weight:bold;margin:0 0 8px;color:'+clr+'">'+title+'</h3><p style="color:#6b7280;margin:0 0 24px;font-size:16px">'+msg+'</p><button id="popup-btn" style="padding:12px 32px;border-radius:8px;font-weight:600;color:#fff;border:none;cursor:pointer;background:'+clr+'">'+(ok?'OK':'Dong')+'</button></div>';

    document.body.appendChild(div);

    document.getElementById('popup-btn').onclick = function() {
      div.parentNode.removeChild(div);
      if (cb) cb();
    };

    if (ok) {
      setTimeout(function() {
        var p = document.getElementById('auth-popup');
        if (p) { p.parentNode.removeChild(p); if (cb) cb(); }
      }, 2000);
    }
  }

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

  window.onload = function() {
    console.log('Auth loaded');

    // Login form
    var lf = document.getElementById('login-form');
    if (lf) {
      lf.onsubmit = function(e) {
        e.preventDefault();
        api('login', {email: lf.email.value, password: lf.password.value}, function(err, res) {
          if (err) { popup('error', 'Loi!', 'Khong ket noi duoc'); return; }
          if (res.success && res.user) {
            popup('success', 'Thanh cong!', 'Chao mung ' + res.user.fullname, function() {
              // Redirect admin về admin.html, user về profile.html
              if (res.user.role === 'admin') {
                window.location.href = 'admin.html';
              } else {
                window.location.href = 'profile.html';
              }
            });
          } else {
            popup('error', 'That bai!', res.error || 'Sai thong tin');
          }
        });
      };
    }

    // Signup form
    var sf = document.getElementById('signup-form');
    if (sf) {
      sf.onsubmit = function(e) {
        e.preventDefault();
        var data = {
          fullname: sf.fullname.value,
          email: sf.email.value,
          password: sf.password.value,
          phone: sf.phone ? sf.phone.value : ''
        };
        api('register', data, function(err, res) {
          if (err) { popup('error', 'Loi!', 'Khong ket noi duoc'); return; }
          if (res.success && res.user) {
            popup('success', 'Thanh cong!', 'Dang ky thanh cong!', function() {
              window.location.href = 'profile.html';
            });
          } else {
            popup('error', 'That bai!', res.error || 'Co loi');
          }
        });
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
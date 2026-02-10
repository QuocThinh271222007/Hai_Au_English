(function() {
  // Auto-detect base path for XAMPP/Hostinger
  function getBasePath() {
    var path = window.location.pathname;
    var match = path.match(/\/Hai_Au_English/i);
    return match ? '/Hai_Au_English' : '';
  }
  var API = getBasePath() + '/backend/php/auth.php';

  function checkSession(callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', API + '?action=check', true);
    xhr.withCredentials = true;
    xhr.onload = function() {
      if (xhr.status === 200) {
        try {
          var res = JSON.parse(xhr.responseText);
          callback(res.success ? res.user : null);
        } catch(e) {
          callback(null);
        }
      } else {
        callback(null);
      }
    };
    xhr.onerror = function() { callback(null); };
    xhr.send();
  }

  // Helper: Generate avatar HTML
  function getAvatarHTML(user, size) {
    size = size || 36;
    // Check for both avatar and avatar_url (for backward compatibility)
    var avatarField = user && (user.avatar || user.avatar_url);
    if (avatarField) {
      // Handle relative path - add base path if needed
      var avatarUrl = avatarField;
      var basePath = getBasePath();
      
      // Nếu URL không bắt đầu bằng http và không bắt đầu bằng /
      if (avatarUrl && !avatarUrl.startsWith('http') && !avatarUrl.startsWith('/')) {
        avatarUrl = basePath + '/' + avatarUrl;
      } 
      // Nếu URL bắt đầu bằng / nhưng chưa có base path (case-insensitive check)
      else if (avatarUrl && avatarUrl.startsWith('/') && !avatarUrl.toLowerCase().startsWith('/hai_au_english')) {
        avatarUrl = basePath + avatarUrl;
      }
      
      // Debug log
      console.log('Avatar URL:', avatarUrl, 'Base path:', basePath);
      
      return '<img src="' + avatarUrl + '" alt="' + (user.fullname || 'Avatar') + '" class="w-full h-full object-cover rounded-full" onerror="this.style.display=\'none\'; this.parentElement.innerHTML=\'<svg class=\\\'w-5 h-5 text-white\\\' fill=\\\'currentColor\\\' viewBox=\\\'0 0 24 24\\\'><path d=\\\'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z\\\'/></svg>\';">';
    }
    // Default SVG avatar
    return '<svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>';
  }

  function updateHeader(user) {
    var authButtons = document.getElementById('auth-buttons');
    var userMenu = document.getElementById('user-menu');
    var mobileAuth = document.getElementById('mobile-auth');
    var mobileUser = document.getElementById('mobile-user');

    if (user) {
      var displayName = user.fullname || 'Hoc vien';

      // Desktop: AN login/signup, HIEN user menu
      if (authButtons) {
        authButtons.classList.add('hidden');
        authButtons.classList.remove('md:flex');
      }
      if (userMenu) {
        userMenu.classList.remove('hidden', 'md:hidden');
        userMenu.classList.add('hidden', 'md:flex');
        var userName = document.getElementById('user-name');
        if (userName) userName.textContent = displayName;
        
        // Update desktop avatar
        var desktopAvatar = document.getElementById('user-avatar');
        if (desktopAvatar) {
          desktopAvatar.innerHTML = getAvatarHTML(user);
          desktopAvatar.classList.remove('bg-blue-500');
        }
      }

      // Mobile
      if (mobileAuth) mobileAuth.style.display = 'none';
      if (mobileUser) {
        mobileUser.style.display = 'block';
        var mobileUserName = document.getElementById('mobile-user-name');
        if (mobileUserName) mobileUserName.textContent = displayName;
        
        // Update mobile avatar
        var mobileAvatar = document.getElementById('mobile-user-avatar');
        if (mobileAvatar) {
          mobileAvatar.innerHTML = getAvatarHTML(user);
          mobileAvatar.classList.remove('bg-blue-500');
        }
      }
    } else {
      // Chua dang nhap: HIEN login/signup (chi desktop), AN user menu
      if (authButtons) {
        authButtons.classList.remove('hidden', 'md:hidden');
        authButtons.classList.add('hidden', 'md:flex');
      }
      if (userMenu) {
        userMenu.classList.add('hidden');
        userMenu.classList.remove('md:flex');
      }
      if (mobileAuth) mobileAuth.style.display = 'block';
      if (mobileUser) mobileUser.style.display = 'none';
    }
  }

  function handleLogout(e) {
    e.preventDefault();
    var basePath = getBasePath();
    var xhr = new XMLHttpRequest();
    xhr.open('GET', API + '?action=logout', true);
    xhr.withCredentials = true;
    xhr.onload = function() {
      window.location.replace(basePath + '/TrangChu');
    };
    xhr.onerror = function() {
      window.location.replace(basePath + '/TrangChu');
    };
    xhr.send();
  }

  function init() {
    checkSession(function(user) {
      updateHeader(user);
    });

    var logoutBtns = document.querySelectorAll('.logout-btn');
    for (var i = 0; i < logoutBtns.length; i++) {
      logoutBtns[i].addEventListener('click', handleLogout);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
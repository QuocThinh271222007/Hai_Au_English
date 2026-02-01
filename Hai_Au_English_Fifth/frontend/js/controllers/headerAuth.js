(function() {
  var API = '/hai_au_english/backend/php/auth.php';

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

  function updateHeader(user) {
    var authButtons = document.getElementById('auth-buttons');
    var userMenu = document.getElementById('user-menu');
    var mobileAuth = document.getElementById('mobile-auth');
    var mobileUser = document.getElementById('mobile-user');

    if (user) {
      var displayName = user.fullname || 'Hoc vien';

      // Desktop: AN login/signup, HIEN user menu
      if (authButtons) {
        authButtons.style.display = 'none';
      }
      if (userMenu) {
        userMenu.style.display = 'flex';
        var userName = document.getElementById('user-name');
        if (userName) userName.textContent = displayName;
      }

      // Mobile
      if (mobileAuth) mobileAuth.style.display = 'none';
      if (mobileUser) {
        mobileUser.style.display = 'block';
        var mobileUserName = document.getElementById('mobile-user-name');
        if (mobileUserName) mobileUserName.textContent = displayName;
      }
    } else {
      // Chua dang nhap: HIEN login/signup, AN user menu
      if (authButtons) {
        authButtons.style.display = 'flex';
      }
      if (userMenu) {
        userMenu.style.display = 'none';
      }
      if (mobileAuth) mobileAuth.style.display = 'block';
      if (mobileUser) mobileUser.style.display = 'none';
    }
  }

  function handleLogout(e) {
    e.preventDefault();
    var xhr = new XMLHttpRequest();
    xhr.open('GET', API + '?action=logout', true);
    xhr.withCredentials = true;
    xhr.onload = function() {
      window.location.href = 'index.html';
    };
    xhr.onerror = function() {
      window.location.href = 'index.html';
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
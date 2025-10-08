// ============== Cookie Manager ==============
window.CookieManager = {
    set: function (name, value, days, path) {
      days = days || 30; // výchozí 30 dní
      path = path || "/";
      var expires = "";
      if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
      }
      document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=" + path;
    },
  
    get: function (name) {
      var nameEQ = encodeURIComponent(name) + "=";
      var ca = document.cookie.split(';');
      for (var i = 0; i < ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length));
      }
      return null;
    },
  
    delete: function (name, path) {
      path = path || "/";
      document.cookie = encodeURIComponent(name) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=" + path;
    },
  
    getAll: function () {
      var cookies = {};
      var ca = document.cookie.split(';');
      for (var i = 0; i < ca.length; i++) {
        var c = ca[i].trim();
        if (c) {
          var parts = c.split('=');
          var key = decodeURIComponent(parts.shift());
          var val = decodeURIComponent(parts.join('='));
          cookies[key] = val;
        }
      }
      return cookies;
    }
  };
  
  // ============== Příklad použití ==============
  // CookieManager.set('lang', 'cs', 7);
  // alert(CookieManager.get('lang'));
  // CookieManager.delete('lang');
  // console.log(CookieManager.getAll());
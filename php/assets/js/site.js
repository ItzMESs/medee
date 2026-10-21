(function () {
  // Off-canvas menu
  var drawer = document.getElementById('drawer');
  function setDrawer(open) {
    document.body.classList.toggle('drawer-open', open);
    if (drawer) drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
  }
  document.querySelectorAll('[data-drawer-open]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(true); }); });
  document.querySelectorAll('[data-drawer-close]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(false); }); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setDrawer(false); });

  // "Шинэ / Хандалттай" tabs
  document.querySelectorAll('[data-tabs]').forEach(function (box) {
    var buttons = box.querySelectorAll('[data-tab]');
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        buttons.forEach(function (b) { b.classList.toggle('active', b === btn); });
        box.querySelectorAll('[data-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== btn.getAttribute('data-tab'); });
      });
    });
  });

  // Search drops down under the header bar
  var searchBtn = document.querySelector('.search-toggle');
  if (searchBtn) {
    searchBtn.addEventListener('click', function () {
      var open = document.body.classList.toggle('search-open');
      searchBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) document.querySelector('.header-search input').focus();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { document.body.classList.remove('search-open'); searchBtn.setAttribute('aria-expanded', 'false'); }
    });
  }

  // Back to top button
  var toTop = document.querySelector('.to-top');
  if (toTop) {
    var onScroll = function () { toTop.classList.toggle('show', window.scrollY > 600); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    toTop.addEventListener('click', function (e) { e.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' }); });
  }

  // Comment character counter
  document.querySelectorAll('[data-counter]').forEach(function (ta) {
    var out = document.getElementById(ta.getAttribute('data-counter'));
    var update = function () { if (out) out.textContent = ta.value.length; };
    ta.addEventListener('input', update);
    update();
  });

  // Reading progress bar on article pages
  var bar = document.querySelector('.read-progress span');
  var body = document.querySelector('.article-body');
  if (bar && body) {
    var progress = function () {
      var rect = body.getBoundingClientRect();
      var total = rect.height - window.innerHeight * 0.6;
      var done = Math.min(1, Math.max(0, -rect.top / Math.max(1, total)));
      bar.style.width = (done * 100).toFixed(1) + '%';
    };
    window.addEventListener('scroll', progress, { passive: true });
    window.addEventListener('resize', progress);
    progress();
  }

  // Emoji reactions
  var box = document.querySelector('.reactions');
  if (box && window.fetch) {
    box.addEventListener('click', function (e) {
      var btn = e.target.closest('.reaction');
      if (!btn || box.classList.contains('busy')) return;
      box.classList.add('busy');
      var body = new URLSearchParams({ type: btn.getAttribute('data-type') });
      fetch(box.getAttribute('data-react-url'), { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data.counts) return;
          box.querySelectorAll('.reaction').forEach(function (b) {
            var t = b.getAttribute('data-type');
            b.querySelector('.count').textContent = data.counts[t];
            b.classList.toggle('active', data.mine === t);
            b.setAttribute('aria-pressed', data.mine === t ? 'true' : 'false');
          });
          if (data.mine) { btn.classList.remove('pop'); void btn.offsetWidth; btn.classList.add('pop'); }
        })
        .catch(function () {})
        .then(function () { box.classList.remove('busy'); });
    });
  }

  // Web Push: bell in the header plus a one-time invitation card
  var bell = document.querySelector('.push-btn');
  if (bell && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window) {
    var base = bell.getAttribute('data-push-base');
    var key = bell.getAttribute('data-push-key');
    var toast = function (text) {
      var t = document.createElement('div');
      t.className = 'toast';
      t.textContent = text;
      document.body.appendChild(t);
      setTimeout(function () { t.classList.add('show'); }, 10);
      setTimeout(function () { t.classList.remove('show'); setTimeout(function () { t.remove(); }, 300); }, 3500);
    };
    var keyBytes = function (b64) {
      var pad = '='.repeat((4 - b64.length % 4) % 4);
      var raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
      var out = new Uint8Array(raw.length);
      for (var i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
      return out;
    };
    var post = function (path, sub) {
      return fetch(base + path, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(sub), credentials: 'same-origin' });
    };
    var setState = function (on) {
      bell.classList.toggle('subscribed', on);
      bell.title = on ? 'Мэдэгдэл идэвхтэй (дарж зогсооно)' : 'Шинэ мэдээний мэдэгдэл авах';
    };
    var subscribe = function () {
      return Notification.requestPermission().then(function (perm) {
        if (perm !== 'granted') { toast('Мэдэгдлийг хөтчийн тохиргооноос зөвшөөрнө үү.'); return; }
        return navigator.serviceWorker.ready.then(function (reg) {
          return reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyBytes(key) });
        }).then(function (sub) {
          return post('push/subscribe', sub.toJSON()).then(function () { setState(true); toast('Баярлалаа! Шинэ мэдээг танд мэдэгдэнэ.'); });
        });
      }).catch(function () { toast('Мэдэгдэл тохируулж чадсангүй.'); });
    };
    navigator.serviceWorker.register(base + 'sw.js', { scope: base }).then(function (reg) {
      bell.hidden = false;
      return reg.pushManager.getSubscription();
    }).then(function (sub) {
      setState(!!sub);
      var seen = false;
      try { seen = localStorage.getItem('push-invite') === '1'; } catch (e) {}
      if (!sub && !seen && Notification.permission === 'default') {
        setTimeout(function () {
          var card = document.createElement('div');
          card.className = 'push-invite';
          card.innerHTML = '<div class="push-invite-icon">🔔</div><div><b>Шинэ мэдээг түрүүлж аваарай</b><p>Чухал мэдээ гармагц танд мэдэгдэнэ.</p>'
            + '<div class="push-invite-actions"><button type="button" class="yes">Зөвшөөрөх</button><button type="button" class="no">Дараа</button></div></div>';
          document.body.appendChild(card);
          setTimeout(function () { card.classList.add('show'); }, 10);
          var close = function () { card.classList.remove('show'); setTimeout(function () { card.remove(); }, 300); try { localStorage.setItem('push-invite', '1'); } catch (e) {} };
          card.querySelector('.yes').addEventListener('click', function () { close(); subscribe(); });
          card.querySelector('.no').addEventListener('click', close);
        }, 25000);
      }
    }).catch(function () {});
    bell.addEventListener('click', function () {
      navigator.serviceWorker.ready.then(function (reg) { return reg.pushManager.getSubscription(); }).then(function (sub) {
        if (!sub) return subscribe();
        if (!confirm('Шинэ мэдээний мэдэгдлийг зогсоох уу?')) return;
        return post('push/unsubscribe', { endpoint: sub.endpoint }).then(function () { return sub.unsubscribe(); })
          .then(function () { setState(false); toast('Мэдэгдлийг зогсоолоо.'); });
      });
    });
  }

  // Header shadow once the page scrolls
  var header = document.querySelector('.site-header');
  if (header) {
    window.addEventListener('scroll', function () { header.classList.toggle('scrolled', window.scrollY > 10); }, { passive: true });
  }

  // Sticky phone banner: hidden for the rest of the visit once closed.
  var sticky = document.querySelector('.ad-sticky');
  if (sticky) {
    var closed = false;
    try { closed = sessionStorage.getItem('adStickyClosed') === '1'; } catch (e) {}
    if (!closed) { sticky.hidden = false; document.body.classList.add('has-sticky-ad'); }
    sticky.querySelector('.ad-sticky-close').addEventListener('click', function () {
      sticky.remove();
      document.body.classList.remove('has-sticky-ad');
      try { sessionStorage.setItem('adStickyClosed', '1'); } catch (e) {}
    });
  }

  // Banner slider: arrows, dashes, auto-advance (paused on hover), swipe on phones.
  document.querySelectorAll('.ad-carousel').forEach(function (box) {
    var track = box.querySelector('.adc-track');
    var slides = box.querySelectorAll('.adc-slide');
    var dots = box.querySelectorAll('.adc-dot');
    var time = parseInt(box.getAttribute('data-interval'), 10) || 6000;
    var seen = {};
    var index = parseInt(box.getAttribute('data-start'), 10) || 0;
    var timer = null;
    var visible = false;
    box.style.setProperty('--adc-time', time / 1000 + 's');
    function countView(i) {
      var id = slides[i].getAttribute('data-ad');
      if (!visible || seen[id]) return;
      seen[id] = true;
      var u = box.getAttribute('data-view-url') + id + '/view';
      if (navigator.sendBeacon) navigator.sendBeacon(u); else fetch(u, { method: 'POST', keepalive: true });
    }
    function go(i) {
      index = (i + slides.length) % slides.length;
      track.style.transform = 'translateX(' + (-100 * index) + '%)';
      slides.forEach(function (s, k) { s.setAttribute('aria-hidden', k === index ? 'false' : 'true'); });
      dots.forEach(function (d, k) {
        d.classList.remove('active');
        d.classList.toggle('done', k < index);
      });
      void box.offsetWidth; // restart the dash animation
      dots[index].classList.add('active');
      countView(index);
      clearTimeout(timer);
      if (!box.classList.contains('paused')) timer = setTimeout(function () { go(index + 1); }, time);
    }
    box.querySelector('.adc-prev').addEventListener('click', function () { go(index - 1); });
    box.querySelector('.adc-next').addEventListener('click', function () { go(index + 1); });
    dots.forEach(function (d, k) { d.addEventListener('click', function () { go(k); }); });
    box.addEventListener('mouseenter', function () { box.classList.add('paused'); clearTimeout(timer); });
    box.addEventListener('mouseleave', function () { box.classList.remove('paused'); go(index); });
    var startX = null;
    track.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend', function (e) {
      if (startX === null) return;
      var dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
      startX = null;
    });
    // Views count only once the slider is actually on screen.
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (en) { visible = en.isIntersecting; if (visible) countView(index); });
      }, { threshold: 0.5 }).observe(box);
    } else {
      visible = true;
    }
    go(index);
  });
})();

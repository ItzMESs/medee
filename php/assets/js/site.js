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

  // Header shadow once the page scrolls
  var header = document.querySelector('.site-header');
  if (header) {
    window.addEventListener('scroll', function () { header.classList.toggle('scrolled', window.scrollY > 10); }, { passive: true });
  }
})();

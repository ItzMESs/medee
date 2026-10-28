(function () {
  var header = document.getElementById('header');
  var body = document.body;

  // Гүйлгэхэд толгой хэсэг бараан болж жижгэрнэ
  function onScroll() { header.classList.toggle('scrolled', window.scrollY > 60); }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Утасны цэс
  var toggle = document.getElementById('navToggle');
  toggle.addEventListener('click', function () {
    var open = body.classList.toggle('nav-open');
    toggle.setAttribute('aria-expanded', open);
  });
  document.querySelectorAll('.has-sub > a').forEach(function (a) {
    a.addEventListener('click', function (ev) {
      if (window.innerWidth <= 900) { ev.preventDefault(); a.parentNode.classList.toggle('open'); }
    });
  });

  // Слайдер
  var slides = document.querySelectorAll('.hero .slide');
  var dots = document.querySelectorAll('.hero-dots button');
  if (slides.length > 1) {
    var current = 0, timer;
    var show = function (i) {
      slides[current].classList.remove('active'); dots[current].classList.remove('active');
      current = (i + slides.length) % slides.length;
      slides[current].classList.add('active'); dots[current].classList.add('active');
    };
    var start = function () { clearInterval(timer); timer = setInterval(function () { show(current + 1); }, 6000); };
    dots.forEach(function (d, i) { d.addEventListener('click', function () { show(i); start(); }); });
    start();
  }

  // Гүйлгэхэд хэсгүүд гарч ирнэ
  var items = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('visible'); io.unobserve(en.target); } });
    }, { threshold: 0.15 });
    items.forEach(function (el) { io.observe(el); });
  } else {
    items.forEach(function (el) { el.classList.add('visible'); });
  }
})();

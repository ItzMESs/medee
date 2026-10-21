  </main>
</div>
<script>
(function () {
  var body = document.body, openBtn = document.querySelector('[data-nav-open]');
  function setOpen(open) {
    body.classList.toggle('nav-open', open);
    if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  if (openBtn) openBtn.addEventListener('click', function () { setOpen(true); });
  document.querySelectorAll('[data-nav-close]').forEach(function (el) { el.addEventListener('click', function () { setOpen(false); }); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
  document.querySelectorAll('.admin-nav nav a').forEach(function (a) { a.addEventListener('click', function () { setOpen(false); }); });
})();
</script>
</body>
</html>

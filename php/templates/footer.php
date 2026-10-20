</main>
<footer class="site-footer">
  <div class="container footer-inner">
    <div>
      <div class="logo logo-sm"><?= e($settings['site_name']) ?></div>
      <p><?= e($settings['site_tagline']) ?></p>
    </div>
    <ul class="footer-cats">
      <?php foreach ($categories as $c): ?><li><a href="<?= url('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?>
    </ul>
    <p class="copy">© <?= date('Y') ?> <?= e($settings['site_name']) ?>. Бүх эрх хуулиар хамгаалагдсан.</p>
  </div>
</footer>
</body>
</html>

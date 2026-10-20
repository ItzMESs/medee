</main>
<footer class="site-footer">
  <?php if (ornaments_enabled()): ?><?= ornament('ugalz', 'footer-ugalz') ?><?php endif; ?>
  <div class="container footer-grid">
    <div class="footer-brand">
      <a href="<?= url('/') ?>" class="logo"><?php if ($settings['logo']): ?><img src="<?= e(media($settings['logo'])) ?>" alt="<?= e($settings['site_name']) ?>"><?php else: ?><?= e($settings['site_name']) ?><?php endif; ?></a>
      <p><?= e($settings['footer_about'] !== '' ? $settings['footer_about'] : $settings['site_tagline']) ?></p>
      <?php if ($socials): ?><div class="socials"><?php foreach ($socials as $net => $link): ?><a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($net) ?>"><?= icon($net, 20) ?></a><?php endforeach; ?></div><?php endif; ?>
    </div>
    <div class="footer-contact">
      <?php if ($settings['contact_address'] !== ''): ?>
        <h4>Хаяг:</h4>
        <p><?= e($settings['contact_address']) ?></p>
      <?php endif; ?>
      <?php if ($settings['contact_phone'] !== '' || $settings['contact_email'] !== ''): ?>
        <h4>Холбоо барих:</h4>
        <?php if ($settings['contact_phone'] !== ''): ?><p>Утас: <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $settings['contact_phone'])) ?>"><?= e($settings['contact_phone']) ?></a></p><?php endif; ?>
        <?php if ($settings['contact_email'] !== ''): ?><p>Цахим шуудан: <a href="mailto:<?= e($settings['contact_email']) ?>"><?= e($settings['contact_email']) ?></a></p><?php endif; ?>
      <?php endif; ?>
      <?php if ($settings['contact_address'] === '' && $settings['contact_phone'] === '' && $settings['contact_email'] === ''): ?>
        <h4>Ангилал</h4>
        <ul class="footer-cats"><?php foreach ($categories as $c): ?><li><a href="<?= url('/category/' . $c['slug']) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?></ul>
      <?php endif; ?>
    </div>
    <ul class="footer-links">
      <?php foreach (footer_pages() as $pg): if ($pg['in_footer']): ?><li><a href="<?= url('/p/' . $pg['slug']) ?>"><?= e($pg['title']) ?></a></li><?php endif; endforeach; ?>
      <li><a href="<?= url('/rss.xml') ?>">RSS</a></li>
    </ul>
  </div>
  <div class="container footer-bottom">
    <span>© <?= date('Y') ?> <?= e($settings['site_name']) ?>. Бүх эрх хуулиар хамгаалагдсан.</span>
    <?php foreach (footer_pages() as $pg): if ($pg['slug'] === 'privacy'): ?><a href="<?= url('/p/privacy') ?>"><?= e($pg['title']) ?></a><?php endif; endforeach; ?>
  </div>
</footer>
<a href="#" class="to-top" aria-label="Дээш буцах"><?= icon('up', 30) ?></a>
<script src="<?= asset('js/site.js') ?>" defer></script>
</body>
</html>

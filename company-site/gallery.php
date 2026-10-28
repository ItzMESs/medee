<?php
$page = 'news'; $title = 'Зургийн цомог';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
?>
<section class="content gallery-section">
  <div class="container">
    <?php if (!$content['gallery']): ?><p class="empty-note">Одоогоор зураг алга.</p><?php endif; ?>
    <div class="gallery">
      <?php foreach ($content['gallery'] as $g): ?>
      <a href="<?= e($g['image']) ?>" class="gallery-item" data-caption="<?= e($g['title']) ?>">
        <span class="gallery-img"><img src="<?= e($g['image']) ?>" alt="<?= e($g['title']) ?>" loading="lazy"><i class="zoom" aria-hidden="true"></i></span>
        <?php if ($g['title'] !== ''): ?><span class="gallery-label"><?= e($g['title']) ?></span><?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<div class="lightbox" id="lightbox" hidden>
  <button class="lb-close" aria-label="Хаах">×</button>
  <button class="lb-prev" aria-label="Өмнөх">‹</button>
  <figure><img alt=""><figcaption></figcaption></figure>
  <button class="lb-next" aria-label="Дараах">›</button>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>

<?php
$page = 'news'; $title = 'Зургийн цомог';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
?>
<section class="content">
  <div class="container">
    <?php if (!$content['gallery']): ?><p>Одоогоор зураг алга.</p><?php endif; ?>
    <div class="gallery">
      <?php foreach ($content['gallery'] as $g): ?>
      <a href="<?= e($g['image']) ?>" class="gallery-item" data-caption="<?= e($g['title']) ?>">
        <img src="<?= e($g['image']) ?>" alt="<?= e($g['title']) ?>" loading="lazy">
        <?php if ($g['title'] !== ''): ?><span><?= e($g['title']) ?></span><?php endif; ?>
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

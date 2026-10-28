<?php
$page = 'operations'; $title = 'Үйл ажиллагаа';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
?>
<section class="content">
  <div class="container">
    <?php foreach ($content['services'] as $i => $it): ?>
    <div class="op-row<?= $i % 2 ? ' rev' : '' ?>" id="<?= e($it['id']) ?>">
      <img src="<?= e($it['image']) ?>" alt="<?= e($it['title']) ?>" loading="lazy">
      <div><h2 class="title-line"><?= e($it['title']) ?></h2><?= paragraphs($it['text']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

<?php
$page = 'about'; $title = 'Бидний тухай';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
$about = $content['about'];
?>
<section class="content">
  <div class="container about-grid">
    <div class="about-img"><img src="<?= e($about['image']) ?>" alt=""></div>
    <div>
      <h2 class="title-line"><?= e($about['heading']) ?></h2>
      <?= paragraphs($about['text']) ?>
    </div>
  </div>
  <div class="container grid-3 values">
    <div class="value"><h3>АЛСЫН ХАРАА</h3><?= paragraphs($about['vision']) ?></div>
    <div class="value"><h3>ЭРХЭМ ЗОРИЛГО</h3><?= paragraphs($about['mission']) ?></div>
    <div class="value"><h3>ҮНЭТ ЗҮЙЛС</h3><?= paragraphs($about['values']) ?></div>
  </div>
  <?php if (trim($about['history']) !== ''): ?>
  <div class="container history">
    <h2 class="title-line">ТҮҮХЭН ЗАМНАЛ</h2>
    <ol class="timeline">
      <?php foreach (preg_split('/\R{2,}/u', trim($about['history'])) as $h):
        $parts = preg_split('/\s+[—–-]\s+/u', $h, 2); $hasYear = count($parts) === 2 && preg_match('/^\d{4}/', $parts[0]); ?>
      <li><?php if ($hasYear): ?><span class="year"><?= e($parts[0]) ?></span><?php endif; ?><div><?= paragraphs($hasYear ? $parts[1] : $h) ?></div></li>
      <?php endforeach; ?>
    </ol>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

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
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

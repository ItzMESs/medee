<?php include __DIR__ . '/header.php'; ?>
<?php if ($featured): $hero = $featured[0]; $rest = array_slice($featured, 1); ?>
<section class="hero">
  <a href="<?= article_url($hero) ?>" class="hero-main">
    <?php if ($hero['image']): ?><img src="<?= e(media($hero['image'])) ?>" alt="<?= e($hero['title']) ?>"><?php endif; ?>
    <div class="hero-overlay">
      <?php if ($hero['category_name']): ?><span class="tag tag-solid"><?= e($hero['category_name']) ?></span><?php endif; ?>
      <h1><?= e($hero['title']) ?></h1>
      <p><?= e($hero['excerpt']) ?></p>
      <span class="meta"><?= e(time_ago($hero['published_at'])) ?></span>
    </div>
  </a>
  <div class="hero-side">
    <?php foreach ($rest as $a) { $variant = 'card-row'; include __DIR__ . '/card.php'; } ?>
  </div>
</section>
<?php endif; ?>

<div class="layout">
  <div class="content">
    <h2 class="section-title"><a href="<?= url('/latest') ?>">Шинэ мэдээ</a></h2>
    <div class="grid">
      <?php foreach ($latest as $a) { $variant = ''; include __DIR__ . '/card.php'; } ?>
    </div>
    <?php if (!$latest && !$featured): ?>
      <p class="empty">Одоогоор нийтлэгдсэн мэдээ алга. <a href="<?= url('/admin') ?>">Админ панелаас</a> мэдээ нэмнэ үү.</p>
    <?php endif; ?>

    <?php foreach ($sections as $s): ?>
      <h2 class="section-title"><a href="<?= url('/category/' . $s['category']['slug']) ?>"><?= e($s['category']['name']) ?></a></h2>
      <div class="grid">
        <?php foreach ($s['articles'] as $a) { $variant = ''; include __DIR__ . '/card.php'; } ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php include __DIR__ . '/sidebar.php'; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

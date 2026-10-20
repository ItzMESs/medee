<?php include __DIR__ . '/header.php'; ?>
<div class="container">
  <section class="home-top">
    <div class="featured-pair">
      <?php foreach ($featured as $a) { $variant = 'card-feature'; $showExcerpt = true; include __DIR__ . '/card.php'; } ?>
      <?php if (!$featured): ?><p class="empty">Одоогоор нийтлэгдсэн мэдээ алга. <a href="<?= url('/admin') ?>">Админ панелаас</a> мэдээ нэмнэ үү.</p><?php endif; ?>
    </div>
    <?php include __DIR__ . '/sidebar.php'; ?>
  </section>

  <?php if ($latest): ?>
  <section class="section">
    <h2 class="section-title"><a href="<?= url('/latest') ?>">Шинэ мэдээ</a></h2>
    <div class="grid grid-4">
      <?php foreach ($latest as $a) { $variant = ''; $showExcerpt = false; $chip = true; include __DIR__ . '/card.php'; } ?>
    </div>
  </section>
  <?php endif; ?>

  <?php foreach ($sections as $i => $s): $lead = $s['articles'][0]; $rest = array_slice($s['articles'], 1); ?>
  <section class="section">
    <h2 class="section-title"><a href="<?= url('/category/' . $s['category']['slug']) ?>"><?= e($s['category']['name']) ?></a><a class="more" href="<?= url('/category/' . $s['category']['slug']) ?>">Бүгдийг үзэх <?= icon('chev', 16) ?></a></h2>
    <?php if (count($s['articles']) >= 4): // enough stories for the lead + list layout ?>
    <div class="mosaic <?= $i % 2 ? 'mosaic-flip' : '' ?>">
      <?php $a = $lead; $variant = 'card-lead'; $showExcerpt = true; $chip = false; include __DIR__ . '/card.php'; ?>
      <div class="mosaic-list">
        <?php foreach ($rest as $a) { $variant = 'card-mini'; $showExcerpt = false; $chip = false; include __DIR__ . '/card.php'; } ?>
      </div>
    </div>
    <?php else: ?>
    <div class="grid grid-3">
      <?php foreach ($s['articles'] as $a) { $variant = ''; $showExcerpt = false; $chip = false; include __DIR__ . '/card.php'; } ?>
    </div>
    <?php endif; ?>
  </section>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

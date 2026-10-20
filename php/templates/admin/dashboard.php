<?php include __DIR__ . '/header.php'; ?>
<h1>Хянах самбар</h1>
<?php if (!$fbReady && $user['role'] === 'admin'): ?>
  <div class="flash flash-info">Facebook пэйж холбогдоогүй байна. Мэдээгээ автоматаар Facebook-д нийтлэхийн тулд <a href="<?= url('/admin/settings') ?>">Тохиргоо</a> хэсэгт пэйжийн ID болон access token оруулна уу.</div>
<?php endif; ?>
<div class="stats">
  <div class="stat"><b><?= number_format($totals['today']) ?></b><span>Өнөөдрийн үзэлт</span></div>
  <div class="stat"><b><?= number_format($totals['yesterday']) ?></b><span>Өчигдөр</span></div>
  <div class="stat"><b><?= number_format($totals['range']) ?></b><span>Сүүлийн <?= $range ?> хоног</span></div>
  <div class="stat"><b><?= (int) $stats['published'] ?></b><span>Нийтлэгдсэн мэдээ</span></div>
  <div class="stat"><b><?= (int) $stats['drafts'] ?></b><span>Ноорог</span></div>
  <div class="stat"><b><?= (int) $stats['comments_today'] ?></b><span>Өнөөдрийн сэтгэгдэл<?php if ($pendingComments): ?> · <a href="<?= url('/admin/comments?status=pending') ?>"><?= (int) $pendingComments ?> хүлээгдэж буй</a><?php endif; ?></span></div>
  <div class="stat"><b><?= (int) $stats['fb'] ?></b><span>Facebook-д нийтэлсэн</span></div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Өдөр бүрийн үзэлт</h2>
    <div class="tabs">
      <a href="?range=7" class="<?= $range === 7 ? 'active' : '' ?>">7 хоног</a>
      <a href="?range=30" class="<?= $range === 30 ? 'active' : '' ?>">30 хоног</a>
    </div>
  </div>
  <?php include __DIR__ . '/chart.php'; ?>
</div>

<div class="two-col">
  <div class="panel">
    <h2>Хамгийн их уншигдсан — сүүлийн <?= $range ?> хоног</h2>
    <?php $list = $top; $countKey = 'period_views'; include __DIR__ . '/top-list.php'; ?>
  </div>
  <div class="panel">
    <h2>Хамгийн их уншигдсан — бүх цаг үе</h2>
    <?php $list = $allTime; $countKey = 'total_views'; include __DIR__ . '/top-list.php'; ?>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Сүүлд засварласан</h2><a href="<?= url('/admin/articles/new') ?>" class="btn btn-primary">+ Мэдээ нэмэх</a></div>
  <?php $articles = $recent; include __DIR__ . '/table.php'; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

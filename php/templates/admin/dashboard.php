<?php include __DIR__ . '/header.php'; ?>
<h1>Сайн байна уу, <?= e($user['name']) ?> 👋</h1>
<div class="quick">
  <a href="<?= url('/admin/articles/new') ?>"><span class="q-ico"><?= icon('plus', 20) ?></span><span>Мэдээ нэмэх<small>Шинэ мэдээ бичих</small></span></a>
  <a href="<?= url('/admin/articles') ?>"><span class="q-ico"><?= icon('file', 20) ?></span><span>Бүх мэдээ<small><?= (int) $stats['published'] ?> нийтлэгдсэн, <?= (int) $stats['drafts'] ?> ноорог</small></span></a>
  <a href="<?= url('/admin/comments' . ($pendingComments ? '?status=pending' : '')) ?>"><span class="q-ico"><?= icon('comment', 20) ?></span><span>Сэтгэгдэл<small><?= $pendingComments ? (int) $pendingComments . ' хүлээгдэж буй' : 'Шинэ сэтгэгдэл алга' ?></small></span></a>
  <?php if ($user['role'] === 'admin'): ?><a href="<?= url('/admin/ads') ?>"><span class="q-ico"><?= icon('megaphone', 20) ?></span><span>Сурталчилгаа<small>Баннер, тайлан</small></span></a><?php endif; ?>
  <a href="<?= url('/') ?>" target="_blank"><span class="q-ico"><?= icon('external', 20) ?></span><span>Сайт үзэх<small><?= e(preg_replace('#^https?://#', '', rtrim($settings['site_url'], '/'))) ?></small></span></a>
</div>
<?php if (!empty($endingAds)): ?>
  <div class="flash flash-error">⏰ Удахгүй дуусах сурталчилгаа:
    <?php foreach ($endingAds as $i => $ea): ?><?= $i ? ', ' : '' ?><a href="<?= url('/admin/ads/' . $ea['id']) ?>"><b><?= e($ea['title']) ?></b></a> (<?= e(format_date($ea['ends_at'])) ?>)<?php endforeach; ?>.
    Захиалагчтай сунгах эсэхийг ярилцаарай.</div>
<?php endif; ?>
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

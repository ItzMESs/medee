<?php include __DIR__ . '/header.php'; ?>
<h1>Хянах самбар</h1>
<?php if (!$fbReady && $user['role'] === 'admin'): ?>
  <div class="flash flash-info">Facebook пэйж холбогдоогүй байна. Мэдээгээ автоматаар Facebook-д нийтлэхийн тулд <a href="<?= url('/admin/settings') ?>">Тохиргоо</a> хэсэгт пэйжийн ID болон access token оруулна уу.</div>
<?php endif; ?>
<div class="stats">
  <div class="stat"><b><?= (int) $stats['total'] ?></b><span>Нийт мэдээ</span></div>
  <div class="stat"><b><?= (int) $stats['published'] ?></b><span>Нийтлэгдсэн</span></div>
  <div class="stat"><b><?= (int) $stats['drafts'] ?></b><span>Ноорог</span></div>
  <div class="stat"><b><?= (int) $stats['views'] ?></b><span>Нийт үзэлт</span></div>
  <div class="stat"><b><?= (int) $stats['fb'] ?></b><span>Facebook-д нийтэлсэн</span></div>
</div>
<div class="panel">
  <div class="panel-head"><h2>Сүүлд засварласан</h2><a href="<?= url('/admin/articles/new') ?>" class="btn btn-primary">+ Мэдээ нэмэх</a></div>
  <?php $articles = $recent; include __DIR__ . '/table.php'; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

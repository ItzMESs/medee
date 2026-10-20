<?php include __DIR__ . '/header.php'; ?>
<div class="container layout">
  <div class="content">
    <h1 class="page-title"><?php if (ornaments_enabled()): ?><?= ornament('ulzii', 'title-ulzii') ?><?php endif; ?><?= e($heading) ?></h1>
    <div class="list">
      <?php foreach ($articles as $a) { $variant = 'card-row'; $showExcerpt = true; include __DIR__ . '/card.php'; } ?>
    </div>
    <?php if (!$articles): ?><p class="empty"><?= $query !== '' ? 'Хайлтад тохирох мэдээ олдсонгүй.' : 'Мэдээ алга.' ?></p><?php endif; ?>
    <?php if ($pages > 1): ?>
      <nav class="pager">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <a href="?<?= $query !== '' ? 'q=' . rawurlencode($query) . '&' : '' ?>page=<?= $p ?>" class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
  <?php include __DIR__ . '/sidebar.php'; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

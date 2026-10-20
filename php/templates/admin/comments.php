<?php include __DIR__ . '/header.php'; ?>
<div class="panel-head">
  <h1>Сэтгэгдэл</h1>
  <div class="tabs">
    <a href="<?= url('/admin/comments') ?>" class="<?= $status === '' ? 'active' : '' ?>">Бүгд</a>
    <a href="<?= url('/admin/comments?status=pending') ?>" class="<?= $status === 'pending' ? 'active' : '' ?>">Хүлээгдэж буй<?= $pendingComments ? ' (' . (int) $pendingComments . ')' : '' ?></a>
    <a href="<?= url('/admin/comments?status=approved') ?>" class="<?= $status === 'approved' ? 'active' : '' ?>">Нийтлэгдсэн</a>
  </div>
</div>
<div class="panel">
  <?php if (!$comments): ?><p class="empty">Сэтгэгдэл алга.</p><?php endif; ?>
  <ul class="admin-comments">
    <?php foreach ($comments as $c): $q = $status !== '' ? '?status=' . rawurlencode($status) : ''; ?>
      <li class="<?= $c['status'] === 'pending' ? 'is-pending' : '' ?>">
        <div class="ac-head">
          <b><?= e($c['name']) ?></b>
          <span class="muted"><?= e(format_date($c['created_at'])) ?></span>
          <?php if ($c['status'] === 'pending'): ?><span class="badge badge-gold">Хүлээгдэж буй</span><?php endif; ?>
        </div>
        <p><?= nl2br(e($c['body'])) ?></p>
        <div class="ac-foot">
          <a href="<?= url('/news/' . $c['aid']) ?>#comments" target="_blank" class="muted">↳ <?= e($c['article_title']) ?></a>
          <div class="ac-actions">
            <?php if ($c['status'] === 'pending'): ?>
            <form method="post" action="<?= url('/admin/comments/' . $c['id'] . '/approve' . $q) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-primary">Зөвшөөрөх</button></form>
            <?php endif; ?>
            <form method="post" action="<?= url('/admin/comments/' . $c['id'] . '/delete' . $q) ?>" onsubmit="return confirm('Сэтгэгдлийг устгах уу?')"><?= csrf_field() ?><button class="btn btn-sm btn-danger">Устгах</button></form>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php if ($pages > 1): ?>
  <nav class="pager">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <a href="?<?= e(http_build_query(['status' => $status, 'page' => $p])) ?>" class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>
<?php include __DIR__ . '/footer.php'; ?>

<?php include __DIR__ . '/header.php'; ?>
<div class="panel-head"><h1>Бүх мэдээ</h1><a href="<?= url('/admin/articles/new') ?>" class="btn btn-primary">+ Мэдээ нэмэх</a></div>
<form class="filters" method="get">
  <input name="q" value="<?= e($q) ?>" placeholder="Гарчгаар хайх">
  <select name="status">
    <option value="">Бүх төлөв</option>
    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Нийтлэгдсэн</option>
    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Ноорог</option>
  </select>
  <select name="category">
    <option value="">Бүх ангилал</option>
    <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= $category === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
  </select>
  <button class="btn">Шүүх</button>
</form>
<div class="panel"><?php include __DIR__ . '/table.php'; ?></div>
<?php if ($pages > 1): ?>
  <nav class="pager">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <a href="?<?= e(http_build_query(['q' => $q, 'status' => $status, 'category' => $category ?: '', 'page' => $p])) ?>" class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>
<?php include __DIR__ . '/footer.php'; ?>

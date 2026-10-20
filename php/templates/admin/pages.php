<?php include __DIR__ . '/header.php'; ?>
<div class="panel-head"><h1>Хуудсууд</h1><a href="<?= url('/admin/pages/new') ?>" class="btn btn-primary">+ Хуудас нэмэх</a></div>
<p class="muted">"Бидний тухай", "Холбоо барих" зэрэг байнгын хуудсууд. "Footer-т харуулах" гэж тэмдэглэсэн нь сайтын доод хэсэгт холбоос болж гарна.</p>
<div class="panel">
  <div class="table-wrap">
  <table class="table">
    <thead><tr><th>Гарчиг</th><th>Хаяг</th><th>Footer</th><th>Дараалал</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($pages as $pg): ?>
      <tr>
        <td><a class="title-link" href="<?= url('/admin/pages/' . $pg['id']) ?>"><?= e($pg['title']) ?></a></td>
        <td><a href="<?= url('/p/' . $pg['slug']) ?>" target="_blank">/p/<?= e($pg['slug']) ?></a></td>
        <td><?= $pg['in_footer'] ? '✓' : '—' ?></td>
        <td><?= (int) $pg['position'] ?></td>
        <td class="actions"><a href="<?= url('/admin/pages/' . $pg['id']) ?>">Засах</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$pages): ?><tr><td colspan="5" class="empty">Хуудас алга.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>

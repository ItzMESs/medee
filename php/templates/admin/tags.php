<?php include __DIR__ . '/header.php'; ?>
<h1>Шошго</h1>
<div class="panel">
  <p class="muted">Шошгыг мэдээ бичих хуудаснаас нэмнэ. Энд нэрийг нь засах, давхардсаныг устгах боломжтой. Шошго бүр өөрийн хуудастай: <code><?= e(rtrim(setting('site_url'), '/')) ?>/tag/...</code></p>
  <?php if (!$tags): ?><p class="empty">Одоогоор шошго алга.</p><?php else: ?>
  <div class="table-wrap">
  <table class="table">
    <thead><tr><th>Нэр / Холбоос</th><th>Мэдээ</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($tags as $t): ?>
      <tr>
        <td>
          <form method="post" action="<?= url('/admin/tags/' . $t['id']) ?>" class="inline-form">
            <?= csrf_field() ?>
            <input name="name" value="<?= e($t['name']) ?>" required maxlength="40">
            <input name="slug" value="<?= e($t['slug']) ?>">
            <button class="btn">Хадгалах</button>
            <a href="<?= url(tag_path($t)) ?>" target="_blank" class="muted">Үзэх ↗</a>
          </form>
        </td>
        <td><?= (int) $t['count'] ?></td>
        <td>
          <form method="post" action="<?= url('/admin/tags/' . $t['id'] . '/delete') ?>" onsubmit="return confirm('Энэ шошгыг устгах уу? Мэдээнүүд устахгүй.')">
            <?= csrf_field() ?>
            <button class="btn btn-danger btn-sm">Устгах</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

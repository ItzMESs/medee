<?php include __DIR__ . '/header.php'; ?>
<h1>Ангилал</h1>
<div class="panel">
  <h2>Шинэ ангилал</h2>
  <form method="post" action="<?= url('/admin/categories') ?>" class="inline-form">
    <?= csrf_field() ?>
    <input name="name" placeholder="Нэр (ж: Эрүүл мэнд)" required>
    <input name="slug" placeholder="Холбоос (заавал биш)">
    <input name="position" type="number" placeholder="Дараалал" value="<?= count($categories) ?>" class="narrow">
    <button class="btn btn-primary">Нэмэх</button>
  </form>
</div>
<div class="panel">
  <div class="table-wrap">
  <table class="table">
    <thead><tr><th colspan="3">Нэр / Холбоос / Дараалал</th><th>Мэдээ</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($categories as $c): ?>
      <tr>
        <td colspan="3">
          <form method="post" action="<?= url('/admin/categories/' . $c['id']) ?>" class="inline-form">
            <?= csrf_field() ?>
            <input name="name" value="<?= e($c['name']) ?>" required>
            <input name="slug" value="<?= e($c['slug']) ?>">
            <input name="position" type="number" value="<?= (int) $c['position'] ?>" class="narrow">
            <button class="btn">Хадгалах</button>
          </form>
        </td>
        <td><?= (int) $c['count'] ?></td>
        <td>
          <form method="post" action="<?= url('/admin/categories/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Энэ ангиллыг устгах уу?')">
            <?= csrf_field() ?>
            <button class="btn btn-danger btn-sm">Устгах</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>

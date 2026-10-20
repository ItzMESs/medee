<?php include __DIR__ . '/header.php'; ?>
<h1>Хэрэглэгчид</h1>
<div class="panel">
  <h2>Шинэ хэрэглэгч</h2>
  <form method="post" action="<?= url('/admin/users') ?>" class="inline-form">
    <?= csrf_field() ?>
    <input name="username" placeholder="Нэвтрэх нэр" required autocomplete="off">
    <input name="name" placeholder="Харагдах нэр (сэтгүүлч)">
    <input name="password" type="password" placeholder="Нууц үг (6+)" required autocomplete="new-password">
    <select name="role"><option value="editor">Редактор</option><option value="admin">Админ</option></select>
    <button class="btn btn-primary">Нэмэх</button>
  </form>
  <p class="muted">Редактор мэдээ, ангилал удирдана. Админ нэмэлтээр хэрэглэгч болон Facebook тохиргоог удирдана.</p>
</div>
<div class="panel">
  <div class="table-wrap">
  <table class="table">
    <thead><tr><th>Нэвтрэх нэр</th><th>Нэр</th><th>Эрх</th><th>Бүртгэсэн</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= e($u['username']) ?></td><td><?= e($u['name']) ?></td>
        <td><?= $u['role'] === 'admin' ? 'Админ' : 'Редактор' ?></td>
        <td><?= e(format_date($u['created_at'])) ?></td>
        <td><?php if ((int) $u['id'] !== $user['id']): ?>
          <form method="post" action="<?= url('/admin/users/' . $u['id'] . '/delete') ?>" onsubmit="return confirm('Хэрэглэгчийг устгах уу?')">
            <?= csrf_field() ?>
            <button class="btn btn-danger btn-sm">Устгах</button>
          </form>
        <?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>

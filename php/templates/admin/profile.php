<?php include __DIR__ . '/header.php'; ?>
<h1>Нууц үг солих</h1>
<div class="panel narrow-panel">
  <form method="post" action="<?= url('/admin/profile') ?>" class="stack">
    <?= csrf_field() ?>
    <label>Одоогийн нууц үг<input type="password" name="current" required autocomplete="current-password"></label>
    <label>Шинэ нууц үг<input type="password" name="password" required minlength="6" autocomplete="new-password"></label>
    <label>Шинэ нууц үг давтах<input type="password" name="confirm" required minlength="6" autocomplete="new-password"></label>
    <button class="btn btn-primary">Солих</button>
  </form>
</div>
<?php include __DIR__ . '/footer.php'; ?>

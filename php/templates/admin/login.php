<!doctype html>
<html lang="mn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Нэвтрэх — <?= e($settings['site_name']) ?></title>
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="login-page">
  <form method="post" action="<?= url('/admin/login') ?>" class="login-box">
    <h1><?= e($settings['site_name']) ?></h1>
    <p>Админ панелд нэвтрэх</p>
    <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label>Нэвтрэх нэр<input name="username" required autofocus autocomplete="username"></label>
    <label>Нууц үг<input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit" class="btn btn-primary">Нэвтрэх</button>
  </form>
</body>
</html>

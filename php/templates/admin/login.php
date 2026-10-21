<!doctype html>
<html lang="mn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Нэвтрэх — <?= e($settings['site_name']) ?></title>
  <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
  <style>:root { --brand: <?= brand_color() ?>; }</style>
</head>
<body class="login-page">
  <form method="post" action="<?= url('/admin/login') ?>" class="login-box">
    <?php $loginLogo = is_file(ROOT . '/assets/img/nationx-logo.png') ? asset('img/nationx-logo.png') : ''; ?>
    <?php if ($loginLogo): ?><img src="<?= e($loginLogo) ?>" alt="<?= e($settings['site_name']) ?>" class="login-logo"><?php endif; ?>
    <h1>Админ панел</h1>
    <p><?= e($settings['site_name']) ?> — нэвтрэх</p>
    <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label>Нэвтрэх нэр<input name="username" required autofocus autocomplete="username"></label>
    <label>Нууц үг<input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit" class="btn btn-primary">Нэвтрэх</button>
  </form>
</body>
</html>

<?php
// Суулгах хуудас: өгөгдлийн сантай холбогдож, хүснэгт үүсгэж, админ нэмнэ.
// Суулгалт дууссаны дараа (config.php ба админ үүссэн бол) дахин ажиллахгүй.
require __DIR__ . '/inc/store.php';

session_name('cs_install');
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

$needDb = !is_installed();
$error = '';

if (!$needDb) {
    try {
        db_install();
        if (admin_get()) {
            header('Location: admin/');
            exit;
        }
    } catch (PDOException $ex) {
        $error = 'config.php доторх тохиргоогоор өгөгдлийн сантай холбогдож чадсангүй: ' . $ex->getMessage();
    }
}

$v = fn($k, $d = '') => e($_POST[$k] ?? $d);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $error = 'Хүсэлт хүчингүй боллоо. Хуудсаа дахин ачаална уу.';
    }
    $user = trim((string) ($_POST['user'] ?? ''));
    $pass = (string) ($_POST['pass'] ?? '');
    if (!$error && ($user === '' || strlen($pass) < 8)) $error = 'Админы нэвтрэх нэр оруулж, нууц үгээ хамгийн багадаа 8 тэмдэгт болгоно уу.';
    if (!$error && $pass !== ($_POST['pass2'] ?? '')) $error = 'Админы нууц үг таарахгүй байна.';

    if (!$error && $needDb) {
        $cfg = [
            'db_host'   => trim((string) ($_POST['db_host'] ?? 'localhost')) ?: 'localhost',
            'db_port'   => (int) ($_POST['db_port'] ?? 3306) ?: 3306,
            'db_name'   => trim((string) ($_POST['db_name'] ?? '')),
            'db_user'   => trim((string) ($_POST['db_user'] ?? '')),
            'db_pass'   => (string) ($_POST['db_pass'] ?? ''),
            'db_prefix' => preg_replace('/[^a-z0-9_]/i', '', (string) ($_POST['db_prefix'] ?? 'cs_')),
        ];
        try {
            db_connect($cfg);
            $php = "<?php\n// Өгөгдлийн сангийн тохиргоо (install.php үүсгэсэн)\nreturn " . var_export($cfg, true) . ";\n";
            if (@file_put_contents(CONFIG_FILE, $php, LOCK_EX) === false) {
                $error = 'config.php файл үүсгэх эрхгүй байна. Сайтын үндсэн хавтсанд бичих эрх (755) өгнө үү.';
            } else {
                @chmod(CONFIG_FILE, 0640);
            }
        } catch (PDOException $ex) {
            $error = 'Өгөгдлийн сантай холбогдож чадсангүй. Мэдээллээ шалгана уу. (' . $ex->getMessage() . ')';
        }
    }

    if (!$error) {
        try {
            db_install();
            if (admin_get()) {
                $error = 'Админ аль хэдийн үүссэн байна.';
            } else {
                admin_create($user, $pass);
                header('Location: admin/');
                exit;
            }
        } catch (PDOException $ex) {
            $error = 'Хүснэгт үүсгэхэд алдаа гарлаа: ' . $ex->getMessage();
        }
    }
}
?><!doctype html>
<html lang="mn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Сайт суулгах</title>
<link rel="stylesheet" href="admin/admin.css">
</head>
<body>
<main class="auth">
  <form method="post" class="card install">
    <img src="assets/img/logo.svg" alt="" class="auth-logo">
    <h1>Сайт суулгах</h1>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <?php if ($needDb): ?>
    <h2>1. Өгөгдлийн сан (MySQL)</h2>
    <p class="muted">cPanel → MySQL Databases хэсгээс өгөгдлийн сан, хэрэглэгч үүсгээд, хэрэглэгчид ALL PRIVILEGES эрх өгнө.</p>
    <div class="field"><label>Сервер</label><input name="db_host" value="<?= $v('db_host', 'localhost') ?>" required></div>
    <div class="field"><label>Өгөгдлийн сангийн нэр</label><input name="db_name" value="<?= $v('db_name') ?>" required placeholder="cpanelner_site"></div>
    <div class="field"><label>Хэрэглэгчийн нэр</label><input name="db_user" value="<?= $v('db_user') ?>" required autocomplete="off"></div>
    <div class="field"><label>Нууц үг</label><input type="password" name="db_pass" autocomplete="off"></div>
    <details><summary class="muted">Нэмэлт тохиргоо</summary>
      <div class="field"><label>Порт</label><input name="db_port" value="<?= $v('db_port', '3306') ?>"></div>
      <div class="field"><label>Хүснэгтийн угтвар</label><input name="db_prefix" value="<?= $v('db_prefix', 'cs_') ?>"></div>
    </details>
    <h2><?= $needDb ? '2. ' : '' ?>Админ</h2>
    <?php else: ?>
    <p class="muted">Өгөгдлийн сан бэлэн. Админы нэвтрэх нэр, нууц үгээ үүсгэнэ үү.</p>
    <?php endif; ?>
    <div class="field"><label>Нэвтрэх нэр</label><input name="user" value="<?= $v('user', 'admin') ?>" required autocomplete="username"></div>
    <div class="field"><label>Нууц үг (8+ тэмдэгт)</label><input type="password" name="pass" required autocomplete="new-password"></div>
    <div class="field"><label>Нууц үг давтах</label><input type="password" name="pass2" required autocomplete="new-password"></div>
    <button class="btn primary wide">Суулгах</button>
  </form>
</main>
</body>
</html>

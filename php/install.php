<?php
// One-time web installer: writes config.php, creates tables and the first admin.
define('ROOT', __DIR__);
define('UPLOAD_DIR', ROOT . '/uploads');
date_default_timezone_set('Asia/Ulaanbaatar');
mb_internal_encoding('UTF-8');

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$installed = is_file(ROOT . '/config.php');
$errors = [];
$done = false;
$f = [
    'db_host' => 'localhost', 'db_name' => '', 'db_user' => '', 'db_pass' => '',
    'site_name' => 'Medee.mn', 'admin_user' => 'admin', 'admin_pass' => '', 'demo' => '1',
];

$checks = [
    'PHP 7.4 ба түүнээс шинэ' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'PDO MySQL' => extension_loaded('pdo_mysql'),
    'mbstring' => extension_loaded('mbstring'),
    'DOM / XML' => class_exists('DOMDocument'),
    'cURL (Facebook-д нийтлэх)' => function_exists('curl_init'),
    'Үндсэн хавтас руу бичих эрх (config.php)' => is_writable(ROOT),
    'uploads хавтас руу бичих эрх' => is_writable(UPLOAD_DIR),
];

if (!$installed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $k => $v) {
        $f[$k] = isset($_POST[$k]) ? trim((string) $_POST[$k]) : ($k === 'demo' ? '' : $v);
    }
    if ($f['db_name'] === '' || $f['db_user'] === '') {
        $errors[] = 'Өгөгдлийн сангийн нэр болон хэрэглэгчийг оруулна уу.';
    }
    if ($f['admin_user'] === '' || strlen($f['admin_pass']) < 6) {
        $errors[] = 'Админы нэвтрэх нэр болон дор хаяж 6 тэмдэгттэй нууц үг оруулна уу.';
    }
    if (!is_writable(ROOT)) {
        $errors[] = 'config.php файл үүсгэх эрх алга. File Manager-ээр хавтасны эрхийг 755 болгоно уу.';
    }

    if (!$errors) {
        try {
            $pdo = new PDO('mysql:host=' . $f['db_host'] . ';dbname=' . $f['db_name'] . ';charset=utf8mb4', $f['db_user'], $f['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec("SET time_zone = '+08:00'");
        } catch (PDOException $ex) {
            $errors[] = 'Өгөгдлийн санд холбогдож чадсангүй: ' . $ex->getMessage();
        }
    }

    if (!$errors) {
        try {
            require ROOT . '/inc/schema.php';
            create_schema($pdo);
            $now = date('Y-m-d H:i:s');
            $exists = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $exists->execute([$f['admin_user']]);
            $hash = password_hash($f['admin_pass'], PASSWORD_DEFAULT);
            if ($id = $exists->fetchColumn()) {
                $pdo->prepare("UPDATE users SET password_hash = ?, role = 'admin' WHERE id = ?")->execute([$hash, $id]);
            } else {
                $pdo->prepare("INSERT INTO users (username, name, password_hash, role, created_at) VALUES (?, ?, ?, 'admin', ?)")
                    ->execute([$f['admin_user'], 'Админ', $hash, $now]);
                $id = $pdo->lastInsertId();
            }
            $pdo->prepare('INSERT INTO settings (name, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)')
                ->execute(['site_name', $f['site_name'] ?: 'Medee.mn']);
            $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $siteUrl = ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
            $pdo->prepare('INSERT INTO settings (name, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)')
                ->execute(['site_url', $siteUrl]);

            if ($f['demo'] && (int) $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn() === 0) {
                require ROOT . '/inc/demo.php';
                insert_demo_articles($pdo, (int) $id);
            }

            $config = "<?php\n// Medee.mn тохиргоо — install.php үүсгэсэн\nreturn " . var_export([
                'db_host' => $f['db_host'], 'db_name' => $f['db_name'], 'db_user' => $f['db_user'], 'db_pass' => $f['db_pass'],
                'debug' => false,
            ], true) . ";\n";
            if (file_put_contents(ROOT . '/config.php', $config) === false) {
                throw new RuntimeException('config.php файл бичиж чадсангүй.');
            }
            @chmod(ROOT . '/config.php', 0640);
            $done = true;
        } catch (Exception $ex) {
            $errors[] = 'Суулгахад алдаа гарлаа: ' . $ex->getMessage();
        }
    }
}
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
?><!doctype html>
<html lang="mn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Суулгах — Medee.mn</title>
  <link rel="stylesheet" href="<?= h($base) ?>/assets/css/admin.css">
  <style>.install{max-width:620px;margin:40px auto;padding:0 16px}.checks{list-style:none;padding:0;margin:0}.checks li{padding:4px 0}.ok{color:#166534}.bad{color:#b91c1c}</style>
</head>
<body>
<div class="install">
  <h1>Medee.mn — Суулгах</h1>
  <?php if ($done): ?>
    <div class="flash flash-success">Амжилттай суулаа!</div>
    <div class="panel">
      <p><b>Аюулгүй байдлын үүднээс</b> File Manager-ээр <code>install.php</code> файлыг одоо устгана уу.</p>
      <p><a class="btn btn-primary" href="<?= h($base) ?>/admin">Админ панел руу орох</a> <a class="btn" href="<?= h($base) ?>/">Сайт үзэх</a></p>
    </div>
  <?php elseif ($installed): ?>
    <div class="flash flash-info">Сайт аль хэдийн суугдсан байна. <code>install.php</code> файлыг устгана уу.</div>
    <p><a class="btn btn-primary" href="<?= h($base) ?>/admin">Админ панел</a></p>
  <?php else: ?>
    <div class="panel">
      <h2>Серверийн шалгалт</h2>
      <ul class="checks">
        <?php foreach ($checks as $label => $ok): ?><li class="<?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? '✓' : '✗' ?> <?= h($label) ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php foreach ($errors as $err): ?><div class="flash flash-error"><?= h($err) ?></div><?php endforeach; ?>
    <form method="post" class="stack">
      <div class="panel">
        <h2>MySQL өгөгдлийн сан</h2>
        <p class="muted">cPanel → <b>MySQL® Databases</b> хэсэгт өгөгдлийн сан, хэрэглэгч үүсгээд хэрэглэгчид <b>ALL PRIVILEGES</b> эрх өгнө.</p>
        <label>Хост<input name="db_host" value="<?= h($f['db_host']) ?>" required></label>
        <label>Өгөгдлийн сангийн нэр <small>ж: cpaneluser_medee</small><input name="db_name" value="<?= h($f['db_name']) ?>" required></label>
        <label>Хэрэглэгч <small>ж: cpaneluser_admin</small><input name="db_user" value="<?= h($f['db_user']) ?>" required></label>
        <label>Нууц үг<input name="db_pass" type="password" value="<?= h($f['db_pass']) ?>"></label>
      </div>
      <div class="panel">
        <h2>Сайт ба админ</h2>
        <label>Сайтын нэр<input name="site_name" value="<?= h($f['site_name']) ?>" required></label>
        <label>Админы нэвтрэх нэр<input name="admin_user" value="<?= h($f['admin_user']) ?>" required></label>
        <label>Админы нууц үг <small>(6+ тэмдэгт)</small><input name="admin_pass" type="password" required minlength="6" autocomplete="new-password"></label>
        <label class="check"><input type="checkbox" name="demo" value="1" <?= $f['demo'] ? 'checked' : '' ?>> Жишээ мэдээ нэмэх (дараа нь устгаж болно)</label>
      </div>
      <div><button class="btn btn-primary">Суулгах</button></div>
    </form>
  <?php endif; ?>
</div>
</body>
</html>

<?php
define('ROOT', dirname(__DIR__));
define('UPLOAD_DIR', ROOT . '/uploads');

date_default_timezone_set('Asia/Ulaanbaatar');
mb_internal_encoding('UTF-8');

if (!is_file(ROOT . '/config.php')) {
    header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/install.php');
    exit;
}
$config = require ROOT . '/config.php';

if (!empty($config['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

require __DIR__ . '/functions.php';
require __DIR__ . '/sanitize.php';
require __DIR__ . '/facebook.php';
require __DIR__ . '/widgets.php';
require_once __DIR__ . '/images.php';

try {
    $pdo = new PDO(
        'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4',
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $ex) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    exit('<h1>Өгөгдлийн санд холбогдож чадсангүй</h1><p>config.php доторх db_name, db_user, db_pass зөв эсэхийг шалгана уу.</p>'
        . (!empty($config['debug']) ? '<pre>' . htmlspecialchars($ex->getMessage()) . '</pre>' : ''));
}
$pdo->exec("SET time_zone = '+08:00'");

// config.php exists but the tables or admin were never created (e.g. config made by hand).
try {
    $hasAdmin = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
} catch (PDOException $ex) {
    $hasAdmin = false;
}
if ($hasAdmin) {
    // Older installs (or medee.sql imports) get new tables automatically.
    require __DIR__ . '/schema.php';
    $ver = $pdo->query("SELECT `value` FROM settings WHERE name = 'schema_version'")->fetchColumn();
    if ((int) $ver < SCHEMA_VERSION) {
        create_schema($pdo);
    }
}
if (!$hasAdmin) {
    header('Location: ' . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/install.php');
    exit;
}

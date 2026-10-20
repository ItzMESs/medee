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

$pdo = new PDO(
    'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4',
    $config['db_user'],
    $config['db_pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
$pdo->exec("SET time_zone = '+08:00'");

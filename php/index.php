<?php
// Built-in PHP dev server: serve real files directly.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && basename($file) !== 'index.php') {
        return false;
    }
}

require __DIR__ . '/inc/bootstrap.php';

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$base = base_path();
if ($base !== '' && strpos($path, $base) === 0) {
    $path = substr($path, strlen($base));
}
$path = '/' . trim($path, '/');
if ($path === '/index.php') {
    $path = '/';
}
$GLOBALS['route_path'] = $path;
$method = $_SERVER['REQUEST_METHOD'];

if ($path === '/admin' || strpos($path, '/admin/') === 0) {
    require __DIR__ . '/inc/admin.php';
    admin_dispatch(substr($path, 6) ?: '/', $method);
} else {
    require __DIR__ . '/inc/public.php';
    public_dispatch($path);
}

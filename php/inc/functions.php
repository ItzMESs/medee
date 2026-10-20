<?php
// ---------- Database ----------

function db()
{
    global $pdo;
    return $pdo;
}

function q($sql, array $params = [])
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function rows($sql, array $params = [])
{
    return q($sql, $params)->fetchAll();
}

function row($sql, array $params = [])
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}

function now()
{
    return date('Y-m-d H:i:s');
}

// ---------- Settings ----------

function default_settings()
{
    return [
        'site_name' => 'Nationalrbc.com',
        'site_tagline' => 'Шуурхай, бодит мэдээ',
        'site_url' => detect_site_url(),
        'fb_page_url' => '',
        'fb_app_id' => '',
        'fb_page_id' => '',
        'fb_page_token' => '',
        'fb_auto_post' => '1',
        'fb_comments' => '0',
        'rates_mode' => 'auto',
        'rate_usd' => '',
        'rate_cny' => '',
        'rate_rub' => '',
        'weather_enabled' => '1',
        'brand_color' => '#0a5cff',
        'logo' => '',
        'ornaments' => '1',
        'footer_about' => '',
        'contact_address' => '',
        'contact_phone' => '',
        'contact_email' => '',
        'social_youtube' => '',
        'social_x' => '',
        'social_instagram' => '',
        'comments_enabled' => '1',
        'comments_moderation' => '0',
        'ga_id' => '',
        'google_verification' => '',
    ];
}

function settings($reload = false)
{
    static $cache = null;
    if ($cache === null || $reload) {
        $cache = default_settings();
        foreach (rows('SELECT name, `value` FROM settings') as $r) {
            $cache[$r['name']] = $r['value'];
        }
    }
    return $cache;
}

function setting($key)
{
    $s = settings();
    return isset($s[$key]) ? $s[$key] : '';
}

function save_setting($key, $value)
{
    q('INSERT INTO settings (name, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)', [$key, (string) $value]);
}

// ---------- URLs ----------

function base_path()
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    return rtrim($dir, '/');
}

function detect_site_url()
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    return ($https ? 'https' : 'http') . '://' . $host . base_path();
}

function url($path = '/')
{
    return base_path() . $path;
}

function absolute_url($path)
{
    return rtrim(setting('site_url'), '/') . $path;
}

function article_path(array $a)
{
    return '/news/' . $a['id'] . '/' . $a['slug'];
}

function article_url(array $a)
{
    return url(article_path($a));
}

function asset($path)
{
    $file = ROOT . '/assets/' . $path;
    $v = is_file($file) ? filemtime($file) : 0;
    return url('/assets/' . $path) . '?v=' . $v;
}

function media($path)
{
    return $path ? url($path) : '';
}

function redirect($path, $code = 302)
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)), true, $code);
    exit;
}

// ---------- Output ----------

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function render($template, array $vars = [])
{
    extract($vars);
    include ROOT . '/templates/' . $template . '.php';
}

function not_found()
{
    http_response_code(404);
    render('404', public_context(['meta' => ['title' => 'Хуудас олдсонгүй']]));
    exit;
}

// ---------- Text ----------

function slugify($text)
{
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'j', 'з' => 'z',
        'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'ө' => 'u', 'п' => 'p',
        'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ү' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];
    $text = strtr(mb_strtolower((string) $text), $map);
    $text = trim(preg_replace('/[^a-z0-9]+/', '-', $text), '-');
    $text = substr($text, 0, 80);
    return $text !== '' ? rtrim($text, '-') : 'medee';
}

function strip_html($html)
{
    $html = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', '', (string) $html);
    $html = preg_replace('#</(p|h\d|li|blockquote|div)>#i', '$0 ', $html);
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', $text));
}

function excerpt_from($html, $len = 200)
{
    $text = strip_html($html);
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    return preg_replace('/\s+\S*$/u', '', mb_substr($text, 0, $len)) . '…';
}

// ---------- Dates ----------

function format_date($value)
{
    return $value ? date('Y.m.d H:i', strtotime($value)) : '';
}

function time_ago($value)
{
    if (!$value) {
        return '';
    }
    $s = time() - strtotime($value);
    if ($s < 60) {
        return 'Саяхан';
    }
    if ($s < 3600) {
        return floor($s / 60) . ' минутын өмнө';
    }
    if ($s < 86400) {
        $m = floor(($s % 3600) / 60);
        return floor($s / 3600) . ' цаг' . ($m ? ' ' . $m . ' минутын' : 'ийн') . ' өмнө';
    }
    if ($s < 7 * 86400) {
        return floor($s / 86400) . ' өдрийн өмнө';
    }
    return format_date($value);
}

function mongolian_date()
{
    $days = ['Ням', 'Даваа', 'Мягмар', 'Лхагва', 'Пүрэв', 'Баасан', 'Бямба'];
    return date('Y') . ' оны ' . (int) date('n') . ' сарын ' . (int) date('j') . ', ' . $days[(int) date('w')] . ' гараг';
}

// ---------- Session, CSRF, flash ----------

function start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('medee_admin');
    session_set_cookie_params([
        'lifetime' => 7 * 86400,
        'path' => base_path() . '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.gc_maxlifetime', (string) (7 * 86400));
    session_start();
}

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function check_csrf()
{
    $token = isset($_POST['_csrf']) ? $_POST['_csrf'] : (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '');
    if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('CSRF токен буруу байна. Хуудсаа сэргээгээд дахин оролдоно уу.');
    }
}

function flash($type, $text)
{
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}

function take_flash()
{
    $f = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
    unset($_SESSION['flash']);
    return $f;
}

function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_admin()
{
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function post($key, $default = '')
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function get($key, $default = '')
{
    return isset($_GET[$key]) && is_string($_GET[$key]) ? trim($_GET[$key]) : $default;
}

// ---------- Uploads ----------

// Saves an uploaded image and returns its public path (e.g. /uploads/2026/10/abc.jpg), or throws.
function save_upload($field, $withThumb = true)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('Зураг серверийн хязгаараас (' . ini_get('upload_max_filesize') . ') том байна. '
            . 'cPanel → MultiPHP INI Editor хэсэгт upload_max_filesize-г нэмэгдүүлнэ үү.');
    }
    if ($f['size'] > 10 * 1024 * 1024) {
        throw new RuntimeException('Зураг хэт том байна (10MB хүртэл).');
    }
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        throw new RuntimeException('Зураг хуулахад алдаа гарлаа.');
    }
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'];
    if (defined('IMAGETYPE_WEBP')) {
        $types[IMAGETYPE_WEBP] = 'webp';
    }
    $info = @getimagesize($f['tmp_name']);
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('Зөвхөн JPG, PNG, WEBP, GIF зураг хуулна.');
    }
    $sub = date('Y/m');
    $dir = UPLOAD_DIR . '/' . $sub;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('uploads хавтас руу бичих эрх алга.');
    }
    $name = time() . '-' . bin2hex(random_bytes(6)) . '.' . $types[$info[2]];
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Зураг хадгалж чадсангүй.');
    }
    $path = '/uploads/' . $sub . '/' . $name;
    if (function_exists('optimize_image')) {
        optimize_image($path, $withThumb);
    }
    return $path;
}

function remove_upload($path)
{
    if (!$path || strpos($path, '/uploads/') !== 0) {
        return;
    }
    foreach ([$path, thumb_path($path)] as $p) {
        $file = realpath(ROOT . $p);
        if ($file && strpos($file, realpath(UPLOAD_DIR)) === 0 && is_file($file)) {
            @unlink($file);
        }
    }
}

// ---------- Public queries ----------

function published_where()
{
    return "a.status = 'published' AND a.published_at <= ?";
}

function article_select()
{
    return 'SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
        FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id
        LEFT JOIN users u ON u.id = a.author_id';
}

function list_articles($where, array $params, $limit, $offset = 0)
{
    $sql = article_select() . ' WHERE ' . published_where() . ($where ? ' AND ' . $where : '')
        . ' ORDER BY a.published_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
    return rows($sql, array_merge([now()], $params));
}

function count_articles($where, array $params)
{
    $sql = 'SELECT COUNT(*) FROM articles a WHERE ' . published_where() . ($where ? ' AND ' . $where : '');
    return (int) q($sql, array_merge([now()], $params))->fetchColumn();
}

function all_categories()
{
    static $cats = null;
    if ($cats === null) {
        $cats = rows('SELECT * FROM categories ORDER BY position, id');
    }
    return $cats;
}

function public_context(array $vars = [])
{
    $weekAgo = date('Y-m-d H:i:s', time() - 7 * 86400);
    return array_merge([
        'settings' => settings(),
        'categories' => all_categories(),
        'popular' => rows(article_select() . ' WHERE ' . published_where() . ' AND a.published_at >= ?
            ORDER BY a.views DESC, a.published_at DESC LIMIT 10', [now(), $weekAgo]),
        'latestList' => list_articles('', [], 10),
        'breaking' => list_articles('a.is_breaking = 1', [], 5),
        'currentPath' => isset($GLOBALS['route_path']) ? $GLOBALS['route_path'] : '/',
        'meta' => [],
        'query' => '',
    ], $vars);
}

// ---------- Statistics ----------

function is_bot()
{
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|facebot|preview|monitor|curl|wget|python|headless/i', $ua);
}

// Counts one page view for today (and for the article, when given). Bots are skipped.
function track_view($articleId = null)
{
    if (is_bot()) {
        return false;
    }
    $day = date('Y-m-d');
    q('INSERT INTO stats_daily (day, page_views, article_views) VALUES (?, 1, ?)
        ON DUPLICATE KEY UPDATE page_views = page_views + 1, article_views = article_views + VALUES(article_views)',
        [$day, $articleId ? 1 : 0]);
    if ($articleId) {
        q('INSERT INTO article_views_daily (article_id, day, views) VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE views = views + 1', [$articleId, $day]);
    }
    return true;
}

// ---------- Comments ----------

function app_secret()
{
    $secret = setting('app_secret');
    if ($secret === '') {
        $secret = bin2hex(random_bytes(32));
        save_setting('app_secret', $secret);
        settings(true);
    }
    return $secret;
}

function client_ip_hash()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    return hash_hmac('sha256', $ip, app_secret());
}

// Signed render time for the public comment form (bots that post instantly or replay old forms fail).
function comment_form_token($articleId)
{
    $t = time();
    return $t . '.' . hash_hmac('sha256', $articleId . '|' . $t, app_secret());
}

function comment_token_valid($articleId, $token)
{
    $parts = explode('.', (string) $token, 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        return false;
    }
    $age = time() - (int) $parts[0];
    return $age >= 3 && $age <= 86400
        && hash_equals(hash_hmac('sha256', $articleId . '|' . $parts[0], app_secret()), $parts[1]);
}

function pending_comment_count()
{
    return (int) q("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
}

// ---------- Design ----------

function brand_color()
{
    $c = setting('brand_color');
    return preg_match('/^#[0-9a-fA-F]{6}$/', $c) ? strtolower($c) : '#0a5cff';
}

// Darkens (negative $amount) or lightens a #rrggbb colour.
function shade_color($hex, $amount)
{
    $out = '#';
    foreach (str_split(ltrim($hex, '#'), 2) as $part) {
        $v = hexdec($part);
        $v = $amount < 0 ? $v * (1 + $amount) : $v + (255 - $v) * $amount;
        $out .= str_pad(dechex((int) round(max(0, min(255, $v)))), 2, '0', STR_PAD_LEFT);
    }
    return $out;
}

function footer_pages()
{
    static $pages = null;
    if ($pages === null) {
        $pages = rows('SELECT title, slug, in_footer FROM pages ORDER BY position, id');
    }
    return $pages;
}

function safe_url($url)
{
    $url = trim((string) $url);
    return preg_match('#^https?://#i', $url) ? $url : '';
}

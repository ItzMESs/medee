<?php
// Сайтын агуулгыг MySQL өгөгдлийн санд хадгална.
// Холболтын тохиргоо config.php-д (install.php суулгахдаа үүсгэнэ).

const CONFIG_FILE = __DIR__ . '/../config.php';
const UPLOAD_DIR = __DIR__ . '/../uploads';

// Жагсаалт бүрийн хүснэгтийн баганууд
const LIST_COLUMNS = [
    'slides'   => ['image', 'title', 'text'],
    'services' => ['title', 'image', 'text'],
    'news'     => ['date', 'title', 'image', 'body'],
    'docs'     => ['category', 'date', 'title', 'body', 'file'],
    'jobs'     => ['title', 'location', 'type', 'body'],
];
const PAGE_SECTIONS = ['settings', 'home', 'about', 'careers'];

if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
}

// Мөр хоорондын текстийг догол мөр болгон хэвлэнэ
function paragraphs($text) {
    $out = '';
    foreach (preg_split('/\R{2,}/', trim((string) $text)) as $p) {
        if ($p !== '') $out .= '<p>' . nl2br(e($p)) . '</p>';
    }
    return $out;
}

function is_installed() {
    return is_file(CONFIG_FILE);
}

function db_config() {
    static $cfg;
    if ($cfg === null) $cfg = require CONFIG_FILE;
    return $cfg;
}

function db_connect($cfg) {
    $dsn = 'mysql:host=' . $cfg['db_host'] . ';port=' . ($cfg['db_port'] ?? 3306) . ';dbname=' . $cfg['db_name'] . ';charset=utf8mb4';
    return new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function db() {
    static $pdo;
    if ($pdo === null) $pdo = db_connect(db_config());
    return $pdo;
}

// Хүснэгтийн нэр (угтвартай)
function t($name) {
    return '`' . preg_replace('/[^a-z0-9_]/i', '', (db_config()['db_prefix'] ?? 'cs_') . $name) . '`';
}

// Хүснэгтүүдийг үүсгэж, хоосон бол анхны агуулгаар дүүргэнэ
function db_install() {
    $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $item = '`id` VARCHAR(16) NOT NULL PRIMARY KEY, `sort` INT NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP';
    $sql = [
        "CREATE TABLE IF NOT EXISTS " . t('settings') . " (`section` VARCHAR(32) NOT NULL, `field` VARCHAR(64) NOT NULL, `value` MEDIUMTEXT NOT NULL, PRIMARY KEY (`section`, `field`)) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('slides') . " ($item, `image` VARCHAR(255) NOT NULL DEFAULT '', `title` VARCHAR(255) NOT NULL DEFAULT '', `text` TEXT NOT NULL) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('services') . " ($item, `title` VARCHAR(255) NOT NULL DEFAULT '', `image` VARCHAR(255) NOT NULL DEFAULT '', `text` TEXT NOT NULL) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('news') . " ($item, `date` DATE NOT NULL, `title` VARCHAR(255) NOT NULL DEFAULT '', `image` VARCHAR(255) NOT NULL DEFAULT '', `body` MEDIUMTEXT NOT NULL, KEY `date` (`date`)) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('docs') . " ($item, `category` VARCHAR(32) NOT NULL DEFAULT '', `date` DATE NOT NULL, `title` VARCHAR(255) NOT NULL DEFAULT '', `body` TEXT NOT NULL, `file` VARCHAR(255) NOT NULL DEFAULT '') $opts",
        "CREATE TABLE IF NOT EXISTS " . t('jobs') . " ($item, `title` VARCHAR(255) NOT NULL DEFAULT '', `location` VARCHAR(255) NOT NULL DEFAULT '', `type` VARCHAR(255) NOT NULL DEFAULT '', `body` MEDIUMTEXT NOT NULL) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('messages') . " (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `created_at` DATETIME NOT NULL, `is_read` TINYINT(1) NOT NULL DEFAULT 0, `name` VARCHAR(100) NOT NULL, `email` VARCHAR(150) NOT NULL, `phone` VARCHAR(30) NOT NULL DEFAULT '', `message` TEXT NOT NULL, `ip` VARCHAR(45) NOT NULL DEFAULT '') $opts",
        "CREATE TABLE IF NOT EXISTS " . t('admins') . " (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(64) NOT NULL UNIQUE, `password_hash` VARCHAR(255) NOT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('login_attempts') . " (`ip` VARCHAR(45) NOT NULL PRIMARY KEY, `attempts` INT NOT NULL, `last_at` INT NOT NULL) $opts",
    ];
    foreach ($sql as $q) db()->exec($q);

    $defaults = store_defaults();
    if (!db()->query('SELECT COUNT(*) FROM ' . t('settings'))->fetchColumn()) {
        foreach (PAGE_SECTIONS as $section) store_save_page($section, $defaults[$section]);
    }
    foreach (LIST_COLUMNS as $list => $cols) {
        if (db()->query('SELECT COUNT(*) FROM ' . t($list))->fetchColumn()) continue;
        foreach ($defaults[$list] as $it) store_save_item($list, $it, true);
    }
}

function store_defaults() {
    return [
        'settings' => [
            'name'        => 'КОМПАНИЙН НЭР ХК',
            'logo_title'  => 'COMPANY NAME',
            'logo_sub'    => 'JOINT STOCK COMPANY',
            'logo'        => 'assets/img/logo.svg',
            'phone'       => '+976 7000-0000',
            'email'       => 'info@example.mn',
            'address'     => 'Монгол улс, Улаанбаатар хот, Сонгинохайрхан дүүрэг, 4-р хороо',
            'facebook'    => 'https://www.facebook.com/',
            'english_url' => '#',
            'mail_to'     => 'info@example.mn',
            'developer'   => '',
        ],
        'home' => [
            'about_title' => 'БИДНИЙ ТУХАЙ',
            'about_text'  => 'Манай компани нь Монгол орны баруун хязгаарт материаллаг бааз, тоног төхөөрөмж, техник хэрэгсэл, машин механизмын баазыг бүрдүүлж, дадлага туршлагатай боловсон хүчний нөөцийг бэхжүүлэн, оновчтой менежментээр ханган ажиллаж байна. Бүтээн байгуулалтынхаа хэмжээг жилээс жилд өсгөн нэмэгдүүлж авто замын салбар болон уурхайд хөрс хуулалт, эрдэс баялгийн олборлолт зэрэг чиглэлүүдээр үйл ажиллагаагаа өргөжүүлэн тэргүүлэх байр сууриа эзлэн ажиллаж байна.',
            'about_image' => 'assets/img/about.svg',
            'video_title' => 'КОМПАНИЙН ТАНИЛЦУУЛГА БИЧЛЭГ',
            'video_file'  => '',
            'video_url'   => '',
            'video_poster'=> 'assets/img/video-poster.svg',
        ],
        'about' => [
            'heading' => 'КОМПАНИЙН ТАНИЛЦУУЛГА',
            'image'   => 'assets/img/about.svg',
            'text'    => "Манай компани нь 1995 онд үүсгэн байгуулагдсан бөгөөд авто зам барилга, зам арчлалт, уул уурхайн гэрээт олборлолтын чиглэлээр үйл ажиллагаа явуулдаг үндэсний компани юм.\n\nБид орчин үеийн техник технологи, туршлагатай мэргэжилтнүүд, аюулгүй ажиллагааны өндөр стандартаар харилцагчдадаа чанартай үйлчилгээ үзүүлдэг.",
            'vision'  => 'Монгол улсын уул уурхайн үйлдвэрлэлийн салбарын шинэчлэлд манлайлагч байж, тэргүүлэх орон зайг эзэлнэ.',
            'mission' => 'Чанартай, аюулгүй, хариуцлагатай ажлаар харилцагч, ажилтан, нийгэмдээ үнэ цэнийг бүтээнэ.',
            'values'  => 'Аюулгүй ажиллагаа, багийн ажил, хариуцлага, тасралтгүй хөгжил.',
        ],
        'careers' => [
            'policy' => 'Бид ажилтнуудынхаа ур чадварыг тасралтгүй хөгжүүлж, аюулгүй, эрүүл ажлын байр, өрсөлдөхүйц цалин хөлс, нийгмийн баталгааг хангахыг эрхэмлэдэг.',
        ],
        'slides' => [
            ['id' => 's1', 'image' => 'assets/img/hero-1.svg', 'title' => 'АЛСЫН ХАРАА', 'text' => 'Монгол улсын уул уурхайн үйлдвэрлэлийн салбарын шинэчлэлд манлайлагч байж, тэргүүлэх орон зайг эзэлнэ.'],
            ['id' => 's2', 'image' => 'assets/img/hero-2.svg', 'title' => 'ЭРХЭМ ЗОРИЛГО', 'text' => 'Чанартай, аюулгүй, хариуцлагатай ажлаар харилцагч, ажилтан, нийгэмдээ үнэ цэнийг бүтээнэ.'],
            ['id' => 's3', 'image' => 'assets/img/hero-1.svg', 'title' => 'ҮНЭТ ЗҮЙЛС', 'text' => 'Аюулгүй ажиллагаа, багийн ажил, хариуцлага, тасралтгүй хөгжил.'],
        ],
        'services' => [
            ['id' => 'o1', 'title' => 'УУЛ УУРХАЙ', 'image' => 'assets/img/mining.svg', 'text' => 'Манай компани 2014 оноос эхлэн уурхайн хөрс хуулалт, нүүрс олборлолтын төслийг хэрэгжүүлэн ажиллаж байна. Орчин үеийн техник, туршлагатай боловсон хүчнээр үйлдвэрлэлийн өндөр бүтээмжийг хангадаг.'],
            ['id' => 'o2', 'title' => 'ЗАМ АРЧЛАЛТ', 'image' => 'assets/img/road-maintenance.svg', 'text' => 'Уурхайгаас боомт хүртэлх 300 гаруй км урттай авто замын засвар, арчлалтын ажлыг жилийн турш тасралтгүй гүйцэтгэж байна.'],
            ['id' => 'o3', 'title' => 'ЗАМ БАРИЛГА', 'image' => 'assets/img/road-construction.svg', 'text' => 'Аймаг, сумдын чиглэлийн 100 гаруй км хатуу хучилттай авто зам барилгын ажлыг өөрийн хөрөнгө, техникээр богино хугацаанд гүйцэтгэсэн туршлагатай.'],
        ],
        'news' => [
            ['id' => 'n1', 'date' => '2026-10-15', 'title' => 'Зам арчлалтын өвлийн бэлтгэл ажил эхэллээ', 'image' => 'assets/img/road-maintenance.svg', 'body' => 'Өвлийн улиралд замын хөдөлгөөний аюулгүй байдлыг хангах зорилгоор цас цэвэрлэгээний техник, материалын бэлтгэлийг бүрэн хангаж эхэллээ.'],
            ['id' => 'n2', 'date' => '2026-09-02', 'title' => 'Шинэ техник, тоног төхөөрөмж нэмэгдлээ', 'image' => 'assets/img/mining.svg', 'body' => 'Уурхайн үйлдвэрлэлийн хүчин чадлыг нэмэгдүүлэх зорилгоор шинэ техник, тоног төхөөрөмж ашиглалтад орууллаа.'],
            ['id' => 'n3', 'date' => '2026-07-20', 'title' => 'Хатуу хучилттай замын шинэ хэсгийг ашиглалтад орууллаа', 'image' => 'assets/img/road-construction.svg', 'body' => 'Төлөвлөсөн хугацаанаас өмнө замын шинэ хэсгийг ашиглалтад хүлээлгэн өглөө.'],
        ],
        'docs' => [],
        'jobs' => [
            ['id' => 'j1', 'title' => 'Хүнд машин механизмын оператор', 'location' => 'Уурхай', 'type' => 'Үндсэн', 'body' => ''],
            ['id' => 'j2', 'title' => 'Авто засварчин', 'location' => 'Уурхай', 'type' => 'Үндсэн', 'body' => ''],
            ['id' => 'j3', 'title' => 'Зам барилгын инженер', 'location' => 'Төсөл', 'type' => 'Үндсэн', 'body' => ''],
        ],
    ];
}

function store_load() {
    $defaults = store_defaults();
    $data = [];
    foreach (PAGE_SECTIONS as $section) $data[$section] = $defaults[$section];
    foreach (db()->query('SELECT `section`, `field`, `value` FROM ' . t('settings')) as $r) {
        if (isset($data[$r['section']])) $data[$r['section']][$r['field']] = $r['value'];
    }
    foreach (LIST_COLUMNS as $list => $cols) {
        $data[$list] = db()->query('SELECT `id`, `' . implode('`, `', $cols) . '` FROM ' . t($list) . ' ORDER BY `sort`, `created_at`')->fetchAll();
    }
    return $data;
}

// Нийтийн хуудсуудад: суулгаагүй бол install.php руу, сан ажиллахгүй бол 503
function load_site() {
    if (!is_installed()) {
        header('Location: install.php');
        exit;
    }
    try {
        return store_load();
    } catch (PDOException $ex) {
        http_response_code(503);
        exit('<!doctype html><meta charset="utf-8"><p style="font:16px sans-serif;text-align:center;margin-top:20vh">Сайт түр засвартай байна. Түр хүлээгээд дахин оролдоно уу.</p>');
    }
}

function store_save_page($section, $values) {
    $st = db()->prepare('REPLACE INTO ' . t('settings') . ' (`section`, `field`, `value`) VALUES (?, ?, ?)');
    foreach ($values as $field => $value) $st->execute([$section, $field, (string) $value]);
}

function store_save_item($list, $item, $isNew) {
    $cols = LIST_COLUMNS[$list];
    $values = [];
    foreach ($cols as $c) $values[] = (string) ($item[$c] ?? '');
    if ($isNew) {
        $sort = (int) db()->query('SELECT COALESCE(MAX(`sort`), 0) + 1 FROM ' . t($list))->fetchColumn();
        $sql = 'INSERT INTO ' . t($list) . ' (`id`, `sort`, `' . implode('`, `', $cols) . '`) VALUES (?, ?' . str_repeat(', ?', count($cols)) . ')';
        db()->prepare($sql)->execute(array_merge([$item['id'], $sort], $values));
    } else {
        $sql = 'UPDATE ' . t($list) . ' SET `' . implode('` = ?, `', $cols) . '` = ? WHERE `id` = ?';
        db()->prepare($sql)->execute(array_merge($values, [$item['id']]));
    }
}

function store_get_item($list, $id) {
    $st = db()->prepare('SELECT * FROM ' . t($list) . ' WHERE `id` = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function store_delete_item($list, $id) {
    db()->prepare('DELETE FROM ' . t($list) . ' WHERE `id` = ?')->execute([$id]);
}

// Дарааллыг хөрш мөртэй нь солино
function store_move_item($list, $id, $up) {
    $ids = db()->query('SELECT `id` FROM ' . t($list) . ' ORDER BY `sort`, `created_at`')->fetchAll(PDO::FETCH_COLUMN);
    $i = array_search($id, $ids, true);
    if ($i === false) return;
    $j = $up ? $i - 1 : $i + 1;
    if ($j < 0 || $j >= count($ids)) return;
    [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
    $st = db()->prepare('UPDATE ' . t($list) . ' SET `sort` = ? WHERE `id` = ?');
    db()->beginTransaction();
    foreach ($ids as $n => $rid) $st->execute([$n + 1, $rid]);
    db()->commit();
}

// ---------- Санал хүсэлт ----------
function messages_all() {
    return db()->query('SELECT *, `is_read` AS `read`, DATE_FORMAT(`created_at`, \'%Y-%m-%d %H:%i\') AS `date` FROM ' . t('messages') . ' ORDER BY `id` DESC')->fetchAll();
}

function message_add($name, $email, $phone, $message, $ip) {
    db()->prepare('INSERT INTO ' . t('messages') . ' (`created_at`, `name`, `email`, `phone`, `message`, `ip`) VALUES (NOW(), ?, ?, ?, ?, ?)')
        ->execute([$name, $email, $phone, $message, $ip]);
}

function message_toggle($id) {
    db()->prepare('UPDATE ' . t('messages') . ' SET `is_read` = 1 - `is_read` WHERE `id` = ?')->execute([(int) $id]);
}

function message_delete($id) {
    db()->prepare('DELETE FROM ' . t('messages') . ' WHERE `id` = ?')->execute([(int) $id]);
}

// ---------- Админ ----------
function admin_get() {
    return db()->query('SELECT * FROM ' . t('admins') . ' ORDER BY `id` LIMIT 1')->fetch() ?: null;
}

function admin_find($username) {
    $st = db()->prepare('SELECT * FROM ' . t('admins') . ' WHERE `username` = ?');
    $st->execute([$username]);
    return $st->fetch() ?: null;
}

function admin_create($username, $password) {
    db()->prepare('INSERT INTO ' . t('admins') . ' (`username`, `password_hash`) VALUES (?, ?)')
        ->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
}

function admin_set_password($id, $password) {
    db()->prepare('UPDATE ' . t('admins') . ' SET `password_hash` = ? WHERE `id` = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
}

// Нэвтрэх оролдлогын хязгаар (IP бүрт 15 минутад 10 удаа)
function login_blocked($ip) {
    $st = db()->prepare('SELECT `attempts` FROM ' . t('login_attempts') . ' WHERE `ip` = ? AND `last_at` > ?');
    $st->execute([$ip, time() - 900]);
    return (int) $st->fetchColumn() >= 10;
}

function login_record($ip, $failed) {
    db()->prepare('DELETE FROM ' . t('login_attempts') . ' WHERE `last_at` <= ?')->execute([time() - 900]);
    if ($failed) {
        db()->prepare('INSERT INTO ' . t('login_attempts') . ' (`ip`, `attempts`, `last_at`) VALUES (?, 1, ?) ON DUPLICATE KEY UPDATE `attempts` = `attempts` + 1, `last_at` = VALUES(`last_at`)')
            ->execute([$ip, time()]);
    } else {
        db()->prepare('DELETE FROM ' . t('login_attempts') . ' WHERE `ip` = ?')->execute([$ip]);
    }
}

function find_item($list, $id) {
    foreach ($list as $item) if (($item['id'] ?? '') === $id) return $item;
    return null;
}

// YouTube холбоосоос embed хаяг гаргана
function youtube_embed($url) {
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~', (string) $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return '';
}

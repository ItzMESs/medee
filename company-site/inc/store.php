<?php
// Сайтын агуулгыг MySQL өгөгдлийн санд хадгална.
// Холболтын тохиргоо config.php-д (install.php суулгахдаа үүсгэнэ).

const CONFIG_FILE = __DIR__ . '/../config.php';
const UPLOAD_DIR = __DIR__ . '/../uploads';

// Жагсаалт бүрийн хүснэгтийн баганууд
const LIST_COLUMNS = [
    'slides'   => ['image', 'title', 'text'],
    'services' => ['title', 'image', 'text'],
    'projects' => ['year', 'title', 'location', 'image', 'body'],
    'gallery'  => ['image', 'title'],
    'jobs'     => ['title', 'location', 'type', 'body'],
];
const PAGE_SECTIONS = ['settings', 'home', 'about', 'careers'];
// Анхны агуулга өөрчлөгдөх бүрт нэмэгдүүлнэ (inc/upgrade.php)
const CONTENT_VERSION = 3;

if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
}

// Файл өөрчлөгдөх бүрт хөтөч шинэ хувилбарыг татахаар ?v= нэмнэ
function asset($path) {
    $file = __DIR__ . '/../' . $path;
    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

// Мөр хоорондын текстийг догол мөр болгон хэвлэнэ
function paragraphs($text) {
    $out = '';
    foreach (preg_split('/\R{2,}/u', trim((string) $text)) as $p) {
        if ($p === '') continue;
        $lines = preg_split('/\R/u', $p);
        // Бүх мөр "- "-ээр эхэлсэн бол жагсаалт болгоно
        if (count(array_filter($lines, fn($l) => preg_match('/^\s*[-•]\s+/u', $l))) === count($lines)) {
            $out .= '<ul class="list">';
            foreach ($lines as $l) $out .= '<li>' . e(preg_replace('/^\s*[-•]\s+/u', '', $l)) . '</li>';
            $out .= '</ul>';
        } else {
            $out .= '<p>' . nl2br(e($p)) . '</p>';
        }
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
function db_create_tables() {
    $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $item = '`id` VARCHAR(16) NOT NULL PRIMARY KEY, `sort` INT NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP';
    $sql = [
        "CREATE TABLE IF NOT EXISTS " . t('settings') . " (`section` VARCHAR(32) NOT NULL, `field` VARCHAR(64) NOT NULL, `value` MEDIUMTEXT NOT NULL, PRIMARY KEY (`section`, `field`)) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('slides') . " ($item, `image` VARCHAR(255) NOT NULL DEFAULT '', `title` VARCHAR(255) NOT NULL DEFAULT '', `text` TEXT NOT NULL) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('services') . " ($item, `title` VARCHAR(255) NOT NULL DEFAULT '', `image` VARCHAR(255) NOT NULL DEFAULT '', `text` TEXT NOT NULL) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('projects') . " ($item, `year` VARCHAR(32) NOT NULL DEFAULT '', `title` VARCHAR(255) NOT NULL DEFAULT '', `location` VARCHAR(255) NOT NULL DEFAULT '', `image` VARCHAR(255) NOT NULL DEFAULT '', `body` MEDIUMTEXT NOT NULL) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('gallery') . " ($item, `image` VARCHAR(255) NOT NULL DEFAULT '', `title` VARCHAR(255) NOT NULL DEFAULT '') $opts",
        "CREATE TABLE IF NOT EXISTS " . t('jobs') . " ($item, `title` VARCHAR(255) NOT NULL DEFAULT '', `location` VARCHAR(255) NOT NULL DEFAULT '', `type` VARCHAR(255) NOT NULL DEFAULT '', `body` MEDIUMTEXT NOT NULL) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('messages') . " (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `created_at` DATETIME NOT NULL, `is_read` TINYINT(1) NOT NULL DEFAULT 0, `name` VARCHAR(100) NOT NULL, `email` VARCHAR(150) NOT NULL, `phone` VARCHAR(30) NOT NULL DEFAULT '', `message` TEXT NOT NULL, `ip` VARCHAR(45) NOT NULL DEFAULT '') $opts",
        "CREATE TABLE IF NOT EXISTS " . t('admins') . " (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(64) NOT NULL UNIQUE, `password_hash` VARCHAR(255) NOT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) $opts",
        "CREATE TABLE IF NOT EXISTS " . t('login_attempts') . " (`ip` VARCHAR(45) NOT NULL PRIMARY KEY, `attempts` INT NOT NULL, `last_at` INT NOT NULL) $opts",
    ];
    foreach ($sql as $q) db()->exec($q);
}

function db_install() {
    db_create_tables();

    $defaults = store_defaults();
    if (!db()->query('SELECT COUNT(*) FROM ' . t('settings'))->fetchColumn()) {
        foreach (PAGE_SECTIONS as $section) store_save_page($section, $defaults[$section]);
        content_version_save();
    }
    foreach (LIST_COLUMNS as $list => $cols) {
        if (db()->query('SELECT COUNT(*) FROM ' . t($list))->fetchColumn()) continue;
        foreach ($defaults[$list] as $it) store_save_item($list, $it, true);
    }
}

function store_defaults() {
    $intro = '"National Road Bridge Construction" ХХК нь Монгол улсын авто замын салбарт олон улсын стандартад нийцсэн, шинэ техник технологийг судлан нэвтрүүлэх, салбартаа өрсөлдөхүйц чанартай үйлчилгээ үзүүлэх, улмаар энэ салбартаа манлайлах байр суурьтай болох зорилгыг өвөртлөн 2017 онд үүсгэн байгуулагдсан. Одоогоор тогтвор суурьшилтай ажилтан, инженерийн баг хамт олонтой үйл ажиллагаагаа явуулж байна. Бид авто замын салбарт захиалагч, үйлчлүүлэгчдийн шаардлага, хүсэл сонирхлыг дээдлэн, ажил үйлчилгээг үр дүнтэй явуулж, үйл ажиллагаандаа чанарыг эрхэмлэн ажилладаг.';
    $vision = 'Хэрэглэгч, захиалагчийн эрэлт хэрэгцээг дээдлэн хууль тогтоомжийн хүрээнд авто зам, төмөр замын дээд болон доод бүтцийн үйл ажиллагааг олон улсын жишигт хүргэнэ.';
    $mission = 'Авто зам, гүүр, туннелийн барилгын салбарт хэрэглэгч, захиалагчаа дээдэлж, чанарыг эрхэмлэсэн олон улсын чанарын стандартад нийцсэн бүтээгдэхүүн, үйлчилгээгээрээ тэргүүлэгч компани байх.';
    return [
        'settings' => [
            'name'          => '"National Road Bridge Construction" ХХК',
            'logo_title'    => 'NATIONAL ROAD BRIDGE',
            'logo_sub'      => 'CONSTRUCTION',
            'logo'          => 'assets/img/logo.png',
            'logo_header'   => 'assets/img/logo.png',
            'logo_wordmark' => 'assets/img/logo-wordmark.png',
            'phone'       => '+976 7000-0000',
            'email'       => 'info@example.mn',
            'address'     => 'Монгол улс, Улаанбаатар хот',
            'facebook'    => 'https://www.facebook.com/',
            'english_url' => '#',
            'mail_to'     => 'info@example.mn',
            'developer'   => '',
        ],
        'home' => [
            'about_title' => 'БИДНИЙ ТУХАЙ',
            'about_text'  => $intro,
            'about_image' => 'assets/img/about.svg',
            'video_title' => 'КОМПАНИЙН ТАНИЛЦУУЛГА БИЧЛЭГ',
            'video_file'  => '',
            'video_url'   => '',
            'video_poster'=> 'assets/img/video-poster.svg',
        ],
        'about' => [
            'heading' => 'КОМПАНИЙН ТАНИЛЦУУЛГА',
            'image'   => 'assets/img/about.svg',
            'text'    => $intro . "\n\nБидний гүйцэтгэсэн ажлуудыг 9 ба түүнээс дээш инженерийн бүрэлдэхүүнтэйгээр хийж, одоог хүртэл тогтвортой үйл ажиллагаа явуулсаар байна.",
            'vision'  => $vision,
            'mission' => $mission,
            'values'  => "- Компанийн олон жилийн мэдлэг, туршлага\n- Аюулгүй ажиллагаа\n- Мэргэжлийн баг хамт олон\n- Чанартай үйлчилгээ\n- Өндөр хариуцлага\n- Харилцагч байгууллагууд",
            'history' => implode("\n\n", [
                '2017 — Хангайн даваа – Өгий нуур чиглэлийн хатуу хучилттай авто замын засвар арчлалтын ажлыг хийж гүйцэтгэсэн. Сүхбаатар аймгийн Сүхбаатар сумын 1.8 км хатуу хучилттай авто замын ажлыг "Борхойн Зам" ХХК-ийн туслан гүйцэтгэгчээр гүйцэтгэсэн.',
                '2018 — "Монкон Констракшн" ХХК-ийн Мандала хотхоны зам талбайн тохижилтын ажлыг хийж гүйцэтгэсэн. Мөн Сүхбаатар аймгийн Сүхбаатар сумын 1.8 км хатуу хучилттай авто замын ажлыг хийж гүйцэтгэсэн. "Асралт Групп" ХХК-ийн захиалгаар Горхи Тэрэлжээс Амбан олом хүртэлх 5.4 км хатуу хучилттай авто замын ажлын зураг төслийг иж бүрнээр хийж гүйцэтгэсэн.',
                '2019 — Дундговь аймгийн Мандалговь нисэх онгоцны буудлын перроны өргөтгөлийн ажлыг хийж гүйцэтгэсэн. А0202 дугаартай Мандалговь – Даланзадгад чиглэлийн авто зам, А0201 дугаартай Улаанбаатар – Мандалговь чиглэлийн авто замын хучилтын засвар арчлалтын ажлыг хийж гүйцэтгэсэн. Мөн Сүхбаатар аймгийн Сүхбаатар сумын 1.8 км хатуу хучилттай авто замын ажлыг хийж гүйцэтгэсэн.',
                '2020 — Өмнөговь аймгийн Тавантолгойн нүүрсний уурхайгаас Дорноговь аймгийн Зүүнбаян сум хүртэлх төмөр замын суурь бүтцийн ажлын 317 км-ээс 322 км хоорондох суурь бүтцийн ажлыг хийж гүйцэтгэсэн.',
                '2020–2021 — Өмнөговь аймгийн Цогтцэций – Гашуун сухайт чиглэлийн хүнд даацын хатуу хучилттай авто замын ПК160+000 – ПК190+000-д "Мотор сервис" ХХК-д инженерийн үйлчилгээ үзүүлсэн.',
                '2022 — Дорноговь аймгийн Зүүнбаян – Ханги чиглэлийн төмөр замын доод бүтцийн ажлын ПК97+000 – ПК105+000-д "Диамонд Рөүд" ХХК-д инженерийн үйлчилгээ үзүүлсэн. Хэнтий аймгийн Өндөрхаан – Норовлин чиглэлийн 200 км хатуу хучилттай авто замын Багц 1-р хэсгийн 68.6 км авто замын техник технологийн хяналтыг хийж гүйцэтгэсэн.',
                '2023 — Өндөрхаан – Норовлин чиглэлийн 200 км хатуу хучилттай авто замын Багц 2-р хэсгийн 62 км авто замын техник технологийн хяналтыг хийж гүйцэтгэсэн.',
                '2024 — Өндөрхаан – Норовлин чиглэлийн 200 км хатуу хучилттай авто замын Багц 1, 2-р хэсгийн 130 км авто замын техник технологийн хяналтыг хийж гүйцэтгэж байна.',
            ]),
        ],
        'careers' => [
            'policy' => 'Бид ажилтнуудынхаа ур чадварыг тасралтгүй хөгжүүлж, аюулгүй, эрүүл ажлын байр, өрсөлдөхүйц цалин хөлс, нийгмийн баталгааг хангахыг эрхэмлэдэг.',
        ],
        'slides' => [
            ['id' => 's1', 'image' => 'assets/img/hero-1.svg', 'title' => 'АЛСЫН ХАРАА', 'text' => $vision],
            ['id' => 's2', 'image' => 'assets/img/hero-2.svg', 'title' => 'ЭРХЭМ ЗОРИЛГО', 'text' => $mission],
            ['id' => 's3', 'image' => 'assets/img/hero-1.svg', 'title' => 'ҮНЭТ ЗҮЙЛС', 'text' => 'Мэдлэг туршлага, аюулгүй ажиллагаа, мэргэжлийн баг хамт олон, чанартай үйлчилгээ, өндөр хариуцлага.'],
        ],
        'services' => [
            ['id' => 'o1', 'title' => 'ЗАМ БАРИЛГА', 'image' => 'assets/img/road-construction.svg', 'text' => "Хатуу хучилттай болон хайрган хучилттай авто зам, замын байгууламжийг барих ажлыг гүйцэтгэдэг.\n\n- Хатуу хучилттай зам барих\n- Хайрган хучилттай зам барих\n- Замын байгууламжид хамаарах талбай барих\n- Үерийн хамгаалалтын далан барих\n- Тавилан барих"],
            ['id' => 'o2', 'title' => 'ЗАСВАР АРЧЛАЛТ', 'image' => 'assets/img/road-maintenance.svg', 'text' => "Хатуу хучилттай болон хайрган хучилттай авто зам, замын байгууламжийн засвар, арчлалтын ажлыг гүйцэтгэдэг.\n\n- Хатуу хучилттай болон хайрган хучилттай зам засварлах\n- Замын байгууламжид хамаарах талбай засварлах\n- Тавилан засварлах"],
            ['id' => 'o3', 'title' => 'ТЕХНИК ХЯНАЛТ', 'image' => 'assets/img/supervision.svg', 'text' => "Авто зам, замын байгууламжийг барих, засварлахад техник, технологийн хяналт тавих зөвлөх үйлчилгээ үзүүлдэг.\n\nХэнтий аймгийн Өндөрхаан – Норовлин чиглэлийн 200 км хатуу хучилттай авто замын техник технологийн хяналтыг гүйцэтгэж байна."],
        ],
        'projects' => [
            ['id' => 'p1', 'year' => '2022–2024', 'title' => 'Өндөрхаан – Норовлин чиглэлийн 200 км авто замын техник хяналт', 'location' => 'Хэнтий аймаг', 'image' => 'assets/img/supervision.svg', 'body' => "Өндөрхаан – Норовлин чиглэлийн 200 км хатуу хучилттай авто замын техник технологийн хяналт.\n\n- 2022 он: Багц 1-р хэсгийн 68.6 км\n- 2023 он: Багц 2-р хэсгийн 62 км\n- 2024 он: Багц 1, 2-р хэсгийн 130 км"],
            ['id' => 'p2', 'year' => '2022', 'title' => 'Зүүнбаян – Ханги чиглэлийн төмөр замын доод бүтэц', 'location' => 'Дорноговь аймаг', 'image' => 'assets/img/supervision.svg', 'body' => 'Зүүнбаян – Ханги чиглэлийн төмөр замын доод бүтцийн ажлын ПК97+000 – ПК105+000-д "Диамонд Рөүд" ХХК-д инженерийн үйлчилгээ үзүүлсэн.'],
            ['id' => 'p3', 'year' => '2020–2021', 'title' => 'Цогтцэций – Гашуун сухайт чиглэлийн хүнд даацын авто зам', 'location' => 'Өмнөговь аймаг', 'image' => 'assets/img/road-construction.svg', 'body' => 'Цогтцэций – Гашуун сухайт чиглэлийн хүнд даацын хатуу хучилттай авто замын ПК160+000 – ПК190+000-д "Мотор сервис" ХХК-д инженерийн үйлчилгээ үзүүлсэн.'],
            ['id' => 'p4', 'year' => '2020', 'title' => 'Тавантолгой – Зүүнбаян төмөр замын суурь бүтэц', 'location' => 'Өмнөговь, Дорноговь аймаг', 'image' => 'assets/img/road-construction.svg', 'body' => 'Тавантолгойн нүүрсний уурхайгаас Зүүнбаян сум хүртэлх төмөр замын суурь бүтцийн ажлын 317 км-ээс 322 км хоорондох суурь бүтцийн ажлыг хийж гүйцэтгэсэн.'],
            ['id' => 'p5', 'year' => '2019', 'title' => 'Мандалговь нисэх онгоцны буудлын перроны өргөтгөл', 'location' => 'Дундговь аймаг', 'image' => 'assets/img/road-construction.svg', 'body' => 'Мандалговь нисэх онгоцны буудлын перроны өргөтгөлийн ажлыг хийж гүйцэтгэсэн.'],
            ['id' => 'p6', 'year' => '2019', 'title' => 'А0201, А0202 чиглэлийн авто замын засвар арчлалт', 'location' => 'Төв, Дундговь, Өмнөговь аймаг', 'image' => 'assets/img/road-maintenance.svg', 'body' => 'А0202 дугаартай Мандалговь – Даланзадгад чиглэлийн авто зам, А0201 дугаартай Улаанбаатар – Мандалговь чиглэлийн авто замын хучилтын засвар арчлалтын ажлыг хийж гүйцэтгэсэн.'],
            ['id' => 'p7', 'year' => '2018', 'title' => 'Горхи-Тэрэлж – Амбан олом 5.4 км авто замын зураг төсөл', 'location' => 'Төв аймаг', 'image' => 'assets/img/road-construction.svg', 'body' => '"Асралт Групп" ХХК-ийн захиалгаар Горхи Тэрэлжээс Амбан олом хүртэлх 5.4 км хатуу хучилттай авто замын ажлын зураг төслийг иж бүрнээр хийж гүйцэтгэсэн.'],
            ['id' => 'p8', 'year' => '2017–2019', 'title' => 'Сүхбаатар сумын 1.8 км хатуу хучилттай авто зам', 'location' => 'Сүхбаатар аймаг', 'image' => 'assets/img/road-construction.svg', 'body' => 'Сүхбаатар аймгийн Сүхбаатар сумын 1.8 км хатуу хучилттай авто замын ажлыг хийж гүйцэтгэсэн.'],
            ['id' => 'p9', 'year' => '2017', 'title' => 'Хангайн даваа – Өгий нуур чиглэлийн авто замын засвар арчлалт', 'location' => 'Архангай аймаг', 'image' => 'assets/img/road-maintenance.svg', 'body' => 'Хангайн даваа – Өгий нуур чиглэлийн хатуу хучилттай авто замын засвар арчлалтын ажлыг хийж гүйцэтгэсэн.'],
        ],
        'gallery' => [],
        'jobs' => [],
    ];
}

function store_load() {
    $defaults = store_defaults();
    $data = [];
    foreach (PAGE_SECTIONS as $section) $data[$section] = $defaults[$section];
    $version = 0;
    foreach (db()->query('SELECT `section`, `field`, `value` FROM ' . t('settings')) as $r) {
        if (isset($data[$r['section']])) $data[$r['section']][$r['field']] = $r['value'];
        if ($r['section'] === 'meta' && $r['field'] === 'content_version') $version = (int) $r['value'];
    }
    if ($version < CONTENT_VERSION) {
        require_once __DIR__ . '/upgrade.php';
        content_upgrade($version);
        return store_load();
    }
    // Устгагдсан хуучин лого файлуудыг одоогийн лого руу шилжүүлнэ
    foreach (['logo', 'logo_header', 'logo_wordmark'] as $k) {
        if (in_array($data['settings'][$k], ['assets/img/logo.svg', 'assets/img/logo-light.png', 'assets/img/logo-wordmark-white.png'], true)) {
            $data['settings'][$k] = $defaults['settings'][$k];
        }
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

function content_version_save() {
    store_save_page('meta', ['content_version' => CONTENT_VERSION]);
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

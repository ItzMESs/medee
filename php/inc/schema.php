<?php
// Bump when tables are added; bootstrap re-runs create_schema() on older databases.
define('SCHEMA_VERSION', 5);

// Creates the tables (safe to run more than once) and default rows.
function create_schema(PDO $pdo)
{
    $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(64) NOT NULL UNIQUE,
        name VARCHAR(128) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(16) NOT NULL DEFAULT 'editor',
        created_at DATETIME NOT NULL
    ) $opts");
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(128) NOT NULL,
        slug VARCHAR(128) NOT NULL UNIQUE,
        position INT NOT NULL DEFAULT 0
    ) $opts");
    $pdo->exec("CREATE TABLE IF NOT EXISTS articles (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(191) NOT NULL,
        excerpt TEXT NOT NULL,
        content MEDIUMTEXT NOT NULL,
        image VARCHAR(255) NULL,
        category_id INT UNSIGNED NULL,
        author_id INT UNSIGNED NULL,
        status VARCHAR(16) NOT NULL DEFAULT 'draft',
        is_featured TINYINT(1) NOT NULL DEFAULT 0,
        is_breaking TINYINT(1) NOT NULL DEFAULT 0,
        views INT UNSIGNED NOT NULL DEFAULT 0,
        fb_post_id VARCHAR(64) NULL,
        fb_error TEXT NULL,
        published_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        INDEX idx_pub (status, published_at),
        INDEX idx_cat (category_id),
        CONSTRAINT fk_art_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        CONSTRAINT fk_art_user FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
    ) $opts");
    $pdo->exec("CREATE TABLE IF NOT EXISTS stats_daily (
        day DATE NOT NULL PRIMARY KEY,
        page_views INT UNSIGNED NOT NULL DEFAULT 0,
        article_views INT UNSIGNED NOT NULL DEFAULT 0
    ) $opts");
    $pdo->exec("CREATE TABLE IF NOT EXISTS article_views_daily (
        article_id INT UNSIGNED NOT NULL,
        day DATE NOT NULL,
        views INT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (article_id, day),
        INDEX idx_day (day),
        CONSTRAINT fk_avd_art FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
    ) $opts");
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        article_id INT UNSIGNED NOT NULL,
        name VARCHAR(80) NOT NULL,
        body TEXT NOT NULL,
        status VARCHAR(16) NOT NULL DEFAULT 'approved',
        ip_hash CHAR(64) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL,
        INDEX idx_art (article_id, status, created_at),
        INDEX idx_status (status, created_at),
        CONSTRAINT fk_com_art FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
    ) $opts");
    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(128) NOT NULL UNIQUE,
        content MEDIUMTEXT NOT NULL,
        in_footer TINYINT(1) NOT NULL DEFAULT 1,
        position INT NOT NULL DEFAULT 0,
        updated_at DATETIME NOT NULL
    ) $opts");
    $pdo->exec("CREATE TABLE IF NOT EXISTS ads (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slot VARCHAR(32) NOT NULL,
        kind VARCHAR(16) NOT NULL DEFAULT 'image',
        image VARCHAR(255) NULL,
        url VARCHAR(500) NOT NULL DEFAULT '',
        code TEXT NULL,
        starts_at DATETIME NULL,
        ends_at DATETIME NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        views INT UNSIGNED NOT NULL DEFAULT 0,
        clicks INT UNSIGNED NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        INDEX idx_slot (slot, active)
    ) $opts");
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        name VARCHAR(64) NOT NULL PRIMARY KEY,
        `value` TEXT NOT NULL
    ) $opts");

    if ((int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() === 0) {
        $cats = [
            ['Улс төр', 'uls-tur'], ['Эдийн засаг', 'ediin-zasag'], ['Нийгэм', 'niigem'], ['Дэлхий', 'delkhii'],
            ['Спорт', 'sport'], ['Технологи', 'tekhnologi'], ['Урлаг, соёл', 'urlag-soyol'],
        ];
        $st = $pdo->prepare('INSERT INTO categories (name, slug, position) VALUES (?, ?, ?)');
        foreach ($cats as $i => $c) {
            $st->execute([$c[0], $c[1], $i]);
        }
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn() === 0) {
        $pages = [
            ['Бидний тухай', 'bidnii-tukhai', '<p>Манай сайт нь Монгол болон дэлхийн өнцөг булан бүрээс шуурхай, бодит мэдээллийг уншигчдадаа хүргэдэг мэдээллийн хэрэгсэл юм.</p><p>Энэ хуудсыг админ панелын <b>Хуудсууд</b> хэсгээс засна уу.</p>'],
            ['Сурталчилгаа байршуулах', 'surtalchilgaa', '<p>Манай сайтад сурталчилгаа байршуулах талаар доорх хаягаар холбогдоно уу.</p><p>Энэ хуудсыг админ панелын <b>Хуудсууд</b> хэсгээс засна уу.</p>'],
            ['Холбоо барих', 'kholboo-barikh', '<p>Мэдээ, санал хүсэлтээ бидэнд илгээнэ үү.</p><p>Энэ хуудсыг админ панелын <b>Хуудсууд</b> хэсгээс засна уу.</p>'],
            ['Нууцлалын бодлого', 'privacy', '<p>Бид уншигчдынхаа хувийн мэдээллийг хүндэтгэж, хадгална.</p><p>Энэ хуудсыг админ панелын <b>Хуудсууд</b> хэсгээс засна уу.</p>'],
        ];
        $st = $pdo->prepare('INSERT INTO pages (title, slug, content, in_footer, position, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($pages as $i => $pg) {
            $st->execute([$pg[0], $pg[1], $pg[2], $pg[1] === 'privacy' ? 0 : 1, $i, date('Y-m-d H:i:s')]);
        }
    }
    $pdo->prepare('INSERT INTO settings (name, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)')
        ->execute(['schema_version', (string) SCHEMA_VERSION]);
}

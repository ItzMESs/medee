<?php
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
}

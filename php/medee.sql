-- Nationalrbc.com мэдээллийн сайтын өгөгдлийн сан (MySQL 5.7 / 8.0)
-- phpMyAdmin → өгөгдлийн сангаа сонгох → Import → энэ файл → Go
-- Анхны админ: admin / admin123  (нэвтэрсний дараа "Нууц үг солих" хэсгээс заавал солино уу)
-- АНХААР: эдгээр хүснэгт өмнө нь байвал устгаад шинээр үүсгэнэ.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `ads`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `article_views_daily`;
DROP TABLE IF EXISTS `stats_daily`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `articles`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `name` varchar(128) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(16) NOT NULL DEFAULT 'editor',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `slug` varchar(128) NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `excerpt` text NOT NULL,
  `content` mediumtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `author_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_breaking` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  `fb_post_id` varchar(64) DEFAULT NULL,
  `fb_error` text DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pub` (`status`,`published_at`),
  KEY `idx_cat` (`category_id`),
  KEY `fk_art_user` (`author_id`),
  CONSTRAINT `fk_art_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_art_user` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned NOT NULL,
  `name` varchar(80) NOT NULL,
  `body` text NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'approved',
  `ip_hash` char(64) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_art` (`article_id`,`status`,`created_at`),
  KEY `idx_status` (`status`,`created_at`),
  CONSTRAINT `fk_com_art` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stats_daily` (
  `day` date NOT NULL,
  `page_views` int(10) unsigned NOT NULL DEFAULT 0,
  `article_views` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `article_views_daily` (
  `article_id` int(10) unsigned NOT NULL,
  `day` date NOT NULL,
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`article_id`,`day`),
  KEY `idx_day` (`day`),
  CONSTRAINT `fk_avd_art` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(128) NOT NULL,
  `content` mediumtext NOT NULL,
  `in_footer` tinyint(1) NOT NULL DEFAULT 1,
  `position` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slot` varchar(32) NOT NULL,
  `kind` varchar(16) NOT NULL DEFAULT 'image',
  `image` varchar(255) DEFAULT NULL,
  `url` varchar(500) NOT NULL DEFAULT '',
  `code` text DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `views` int(10) unsigned NOT NULL DEFAULT 0,
  `clicks` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_slot` (`slot`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `name` varchar(64) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Админ хэрэглэгч
INSERT INTO `users` (`id`, `username`, `name`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', 'Админ', '$2y$10$P8KYCCq4TfVw5fG18aZS8.upQ9wbzhWv6NP/OJ/a3gG286.kg/t.G', 'admin', '2026-10-20 22:24:23');

-- Ангиллууд
INSERT INTO `categories` (`id`, `name`, `slug`, `position`) VALUES
(1, 'Улс төр', 'uls-tur', 0),
(2, 'Эдийн засаг', 'ediin-zasag', 1),
(3, 'Нийгэм', 'niigem', 2),
(4, 'Дэлхий', 'delkhii', 3),
(5, 'Спорт', 'sport', 4),
(6, 'Технологи', 'tekhnologi', 5),
(7, 'Урлаг, соёл', 'urlag-soyol', 6);

-- Жишээ мэдээ (админ панелаас устгаж болно)
INSERT INTO `articles` (`id`, `title`, `slug`, `excerpt`, `content`, `image`, `category_id`, `author_id`, `status`, `is_featured`, `is_breaking`, `views`, `fb_post_id`, `fb_error`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'УИХ-ын намрын чуулган өнөөдөр эхэллээ', 'uikh-yn-namryn-chuulgan-unuudur-ekhellee', 'Улсын Их Хурлын ээлжит бус чуулганаар төсвийн тодотгол, хууль тогтоомжийн шинэчлэлийн асуудлыг хэлэлцэнэ.', '<p>Улсын Их Хурлын ээлжит бус чуулганаар төсвийн тодотгол, хууль тогтоомжийн шинэчлэлийн асуудлыг хэлэлцэнэ.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-1.jpg', 1, 1, 'published', 1, 1, 1589, NULL, NULL, '2026-10-20 22:24:23', '2026-10-20 22:24:23', '2026-10-20 22:24:23'),
(2, 'Төгрөгийн ханш сүүлийн долоо хоногт тогтвортой байлаа', 'tugrugiin-khansh-suuliin-doloo-khonogt-togtvortoi-bailaa', 'Монголбанкны мэдээллээр гадаад валютын албан ханш өмнөх долоо хоногтой харьцуулахад бага зэрэг чангарсан байна.', '<p>Монголбанкны мэдээллээр гадаад валютын албан ханш өмнөх долоо хоногтой харьцуулахад бага зэрэг чангарсан байна.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-2.jpg', 2, 1, 'published', 1, 0, 1980, NULL, NULL, '2026-10-20 19:54:23', '2026-10-20 19:54:23', '2026-10-20 19:54:23'),
(3, 'Нийслэлд өвлийн бэлтгэл ажил 90 хувьтай байна', 'niisleld-uvliin-beltgel-ajil-90-khuvtai-baina', 'Дулаан, цахилгааны шугам сүлжээний засвар үйлчилгээний ажлыг энэ сарын эцэс гэхэд бүрэн дуусгахаар төлөвлөжээ.', '<p>Дулаан, цахилгааны шугам сүлжээний засвар үйлчилгээний ажлыг энэ сарын эцэс гэхэд бүрэн дуусгахаар төлөвлөжээ.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-3.jpg', 3, 1, 'published', 1, 0, 2239, NULL, NULL, '2026-10-20 17:24:23', '2026-10-20 17:24:23', '2026-10-20 17:24:23'),
(4, 'Монголын бөхчүүд дэлхийн аваргаас гурван медаль хүртлээ', 'mongolyn-bukhchuud-delkhiin-avargaas-gurvan-medal-khurtlee', 'Чөлөөт бөхийн дэлхийн аварга шалгаруулах тэмцээнд манай тамирчид алт, мөнгө, хүрэл медаль хүртэв.', '<p>Чөлөөт бөхийн дэлхийн аварга шалгаруулах тэмцээнд манай тамирчид алт, мөнгө, хүрэл медаль хүртэв.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-4.jpg', 5, 1, 'published', 1, 1, 1525, NULL, NULL, '2026-10-20 14:54:23', '2026-10-20 14:54:23', '2026-10-20 14:54:23'),
(5, 'Улаанбаатарт 5G сүлжээний хамрах хүрээ өргөжлөө', 'ulaanbaatart-5g-suljeenii-khamrakh-khuree-urgujluu', 'Үүрэн холбооны операторууд нийслэлийн бүх дүүрэгт 5G сүлжээг нэвтрүүлэх ажлыг эрчимжүүлж байна.', '<p>Үүрэн холбооны операторууд нийслэлийн бүх дүүрэгт 5G сүлжээг нэвтрүүлэх ажлыг эрчимжүүлж байна.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-5.jpg', 6, 1, 'published', 1, 0, 78, NULL, NULL, '2026-10-20 12:24:23', '2026-10-20 12:24:23', '2026-10-20 12:24:23'),
(6, 'Дэлхийн цаг уурын бага хурал шинэ тохиролцоонд хүрлээ', 'delkhiin-tsag-uuryn-baga-khural-shine-tokhiroltsoond-khurlee', 'Оролцогч орнууд хүлэмжийн хийн ялгарлыг бууруулах шинэ зорилтыг баталсан байна.', '<p>Оролцогч орнууд хүлэмжийн хийн ялгарлыг бууруулах шинэ зорилтыг баталсан байна.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-6.jpg', 4, 1, 'published', 0, 0, 2507, NULL, NULL, '2026-10-20 09:54:23', '2026-10-20 09:54:23', '2026-10-20 09:54:23'),
(7, 'Улсын драмын театр шинэ улирлаа нээлээ', 'ulsyn-dramyn-teatr-shine-ulirlaa-neelee', 'Энэ улиралд сонгодог болон орчин үеийн нийт 12 жүжгийг үзэгчдэд хүргэнэ.', '<p>Энэ улиралд сонгодог болон орчин үеийн нийт 12 жүжгийг үзэгчдэд хүргэнэ.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-7.jpg', 7, 1, 'published', 0, 0, 1933, NULL, NULL, '2026-10-20 07:24:23', '2026-10-20 07:24:23', '2026-10-20 07:24:23'),
(8, 'Уул уурхайн экспорт өмнөх оноос 12 хувиар өслөө', 'uul-uurkhain-eksport-umnukh-onoos-12-khuviar-usluu', 'Нүүрс, зэсийн баяжмалын экспортын хэмжээ нэмэгдсэн нь гадаад худалдааны тэнцэлд эерэг нөлөө үзүүлжээ.', '<p>Нүүрс, зэсийн баяжмалын экспортын хэмжээ нэмэгдсэн нь гадаад худалдааны тэнцэлд эерэг нөлөө үзүүлжээ.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-8.jpg', 2, 1, 'published', 0, 0, 604, NULL, NULL, '2026-10-20 04:54:23', '2026-10-20 04:54:23', '2026-10-20 04:54:23'),
(9, 'Сургуулиудад хоёр ээлжийн хичээллэлтийг багасгана', 'surguuliudad-khoyor-eeljiin-khicheelleltiig-bagasgana', 'Боловсролын яамнаас шинэ сургуулийн барилгуудыг ашиглалтад оруулснаар ачааллыг бууруулна гэж мэдээллээ.', '<p>Боловсролын яамнаас шинэ сургуулийн барилгуудыг ашиглалтад оруулснаар ачааллыг бууруулна гэж мэдээллээ.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-9.jpg', 3, 1, 'published', 0, 0, 526, NULL, NULL, '2026-10-20 02:24:23', '2026-10-20 02:24:23', '2026-10-20 02:24:23'),
(10, 'Сагсан бөмбөгийн дээд лигийн шинэ улирал эхэллээ', 'sagsan-bumbugiin-deed-ligiin-shine-uliral-ekhellee', 'Энэ улиралд нийт 10 баг аваргын төлөө өрсөлдөнө.', '<p>Энэ улиралд нийт 10 баг аваргын төлөө өрсөлдөнө.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-10.jpg', 5, 1, 'published', 0, 0, 1754, NULL, NULL, '2026-10-19 23:54:23', '2026-10-19 23:54:23', '2026-10-19 23:54:23'),
(11, 'Монгол стартапууд олон улсын хөрөнгө оруулалт татлаа', 'mongol-startapuud-olon-ulsyn-khurungu-oruulalt-tatlaa', 'Санхүүгийн технологийн чиглэлийн гурван компани нийт 5 сая ам.долларын хөрөнгө оруулалт авчээ.', '<p>Санхүүгийн технологийн чиглэлийн гурван компани нийт 5 сая ам.долларын хөрөнгө оруулалт авчээ.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-11.jpg', 6, 1, 'published', 0, 0, 2277, NULL, NULL, '2026-10-19 21:24:23', '2026-10-19 21:24:23', '2026-10-19 21:24:23'),
(12, 'Азийн орнуудын эдийн засгийн өсөлт хүлээлтээс давлаа', 'aziin-ornuudyn-ediin-zasgiin-usult-khuleeltees-davlaa', 'Азийн хөгжлийн банкны тайланд бүс нутгийн эдийн засаг 4.8 хувиар өснө гэж таамаглажээ.', '<p>Азийн хөгжлийн банкны тайланд бүс нутгийн эдийн засаг 4.8 хувиар өснө гэж таамаглажээ.</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p><h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p><blockquote>\"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна.\"</blockquote>', '/uploads/demo/demo-12.jpg', 4, 1, 'published', 0, 0, 2541, NULL, NULL, '2026-10-19 18:54:23', '2026-10-19 18:54:23', '2026-10-19 18:54:23');

-- Хуудсууд (Бидний тухай ...)
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `in_footer`, `position`, `updated_at`) VALUES
(1, 'Бидний тухай', 'bidnii-tukhai', '<p>Манай сайт нь Монгол болон дэлхийн өнцөг булан бүрээс шуурхай, бодит мэдээллийг уншигчдадаа хүргэдэг мэдээллийн хэрэгсэл юм.</p><p>Энэ хуудсыг админ панелын <b>Хуудсууд</b> хэсгээс засна уу.</p>', 1, 0, '2026-10-20 22:24:23'),
(2, 'Сурталчилгаа байршуулах', 'surtalchilgaa', '<p>Манай сайтад сурталчилгаа байршуулах талаар доорх хаягаар холбогдоно уу.</p><p>Энэ хуудсыг админ панелын <b>Хуудсууд</b> хэсгээс засна уу.</p>', 1, 1, '2026-10-20 22:24:23'),
(3, 'Холбоо барих', 'kholboo-barikh', '<p>Мэдээ, санал хүсэлтээ бидэнд илгээнэ үү.</p><p>Энэ хуудсыг админ панелын <b>Хуудсууд</b> хэсгээс засна уу.</p>', 1, 2, '2026-10-20 22:24:23'),
(4, 'Нууцлалын бодлого', 'privacy', '<p>Бид уншигчдынхаа хувийн мэдээллийг хүндэтгэж, хадгална.</p><p>Энэ хуудсыг админ панелын <b>Хуудсууд</b> хэсгээс засна уу.</p>', 0, 3, '2026-10-20 22:24:23');

-- Тохиргоо
INSERT INTO `settings` (`name`, `value`) VALUES
('schema_version', 5),
('site_name', 'Nationalrbc.com');

SET FOREIGN_KEY_CHECKS = 1;

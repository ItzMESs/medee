<?php
$site = require __DIR__ . '/../config.php';
$page = $page ?? 'home';
$title = $title ?? '';
function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
$menu = [
    'home'       => ['НҮҮР', 'index.php'],
    'about'      => ['БИДНИЙ ТУХАЙ', 'about.php'],
    'operations' => ['ҮЙЛ АЖИЛЛАГАА', 'operations.php'],
    'news'       => ['МЭДЭЭ, МЭДЭЭЛЭЛ', 'news.php', [
        ['Мэдээ', 'news.php'],
        ['Тайлан', 'news.php#tailan'],
        ['Худалдан авалт', 'news.php#hudaldan-avalt'],
    ]],
    'careers'    => ['ХҮНИЙ НӨӨЦ', 'careers.php'],
];
?><!doctype html>
<html lang="mn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ? $title . ' | ' . $site['name'] : $site['name']) ?></title>
<meta name="description" content="<?= e($site['name']) ?> — уул уурхай, зам барилга, зам арчлалт">
<link rel="icon" href="<?= e($site['logo']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-<?= e($page) ?>">
<header class="site-header<?= $page === 'home' ? '' : ' solid' ?>" id="header">
  <div class="container header-inner">
    <a class="brand" href="index.php">
      <img src="<?= e($site['logo']) ?>" alt="" width="56" height="64">
      <span class="brand-text">
        <span class="brand-title"><?= e($site['logo_title']) ?></span>
        <span class="brand-sub"><?= e($site['logo_sub']) ?></span>
      </span>
    </a>
    <button class="nav-toggle" id="navToggle" aria-label="Цэс" aria-expanded="false"><span></span><span></span><span></span></button>
    <nav class="main-nav" id="mainNav">
      <ul>
        <?php foreach ($menu as $key => $item): ?>
        <li class="<?= $key === $page ? 'active' : '' ?><?= isset($item[2]) ? ' has-sub' : '' ?>">
          <a href="<?= e($item[1]) ?>"><?= e($item[0]) ?><?php if (isset($item[2])): ?><svg class="chev" viewBox="0 0 10 6" width="10" height="6"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.6" fill="none"/></svg><?php endif; ?></a>
          <?php if (isset($item[2])): ?>
          <ul class="sub">
            <?php foreach ($item[2] as $sub): ?><li><a href="<?= e($sub[1]) ?>"><?= e($sub[0]) ?></a></li><?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <li class="lang"><a href="<?= e($site['english_url']) ?>" title="English"><svg viewBox="0 0 60 30" width="24" height="12"><clipPath id="t"><path d="M30 15h30v15zv15H0zH0V0zV0h30z"/></clipPath><path d="M0 0v30h60V0z" fill="#012169"/><path d="M0 0l60 30m0-30L0 30" stroke="#fff" stroke-width="6"/><path d="M0 0l60 30m0-30L0 30" clip-path="url(#t)" stroke="#C8102E" stroke-width="4"/><path d="M30 0v30M0 15h60" stroke="#fff" stroke-width="10"/><path d="M30 0v30M0 15h60" stroke="#C8102E" stroke-width="6"/></svg></a></li>
      </ul>
    </nav>
  </div>
</header>

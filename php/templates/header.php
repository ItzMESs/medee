<?php
$pageTitle = !empty($meta['title']) ? $meta['title'] . ' | ' . $settings['site_name'] : $settings['site_name'] . ' — ' . $settings['site_tagline'];
$desc = !empty($meta['description']) ? $meta['description'] : $settings['site_tagline'];
?><!doctype html>
<html lang="mn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($desc) ?>">
  <meta property="og:site_name" content="<?= e($settings['site_name']) ?>">
  <meta property="og:title" content="<?= e(!empty($meta['title']) ? $meta['title'] : $settings['site_name']) ?>">
  <meta property="og:description" content="<?= e($desc) ?>">
  <meta property="og:type" content="<?= e(!empty($meta['type']) ? $meta['type'] : 'website') ?>">
  <meta property="og:locale" content="mn_MN">
  <?php if (!empty($meta['url'])): ?><meta property="og:url" content="<?= e($meta['url']) ?>"><link rel="canonical" href="<?= e($meta['url']) ?>"><?php endif; ?>
  <?php if (!empty($meta['image'])): ?><meta property="og:image" content="<?= e($meta['image']) ?>"><meta name="twitter:card" content="summary_large_image"><?php endif; ?>
  <?php if (!empty($meta['published'])): ?><meta property="article:published_time" content="<?= e($meta['published']) ?>"><?php endif; ?>
  <?php if ($settings['fb_app_id']): ?><meta property="fb:app_id" content="<?= e($settings['fb_app_id']) ?>"><?php endif; ?>
  <link rel="alternate" type="application/rss+xml" title="<?= e($settings['site_name']) ?>" href="<?= url('/rss.xml') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Roboto+Slab:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<header class="site-header">
  <div class="topbar">
    <div class="container topbar-inner">
      <span><?= e(mongolian_date()) ?></span>
      <div class="topbar-links">
        <?php if ($settings['fb_page_url']): ?><a href="<?= e($settings['fb_page_url']) ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
        <a href="<?= url('/rss.xml') ?>">RSS</a>
      </div>
    </div>
  </div>
  <div class="container brand-row">
    <a href="<?= url('/') ?>" class="logo"><?= e($settings['site_name']) ?></a>
    <form action="<?= url('/search') ?>" method="get" class="search">
      <input type="search" name="q" placeholder="Мэдээ хайх..." value="<?= e($query) ?>" aria-label="Хайх">
      <button type="submit">Хайх</button>
    </form>
  </div>
  <nav class="main-nav">
    <div class="container nav-inner">
      <button class="nav-toggle" aria-label="Цэс" onclick="document.body.classList.toggle('nav-open')">☰</button>
      <ul>
        <li><a href="<?= url('/') ?>" class="<?= $currentPath === '/' ? 'active' : '' ?>">Нүүр</a></li>
        <li><a href="<?= url('/latest') ?>" class="<?= $currentPath === '/latest' ? 'active' : '' ?>">Шинэ</a></li>
        <?php foreach ($categories as $c): ?>
          <li><a href="<?= url('/category/' . $c['slug']) ?>" class="<?= $currentPath === '/category/' . $c['slug'] ? 'active' : '' ?>"><?= e($c['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>
  <?php if ($breaking): ?>
  <div class="breaking">
    <div class="container breaking-inner">
      <span class="breaking-label">ШУУРХАЙ</span>
      <div class="ticker"><div class="ticker-track">
        <?php foreach (array_merge($breaking, $breaking) as $a): ?><a href="<?= article_url($a) ?>"><?= e($a['title']) ?></a><?php endforeach; ?>
      </div></div>
    </div>
  </div>
  <?php endif; ?>
</header>
<main class="container">

<?php
$navLink = function ($href, $icon, $label, $active, $extra = '') {
    return '<a href="' . url($href) . '" class="' . ($active ? 'active' : '') . '"' . $extra . '>' . icon($icon, 19) . '<span>' . $label . '</span></a>';
};
$logoUrl = site_logo_url();
$initial = mb_strtoupper(mb_substr($user['name'] !== '' ? $user['name'] : $user['username'], 0, 1));
?><!doctype html>
<html lang="mn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <meta name="theme-color" content="#0f172a">
  <title>Админ — <?= e($settings['site_name']) ?></title>
  <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
  <style>:root { --brand: <?= brand_color() ?>; --brand-soft: <?= shade_color(brand_color(), 0.9) ?>; }</style>
  <?php if (!empty($editor)): ?><link rel="stylesheet" href="<?= asset('vendor/quill/quill.snow.css') ?>"><?php endif; ?>
</head>
<body>
<header class="admin-topbar">
  <button type="button" class="topbar-btn" data-nav-open aria-label="Цэс нээх" aria-controls="admin-nav" aria-expanded="false"><?= icon('menu', 24) ?></button>
  <a href="<?= url('/admin') ?>" class="topbar-logo"><?php if ($logoUrl): ?><img src="<?= e($logoUrl) ?>" alt="<?= e($settings['site_name']) ?>"><?php else: ?><?= e($settings['site_name']) ?><?php endif; ?></a>
  <a href="<?= url('/admin/articles/new') ?>" class="topbar-btn topbar-add" aria-label="Мэдээ нэмэх"><?= icon('plus', 22) ?></a>
</header>
<div class="nav-overlay" data-nav-close></div>
<div class="admin">
  <aside class="admin-nav" id="admin-nav">
    <div class="nav-head">
      <a href="<?= url('/admin') ?>" class="admin-logo">
        <?php if ($logoUrl): ?><img src="<?= e($logoUrl) ?>" alt="<?= e($settings['site_name']) ?>"><?php else: ?><?= e($settings['site_name']) ?><?php endif; ?>
        <small>Админ панел</small>
      </a>
      <button type="button" class="nav-close" data-nav-close aria-label="Цэс хаах"><?= icon('close', 22) ?></button>
    </div>
    <a href="<?= url('/admin/articles/new') ?>" class="nav-cta<?= $path === '/admin/articles/new' ? ' active' : '' ?>"><?= icon('plus', 18) ?> Мэдээ нэмэх</a>
    <nav>
      <div class="nav-group">Агуулга</div>
      <?= $navLink('/admin', 'grid', 'Хянах самбар', $path === '/admin') ?>
      <?= $navLink('/admin/articles', 'file', 'Бүх мэдээ', $path === '/admin/articles' || (strpos($path, '/admin/articles/') === 0 && $path !== '/admin/articles/new')) ?>
      <?= $navLink('/admin/categories', 'folder', 'Ангилал', $path === '/admin/categories') ?>
      <?= $navLink('/admin/tags', 'tag', 'Шошго', $path === '/admin/tags') ?>
      <?= $navLink('/admin/pages', 'pages', 'Хуудсууд', strpos($path, '/admin/pages') === 0) ?>
      <?= $navLink('/admin/comments', 'comment', 'Сэтгэгдэл' . ($pendingComments ? ' <em class="nav-badge">' . (int) $pendingComments . '</em>' : ''), $path === '/admin/comments') ?>
      <?php if ($user['role'] === 'admin'): ?>
        <div class="nav-group">Орлого</div>
        <?= $navLink('/admin/ads', 'megaphone', 'Сурталчилгаа', strpos($path, '/admin/ads') === 0) ?>
        <div class="nav-group">Систем</div>
        <?= $navLink('/admin/users', 'users', 'Хэрэглэгчид', $path === '/admin/users') ?>
        <?= $navLink('/admin/settings', 'settings', 'Тохиргоо', $path === '/admin/settings') ?>
      <?php endif; ?>
      <?= $navLink('/admin/profile', 'key', 'Нууц үг солих', $path === '/admin/profile') ?>
      <?= $navLink('/', 'external', 'Сайт үзэх', false, ' target="_blank"') ?>
    </nav>
    <form method="post" action="<?= url('/admin/logout') ?>" class="nav-user">
      <?= csrf_field() ?>
      <span class="nav-avatar"><?= e($initial) ?></span>
      <span class="nav-user-name"><b><?= e($user['name']) ?></b><small><?= $user['role'] === 'admin' ? 'Админ' : 'Редактор' ?></small></span>
      <button type="submit" class="nav-logout" title="Гарах" aria-label="Гарах"><?= icon('logout', 19) ?></button>
    </form>
  </aside>
  <main class="admin-main">
    <?php if (is_admin() && site_url_mismatch()): ?>
      <form method="post" action="<?= url('/admin/site-url') ?>" class="flash flash-error site-url-warn"><?= csrf_field() ?>
        Тохиргоонд хадгалсан сайтын хаяг <b><?= e(setting('site_url')) ?></b> нь одоогийн хаяг <b><?= e(detect_site_url()) ?></b>-аас өөр байна. Ийм үед Facebook, Messenger дээр мэдээний зураг, гарчиг гарахгүй.
        <button class="btn btn-sm btn-primary">Засах</button>
      </form>
    <?php endif; ?>
    <?php if ($flash): ?><div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['text']) ?></div><?php endif; ?>

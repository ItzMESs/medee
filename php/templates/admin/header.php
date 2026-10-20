<?php
$navLink = function ($href, $label, $active, $extra = '') use ($path) {
    return '<a href="' . url($href) . '" class="' . ($active ? 'active' : '') . '"' . $extra . '>' . $label . '</a>';
};
?><!doctype html>
<html lang="mn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title>Админ — <?= e($settings['site_name']) ?></title>
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
  <?php if (!empty($editor)): ?><link rel="stylesheet" href="<?= asset('vendor/quill/quill.snow.css') ?>"><?php endif; ?>
</head>
<body>
<div class="admin">
  <aside class="admin-nav">
    <a href="<?= url('/admin') ?>" class="admin-logo"><?= e($settings['site_name']) ?><small>Админ</small></a>
    <nav>
      <?= $navLink('/admin', 'Хянах самбар', $path === '/admin') ?>
      <?= $navLink('/admin/articles/new', '+ Мэдээ нэмэх', $path === '/admin/articles/new') ?>
      <?= $navLink('/admin/articles', 'Бүх мэдээ', $path === '/admin/articles') ?>
      <?= $navLink('/admin/categories', 'Ангилал', $path === '/admin/categories') ?>
      <?php if ($user['role'] === 'admin'): ?>
        <?= $navLink('/admin/users', 'Хэрэглэгчид', $path === '/admin/users') ?>
        <?= $navLink('/admin/settings', 'Тохиргоо / Facebook', $path === '/admin/settings') ?>
      <?php endif; ?>
      <?= $navLink('/admin/profile', 'Нууц үг солих', $path === '/admin/profile') ?>
      <?= $navLink('/', 'Сайт үзэх ↗', false, ' target="_blank"') ?>
    </nav>
    <form method="post" action="<?= url('/admin/logout') ?>" class="logout">
      <?= csrf_field() ?>
      <span><?= e($user['name']) ?> (<?= $user['role'] === 'admin' ? 'Админ' : 'Редактор' ?>)</span>
      <button type="submit">Гарах</button>
    </form>
  </aside>
  <main class="admin-main">
    <?php if ($flash): ?><div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['text']) ?></div><?php endif; ?>

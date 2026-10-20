<?php
$pageTitle = !empty($meta['title']) ? $meta['title'] . ' | ' . $settings['site_name'] : $settings['site_name'] . ' — ' . $settings['site_tagline'];
$desc = !empty($meta['description']) ? $meta['description'] : $settings['site_tagline'];
$brand = brand_color();
$logoUrl = site_logo_url();
if (empty($meta['image'])) {
    $meta['image'] = default_og_image();
}
$weather = ub_weather();
$rates = currency_rates();
$socials = array_filter([
    'facebook' => safe_url($settings['fb_page_url']),
    'youtube' => safe_url($settings['social_youtube']),
    'x' => safe_url($settings['social_x']),
    'instagram' => safe_url($settings['social_instagram']),
]);
?><!doctype html>
<html lang="mn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($desc) ?>">
  <meta name="theme-color" content="<?= e($brand) ?>">
  <meta property="og:site_name" content="<?= e($settings['site_name']) ?>">
  <meta property="og:title" content="<?= e(!empty($meta['title']) ? $meta['title'] : $settings['site_name']) ?>">
  <meta property="og:description" content="<?= e($desc) ?>">
  <meta property="og:type" content="<?= e(!empty($meta['type']) ? $meta['type'] : 'website') ?>">
  <meta property="og:locale" content="mn_MN">
  <?php if (!empty($meta['url'])): ?><meta property="og:url" content="<?= e($meta['url']) ?>"><link rel="canonical" href="<?= e($meta['url']) ?>"><?php endif; ?>
  <?php if (!empty($meta['image'])): ?><meta property="og:image" content="<?= e($meta['image']) ?>"><meta name="twitter:card" content="summary_large_image"><?php endif; ?>
  <?php if (!empty($meta['published'])): ?><meta property="article:published_time" content="<?= e($meta['published']) ?>"><?php endif; ?>
  <?php if ($settings['fb_app_id']): ?><meta property="fb:app_id" content="<?= e($settings['fb_app_id']) ?>"><?php endif; ?>
  <?php if ($settings['google_verification'] !== ''): ?><meta name="google-site-verification" content="<?= e($settings['google_verification']) ?>"><?php endif; ?>
  <?php if (!empty($meta['jsonld'])): ?><script type="application/ld+json"><?= json_encode($meta['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script><?php endif; ?>
  <?php if (preg_match('/^G-[A-Z0-9]{4,20}$/', $settings['ga_id'])): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($settings['ga_id']) ?>"></script>
  <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '<?= e($settings['ga_id']) ?>');</script>
  <?php endif; ?>
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="12" fill="' . $brand . '"/><text x="32" y="46" font-family="Georgia,serif" font-size="40" font-weight="700" fill="#fff" text-anchor="middle">' . htmlspecialchars(mb_strtoupper(mb_substr($settings['site_name'], 0, 1)), ENT_XML1) . '</text></svg>') ?>">
  <link rel="alternate" type="application/rss+xml" title="<?= e($settings['site_name']) ?>" href="<?= url('/rss.xml') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Serif:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <style>:root { --brand: <?= $brand ?>; --brand-dark: <?= shade_color($brand, -0.18) ?>; --brand-soft: <?= shade_color($brand, 0.92) ?>; }</style>
</head>
<body class="<?= !empty($isArticle) ? 'is-article' : '' ?><?= ornaments_enabled() ? ' has-ornaments' : '' ?>">
<header class="site-header">
  <div class="header-bar">
    <div class="container header-inner">
      <div class="header-left">
        <button type="button" class="icon-btn" data-drawer-open aria-label="Цэс нээх"><?= icon('menu', 30) ?></button>
        <div class="header-info">
          <div class="info-item"><?= icon('calendar', 26) ?><div><small>Өнөөдөр</small><b><?= date('Y.m.d') ?></b></div></div>
          <?php if ($weather): list(, $wText) = weather_label($weather['code'], $weather['is_day']); ?>
            <div class="info-item" title="Улаанбаатар: <?= e($wText) ?>"><?= icon(weather_icon_name($weather['code']), 26) ?><div><small>Улаанбаатар</small><b><?= $weather['temp'] > 0 ? '+' : '' ?><?= $weather['temp'] ?>°C <?= e(weather_short($weather['code'])) ?></b></div></div>
          <?php endif; ?>
          <?php if ($rates): $first = key($rates); ?>
            <div class="info-item" title="<?php foreach ($rates as $c => $v) { echo e($c . ': ' . format_rate($v) . '₮  '); } ?>"><?= icon('coins', 26) ?><div><small>Валютын ханш</small><b><?= e($first) ?>: <?= format_rate($rates[$first]) ?>₮</b></div></div>
          <?php endif; ?>
        </div>
      </div>
      <a href="<?= url('/') ?>" class="logo" aria-label="<?= e($settings['site_name']) ?>">
        <?php if ($logoUrl): ?><img src="<?= e($logoUrl) ?>" alt="<?= e($settings['site_name']) ?>"><?php else: ?><?= e($settings['site_name']) ?><?php endif; ?>
      </a>
      <div class="header-right">
        <form action="<?= url('/search') ?>" method="get" class="header-search" role="search">
          <input type="search" name="q" placeholder="Мэдээ хайх..." value="<?= e($query) ?>" aria-label="Мэдээ хайх">
          <button type="submit" aria-label="Хайх"><?= icon('search', 24) ?></button>
        </form>
        <button type="button" class="icon-btn search-toggle" aria-label="Хайх" onclick="document.body.classList.toggle('search-open'); var i = document.querySelector('.header-search input'); if (document.body.classList.contains('search-open')) i.focus();"><?= icon('search', 26) ?></button>
      </div>
    </div>
  </div>
  <nav class="cat-bar" aria-label="Ангилал">
    <div class="container cat-bar-inner">
      <a href="<?= url('/') ?>" class="<?= $currentPath === '/' ? 'active' : '' ?>">Нүүр</a>
      <a href="<?= url('/latest') ?>" class="<?= $currentPath === '/latest' ? 'active' : '' ?>">Шинэ</a>
      <?php foreach ($categories as $c): ?>
        <a href="<?= url('/category/' . $c['slug']) ?>" class="<?= $currentPath === '/category/' . $c['slug'] ? 'active' : '' ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </nav>
  <?php if (!empty($isArticle)): ?><div class="read-progress" aria-hidden="true"><span></span></div><?php endif; ?>
  <?php if ($breaking): ?>
  <div class="breaking">
    <div class="container breaking-inner">
      <span class="breaking-label"><i class="pulse"></i>Шуурхай</span>
      <div class="ticker"><div class="ticker-track">
        <?php foreach (array_merge($breaking, $breaking) as $a): ?><a href="<?= article_url($a) ?>"><?= e($a['title']) ?></a><?php endforeach; ?>
      </div></div>
    </div>
  </div>
  <?php endif; ?>
</header>

<div class="drawer-overlay" data-drawer-close></div>
<nav class="drawer" id="drawer" aria-label="Үндсэн цэс" aria-hidden="true">
  <div class="drawer-head">
    <a href="<?= url('/') ?>" class="logo logo-sm"><?php if ($logoUrl): ?><img src="<?= e($logoUrl) ?>" alt="<?= e($settings['site_name']) ?>"><?php else: ?><?= e($settings['site_name']) ?><?php endif; ?></a>
    <button type="button" class="icon-btn" data-drawer-close aria-label="Цэс хаах"><?= icon('close', 28) ?></button>
  </div>
  <ul class="drawer-nav">
    <li><a href="<?= url('/') ?>" class="<?= $currentPath === '/' ? 'active' : '' ?>">Нүүр</a></li>
    <li><a href="<?= url('/latest') ?>" class="<?= $currentPath === '/latest' ? 'active' : '' ?>">Шинэ мэдээ</a></li>
    <?php foreach ($categories as $c): ?>
      <li><a href="<?= url('/category/' . $c['slug']) ?>" class="<?= $currentPath === '/category/' . $c['slug'] ? 'active' : '' ?>"><?= e($c['name']) ?></a></li>
    <?php endforeach; ?>
  </ul>
  <?php if ($rates): ?>
    <div class="drawer-rates"><?php foreach ($rates as $c => $v): ?><span><?= e($c) ?> <b><?= format_rate($v) ?>₮</b></span><?php endforeach; ?></div>
  <?php endif; ?>
  <ul class="drawer-pages">
    <?php foreach (footer_pages() as $pg): if ($pg['in_footer']): ?><li><a href="<?= url('/p/' . $pg['slug']) ?>"><?= e($pg['title']) ?></a></li><?php endif; endforeach; ?>
  </ul>
  <?php if ($socials): ?><div class="socials"><?php foreach ($socials as $net => $link): ?><a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e($net) ?>"><?= icon($net, 20) ?></a><?php endforeach; ?></div><?php endif; ?>
</nav>
<main class="site-main">
<?php $topAd = ad_slot('top'); if ($topAd !== ''): ?><div class="container ad-wrap-top"><?= $topAd ?></div><?php endif; ?>

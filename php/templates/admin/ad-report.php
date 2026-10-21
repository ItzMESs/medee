<?php
$totalViews = array_sum(array_column($daily, 'views'));
$totalClicks = array_sum(array_column($daily, 'clicks'));
$maxViews = max(1, max(array_column($daily, 'views')));
$dayCount = count($daily);
?><!doctype html>
<html lang="mn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Сурталчилгааны тайлан — <?= e($ad['title']) ?></title>
  <style>
    body { margin: 0; background: #eef0f4; font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif; color: #14171f; }
    .sheet { max-width: 820px; margin: 24px auto; background: #fff; padding: 44px 48px; box-shadow: 0 4px 24px rgba(0, 0, 0, .08); }
    .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 3px solid #0a5cff; padding-bottom: 18px; }
    .top img { height: 42px; }
    .top .site { font: 700 22px Georgia, serif; }
    h1 { font-size: 24px; margin: 26px 0 4px; }
    .sub { color: #7b808c; margin: 0 0 24px; font-size: 14px; }
    .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 26px; }
    .kpi { background: #f4f7ff; border-radius: 10px; padding: 14px 16px; }
    .kpi b { display: block; font-size: 24px; color: #0a5cff; }
    .kpi span { font-size: 12.5px; color: #4b5263; }
    .chart { display: flex; align-items: flex-end; gap: 2px; height: 140px; padding: 8px; border: 1px solid #e8e9ee; border-radius: 8px; margin-bottom: 8px; }
    .chart i { flex: 1; background: #0a5cff; border-radius: 2px 2px 0 0; min-height: 2px; }
    .axis { display: flex; justify-content: space-between; font-size: 11px; color: #7b808c; margin-bottom: 26px; }
    table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    th, td { padding: 7px 10px; border-bottom: 1px solid #e8e9ee; text-align: right; }
    th:first-child, td:first-child { text-align: left; }
    th { background: #f6f7f9; font-size: 12px; text-transform: uppercase; color: #4b5263; }
    tfoot td { font-weight: 700; border-top: 2px solid #14171f; }
    .banner { margin: 0 0 26px; } .banner img { max-width: 100%; max-height: 160px; border: 1px solid #e8e9ee; }
    .foot { margin-top: 30px; font-size: 12px; color: #7b808c; display: flex; justify-content: space-between; }
    .tools { max-width: 820px; margin: 16px auto 0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; font-size: 14px; }
    .tools input { padding: 6px 8px; border: 1px solid #ccd; border-radius: 6px; }
    .tools button, .tools a { padding: 8px 14px; border-radius: 6px; border: 0; background: #0a5cff; color: #fff; cursor: pointer; text-decoration: none; font-size: 14px; }
    .tools a { background: #fff; color: #14171f; border: 1px solid #ccd; }
    @media (max-width: 640px) { .sheet { padding: 24px 18px; margin: 0; } .kpis { grid-template-columns: 1fr 1fr; } }
    @media print { body { background: #fff; } .tools { display: none; } .sheet { box-shadow: none; margin: 0; max-width: none; padding: 0; } }
  </style>
</head>
<body>
<form class="tools" method="get">
  <a href="<?= url('/admin/ads/' . $ad['id']) ?>">← Буцах</a>
  <label>Эхлэх <input type="date" name="from" value="<?= e($from) ?>"></label>
  <label>Дуусах <input type="date" name="to" value="<?= e($to) ?>"></label>
  <button type="submit">Шинэчлэх</button>
  <button type="button" onclick="window.print()">🖨 Хэвлэх / PDF</button>
</form>
<div class="sheet">
  <div class="top">
    <?php $logo = site_logo_url(); $logo = $logo ? str_replace('-white.png', '.png', $logo) : ''; ?>
    <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e($settings['site_name']) ?>"><?php else: ?><span class="site"><?= e($settings['site_name']) ?></span><?php endif; ?>
    <div style="text-align:right;font-size:13px;color:#4b5263"><?= e(rtrim($settings['site_url'], '/')) ?><br>Тайлан гаргасан: <?= date('Y.m.d') ?></div>
  </div>
  <h1>Сурталчилгааны тайлан</h1>
  <p class="sub">
    <?php if ($ad['client'] !== ''): ?><b><?= e($ad['client']) ?></b> · <?php endif; ?><?= e($ad['title']) ?><br>
    Байрлал: <?= e(isset($slots[$ad['slot']]) ? $slots[$ad['slot']][0] : $ad['slot']) ?> · Хугацаа: <?= e(date('Y.m.d', strtotime($from))) ?> — <?= e(date('Y.m.d', strtotime($to))) ?> (<?= $dayCount ?> хоног)
  </p>
  <?php if ($ad['kind'] === 'image' && $ad['image']): ?><div class="banner"><img src="<?= e(media($ad['image'])) ?>" alt=""></div><?php endif; ?>
  <div class="kpis">
    <div class="kpi"><b><?= number_format($totalViews) ?></b><span>Нийт харагдсан</span></div>
    <div class="kpi"><b><?= number_format($totalClicks) ?></b><span>Нийт дарсан</span></div>
    <div class="kpi"><b><?= $totalViews ? number_format($totalClicks / $totalViews * 100, 2) . '%' : '—' ?></b><span>Дарсан хувь (CTR)</span></div>
    <div class="kpi"><b><?= number_format($dayCount ? $totalViews / $dayCount : 0) ?></b><span>Өдрийн дундаж үзэлт</span></div>
  </div>
  <div class="chart"><?php foreach ($daily as $d): ?><i style="height:<?= max(1, round($d['views'] / $maxViews * 100)) ?>%" title="<?= e($d['day']) ?>: <?= (int) $d['views'] ?>"></i><?php endforeach; ?></div>
  <div class="axis"><span><?= e(date('m.d', strtotime($from))) ?></span><span>Өдөр бүрийн харагдсан тоо</span><span><?= e(date('m.d', strtotime($to))) ?></span></div>
  <table>
    <thead><tr><th>Огноо</th><th>Харагдсан</th><th>Дарсан</th><th>CTR</th></tr></thead>
    <tbody>
      <?php foreach (array_reverse($daily) as $d): ?>
        <tr><td><?= e(date('Y.m.d', strtotime($d['day']))) ?></td><td><?= number_format($d['views']) ?></td><td><?= number_format($d['clicks']) ?></td><td><?= $d['views'] ? number_format($d['clicks'] / $d['views'] * 100, 2) . '%' : '—' ?></td></tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td>Нийт</td><td><?= number_format($totalViews) ?></td><td><?= number_format($totalClicks) ?></td><td><?= $totalViews ? number_format($totalClicks / $totalViews * 100, 2) . '%' : '—' ?></td></tr></tfoot>
  </table>
  <div class="foot"><span>Харагдсан тоонд хайлтын систем, ботыг оруулаагүй.</span><span><?= e($settings['site_name']) ?></span></div>
</div>
</body>
</html>

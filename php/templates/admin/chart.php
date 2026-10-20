<?php
// Bar chart of daily page views; $daily = [['day' => 'Y-m-d', 'page_views' => n, 'article_views' => n], ...]
$w = 900; $h = 260; $padL = 44; $padB = 28; $padT = 12;
$max = max(1, max(array_column($daily, 'page_views')));
$step = pow(10, max(0, (int) floor(log10($max))));
$niceMax = (int) (ceil($max / $step) * $step);
if ($niceMax / $step <= 2) { $step = max(1, $step / 2); }
$n = count($daily);
$slot = ($w - $padL) / $n;
$barW = max(2, $slot * 0.7);
$y = function ($v) use ($h, $padB, $padT, $niceMax) { return $h - $padB - ($v / $niceMax) * ($h - $padB - $padT); };
?>
<svg viewBox="0 0 <?= $w ?> <?= $h ?>" class="chart" role="img" aria-label="Өдөр бүрийн үзэлт">
  <?php for ($v = 0; $v <= $niceMax; $v += $step): $yy = $y($v); ?>
    <line x1="<?= $padL ?>" x2="<?= $w ?>" y1="<?= $yy ?>" y2="<?= $yy ?>" class="grid"/>
    <text x="<?= $padL - 6 ?>" y="<?= $yy + 4 ?>" text-anchor="end" class="axis"><?= $v ?></text>
  <?php endfor; ?>
  <?php foreach ($daily as $i => $d):
      $x = $padL + $i * $slot + ($slot - $barW) / 2;
      $barTop = $y($d['page_views']);
      $topA = $y($d['article_views']);
      $label = date('m.d', strtotime($d['day'])); ?>
    <g class="bar">
      <title><?= $label ?>: <?= $d['page_views'] ?> үзэлт (мэдээ: <?= $d['article_views'] ?>)</title>
      <rect x="<?= $x ?>" y="<?= $barTop ?>" width="<?= $barW ?>" height="<?= max(0, $h - $padB - $barTop) ?>" class="pv"/>
      <rect x="<?= $x ?>" y="<?= $topA ?>" width="<?= $barW ?>" height="<?= max(0, $h - $padB - $topA) ?>" class="av"/>
      <?php if ($n <= 7 || $i % 5 === ($n - 1) % 5): ?>
        <text x="<?= $x + $barW / 2 ?>" y="<?= $h - 8 ?>" text-anchor="middle" class="axis"><?= $label ?></text>
      <?php endif; ?>
    </g>
  <?php endforeach; ?>
</svg>
<div class="legend"><span class="sw pv"></span> Нийт хуудас үзэлт <span class="sw av"></span> Мэдээ уншсан</div>

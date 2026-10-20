<?php
// $variant: '' (grid card), 'card-feature' (big, labelled), 'card-row' (horizontal list item)
$variant = isset($variant) ? $variant : '';
$showExcerpt = !empty($showExcerpt);
$isFeature = $variant === 'card-feature';
$imgSrc = $isFeature ? media($a['image']) : thumb_url($a['image']);
?>
<article class="card <?= e($variant) ?>">
  <a href="<?= article_url($a) ?>" class="card-img">
    <?php if ($a['image']): ?><img src="<?= e($imgSrc) ?>" alt="<?= e($a['title']) ?>" loading="lazy"><?php else: ?><span class="noimg"><?= e($settings['site_name']) ?></span><?php endif; ?>
    <?php if ($isFeature): ?><span class="card-label">Онцлох мэдээ</span><?php endif; ?>
  </a>
  <div class="card-body">
    <h3 class="card-title"><a href="<?= article_url($a) ?>"><?= e($a['title']) ?></a></h3>
    <?php if ($showExcerpt && $a['excerpt']): ?><p class="excerpt"><?= e($a['excerpt']) ?></p><?php endif; ?>
    <?php $withDate = $isFeature || $variant === 'card-row'; include __DIR__ . '/meta.php'; ?>
  </div>
</article>

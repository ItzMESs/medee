<?php
// $variant: '' (grid card), 'card-feature' (big, labelled), 'card-row' (horizontal list item)
$variant = isset($variant) ? $variant : '';
$showExcerpt = !empty($showExcerpt);
$chip = isset($chip) ? $chip : false;
$isFeature = $variant === 'card-feature';
$isLead = $variant === 'card-lead';
$imgSrc = $isFeature || $isLead ? media($a['image']) : thumb_url($a['image']);
?>
<article class="card <?= e($variant) ?>">
  <a href="<?= article_url($a) ?>" class="card-img">
    <?php if ($a['image']): ?><img src="<?= e($imgSrc) ?>" alt="<?= e($a['title']) ?>" loading="lazy" onload="this.classList.add('loaded')"><?php else: ?><span class="noimg"><span><?= e($settings['site_name']) ?></span></span><?php endif; ?>
    <?php if ($isFeature): ?><span class="card-label">Онцлох мэдээ</span><?php elseif (!empty($chip) && !empty($a['category_name'])): ?><span class="card-chip"><?= e($a['category_name']) ?></span><?php endif; ?>
  </a>
  <div class="card-body">
    <h3 class="card-title"><a href="<?= article_url($a) ?>"><?= e($a['title']) ?></a></h3>
    <?php if ($showExcerpt && $a['excerpt']): ?><p class="excerpt"><?= e($a['excerpt']) ?></p><?php endif; ?>
    <?php $withDate = $isFeature || $isLead || $variant === 'card-row'; include __DIR__ . '/meta.php'; ?>
  </div>
</article>

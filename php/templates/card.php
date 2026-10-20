<?php $variant = isset($variant) ? $variant : ''; $showExcerpt = !empty($showExcerpt); ?>
<article class="card <?= e($variant) ?>">
  <a href="<?= article_url($a) ?>" class="card-img">
    <?php if ($a['image']): ?><img src="<?= e(media($a['image'])) ?>" alt="<?= e($a['title']) ?>" loading="lazy"><?php else: ?><span class="noimg"><?= e($settings['site_name']) ?></span><?php endif; ?>
  </a>
  <div class="card-body">
    <?php if ($a['category_name']): ?><a href="<?= url('/category/' . $a['category_slug']) ?>" class="tag"><?= e($a['category_name']) ?></a><?php endif; ?>
    <h3><a href="<?= article_url($a) ?>"><?= e($a['title']) ?></a></h3>
    <?php if ($showExcerpt && $a['excerpt']): ?><p class="excerpt"><?= e($a['excerpt']) ?></p><?php endif; ?>
    <div class="meta"><?= e(time_ago($a['published_at'])) ?> · <?= (int) $a['views'] ?> үзсэн</div>
  </div>
</article>

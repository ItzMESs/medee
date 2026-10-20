<?php /* Meta line: Category / Author • time ago */ ?>
<div class="meta">
  <?php if (!empty($a['category_name'])): ?><a href="<?= url('/category/' . $a['category_slug']) ?>" class="meta-cat"><?= e($a['category_name']) ?></a><?php endif; ?>
  <?php if (!empty($a['author_name'])): ?><span class="meta-author"><?= !empty($a['category_name']) ? '/ ' : '' ?><?= e($a['author_name']) ?></span><?php endif; ?>
  <span class="meta-dot">•</span><span><?= e(time_ago($a['published_at'])) ?></span>
  <?php if (!empty($withDate)): ?><span class="meta-dot">•</span><span><?= e(date('Y/m/d', strtotime($a['published_at']))) ?></span><?php endif; ?>
</div>

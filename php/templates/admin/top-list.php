<?php if (!$list): ?><p class="muted">Энэ хугацаанд уншсан мэдээ алга.</p><?php else: ?>
<ol class="top-list">
  <?php $maxCount = max(1, (int) $list[0][$countKey]); foreach ($list as $a): ?>
    <li>
      <div class="top-row">
        <a href="<?= url('/admin/articles/' . $a['id'] . '/edit') ?>"><?= e($a['title']) ?></a>
        <b><?= number_format((int) $a[$countKey]) ?></b>
      </div>
      <div class="top-bar"><span style="width: <?= round((int) $a[$countKey] / $maxCount * 100) ?>%"></span></div>
      <?php if ($a['category_name']): ?><small class="muted"><?= e($a['category_name']) ?></small><?php endif; ?>
    </li>
  <?php endforeach; ?>
</ol>
<?php endif; ?>

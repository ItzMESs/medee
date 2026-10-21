<div class="table-wrap">
<table class="table cards">
  <thead><tr><th>Гарчиг</th><th>Ангилал</th><th>Төлөв</th><th>Огноо</th><th>Үзэлт</th><th>Facebook</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($articles as $a): ?>
    <tr>
      <td class="art-main">
        <div class="art-cell">
          <a href="<?= url('/admin/articles/' . $a['id'] . '/edit') ?>" class="art-thumb" style="<?= !empty($a['image']) ? 'background-image:url(\'' . e(thumb_url($a['image'])) . '\')' : '' ?>" aria-hidden="true" tabindex="-1"></a>
          <div>
            <a href="<?= url('/admin/articles/' . $a['id'] . '/edit') ?>" class="title-link"><?= e($a['title']) ?></a>
            <?php if ($a['is_featured']): ?><span class="badge badge-gold">Онцлох</span><?php endif; ?>
            <?php if ($a['is_breaking']): ?><span class="badge badge-red">Шуурхай</span><?php endif; ?>
          </div>
        </div>
      </td>
      <td data-label="Ангилал"><?= e($a['category_name'] ?: '—') ?></td>
      <td data-label="Төлөв"><span class="badge <?= $a['status'] === 'published' ? 'badge-green' : 'badge-gray' ?>" style="margin:0"><?= $a['status'] === 'published' ? 'Нийтлэгдсэн' : 'Ноорог' ?></span></td>
      <td data-label="Огноо" class="nowrap"><?= e(format_date($a['published_at'] ?: $a['created_at'])) ?></td>
      <td data-label="Үзэлт" class="nowrap"><?= icon('eye', 14) ?> <?= number_format((int) $a['views']) ?></td>
      <td data-label="Facebook" class="hide-sm">
        <?php if ($a['fb_post_id']): ?><a href="https://www.facebook.com/<?= e($a['fb_post_id']) ?>" target="_blank" rel="noopener" class="badge badge-blue" style="margin:0">Нийтэлсэн ↗</a>
        <?php elseif ($a['fb_error']): ?><span class="badge badge-red" title="<?= e($a['fb_error']) ?>" style="margin:0">Алдаа</span>
        <?php else: ?>—<?php endif; ?>
      </td>
      <td class="nowrap actions">
        <a href="<?= url('/admin/articles/' . $a['id'] . '/edit') ?>">Засах</a>
        <?php if ($a['status'] === 'published'): ?><a href="<?= article_url($a) ?>" target="_blank">Үзэх ↗</a><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$articles): ?><tr><td colspan="7" class="empty">Мэдээ алга.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

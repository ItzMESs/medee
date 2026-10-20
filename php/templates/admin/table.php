<div class="table-wrap">
<table class="table">
  <thead><tr><th>Гарчиг</th><th>Ангилал</th><th>Төлөв</th><th>Огноо</th><th>Үзэлт</th><th>Facebook</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($articles as $a): ?>
    <tr>
      <td><a href="<?= url('/admin/articles/' . $a['id'] . '/edit') ?>" class="title-link"><?= e($a['title']) ?></a>
        <?php if ($a['is_featured']): ?><span class="badge badge-gold">Онцлох</span><?php endif; ?>
        <?php if ($a['is_breaking']): ?><span class="badge badge-red">Шуурхай</span><?php endif; ?>
      </td>
      <td><?= e($a['category_name'] ?: '—') ?></td>
      <td><span class="badge <?= $a['status'] === 'published' ? 'badge-green' : 'badge-gray' ?>"><?= $a['status'] === 'published' ? 'Нийтлэгдсэн' : 'Ноорог' ?></span></td>
      <td class="nowrap"><?= e(format_date($a['published_at'] ?: $a['created_at'])) ?></td>
      <td><?= (int) $a['views'] ?></td>
      <td>
        <?php if ($a['fb_post_id']): ?><a href="https://www.facebook.com/<?= e($a['fb_post_id']) ?>" target="_blank" rel="noopener" class="badge badge-blue">Нийтэлсэн ↗</a>
        <?php elseif ($a['fb_error']): ?><span class="badge badge-red" title="<?= e($a['fb_error']) ?>">Алдаа</span>
        <?php else: ?>—<?php endif; ?>
      </td>
      <td class="nowrap actions">
        <a href="<?= url('/admin/articles/' . $a['id'] . '/edit') ?>">Засах</a>
        <?php if ($a['status'] === 'published'): ?><a href="<?= article_url($a) ?>" target="_blank">Үзэх</a><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$articles): ?><tr><td colspan="7" class="empty">Мэдээ алга.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

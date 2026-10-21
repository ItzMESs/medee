<?php include __DIR__ . '/header.php'; ?>
<div class="panel-head"><h1>Сурталчилгаа</h1><a href="<?= url('/admin/ads/new') ?>" class="btn btn-primary">+ Сурталчилгаа нэмэх</a></div>
<p class="muted">Нэг байрлалд хэд хэдэн сурталчилгаа идэвхтэй байвал гүйдэг баннер (слайд) болж, 6 секунд тутам ээлжилнэ. Дээр нь зураас, хоёр талд нь сум гарна. Дуусах огноо өнгөрөхөд автоматаар алга болно.</p>
<div class="panel">
  <div class="table-wrap">
  <table class="table">
    <thead><tr><th>Нэр / Захиалагч</th><th>Байрлал</th><th>Хугацаа</th><th>Төлөв</th><th>Харагдсан<br><small>(7 хоног)</small></th><th>Дарсан</th><th>CTR</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($ads as $ad):
        $live = $ad['active'] && (!$ad['starts_at'] || $ad['starts_at'] <= $now) && (!$ad['ends_at'] || $ad['ends_at'] >= $now);
        $expired = $ad['ends_at'] && $ad['ends_at'] < $now;
        $endingSoon = $live && $ad['ends_at'] && $ad['ends_at'] <= $soon;
        $w = isset($week[$ad['id']]) ? $week[$ad['id']] : ['v' => 0, 'c' => 0]; ?>
      <tr>
        <td>
          <a class="title-link" href="<?= url('/admin/ads/' . $ad['id']) ?>"><?= e($ad['title']) ?></a>
          <?php if ($ad['client'] !== ''): ?><small class="muted"><?= e($ad['client']) ?></small><?php endif; ?>
          <?php if ($ad['kind'] === 'image' && $ad['image']): ?><img src="<?= e(media($ad['image'])) ?>" alt="" class="ad-thumb"><?php else: ?><span class="badge badge-gray">HTML код</span><?php endif; ?>
        </td>
        <td><?= e(isset($slots[$ad['slot']]) ? $slots[$ad['slot']][0] : $ad['slot']) ?></td>
        <td class="nowrap"><?= $ad['starts_at'] ? e(format_date($ad['starts_at'])) : '—' ?><br><?= $ad['ends_at'] ? e(format_date($ad['ends_at'])) : 'Хугацаагүй' ?></td>
        <td><?php if ($live): ?><span class="badge badge-green">Гарч байна</span><?php if ($endingSoon): ?><br><span class="badge badge-gold">Удахгүй дуусна</span><?php endif; ?><?php elseif ($expired): ?><span class="badge badge-red">Дууссан</span><?php elseif (!$ad['active']): ?><span class="badge badge-gray">Зогсоосон</span><?php else: ?><span class="badge badge-gold">Хүлээгдэж буй</span><?php endif; ?></td>
        <td><?= number_format((int) $ad['views']) ?><br><small class="muted"><?= number_format((int) $w['v']) ?></small></td>
        <td><?= number_format((int) $ad['clicks']) ?></td>
        <td><?= $ad['views'] ? number_format($ad['clicks'] / $ad['views'] * 100, 2) . '%' : '—' ?></td>
        <td class="actions"><a href="<?= url('/admin/ads/' . $ad['id']) ?>">Засах</a> · <a href="<?= url('/admin/ads/' . $ad['id'] . '/report') ?>" target="_blank">Тайлан</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$ads): ?><tr><td colspan="8" class="empty">Сурталчилгаа алга. "+ Сурталчилгаа нэмэх" дээр дарж эхний баннераа оруулна уу.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>

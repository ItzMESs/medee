<?php include __DIR__ . '/header.php'; ?>
<div class="panel-head"><h1><?= $ad['id'] ? 'Сурталчилгаа засах' : 'Шинэ сурталчилгаа' ?></h1></div>
<?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= url($ad['id'] ? '/admin/ads/' . $ad['id'] : '/admin/ads/save') ?>" enctype="multipart/form-data" class="article-form">
  <?= csrf_field() ?>
  <div class="form-main">
    <label>Нэр <small>(зөвхөн админд харагдана, ж: "Хаан банк — 10-р сар")</small><input name="title" value="<?= e($ad['title']) ?>" required maxlength="250"></label>
    <div class="grid-2">
      <label>Захиалагч <small>(байгууллагын нэр)</small><input name="client" value="<?= e($ad['client']) ?>" maxlength="250" placeholder="ж: Хаан банк"></label>
      <label>Тэмдэглэл <small>(үнэ, утас, гэрээ…)</small><input name="note" value="<?= e($ad['note']) ?>" maxlength="2000" placeholder="ж: 500,000₮ / сар, 9911-xxxx"></label>
    </div>
    <label>Байрлал
      <select name="slot">
        <?php foreach ($slots as $key => $info): ?><option value="<?= e($key) ?>" <?= $ad['slot'] === $key ? 'selected' : '' ?>><?= e($info[0]) ?> — <?= e($info[1]) ?> px</option><?php endforeach; ?>
      </select></label>
    <div class="label">Төрөл</div>
    <label class="check"><input type="radio" name="kind" value="image" <?= $ad['kind'] !== 'code' ? 'checked' : '' ?> onchange="document.body.dataset.kind='image'"> Зураг (баннер) + холбоос</label>
    <label class="check"><input type="radio" name="kind" value="code" <?= $ad['kind'] === 'code' ? 'checked' : '' ?> onchange="document.body.dataset.kind='code'"> HTML код (Google AdSense гэх мэт)</label>

    <div class="kind-image">
      <label>Баннерын зураг <small>JPG, PNG, GIF (хөдөлгөөнт), WEBP — санал болгох хэмжээ: <b class="slot-size"><?= e($slots[$ad['slot']][1] ?? '') ?></b></small>
        <?php if ($ad['image']): ?><img src="<?= e(media($ad['image'])) ?>" alt="" class="ad-preview"><?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
      <label>Утсанд зориулсан зураг <small>(заавал биш) — утсан дээр энэ зураг гарна. Өргөн баннер утсанд жижиг харагддаг тул 640 × 200 орчим хэмжээтэй зураг тавихыг зөвлөнө.</small>
        <?php if ($ad['image_mobile']): ?><img src="<?= e(media($ad['image_mobile'])) ?>" alt="" class="ad-preview ad-preview-mobile"><?php endif; ?>
        <input type="file" name="image_mobile" accept="image/jpeg,image/png,image/webp,image/gif"></label>
      <?php if ($ad['image_mobile']): ?><label class="check"><input type="checkbox" name="remove_image_mobile" value="1"> Утасны зургийг устгах</label><?php endif; ?>
      <label>Дарахад очих холбоос<input name="url" value="<?= e($ad['url']) ?>" placeholder="https://..."></label>
    </div>
    <div class="kind-code">
      <label>Сурталчилгааны код<textarea name="code" rows="8" spellcheck="false" class="mono"><?= e($ad['code']) ?></textarea></label>
      <small class="muted">Кодыг сайт дээр яг байгаагаар нь харуулна. Зөвхөн итгэлтэй эх сурвалжийн (Google AdSense гэх мэт) кодыг оруулна уу.</small>
    </div>
  </div>
  <div class="form-side">
    <div class="panel">
      <h3>Хугацаа</h3>
      <label>Эхлэх <small>(хоосон бол шууд)</small><input type="datetime-local" name="starts_at" value="<?= e($startsLocal) ?>"></label>
      <label>Дуусах <small>(хоосон бол хугацаагүй)</small><input type="datetime-local" name="ends_at" value="<?= e($endsLocal) ?>"></label>
      <label class="check"><input type="checkbox" name="active" value="1" <?= $ad['active'] ? 'checked' : '' ?>> Идэвхтэй</label>
      <button type="submit" class="btn btn-primary btn-block">Хадгалах</button>
    </div>
    <?php if ($ad['id']): ?>
    <div class="panel">
      <h3>Үр дүн</h3>
      <p>Харагдсан: <b><?= number_format((int) $ad['views']) ?></b><br>Дарсан: <b><?= number_format((int) $ad['clicks']) ?></b><br>
      CTR: <b><?= $ad['views'] ? number_format($ad['clicks'] / $ad['views'] * 100, 2) . '%' : '—' ?></b></p>
      <?php $maxV = max(1, max(array_column($daily, 'views'))); ?>
      <div class="ad-spark" title="Сүүлийн 30 хоногийн үзэлт">
        <?php foreach ($daily as $d): ?><i style="height:<?= max(2, round($d['views'] / $maxV * 100)) ?>%" title="<?= e($d['day']) ?>: <?= (int) $d['views'] ?> үзэлт, <?= (int) $d['clicks'] ?> дарсан"></i><?php endforeach; ?>
      </div>
      <small class="muted">Сүүлийн 30 хоног · 7 хоногт <?= number_format(array_sum(array_column(array_slice($daily, -7), 'views'))) ?> үзэлт</small>
      <a href="<?= url('/admin/ads/' . $ad['id'] . '/report') ?>" target="_blank" class="btn btn-block" style="margin-top:12px">📄 Захиалагчийн тайлан</a>
    </div>
    <div class="panel">
      <form method="post" action="<?= url('/admin/ads/' . $ad['id'] . '/copy') ?>"><?= csrf_field() ?>
        <button class="btn btn-block">Хуулбарлах (дараагийн сард сунгах)</button></form>
    </div>
    <?php endif; ?>
  </div>
</form>
<?php if ($ad['id']): ?>
<div class="danger-row">
  <form method="post" action="<?= url('/admin/ads/' . $ad['id'] . '/delete') ?>" onsubmit="return confirm('Сурталчилгааг устгах уу?')"><?= csrf_field() ?><button class="btn btn-danger">Устгах</button></form>
</div>
<?php endif; ?>
<script>
document.querySelector('select[name=slot]').addEventListener('change', function () {
  var sizes = <?= json_encode(array_map(function ($s) { return $s[1]; }, $slots), JSON_UNESCAPED_UNICODE) ?>;
  var el = document.querySelector('.slot-size'); if (el) el.textContent = sizes[this.value] || '';
});
</script>
<script>document.body.dataset.kind = <?= json_encode($ad['kind'] === 'code' ? 'code' : 'image') ?>;</script>
<?php include __DIR__ . '/footer.php'; ?>

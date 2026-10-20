<?php include __DIR__ . '/header.php'; ?>
<div class="panel-head"><h1><?= $ad['id'] ? 'Сурталчилгаа засах' : 'Шинэ сурталчилгаа' ?></h1></div>
<?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= url($ad['id'] ? '/admin/ads/' . $ad['id'] : '/admin/ads/save') ?>" enctype="multipart/form-data" class="article-form">
  <?= csrf_field() ?>
  <div class="form-main">
    <label>Нэр <small>(зөвхөн админд харагдана, ж: "Хаан банк — 10-р сар")</small><input name="title" value="<?= e($ad['title']) ?>" required maxlength="250"></label>
    <label>Байрлал
      <select name="slot">
        <?php foreach ($slots as $key => $info): ?><option value="<?= e($key) ?>" <?= $ad['slot'] === $key ? 'selected' : '' ?>><?= e($info[0]) ?> — <?= e($info[1]) ?> px</option><?php endforeach; ?>
      </select></label>
    <div class="label">Төрөл</div>
    <label class="check"><input type="radio" name="kind" value="image" <?= $ad['kind'] !== 'code' ? 'checked' : '' ?> onchange="document.body.dataset.kind='image'"> Зураг (баннер) + холбоос</label>
    <label class="check"><input type="radio" name="kind" value="code" <?= $ad['kind'] === 'code' ? 'checked' : '' ?> onchange="document.body.dataset.kind='code'"> HTML код (Google AdSense гэх мэт)</label>

    <div class="kind-image">
      <label>Баннерын зураг <small>JPG, PNG, GIF (хөдөлгөөнт), WEBP</small>
        <?php if ($ad['image']): ?><img src="<?= e(media($ad['image'])) ?>" alt="" class="ad-preview"><?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
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
    </div>
    <?php endif; ?>
  </div>
</form>
<?php if ($ad['id']): ?>
<div class="danger-row">
  <form method="post" action="<?= url('/admin/ads/' . $ad['id'] . '/delete') ?>" onsubmit="return confirm('Сурталчилгааг устгах уу?')"><?= csrf_field() ?><button class="btn btn-danger">Устгах</button></form>
</div>
<?php endif; ?>
<script>document.body.dataset.kind = <?= json_encode($ad['kind'] === 'code' ? 'code' : 'image') ?>;</script>
<?php include __DIR__ . '/footer.php'; ?>

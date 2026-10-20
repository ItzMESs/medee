<?php $editor = true; include __DIR__ . '/header.php'; ?>
<div class="panel-head">
  <h1><?= $article['id'] ? 'Мэдээ засах' : 'Шинэ мэдээ' ?></h1>
  <?php if ($article['id'] && $article['status'] === 'published'): ?><a href="<?= article_url($article) ?>" target="_blank" class="btn">Сайт дээр үзэх ↗</a><?php endif; ?>
</div>
<?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
<?php if ($autosave): ?>
<div class="flash flash-info autosave-offer">
  Энэ мэдээнд хадгалагдаагүй ноорог байна (<?= e(format_date($autosave['saved_at'])) ?>).
  <button type="button" class="btn btn-sm btn-primary" data-autosave-restore>Сэргээх</button>
  <button type="button" class="btn btn-sm" data-autosave-discard>Устгах</button>
  <script type="application/json" id="autosave-data"><?= json_encode(json_decode($autosave['data'], true), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</div>
<?php endif; ?>

<form method="post" action="<?= url('/admin/articles' . ($article['id'] ? '/' . $article['id'] : '')) ?>" enctype="multipart/form-data" id="article-form" class="article-form" data-article-id="<?= (int) $article['id'] ?>" data-autosave-url="<?= url('/admin/articles/autosave') ?>">
  <?= csrf_field() ?>
  <div class="form-main">
    <label>Гарчиг<input name="title" value="<?= e($article['title']) ?>" required maxlength="250" class="input-title"></label>
    <label>Товч агуулга <small>(хоосон үлдээвэл агуулгаас автоматаар үүснэ; Facebook пост дээр харагдана)</small>
      <textarea name="excerpt" rows="3" maxlength="400"><?= e($article['excerpt']) ?></textarea></label>
    <div class="label">Агуулга</div>
    <div id="editor"><?= $article['content'] ?></div>
    <input type="hidden" name="content" id="content">
  </div>

  <div class="form-side">
    <div class="panel">
      <h3>Нийтлэх</h3>
      <label>Төлөв
        <select name="status">
          <option value="draft" <?= $article['status'] !== 'published' ? 'selected' : '' ?>>Ноорог</option>
          <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Нийтлэх</option>
        </select>
      </label>
      <label>Нийтлэх огноо <small>(ирээдүйн огноо сонговол тэр үед гарна)</small>
        <input type="datetime-local" name="published_at" value="<?= e($publishedLocal) ?>"></label>
      <label class="check"><input type="checkbox" name="is_featured" value="1" <?= $article['is_featured'] ? 'checked' : '' ?>> Онцлох мэдээ (нүүрний том хэсэгт)</label>
      <label class="check"><input type="checkbox" name="is_breaking" value="1" <?= $article['is_breaking'] ? 'checked' : '' ?>> Шуурхай мэдээ (гүйдэг мөрөнд)</label>

      <div class="fb-box">
        <?php if ($article['fb_post_id']): ?>
          <p>✓ Facebook-д нийтлэгдсэн. <a href="https://www.facebook.com/<?= e($article['fb_post_id']) ?>" target="_blank" rel="noopener">Пост үзэх ↗</a></p>
        <?php elseif ($fbReady): ?>
          <label class="check"><input type="checkbox" name="post_to_facebook" value="1" <?= $settings['fb_auto_post'] === '1' ? 'checked' : '' ?>> Нийтлэхэд Facebook пэйж дээр автоматаар тавих</label>
          <?php if ($article['fb_error']): ?><p class="error-text">Өмнөх алдаа: <?= e($article['fb_error']) ?></p><?php endif; ?>
        <?php else: ?>
          <p class="muted">Facebook пэйж холбогдоогүй. <?php if ($user['role'] === 'admin'): ?><a href="<?= url('/admin/settings') ?>">Тохируулах</a><?php endif; ?></p>
        <?php endif; ?>
      </div>
      <div class="fb-box push-box">
        <?php if ($pushLog): ?>
          <p>🔔 Push мэдэгдэл <?= (int) $pushLog['sent'] ?> уншигчид илгээгдсэн (<?= e(format_date($pushLog['sent_at'])) ?>).</p>
        <?php elseif ($pushReady): ?>
          <label class="check"><input type="checkbox" name="send_push" value="1" <?= $settings['push_auto'] === '1' ? 'checked' : '' ?>> Нийтлэхэд push мэдэгдэл илгээх (<?= (int) $pushSubscribers ?> захиалагч)</label>
        <?php else: ?>
          <p class="muted">Push мэдэгдэл идэвхгүй.</p>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Хадгалах</button>
      <small class="autosave-status muted" aria-live="polite"></small>
    </div>

    <div class="panel">
      <h3>Ангилал</h3>
      <select name="category_id">
        <option value="">— Ангилалгүй —</option>
        <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $article['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
      </select>
    </div>

    <div class="panel">
      <h3>Нүүр зураг</h3>
      <img id="image-preview"<?= $article['image'] ? ' src="' . e(media($article['image'])) . '"' : '' ?> alt="" class="<?= $article['image'] ? '' : 'hidden' ?>">
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" id="image-input">
      <?php if ($article['image']): ?><label class="check"><input type="checkbox" name="remove_image" value="1"> Зургийг устгах</label><?php endif; ?>
      <small class="muted">Facebook-д 1200×630 хэмжээтэй зураг хамгийн сайн харагдана.</small>
    </div>
  </div>
</form>

<?php if ($article['id']): ?>
<div class="danger-row">
  <?php if ($article['status'] === 'published' && $fbReady): ?>
  <form method="post" action="<?= url('/admin/articles/' . $article['id'] . '/facebook') ?>" onsubmit="return confirm('Энэ мэдээг Facebook пэйж дээр <?= $article['fb_post_id'] ? 'дахин ' : '' ?>нийтлэх үү?')">
    <?= csrf_field() ?>
    <button class="btn btn-fb"><?= $article['fb_post_id'] ? 'Facebook-д дахин нийтлэх' : 'Facebook-д одоо нийтлэх' ?></button>
  </form>
  <?php endif; ?>
  <form method="post" action="<?= url('/admin/articles/' . $article['id'] . '/delete') ?>" onsubmit="return confirm('Энэ мэдээг бүр мөсөн устгах уу?')">
    <?= csrf_field() ?>
    <button class="btn btn-danger">Мэдээ устгах</button>
  </form>
</div>
<?php endif; ?>

<script src="<?= asset('vendor/quill/quill.js') ?>"></script>
<script>window.UPLOAD_URL = <?= json_encode(url('/admin/upload')) ?>;</script>
<script src="<?= asset('js/admin.js') ?>"></script>
<?php include __DIR__ . '/footer.php'; ?>

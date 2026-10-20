<?php $editor = true; include __DIR__ . '/header.php'; ?>
<div class="panel-head">
  <h1><?= $page['id'] ? 'Хуудас засах' : 'Шинэ хуудас' ?></h1>
  <?php if ($page['id']): ?><a href="<?= url('/p/' . $page['slug']) ?>" target="_blank" class="btn">Сайт дээр үзэх ↗</a><?php endif; ?>
</div>
<?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= url($page['id'] ? '/admin/pages/' . $page['id'] : '/admin/pages/save') ?>" id="article-form" class="article-form">
  <?= csrf_field() ?>
  <div class="form-main">
    <label>Гарчиг<input name="title" value="<?= e($page['title']) ?>" required maxlength="250" class="input-title"></label>
    <div class="label">Агуулга</div>
    <div id="editor"><?= $page['content'] ?></div>
    <input type="hidden" name="content" id="content">
  </div>
  <div class="form-side">
    <div class="panel">
      <label>Хаяг (slug) <small>хоосон бол гарчгаас үүснэ</small><input name="slug" value="<?= e($page['slug']) ?>"></label>
      <label>Дараалал<input name="position" type="number" value="<?= (int) $page['position'] ?>"></label>
      <label class="check"><input type="checkbox" name="in_footer" value="1" <?= $page['in_footer'] ? 'checked' : '' ?>> Footer-т харуулах</label>
      <button type="submit" class="btn btn-primary btn-block">Хадгалах</button>
    </div>
  </div>
</form>
<?php if ($page['id']): ?>
<div class="danger-row">
  <form method="post" action="<?= url('/admin/pages/' . $page['id'] . '/delete') ?>" onsubmit="return confirm('Хуудсыг устгах уу?')">
    <?= csrf_field() ?><button class="btn btn-danger">Хуудас устгах</button>
  </form>
</div>
<?php endif; ?>
<script src="<?= asset('vendor/quill/quill.js') ?>"></script>
<script>window.UPLOAD_URL = <?= json_encode(url('/admin/upload')) ?>;</script>
<script src="<?= asset('js/admin.js') ?>"></script>
<?php include __DIR__ . '/footer.php'; ?>

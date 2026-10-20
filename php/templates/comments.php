<?php
$notices = [
    'ok' => ['success', 'Сэтгэгдэл нэмэгдлээ. Баярлалаа!'],
    'pending' => ['info', 'Сэтгэгдэл хүлээн авлаа. Админ шалгасны дараа нийтлэгдэнэ.'],
    'empty' => ['error', 'Сэтгэгдлээ бичнэ үү.'],
    'long' => ['error', 'Сэтгэгдэл хэт урт байна (1000 тэмдэгт хүртэл).'],
    'wait' => ['error', 'Түр хүлээгээд дахин бичнэ үү (30 секунд).'],
    'spam' => ['error', 'Сэтгэгдэл илгээж чадсангүй. Хуудсаа сэргээгээд дахин оролдоно уу.'],
];
?>
<section class="comments" id="comments">
  <h2 class="section-title">Сэтгэгдэл <span class="count"><?= count($comments) ?></span></h2>
  <?php if (isset($notices[$commentNotice])): ?>
    <div class="notice notice-<?= $notices[$commentNotice][0] ?>"><?= e($notices[$commentNotice][1]) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= url('/news/' . (int) $article['id'] . '/comment') ?>" class="comment-form">
    <input type="hidden" name="token" value="<?= e(comment_form_token($article['id'])) ?>">
    <div class="hp" aria-hidden="true"><label>Вэбсайт<input name="website" tabindex="-1" autocomplete="off"></label></div>
    <input name="name" maxlength="60" placeholder="Таны нэр" autocomplete="nickname">
    <textarea name="body" rows="6" maxlength="1000" required placeholder="Сэтгэгдлээ энд бичнэ үү..." data-counter="comment-count"></textarea>
    <div class="comment-note">
      <small>Та сэтгэгдэл бичихдээ хууль зүйн болон ёс суртахууны хэм хэмжээг баримтална уу. Ёс бус сэтгэгдлийг админ устгах эрхтэй.</small>
      <small class="counter"><span id="comment-count">0</span>/1000</small>
    </div>
    <button type="submit" class="btn-block">Сэтгэгдэл үлдээх</button>
  </form>
  <ul class="comment-list">
    <?php foreach ($comments as $c): ?>
      <li>
        <div class="avatar"><?= e(mb_strtoupper(mb_substr($c['name'], 0, 1))) ?></div>
        <div>
          <div class="comment-head"><b><?= e($c['name']) ?></b> <span class="meta"><?= e(time_ago($c['created_at'])) ?></span></div>
          <p><?= nl2br(e($c['body'])) ?></p>
        </div>
      </li>
    <?php endforeach; ?>
    <?php if (!$comments): ?><li class="empty">Анхны сэтгэгдлийг та бичээрэй.</li><?php endif; ?>
  </ul>
</section>

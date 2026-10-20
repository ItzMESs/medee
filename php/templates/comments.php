<?php
$notices = [
    'ok' => ['success', 'Сэтгэгдэл нэмэгдлээ. Баярлалаа!'],
    'pending' => ['info', 'Сэтгэгдэл хүлээн авлаа. Админ шалгасны дараа нийтлэгдэнэ.'],
    'empty' => ['error', 'Сэтгэгдлээ бичнэ үү.'],
    'long' => ['error', 'Сэтгэгдэл хэт урт байна (2000 тэмдэгт хүртэл).'],
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
    <input name="name" maxlength="60" placeholder="Нэр (заавал биш)" autocomplete="nickname">
    <textarea name="body" rows="3" maxlength="2000" required placeholder="Сэтгэгдлээ бичнэ үү..."></textarea>
    <div class="comment-actions">
      <small>Бусдыг хүндэлсэн, ёс зүйтэй сэтгэгдэл бичнэ үү.</small>
      <button type="submit">Илгээх</button>
    </div>
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

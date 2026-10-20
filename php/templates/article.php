<?php include __DIR__ . '/header.php'; ?>
<div class="layout">
  <article class="content article">
    <?php if ($article['category_name']): ?><a href="<?= url('/category/' . $article['category_slug']) ?>" class="tag"><?= e($article['category_name']) ?></a><?php endif; ?>
    <h1 class="article-title"><?= e($article['title']) ?></h1>
    <div class="article-meta">
      <span><?= e(format_date($article['published_at'])) ?></span>
      <?php if ($article['author_name']): ?><span><?= e($article['author_name']) ?></span><?php endif; ?>
      <span><?= (int) $article['views'] ?> үзсэн</span>
      <?php if ($settings['comments_enabled'] === '1'): ?><a href="#comments"><?= count($comments) ?> сэтгэгдэл</a><?php endif; ?>
    </div>
    <?php include __DIR__ . '/share.php'; ?>
    <?php if ($article['image']): ?><figure class="article-cover"><img src="<?= e(media($article['image'])) ?>" alt="<?= e($article['title']) ?>"></figure><?php endif; ?>
    <?php if ($article['excerpt']): ?><p class="lead"><?= e($article['excerpt']) ?></p><?php endif; ?>
    <div class="article-body"><?= $article['content'] ?></div>
    <?php include __DIR__ . '/share.php'; ?>

    <?php if ($settings['comments_enabled'] === '1') { include __DIR__ . '/comments.php'; } ?>

    <?php if ($settings['fb_comments'] === '1'): ?>
      <div id="fb-root"></div>
      <script async defer crossorigin="anonymous" src="https://connect.facebook.net/mn_MN/sdk.js#xfbml=1&version=v21.0<?= $settings['fb_app_id'] ? '&appId=' . rawurlencode($settings['fb_app_id']) : '' ?>"></script>
      <h2 class="section-title">Facebook сэтгэгдэл</h2>
      <div class="fb-comments" data-href="<?= e($meta['url']) ?>" data-width="100%" data-numposts="10"></div>
    <?php endif; ?>

    <?php if ($related): ?>
      <h2 class="section-title">Холбоотой мэдээ</h2>
      <div class="grid">
        <?php foreach ($related as $a) { $variant = ''; $showExcerpt = false; include __DIR__ . '/card.php'; } ?>
      </div>
    <?php endif; ?>
  </article>
  <?php include __DIR__ . '/sidebar.php'; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

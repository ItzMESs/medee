<?php include __DIR__ . '/header.php'; $a = $article; ?>
<div class="container layout">
  <article class="content article">
    <?php if ($article['category_name']): ?><a href="<?= url('/category/' . $article['category_slug']) ?>" class="article-kicker"><?= e($article['category_name']) ?></a><?php endif; ?>
    <h1 class="article-title"><?= e($article['title']) ?></h1>
    <div class="article-meta">
      <?php $withDate = true; include __DIR__ . '/meta.php'; ?>
      <span class="views"><?= $readMinutes ?> минут унших · <?= (int) $article['views'] ?> үзсэн<?php if ($settings['comments_enabled'] === '1'): ?> · <a href="#comments"><?= count($comments) ?> сэтгэгдэл</a><?php endif; ?></span>
    </div>
    <?php if ($article['image']): ?><figure class="article-cover"><img src="<?= e(media($article['image'])) ?>" alt="<?= e($article['title']) ?>"></figure><?php endif; ?>
    <?php if ($article['excerpt']): ?><p class="lead"><?= e($article['excerpt']) ?></p><?php endif; ?>
    <div class="article-body"><?= insert_after_paragraph($article['content'], 3, ad_slot('article')) ?></div>
    <?= ad_slot('article_end') ?>
    <section class="reactions" data-react-url="<?= url('/news/' . (int) $article['id'] . '/react') ?>">
      <h3>Энэ мэдээ танд ямар сэтгэгдэл төрүүлэв?</h3>
      <div class="reaction-list">
        <?php foreach (reaction_types() as $key => $r): ?>
          <button type="button" class="reaction<?= $myReaction === $key ? ' active' : '' ?>" data-type="<?= $key ?>" aria-pressed="<?= $myReaction === $key ? 'true' : 'false' ?>" title="<?= e($r[1]) ?>">
            <span class="emoji"><?= $r[0] ?></span><span class="count"><?= (int) $reactions[$key] ?></span><span class="label"><?= e($r[1]) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if (ornaments_enabled()): ?><div class="orn-divider"><?= ornament('ulzii') ?></div><?php endif; ?>
    <div class="article-foot">
      <div class="author-box">
        <span class="avatar avatar-lg"><?= e(mb_strtoupper(mb_substr($article['author_name'] ?: $settings['site_name'], 0, 1))) ?></span>
        <div>
          <small>Нийтэлсэн</small>
          <b><?= e($article['author_name'] ?: $settings['site_name']) ?></b>
          <span><?= e($settings['site_name']) ?> · <?= e(format_date($article['published_at'])) ?></span>
        </div>
      </div>
      <?php include __DIR__ . '/share.php'; ?>
    </div>

    <?php if ($settings['comments_enabled'] === '1') { include __DIR__ . '/comments.php'; } ?>

    <?php if ($settings['fb_comments'] === '1'): ?>
      <div id="fb-root"></div>
      <script async defer crossorigin="anonymous" src="https://connect.facebook.net/mn_MN/sdk.js#xfbml=1&version=v21.0<?= $settings['fb_app_id'] ? '&appId=' . rawurlencode($settings['fb_app_id']) : '' ?>"></script>
      <h2 class="section-title"><span>Facebook сэтгэгдэл</span></h2>
      <div class="fb-comments" data-href="<?= e($meta['url']) ?>" data-width="100%" data-numposts="10"></div>
    <?php endif; ?>

    <?php if ($related): ?>
      <section class="section">
        <h2 class="section-title"><span>Холбоотой мэдээ</span></h2>
        <div class="grid grid-3">
          <?php foreach ($related as $a) { $variant = ''; $showExcerpt = false; include __DIR__ . '/card.php'; } ?>
        </div>
      </section>
    <?php endif; ?>
  </article>
  <?php include __DIR__ . '/sidebar.php'; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

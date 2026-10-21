<aside class="sidebar">
  <div class="rank-box" data-tabs>
    <div class="tab-buttons" role="tablist">
      <button type="button" class="active" data-tab="latest" role="tab">Шинэ</button>
      <button type="button" data-tab="popular" role="tab">Хандалттай</button>
    </div>
    <?php foreach (['latest' => $latestList, 'popular' => $popular] as $key => $list): ?>
      <ol class="rank-list" data-panel="<?= $key ?>" <?= $key === 'popular' ? 'hidden' : '' ?>>
        <?php foreach ($list as $i => $a): ?>
          <li>
            <a href="<?= article_url($a) ?>" class="rank-thumb<?= $a['image'] ? '' : ' no-img' ?>" tabindex="-1" aria-hidden="true">
              <?php if ($a['image']): ?><img src="<?= e(thumb_url($a['image'])) ?>" alt="" loading="lazy"><?php endif; ?>
              <?php if ($key === 'popular'): ?><span class="rank-num"><?= $i + 1 ?></span><?php endif; ?>
            </a>
            <div>
              <a href="<?= article_url($a) ?>" class="rank-title"><?= e($a['title']) ?></a>
              <small class="rank-meta"><?php if ($a['category_name']): ?><a href="<?= url('/category/' . $a['category_slug']) ?>"><?= e($a['category_name']) ?></a> · <?php endif; ?><?= e(time_ago($a['published_at'])) ?></small>
            </div>
          </li>
        <?php endforeach; ?>
        <?php if (!$list): ?><li class="empty">Одоогоор мэдээ алга.</li><?php endif; ?>
      </ol>
    <?php endforeach; ?>
  </div>
  <?php $sideTags = popular_tags(15); if ($sideTags): ?>
  <section class="widget tag-widget">
    <h3 class="widget-title">Түгээмэл сэдэв</h3>
    <div class="tag-cloud"><?php foreach ($sideTags as $t): ?><a href="<?= url(tag_path($t)) ?>" class="tag-chip<?= !empty($currentTag) && $currentTag['slug'] === $t['slug'] ? ' active' : '' ?>">#<?= e($t['name']) ?><small><?= (int) $t['cnt'] ?></small></a><?php endforeach; ?></div>
  </section>
  <?php endif; ?>
  <?= ad_slot('sidebar') ?>
  <?php if (safe_url($settings['fb_page_url'])): ?>
  <section class="widget">
    <iframe src="https://www.facebook.com/plugins/page.php?href=<?= rawurlencode($settings['fb_page_url']) ?>&tabs=timeline&width=340&height=420&small_header=true&adapt_container_width=true&hide_cover=false"
      width="100%" height="420" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true"
      allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" loading="lazy" title="Facebook"></iframe>
  </section>
  <?php endif; ?>
</aside>

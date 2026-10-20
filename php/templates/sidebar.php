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
            <span class="rank"><?= $i + 1 ?></span>
            <div>
              <a href="<?= article_url($a) ?>" class="rank-title"><?= e($a['title']) ?></a>
              <?php $withDate = false; include __DIR__ . '/meta.php'; ?>
            </div>
          </li>
        <?php endforeach; ?>
        <?php if (!$list): ?><li class="empty">Одоогоор мэдээ алга.</li><?php endif; ?>
      </ol>
    <?php endforeach; ?>
  </div>
  <?= ad_slot('sidebar') ?>
  <?php if (safe_url($settings['fb_page_url'])): ?>
  <section class="widget">
    <iframe src="https://www.facebook.com/plugins/page.php?href=<?= rawurlencode($settings['fb_page_url']) ?>&tabs=timeline&width=340&height=420&small_header=true&adapt_container_width=true&hide_cover=false"
      width="100%" height="420" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true"
      allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" loading="lazy" title="Facebook"></iframe>
  </section>
  <?php endif; ?>
</aside>

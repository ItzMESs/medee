<aside class="sidebar">
  <section class="widget">
    <h2 class="section-title">Их уншсан</h2>
    <ol class="popular">
      <?php foreach ($popular as $i => $a): ?>
        <li><span class="num"><?= $i + 1 ?></span><div><a href="<?= article_url($a) ?>"><?= e($a['title']) ?></a><div class="meta"><?= (int) $a['views'] ?> үзсэн</div></div></li>
      <?php endforeach; ?>
      <?php if (!$popular): ?><li class="empty">Одоогоор мэдээ алга.</li><?php endif; ?>
    </ol>
  </section>
  <?php if ($settings['fb_page_url']): ?>
  <section class="widget">
    <h2 class="section-title">Facebook</h2>
    <iframe src="https://www.facebook.com/plugins/page.php?href=<?= rawurlencode($settings['fb_page_url']) ?>&tabs=timeline&width=320&height=400&small_header=true&adapt_container_width=true&hide_cover=false"
      width="100%" height="400" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true"
      allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" loading="lazy"></iframe>
  </section>
  <?php endif; ?>
</aside>

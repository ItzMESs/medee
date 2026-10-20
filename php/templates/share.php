<?php $u = rawurlencode($meta['url']); ?>
<div class="share">
  <a class="share-btn" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $u ?>" aria-label="Facebook-т хуваалцах"><?= icon('facebook', 22) ?></a>
  <?php if ($settings['fb_app_id']): ?>
  <a class="share-btn" target="_blank" rel="noopener" href="https://www.facebook.com/dialog/send?link=<?= $u ?>&app_id=<?= rawurlencode($settings['fb_app_id']) ?>&redirect_uri=<?= $u ?>" aria-label="Messenger"><?= icon('messenger', 22) ?></a>
  <?php endif; ?>
  <a class="share-btn" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= $u ?>" aria-label="X"><?= icon('x', 20) ?></a>
  <button type="button" class="share-btn" data-url="<?= e($meta['url']) ?>" aria-label="Холбоос хуулах" onclick="navigator.clipboard.writeText(this.dataset.url).then(() => { this.classList.add('copied') })"><?= icon('link', 20) ?></button>
</div>

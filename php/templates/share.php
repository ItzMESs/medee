<?php $u = rawurlencode($meta['url']); ?>
<div class="share">
  <a class="share-fb" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $u ?>">Facebook-т хуваалцах</a>
  <?php if ($settings['fb_app_id']): ?>
  <a class="share-ms" target="_blank" rel="noopener" href="https://www.facebook.com/dialog/send?link=<?= $u ?>&app_id=<?= rawurlencode($settings['fb_app_id']) ?>&redirect_uri=<?= $u ?>">Messenger</a>
  <?php endif; ?>
  <a class="share-x" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= $u ?>">X</a>
  <button type="button" class="share-copy" data-url="<?= e($meta['url']) ?>" onclick="navigator.clipboard.writeText(this.dataset.url).then(() => { this.textContent = 'Хуулсан ✓' })">Холбоос хуулах</button>
</div>

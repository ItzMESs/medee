<?php include __DIR__ . '/header.php'; ?>
<div class="container notfound">
  <?php if (ornaments_enabled()): ?><div class="page-ugalz"><?= ornament('ugalz') ?></div><?php endif; ?>
  <h1>404</h1>
  <p>Таны хайсан хуудас олдсонгүй.</p>
  <a href="<?= url('/') ?>" class="btn">Нүүр хуудас руу буцах</a>
</div>
<?php include __DIR__ . '/footer.php'; ?>

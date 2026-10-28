<?php
$page = 'news'; $title = 'Төсөл';
require_once __DIR__ . '/inc/store.php';
$content = load_site();
$item = isset($_GET['id']) ? find_item($content['projects'], (string) $_GET['id']) : null;
if (isset($_GET['id']) && !$item) http_response_code(404);
if ($item) $title = $item['title'];
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
?>
<section class="content">
  <?php if ($item): ?>
  <div class="container article">
    <p class="meta"><?= e(implode(' · ', array_filter([$item['year'], $item['location']]))) ?></p>
    <?php if ($item['image']): ?><img src="<?= e($item['image']) ?>" alt=""><?php endif; ?>
    <?= paragraphs($item['body']) ?>
    <a class="btn btn-yellow" href="projects.php">← БУЦАХ</a>
  </div>
  <?php elseif (isset($_GET['id'])): ?>
  <div class="container"><p>Төсөл олдсонгүй.</p><a class="btn btn-yellow" href="projects.php">← БУЦАХ</a></div>
  <?php else: ?>
  <div class="container">
    <h2 class="title-line">ХЭРЭГЖҮҮЛСЭН ТӨСЛҮҮД</h2>
    <?php if (!$content['projects']): ?><p>Одоогоор төсөл алга.</p><?php endif; ?>
  </div>
  <div class="container grid-3 news-grid">
    <?php foreach ($content['projects'] as $pr): ?>
    <a class="news-card" href="projects.php?id=<?= e($pr['id']) ?>">
      <?php if ($pr['image']): ?><img src="<?= e($pr['image']) ?>" alt="" loading="lazy"><?php endif; ?>
      <div class="news-body"><time><?= e(implode(' · ', array_filter([$pr['year'], $pr['location']]))) ?></time><h3><?= e($pr['title']) ?></h3></div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

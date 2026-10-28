<?php
$page = 'news'; $title = 'Мэдээ, мэдээлэл';
require_once __DIR__ . '/inc/store.php';
$content = load_site();
$item = isset($_GET['id']) ? find_item($content['news'], (string) $_GET['id']) : null;
if (isset($_GET['id']) && !$item) { http_response_code(404); }
if ($item) $title = $item['title'];
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';

$news = $content['news'];
usort($news, fn($a, $b) => strcmp($b['date'], $a['date']));
$docCats = ['tailan' => 'ТАЙЛАН', 'hudaldan-avalt' => 'ХУДАЛДАН АВАЛТ'];
?>
<section class="content">
  <?php if ($item): ?>
  <div class="container article">
    <time><?= e($item['date']) ?></time>
    <?php if ($item['image']): ?><img src="<?= e($item['image']) ?>" alt=""><?php endif; ?>
    <?= paragraphs($item['body']) ?>
    <a class="btn btn-yellow" href="news.php">← БУЦАХ</a>
  </div>
  <?php elseif (isset($_GET['id'])): ?>
  <div class="container"><p>Мэдээ олдсонгүй.</p><a class="btn btn-yellow" href="news.php">← БУЦАХ</a></div>
  <?php else: ?>
  <div class="container">
    <h2 class="title-line">МЭДЭЭ</h2>
    <?php if (!$news): ?><p>Одоогоор мэдээ алга.</p><?php endif; ?>
  </div>
  <div class="container grid-3 news-grid">
    <?php foreach ($news as $n): ?>
    <a class="news-card" href="news.php?id=<?= e($n['id']) ?>">
      <?php if ($n['image']): ?><img src="<?= e($n['image']) ?>" alt="" loading="lazy"><?php endif; ?>
      <div class="news-body"><time><?= e($n['date']) ?></time><h3><?= e($n['title']) ?></h3></div>
    </a>
    <?php endforeach; ?>
  </div>
  <div class="container">
    <?php foreach ($docCats as $cat => $label):
      $docs = array_values(array_filter($content['docs'], fn($d) => $d['category'] === $cat)); ?>
    <h2 class="title-line" id="<?= $cat ?>"><?= $label ?></h2>
    <?php if (!$docs): ?>
    <p>Одоогоор мэдээлэл алга.</p>
    <?php else: ?>
    <div class="jobs docs">
      <?php foreach ($docs as $d): ?>
      <div class="job"><div><h3><?= e($d['title']) ?></h3><span><?= e($d['date']) ?></span><?= paragraphs($d['body'] ?? '') ?></div>
        <?php if ($d['file']): ?><a class="btn btn-yellow" href="<?= e($d['file']) ?>" target="_blank" rel="noopener">ТАТАХ</a><?php endif; ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

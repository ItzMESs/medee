<?php
$page = 'home';
require __DIR__ . '/inc/header.php';
$home = $content['home'];
$slides = $content['slides'];
?>
<section class="hero" id="hero">
  <?php foreach ($slides as $i => $s): ?>
  <div class="slide<?= $i === 0 ? ' active' : '' ?>" style="background-image:url('<?= e($s['image']) ?>')">
    <div class="container slide-content">
      <div class="slide-main">
        <?php if ($s['headline'] !== ''): ?><span class="slide-label"><?= e($s['title']) ?></span><?php endif; ?>
        <<?= $i === 0 ? 'h1' : 'h2' ?> class="slide-title"><?= e($s['headline'] !== '' ? $s['headline'] : $s['title']) ?></<?= $i === 0 ? 'h1' : 'h2' ?>>
      </div>
      <div class="slide-side">
        <?php if ($s['text'] !== ''): ?><p><?= e($s['text']) ?></p><?php endif; ?>
        <a href="projects.php" class="btn btn-yellow slide-btn">ТӨСЛҮҮД →</a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (trim($home['hero_big']) !== ''): ?>
  <div class="hero-big" aria-hidden="true">
    <span class="hero-big-text"><?= e($home['hero_big']) ?></span>
    <?php if (trim($home['hero_big_sub']) !== ''): ?><span class="hero-big-sub"><?= e($home['hero_big_sub']) ?></span><?php endif; ?>
  </div>
  <?php endif; ?>
  <?php if (count($slides) > 1): ?>
  <div class="hero-dots">
    <?php foreach ($slides as $i => $s): ?><button class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Слайд <?= $i + 1 ?>"></button><?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php if ($content['services']): ?>
<section class="services">
  <div class="container grid-3">
    <?php foreach ($content['services'] as $s): ?>
    <article class="service reveal">
      <h2><?= e($s['title']) ?></h2>
      <a href="operations.php#<?= e($s['id']) ?>" class="service-img"><img src="<?= e($s['image']) ?>" alt="<?= e($s['title']) ?>" loading="lazy"></a>
      <p><?= e(preg_split('/\R{2,}/u', trim($s['text']))[0]) ?></p>
      <a href="operations.php#<?= e($s['id']) ?>" class="btn btn-white">ДЭЛГЭРЭНГҮЙ</a>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="about">
  <div class="container about-grid">
    <div class="about-img reveal"><img src="<?= e($home['about_image']) ?>" alt="<?= e($home['about_title']) ?>" loading="lazy"></div>
    <div class="about-text reveal">
      <h2 class="title-line"><?= e($home['about_title']) ?></h2>
      <?= paragraphs($home['about_text']) ?>
      <a href="about.php" class="btn btn-yellow">ДЭЛГЭРЭНГҮЙ</a>
    </div>
  </div>
</section>

<section class="video-section">
  <div class="container">
    <h2 class="title-line light"><?= e($home['video_title']) ?></h2>
    <div class="video-wrap reveal">
      <?php if ($yt = youtube_embed($home['video_url'])): ?>
      <iframe src="<?= e($yt) ?>" title="<?= e($home['video_title']) ?>" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
      <?php elseif ($home['video_file']): ?>
      <video controls preload="metadata" poster="<?= e($home['video_poster']) ?>"><source src="<?= e($home['video_file']) ?>"></video>
      <?php else: ?>
      <img src="<?= e($home['video_poster']) ?>" alt="<?= e($home['video_title']) ?>">
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>

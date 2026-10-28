<?php
$page = 'news'; $title = 'Мэдээ, мэдээлэл';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
$news = [
    ['2026-10-15', 'Зам арчлалтын өвлийн бэлтгэл ажил эхэллээ', 'assets/img/road-maintenance.svg'],
    ['2026-09-02', 'Шинэ техник, тоног төхөөрөмж нэмэгдлээ', 'assets/img/mining.svg'],
    ['2026-07-20', 'Хатуу хучилттай замын шинэ хэсгийг ашиглалтад орууллаа', 'assets/img/road-construction.svg'],
];
?>
<section class="content">
  <div class="container grid-3 news-grid">
    <?php foreach ($news as $n): ?>
    <article class="news-card">
      <img src="<?= e($n[2]) ?>" alt="" loading="lazy">
      <div class="news-body"><time><?= e($n[0]) ?></time><h3><?= e($n[1]) ?></h3></div>
    </article>
    <?php endforeach; ?>
  </div>
  <div class="container">
    <h2 class="title-line" id="tailan">ТАЙЛАН</h2>
    <p>Жилийн үйл ажиллагааны болон санхүүгийн тайлангууд энд байршина.</p>
    <h2 class="title-line" id="hudaldan-avalt">ХУДАЛДАН АВАЛТ</h2>
    <p>Худалдан авалтын зар, тендерийн урилга энд байршина.</p>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

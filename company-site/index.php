<?php
$page = 'home';
require __DIR__ . '/inc/header.php';

$slides = [
    ['assets/img/hero-1.svg', 'АЛСЫН ХАРАА', 'Монгол улсын уул уурхайн үйлдвэрлэлийн салбарын шинэчлэлд манлайлагч байж, тэргүүлэх орон зайг эзэлнэ.'],
    ['assets/img/hero-2.svg', 'ЭРХЭМ ЗОРИЛГО', 'Чанартай, аюулгүй, хариуцлагатай ажлаар харилцагч, ажилтан, нийгэмдээ үнэ цэнийг бүтээнэ.'],
    ['assets/img/hero-1.svg', 'ҮНЭТ ЗҮЙЛС', 'Аюулгүй ажиллагаа, багийн ажил, хариуцлага, тасралтгүй хөгжил.'],
];

$services = [
    ['УУЛ УУРХАЙ', 'assets/img/mining.svg', 'Манай компани 2014 оноос эхлэн уурхайн хөрс хуулалт, нүүрс олборлолтын төслийг хэрэгжүүлэн ажиллаж байна. Орчин үеийн техник, туршлагатай боловсон хүчнээр үйлдвэрлэлийн өндөр бүтээмжийг хангадаг.'],
    ['ЗАМ АРЧЛАЛТ', 'assets/img/road-maintenance.svg', 'Уурхайгаас боомт хүртэлх 300 гаруй км урттай авто замын засвар, арчлалтын ажлыг жилийн турш тасралтгүй гүйцэтгэж байна.'],
    ['ЗАМ БАРИЛГА', 'assets/img/road-construction.svg', 'Аймаг, сумдын чиглэлийн 100 гаруй км хатуу хучилттай авто зам барилгын ажлыг өөрийн хөрөнгө, техникээр богино хугацаанд гүйцэтгэсэн туршлагатай.'],
];
?>
<section class="hero" id="hero">
  <?php foreach ($slides as $i => $s): ?>
  <div class="slide<?= $i === 0 ? ' active' : '' ?>" style="background-image:url('<?= e($s[0]) ?>')">
    <div class="container slide-content">
      <h1><?= e($s[1]) ?></h1>
      <p><?= e($s[2]) ?></p>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="hero-dots">
    <?php foreach ($slides as $i => $s): ?><button class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Слайд <?= $i + 1 ?>"></button><?php endforeach; ?>
  </div>
</section>

<section class="services">
  <div class="container grid-3">
    <?php foreach ($services as $s): ?>
    <article class="service reveal">
      <h2><?= e($s[0]) ?></h2>
      <a href="operations.php" class="service-img"><img src="<?= e($s[1]) ?>" alt="<?= e($s[0]) ?>" loading="lazy"></a>
      <p><?= e($s[2]) ?></p>
      <a href="operations.php" class="btn btn-white">ДЭЛГЭРЭНГҮЙ</a>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="about">
  <div class="container about-grid">
    <div class="about-img reveal"><img src="assets/img/about.svg" alt="Бидний тухай" loading="lazy"></div>
    <div class="about-text reveal">
      <h2 class="title-line">БИДНИЙ ТУХАЙ</h2>
      <p>Манай компани нь Монгол орны баруун хязгаарт материаллаг бааз, тоног төхөөрөмж, техник хэрэгсэл, машин механизмын баазыг бүрдүүлж, дадлага туршлагатай боловсон хүчний нөөцийг бэхжүүлэн, оновчтой менежментээр ханган ажиллаж байна. Бүтээн байгуулалтынхаа хэмжээг жилээс жилд өсгөн нэмэгдүүлж авто замын салбар болон уурхайд хөрс хуулалт, эрдэс баялгийн олборлолт зэрэг чиглэлүүдээр үйл ажиллагаагаа өргөжүүлэн тэргүүлэх байр сууриа эзлэн ажиллаж байна.</p>
      <a href="about.php" class="btn btn-yellow">ДЭЛГЭРЭНГҮЙ</a>
    </div>
  </div>
</section>

<section class="video-section">
  <div class="container">
    <h2 class="title-line light">КОМПАНИЙН ТАНИЛЦУУЛГА БИЧЛЭГ</h2>
    <div class="video-wrap reveal">
      <?php if (is_file(__DIR__ . '/assets/video/intro.mp4')): ?>
      <video controls preload="metadata" poster="assets/img/video-poster.svg"><source src="assets/video/intro.mp4" type="video/mp4"></video>
      <?php else: ?>
      <img src="assets/img/video-poster.svg" alt="Танилцуулга бичлэг">
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>

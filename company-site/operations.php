<?php
$page = 'operations'; $title = 'Үйл ажиллагаа';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
$items = [
    ['УУЛ УУРХАЙ', 'assets/img/mining.svg', 'Уурхайн хөрс хуулалт, нүүрс олборлолт, тээвэрлэлтийн гэрээт ажлыг 170 гаруй машин механизмаар гүйцэтгэдэг. Жилд 16 сая м³ хөрс хуулж, 7.2 сая тн нүүрс олборлох хүчин чадалтай.'],
    ['ЗАМ АРЧЛАЛТ', 'assets/img/road-maintenance.svg', 'Уурхайгаас боомт хүртэлх 300 гаруй км авто замын засвар, арчлалт, өвлийн цас цэвэрлэгээний ажлыг жилийн турш гүйцэтгэдэг.'],
    ['ЗАМ БАРИЛГА', 'assets/img/road-construction.svg', 'Хатуу хучилттай авто зам, гүүр, хоолойн барилгын ажлыг зураг төслөөс эхлээд ашиглалтад хүлээлгэн өгөх хүртэл иж бүрэн гүйцэтгэдэг.'],
];
?>
<section class="content">
  <div class="container">
    <?php foreach ($items as $i => $it): ?>
    <div class="op-row<?= $i % 2 ? ' rev' : '' ?>">
      <img src="<?= e($it[1]) ?>" alt="<?= e($it[0]) ?>" loading="lazy">
      <div><h2 class="title-line"><?= e($it[0]) ?></h2><p><?= e($it[2]) ?></p></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

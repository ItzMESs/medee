<?php
$page = 'careers'; $title = 'Хүний нөөц';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
$jobs = [
    ['Хүнд машин механизмын оператор', 'Уурхай', 'Үндсэн'],
    ['Авто засварчин', 'Уурхай', 'Үндсэн'],
    ['Зам барилгын инженер', 'Төсөл', 'Үндсэн'],
];
?>
<section class="content">
  <div class="container">
    <h2 class="title-line">ХҮНИЙ НӨӨЦИЙН БОДЛОГО</h2>
    <p>Бид ажилтнуудынхаа ур чадварыг тасралтгүй хөгжүүлж, аюулгүй, эрүүл ажлын байр, өрсөлдөхүйц цалин хөлс, нийгмийн баталгааг хангахыг эрхэмлэдэг.</p>
    <h2 class="title-line">НЭЭЛТТЭЙ АЖЛЫН БАЙР</h2>
    <div class="jobs">
      <?php foreach ($jobs as $j): ?>
      <div class="job"><div><h3><?= e($j[0]) ?></h3><span><?= e($j[1]) ?> · <?= e($j[2]) ?></span></div><a class="btn btn-yellow" href="mailto:<?= e($site['email']) ?>?subject=<?= rawurlencode('Анкет: ' . $j[0]) ?>">АНКЕТ ИЛГЭЭХ</a></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

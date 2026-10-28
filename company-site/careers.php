<?php
$page = 'careers'; $title = 'Хүний нөөц';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
?>
<section class="content">
  <div class="container">
    <h2 class="title-line">ХҮНИЙ НӨӨЦИЙН БОДЛОГО</h2>
    <?= paragraphs($content['careers']['policy']) ?>
    <h2 class="title-line">НЭЭЛТТЭЙ АЖЛЫН БАЙР</h2>
    <?php if (!$content['jobs']): ?><p>Одоогоор нээлттэй ажлын байр алга.</p><?php endif; ?>
    <div class="jobs">
      <?php foreach ($content['jobs'] as $j): ?>
      <div class="job"><div><h3><?= e($j['title']) ?></h3><span><?= e(implode(' · ', array_filter([$j['location'], $j['type']]))) ?></span><?= paragraphs($j['body']) ?></div><a class="btn btn-yellow" href="mailto:<?= e($site['email']) ?>?subject=<?= rawurlencode('Анкет: ' . $j['title']) ?>">АНКЕТ ИЛГЭЭХ</a></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

<?php
$page = 'contact'; $title = 'Холбоо барих';
require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/subpage.php';
?>
<section class="content">
  <div class="container grid-3 values">
    <div class="value"><h3>УТАС</h3><p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>"><?= e($site['phone']) ?></a></p></div>
    <div class="value"><h3>И-МЭЙЛ</h3><p><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></p></div>
    <div class="value"><h3>ХАЯГ</h3><p><?= e($site['address']) ?></p></div>
  </div>
</section>
<?php require __DIR__ . '/inc/footer.php'; ?>

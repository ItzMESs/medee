<?php include __DIR__ . '/header.php'; ?>
<div class="container layout">
  <article class="content article">
    <h1 class="article-title"><?= e($page['title']) ?></h1>
    <div class="article-body"><?= $page['content'] ?></div>
  </article>
  <?php include __DIR__ . '/sidebar.php'; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

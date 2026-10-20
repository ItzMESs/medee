<?php include __DIR__ . '/header.php'; ?>
<h1>Тохиргоо</h1>
<form method="post" action="<?= url('/admin/settings') ?>" class="stack">
  <?= csrf_field() ?>
  <div class="panel narrow-panel">
    <h2>Сайт</h2>
    <label>Сайтын нэр<input name="site_name" value="<?= e($settings['site_name']) ?>" required></label>
    <label>Уриа<input name="site_tagline" value="<?= e($settings['site_tagline']) ?>"></label>
    <label>Сайтын хаяг (домэйн) <small>ж: https://medee.mn — Facebook пост дээрх холбоос энэ хаягаар үүснэ</small>
      <input name="site_url" value="<?= e($settings['site_url']) ?>" required></label>
  </div>

  <div class="panel narrow-panel">
    <h2>Facebook</h2>
    <label>Facebook пэйжийн хаяг <small>ж: https://www.facebook.com/medee.mn — сайтын хажууд пэйж харагдана</small>
      <input name="fb_page_url" value="<?= e($settings['fb_page_url']) ?>"></label>
    <label>Facebook App ID <small>(Messenger хуваалцах, сэтгэгдэлд хэрэглэнэ)</small>
      <input name="fb_app_id" value="<?= e($settings['fb_app_id']) ?>"></label>
    <label>Facebook Page ID<input name="fb_page_id" value="<?= e($settings['fb_page_id']) ?>"></label>
    <label>Page Access Token <small><?= $settings['fb_page_token'] ? '✓ Хадгалагдсан. Солих бол шинээр оруулна уу.' : 'Оруулаагүй байна.' ?></small>
      <input name="fb_page_token" type="password" autocomplete="off" placeholder="<?= $settings['fb_page_token'] ? '••••••••••••' : 'EAAG...' ?>"></label>
    <?php if ($settings['fb_page_token']): ?><label class="check"><input type="checkbox" name="clear_token" value="1"> Token устгах</label><?php endif; ?>
    <label class="check"><input type="checkbox" name="fb_auto_post" value="1" <?= $settings['fb_auto_post'] === '1' ? 'checked' : '' ?>> Шинэ мэдээ нийтлэхэд "Facebook-д тавих" сонголт анхнаасаа идэвхтэй байх</label>
    <label class="check"><input type="checkbox" name="fb_comments" value="1" <?= $settings['fb_comments'] === '1' ? 'checked' : '' ?>> Мэдээн доор Facebook сэтгэгдэл харуулах</label>

    <details class="help">
      <summary>Page ID болон Access Token хэрхэн авах вэ?</summary>
      <ol>
        <li><a href="https://developers.facebook.com/apps" target="_blank" rel="noopener">developers.facebook.com</a> дээр App үүсгэнэ (Business төрөл).</li>
        <li><a href="https://developers.facebook.com/tools/explorer" target="_blank" rel="noopener">Graph API Explorer</a> дээр өөрийн App-ийг сонгоод <code>pages_manage_posts</code>, <code>pages_read_engagement</code>, <code>pages_show_list</code> эрхүүдийг нэмж User Token авна.</li>
        <li><a href="https://developers.facebook.com/tools/debug/accesstoken" target="_blank" rel="noopener">Access Token Debugger</a> дээр "Extend Access Token" дарж урт хугацааны token болгоно.</li>
        <li>Explorer дээр <code>me/accounts</code> гэж хүсэлт явуулна. Хариунд таны пэйжийн <b>id</b> (Page ID) болон <b>access_token</b> (Page Access Token, хугацаагүй) гарна. Тэдгээрийг энд хуулна.</li>
        <li>Сайт интернэтэд нээлттэй (domain-тэй) байх ёстой — Facebook нь мэдээний зураг, гарчгийг сайтаас уншиж пост үүсгэнэ.</li>
      </ol>
    </details>
  </div>
  <div><button class="btn btn-primary">Хадгалах</button></div>
</form>
<?php include __DIR__ . '/footer.php'; ?>

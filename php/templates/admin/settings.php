<?php include __DIR__ . '/header.php'; ?>
<h1>Тохиргоо</h1>
<form method="post" action="<?= url('/admin/settings') ?>" class="stack" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="panel narrow-panel">
    <h2>Сайт</h2>
    <label>Сайтын нэр<input name="site_name" value="<?= e($settings['site_name']) ?>" required></label>
    <label>Уриа<input name="site_tagline" value="<?= e($settings['site_tagline']) ?>"></label>
    <label>Сайтын хаяг (домэйн) <small>ж: https://medee.mn — Facebook пост дээрх холбоос энэ хаягаар үүснэ</small>
      <input name="site_url" value="<?= e($settings['site_url']) ?>" required></label>
  </div>

  <div class="panel narrow-panel">
    <h2>Дизайн</h2>
    <label>Үндсэн өнгө <small>толгой хэсэг, товч, холбоосын өнгө</small>
      <span class="color-row"><input type="color" name="brand_color" value="<?= e(brand_color()) ?>"><code><?= e(brand_color()) ?></code></span></label>
    <label>Лого <small>PNG (тунгалаг дэвсгэртэй), өндөр нь 80-120px байхад тохиромжтой. Байхгүй бол сайтын нэр бичигдэнэ.</small>
      <?php if (site_logo_url()): ?><span class="logo-preview" style="background: <?= e(brand_color()) ?>"><img src="<?= e(site_logo_url()) ?>" alt=""></span><?php if (!$settings['logo']): ?><small> (анхдагч NATIONAL RBC цагаан лого)</small><?php endif; ?><?php endif; ?>
      <input type="file" name="logo_file" accept="image/png,image/jpeg,image/webp"></label>
    <?php if ($settings['logo']): ?><label class="check"><input type="checkbox" name="remove_logo" value="1"> Логог устгах</label><?php endif; ?>
    <label class="check"><input type="checkbox" name="ornaments" value="1" <?= $settings['ornaments'] !== '0' ? 'checked' : '' ?>> Монгол хээ угалз харуулах (алхан хээ, өлзий, эвэр угалз)</label>
  </div>

  <div class="panel narrow-panel">
    <h2>Зурган дээрх лого</h2>
    <label class="check"><input type="checkbox" name="watermark_enabled" value="1" <?= $settings['watermark_enabled'] === '1' ? 'checked' : '' ?>> Хуулсан зураг дээр лого тавих</label>
    <div class="inline-form rate-inputs">
      <label>Байрлал
        <select name="watermark_position">
          <?php foreach (['br' => 'Баруун доод', 'bl' => 'Зүүн доод', 'tr' => 'Баруун дээд', 'tl' => 'Зүүн дээд'] as $k => $v): ?>
            <option value="<?= $k ?>" <?= $settings['watermark_position'] === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select></label>
      <label>Хэмжээ (зургийн өргөний %)<input type="number" name="watermark_size" min="8" max="40" value="<?= (int) $settings['watermark_size'] ?>"></label>
      <label>Тод байдал (%)<input type="number" name="watermark_opacity" min="20" max="100" value="<?= (int) $settings['watermark_opacity'] ?>"></label>
    </div>
  </div>

  <div class="panel narrow-panel">
    <h2>Footer ба холбоо барих</h2>
    <label>Сайтын тухай товч <small>footer-т логоны доор гарна</small><textarea name="footer_about" rows="3"><?= e($settings['footer_about']) ?></textarea></label>
    <label>Хаяг<input name="contact_address" value="<?= e($settings['contact_address']) ?>" placeholder="Сүхбаатар дүүрэг, ... Улаанбаатар"></label>
    <label>Утас<input name="contact_phone" value="<?= e($settings['contact_phone']) ?>" placeholder="7700 0000"></label>
    <label>Цахим шуудан<input name="contact_email" value="<?= e($settings['contact_email']) ?>" placeholder="info@domain.mn"></label>
    <label>YouTube<input name="social_youtube" value="<?= e($settings['social_youtube']) ?>" placeholder="https://youtube.com/@..."></label>
    <label>X (Twitter)<input name="social_x" value="<?= e($settings['social_x']) ?>" placeholder="https://x.com/..."></label>
    <label>Instagram<input name="social_instagram" value="<?= e($settings['social_instagram']) ?>" placeholder="https://instagram.com/..."></label>
    <small class="muted">Facebook холбоосыг доорх "Facebook пэйжийн хаяг" талбараас авна. Footer-ийн холбоосуудыг <a href="<?= url('/admin/pages') ?>">Хуудсууд</a> хэсгээс удирдана.</small>
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
  <div class="panel narrow-panel">
    <h2>Сэтгэгдэл</h2>
    <label class="check"><input type="checkbox" name="comments_enabled" value="1" <?= $settings['comments_enabled'] === '1' ? 'checked' : '' ?>> Уншигчид мэдээн доор сэтгэгдэл бичих боломжтой</label>
    <label class="check"><input type="checkbox" name="comments_moderation" value="1" <?= $settings['comments_moderation'] === '1' ? 'checked' : '' ?>> Сэтгэгдлийг админ зөвшөөрсний дараа нийтлэх</label>
    <small class="muted">Унтраалттай үед сэтгэгдэл шууд гарна. 2-оос дээш холбоостой сэтгэгдэл ямагт хүлээлгэнд орно.</small>
  </div>

  <div class="panel narrow-panel">
    <h2>Ханш, цаг агаар</h2>
    <label>Валютын ханш (дээд мөрөнд)
      <select name="rates_mode">
        <option value="auto" <?= $settings['rates_mode'] === 'auto' ? 'selected' : '' ?>>Автоматаар (6 цаг тутам шинэчлэгдэнэ, олон улсын зах зээлийн ханш)</option>
        <option value="manual" <?= $settings['rates_mode'] === 'manual' ? 'selected' : '' ?>>Гараар оруулах (ж: Монголбанкны албан ханш)</option>
        <option value="off" <?= $settings['rates_mode'] === 'off' ? 'selected' : '' ?>>Харуулахгүй</option>
      </select></label>
    <div class="inline-form rate-inputs">
      <label>USD (₮)<input name="rate_usd" value="<?= e($settings['rate_usd']) ?>" placeholder="3450"></label>
      <label>CNY (₮)<input name="rate_cny" value="<?= e($settings['rate_cny']) ?>" placeholder="475"></label>
      <label>RUB (₮)<input name="rate_rub" value="<?= e($settings['rate_rub']) ?>" placeholder="38.5"></label>
    </div>
    <small class="muted">Гараар оруулах горимд л эдгээр утгыг ашиглана.</small>
    <label class="check" style="margin-top:14px"><input type="checkbox" name="weather_enabled" value="1" <?= $settings['weather_enabled'] !== '0' ? 'checked' : '' ?>> Улаанбаатарын цаг агаар харуулах (30 минут тутам шинэчлэгдэнэ)</label>
  </div>

  <div class="panel narrow-panel">
    <h2>Google (SEO)</h2>
    <label>Google Analytics хэмжилтийн ID <small>ж: G-XXXXXXXXXX — analytics.google.com → Admin → Data streams</small>
      <input name="ga_id" value="<?= e($settings['ga_id']) ?>" placeholder="G-XXXXXXXXXX"></label>
    <label>Google Search Console баталгаажуулах код <small>"HTML tag" аргын content="..." доторх утга</small>
      <input name="google_verification" value="<?= e($settings['google_verification']) ?>"></label>
    <p class="muted">Sitemap хаяг: <code><?= e(rtrim($settings['site_url'], '/')) ?>/sitemap.xml</code> — Search Console → Sitemaps хэсэгт нэмнэ.</p>
  </div>

  <div><button class="btn btn-primary">Хадгалах</button></div>
</form>
<div class="panel narrow-panel" id="watermark">
  <h2>Зурган дээрх лого (watermark)</h2>
  <p class="muted">Шинээр хуулсан нүүр зураг болон мэдээн доторх зураг дээр лого автоматаар тавигдана. Гэрэлтэй буланд өнгөт лого, бараан буланд цагаан лого сонгогдоно.</p>
  <form method="post" action="<?= url('/admin/images/watermark') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="offset" value="<?= (int) (isset($_SESSION['watermark_offset']) ? $_SESSION['watermark_offset'] : 0) ?>">
    <button class="btn"><?= $settings['watermark_enabled'] === '1' ? 'Одоо байгаа бүх зураг дээр тавих / шинэчлэх' : 'Одоо байгаа зургуудаас лого авах' ?></button>
  </form>
  <small class="muted">Тохиргоогоо доорх "Хадгалах" товчоор хадгалсны дараа энэ товчийг дарна. Эх зураг хадгалагддаг тул лого давхардахгүй.</small>
</div>

<div class="panel narrow-panel">
  <h2>Зураг</h2>
  <?php if (!$imageStats['gd']): ?>
    <p class="error-text">Сервер дээр PHP GD өргөтгөл идэвхгүй тул зургийг жижигрүүлэх боломжгүй. cPanel → Select PHP Version хэсгээс <code>gd</code>-г идэвхжүүлнэ үү.</p>
  <?php else: ?>
    <p>Шинээр хуулсан зураг автоматаар жижигрүүлэгдэнэ: том талаараа хамгийн ихдээ 1600px, мэдээний картанд 480px хувилбар.</p>
    <p class="muted">Нүүр зурагтай мэдээ: <?= (int) $imageStats['total'] ?> · Жижиг хувилбаргүй: <?= (int) $imageStats['missing'] ?></p>
    <?php if ($imageStats['missing']): ?>
    <form method="post" action="<?= url('/admin/images/optimize') ?>">
      <?= csrf_field() ?>
      <button class="btn">Хуучин зургуудыг жижигрүүлэх</button>
    </form>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>

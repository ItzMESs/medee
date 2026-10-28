<footer class="site-footer" id="footer">
  <div class="container footer-grid">
    <div>
      <h4>САЙТЫН БҮТЭЦ</h4>
      <ul class="footer-links">
        <li><a href="about.php">БИДНИЙ ТУХАЙ</a></li>
        <li><a href="operations.php">ҮЙЛ АЖИЛЛАГАА</a></li>
        <li><a href="news.php">МЭДЭЭ, МЭДЭЭЛЭЛ</a></li>
        <li><a href="careers.php">ХҮНИЙ НӨӨЦ</a></li>
        <li><a href="contact.php">ХОЛБОО БАРИХ</a></li>
      </ul>
    </div>
    <div>
      <h4>ХОЛБОО БАРИХ</h4>
      <p>Утасны дугаар:<br><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>"><?= e($site['phone']) ?></a></p>
      <p>И-мэйл хаяг:<br><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></p>
    </div>
    <div>
      <h4>КОМПАНИЙН ХАЯГ</h4>
      <p><?= e($site['address']) ?></p>
    </div>
    <div>
      <h4>САНАЛ ХҮСЭЛТ</h4>
      <?php $sent = $_GET['sent'] ?? null; if ($sent !== null): ?>
      <p class="form-msg <?= $sent === '1' ? 'ok' : 'err' ?>"><?= $sent === '1' ? 'Баярлалаа! Таны санал хүсэлт илгээгдлээ.' : 'Илгээхэд алдаа гарлаа. Талбаруудаа шалгаад дахин оролдоно уу.' ?></p>
      <?php endif; ?>
      <form class="feedback" action="send.php" method="post">
        <input type="text" name="name" placeholder="Таны нэр" required maxlength="100">
        <input type="email" name="email" placeholder="Имэйл хаяг" required maxlength="150">
        <input type="tel" name="phone" placeholder="Утасны дугаар" maxlength="30">
        <textarea name="message" rows="4" placeholder="Санал хүсэлт" required maxlength="3000"></textarea>
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <input type="hidden" name="back" value="<?= e(basename($_SERVER['SCRIPT_NAME'])) ?>">
        <button type="submit" class="btn btn-yellow">ИЛГЭЭХ</button>
      </form>
    </div>
  </div>
  <div class="container footer-bottom">
    <p>© <?= date('Y') ?> он. <?= e($site['name']) ?>. Бүх эрх хуулиар хамгаалагдсан.<?= $site['developer'] ? ' Вэб хөгжүүлэгч ' . e($site['developer']) : '' ?></p>
    <a class="fb" href="<?= e($site['facebook']) ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M14 8V6c0-.8.2-1.3 1.4-1.3H17V1.2C16.7 1.1 15.6 1 14.4 1 11.8 1 10 2.6 10 5.5V8H7v3.9h3V23h4V11.9h3.1l.5-3.9H14z"/></svg></a>
  </div>
</footer>
<script src="<?= e(asset('assets/js/main.js')) ?>"></script>
</body>
</html>

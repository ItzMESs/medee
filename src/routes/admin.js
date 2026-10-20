const path = require('node:path');
const fs = require('node:fs');
const crypto = require('node:crypto');
const express = require('express');
const multer = require('multer');
const { db, getSettings, setSetting } = require('../db');
const { hashPassword, verifyPassword, requireAuth, requireAdmin, csrf, checkCsrf } = require('../auth');
const { slugify, cleanHtml, excerptFrom, toDb, toLocalInput, fromLocalInput, formatDate, articleUrl } = require('../util');
const facebook = require('../facebook');

const router = express.Router();
const uploadDir = process.env.UPLOAD_DIR || path.join(__dirname, '..', '..', 'uploads');
fs.mkdirSync(uploadDir, { recursive: true });

const IMAGE_TYPES = { 'image/jpeg': '.jpg', 'image/png': '.png', 'image/webp': '.webp', 'image/gif': '.gif' };
const upload = multer({
  storage: multer.diskStorage({
    destination: uploadDir,
    filename: (req, file, cb) => cb(null, `${Date.now()}-${crypto.randomBytes(6).toString('hex')}${IMAGE_TYPES[file.mimetype]}`),
  }),
  limits: { fileSize: 8 * 1024 * 1024 },
  fileFilter: (req, file, cb) => cb(null, Boolean(IMAGE_TYPES[file.mimetype])),
});

router.use(express.urlencoded({ extended: false }));
router.use(csrf);
router.use((req, res, next) => {
  res.locals.user = req.session.user || null;
  res.locals.settings = getSettings();
  res.locals.flash = req.session.flash || null;
  delete req.session.flash;
  res.locals.formatDate = formatDate;
  res.locals.articleUrl = articleUrl;
  res.locals.path = req.path;
  next();
});

function flash(req, type, text) {
  req.session.flash = { type, text };
}

// ---------- Auth ----------

router.get('/login', (req, res) => {
  if (req.session.user) return res.redirect('/admin');
  res.render('admin/login', { error: null });
});

router.post('/login', checkCsrf, (req, res) => {
  const user = db.prepare('SELECT * FROM users WHERE username = ?').get(String(req.body.username || '').trim());
  if (!user || !verifyPassword(String(req.body.password || ''), user.password_hash)) {
    return res.status(401).render('admin/login', { error: 'Нэвтрэх нэр эсвэл нууц үг буруу байна.' });
  }
  req.session.regenerate((err) => {
    if (err) return res.status(500).send('Session алдаа');
    req.session.user = { id: user.id, username: user.username, name: user.name, role: user.role };
    res.redirect('/admin');
  });
});

router.post('/logout', checkCsrf, (req, res) => {
  req.session.destroy(() => res.redirect('/admin/login'));
});

router.use(requireAuth);

// ---------- Dashboard ----------

router.get('/', (req, res) => {
  const stats = db.prepare(`SELECT
      COUNT(*) AS total,
      SUM(status = 'published') AS published,
      SUM(status = 'draft') AS drafts,
      COALESCE(SUM(views), 0) AS views,
      SUM(fb_post_id IS NOT NULL) AS fb
    FROM articles`).get();
  const recent = db.prepare(`SELECT a.*, c.name AS category_name FROM articles a
    LEFT JOIN categories c ON c.id = a.category_id ORDER BY a.updated_at DESC LIMIT 8`).all();
  res.render('admin/dashboard', { stats, recent, fbReady: facebook.isConfigured() });
});

// ---------- Articles ----------

const PAGE_SIZE = 20;

router.get('/articles', (req, res) => {
  const q = String(req.query.q || '').trim();
  const status = ['published', 'draft'].includes(req.query.status) ? req.query.status : '';
  const category = parseInt(req.query.category, 10) || 0;
  const page = Math.max(1, parseInt(req.query.page, 10) || 1);

  const where = [];
  const params = [];
  if (q) { where.push('a.title LIKE ?'); params.push(`%${q}%`); }
  if (status) { where.push('a.status = ?'); params.push(status); }
  if (category) { where.push('a.category_id = ?'); params.push(category); }
  const w = where.length ? 'WHERE ' + where.join(' AND ') : '';

  const total = db.prepare(`SELECT COUNT(*) AS n FROM articles a ${w}`).get(...params).n;
  const articles = db.prepare(`SELECT a.*, c.name AS category_name, u.name AS author_name FROM articles a
    LEFT JOIN categories c ON c.id = a.category_id LEFT JOIN users u ON u.id = a.author_id
    ${w} ORDER BY COALESCE(a.published_at, a.created_at) DESC LIMIT ? OFFSET ?`)
    .all(...params, PAGE_SIZE, (page - 1) * PAGE_SIZE);
  const categories = db.prepare('SELECT * FROM categories ORDER BY position, id').all();
  res.render('admin/articles', { articles, categories, q, status, category, page, pages: Math.ceil(total / PAGE_SIZE) });
});

function renderForm(res, article, error = null) {
  const categories = db.prepare('SELECT * FROM categories ORDER BY position, id').all();
  res.render('admin/article-form', {
    article, categories, error, fbReady: facebook.isConfigured(),
    publishedLocal: toLocalInput(article.published_at),
  });
}

router.get('/articles/new', (req, res) => {
  renderForm(res, { id: null, title: '', excerpt: '', content: '', status: 'draft', category_id: null, is_featured: 0, is_breaking: 0, image: null, published_at: null });
});

router.get('/articles/:id/edit', (req, res, next) => {
  const article = db.prepare('SELECT * FROM articles WHERE id = ?').get(Number(req.params.id));
  if (!article) return next();
  renderForm(res, article);
});

function removeUpload(image) {
  if (image && image.startsWith('/uploads/')) fs.unlink(path.join(uploadDir, path.basename(image)), () => {});
}

async function saveArticle(req, res) {
  const existing = req.params.id ? db.prepare('SELECT * FROM articles WHERE id = ?').get(Number(req.params.id)) : null;
  if (req.params.id && !existing) return res.status(404).send('Мэдээ олдсонгүй');

  const b = req.body;
  const content = cleanHtml(b.content);
  const status = b.status === 'published' ? 'published' : 'draft';
  const data = {
    title: String(b.title || '').trim(),
    excerpt: String(b.excerpt || '').trim() || excerptFrom(content),
    content,
    category_id: parseInt(b.category_id, 10) || null,
    status,
    is_featured: b.is_featured ? 1 : 0,
    is_breaking: b.is_breaking ? 1 : 0,
    published_at: fromLocalInput(b.published_at) || (existing && existing.published_at) || (status === 'published' ? toDb(new Date()) : null),
    image: existing ? existing.image : null,
  };
  data.slug = slugify(data.title);

  if (req.file) {
    if (existing) removeUpload(existing.image);
    data.image = `/uploads/${req.file.filename}`;
  } else if (b.remove_image && existing) {
    removeUpload(existing.image);
    data.image = null;
  }

  if (!data.title) {
    if (req.file && !existing) removeUpload(data.image);
    return renderForm(res, { ...(existing || {}), ...data, id: existing && existing.id }, 'Гарчиг заавал оруулна уу.');
  }

  let id;
  if (existing) {
    db.prepare(`UPDATE articles SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, category_id = ?, status = ?,
      is_featured = ?, is_breaking = ?, published_at = ?, updated_at = datetime('now') WHERE id = ?`)
      .run(data.title, data.slug, data.excerpt, data.content, data.image, data.category_id, data.status,
        data.is_featured, data.is_breaking, data.published_at, existing.id);
    id = existing.id;
  } else {
    id = Number(db.prepare(`INSERT INTO articles (title, slug, excerpt, content, image, category_id, author_id, status,
      is_featured, is_breaking, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`)
      .run(data.title, data.slug, data.excerpt, data.content, data.image, data.category_id, req.session.user.id,
        data.status, data.is_featured, data.is_breaking, data.published_at).lastInsertRowid);
  }

  const article = db.prepare('SELECT * FROM articles WHERE id = ?').get(id);
  let message = 'Мэдээ хадгалагдлаа.';
  let type = 'success';

  // Share to the Facebook Page once, the first time an article goes live.
  if (b.post_to_facebook && article.status === 'published' && !article.fb_post_id && facebook.isConfigured()) {
    try {
      await facebook.postArticle(article);
      message += ' Facebook пэйж дээр нийтлэгдлээ.';
    } catch (e) {
      message += ` Гэхдээ Facebook-д нийтлэхэд алдаа гарлаа: ${e.message}`;
      type = 'error';
    }
  }
  flash(req, type, message);
  res.redirect(`/admin/articles/${id}/edit`);
}

const handleUpload = (req, res, next) => upload.single('image')(req, res, (err) => {
  if (err) return res.status(400).send(`Зураг хуулахад алдаа гарлаа: ${err.message}`);
  next();
});

router.post('/articles', handleUpload, checkCsrf, saveArticle);
router.post('/articles/:id', handleUpload, checkCsrf, saveArticle);

router.post('/articles/:id/delete', checkCsrf, (req, res) => {
  const article = db.prepare('SELECT * FROM articles WHERE id = ?').get(Number(req.params.id));
  if (article) {
    removeUpload(article.image);
    db.prepare('DELETE FROM articles WHERE id = ?').run(article.id);
    flash(req, 'success', 'Мэдээ устгагдлаа.');
  }
  res.redirect('/admin/articles');
});

router.post('/articles/:id/facebook', checkCsrf, async (req, res) => {
  const article = db.prepare('SELECT * FROM articles WHERE id = ?').get(Number(req.params.id));
  if (!article) return res.status(404).send('Мэдээ олдсонгүй');
  if (article.status !== 'published') {
    flash(req, 'error', 'Эхлээд мэдээгээ нийтэлсэн төлөвт оруулна уу.');
  } else {
    try {
      await facebook.postArticle(article);
      flash(req, 'success', 'Facebook пэйж дээр нийтлэгдлээ.');
    } catch (e) {
      flash(req, 'error', `Facebook алдаа: ${e.message}`);
    }
  }
  res.redirect(req.get('referer') && req.get('referer').includes('/admin/') ? req.get('referer') : '/admin/articles');
});

// Images inserted inside the article body from the editor.
router.post('/upload', handleUpload, checkCsrf, (req, res) => {
  if (!req.file) return res.status(400).json({ error: 'Зөвхөн JPG, PNG, WEBP, GIF зураг хуулна.' });
  res.json({ url: `/uploads/${req.file.filename}` });
});

// ---------- Categories ----------

router.get('/categories', (req, res) => {
  const categories = db.prepare(`SELECT c.*, COUNT(a.id) AS count FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id GROUP BY c.id ORDER BY c.position, c.id`).all();
  res.render('admin/categories', { categories });
});

router.post('/categories', checkCsrf, (req, res) => {
  const name = String(req.body.name || '').trim();
  if (name) {
    const slug = String(req.body.slug || '').trim() ? slugify(req.body.slug) : slugify(name);
    try {
      db.prepare('INSERT INTO categories (name, slug, position) VALUES (?, ?, ?)').run(name, slug, parseInt(req.body.position, 10) || 0);
      flash(req, 'success', 'Ангилал нэмэгдлээ.');
    } catch {
      flash(req, 'error', 'Ийм холбоостой (slug) ангилал аль хэдийн байна.');
    }
  }
  res.redirect('/admin/categories');
});

router.post('/categories/:id', checkCsrf, (req, res) => {
  const name = String(req.body.name || '').trim();
  if (name) {
    try {
      db.prepare('UPDATE categories SET name = ?, slug = ?, position = ? WHERE id = ?')
        .run(name, slugify(req.body.slug || name), parseInt(req.body.position, 10) || 0, Number(req.params.id));
      flash(req, 'success', 'Ангилал шинэчлэгдлээ.');
    } catch {
      flash(req, 'error', 'Ийм холбоостой (slug) ангилал аль хэдийн байна.');
    }
  }
  res.redirect('/admin/categories');
});

router.post('/categories/:id/delete', checkCsrf, (req, res) => {
  db.prepare('DELETE FROM categories WHERE id = ?').run(Number(req.params.id));
  flash(req, 'success', 'Ангилал устгагдлаа. Түүнд хамаарах мэдээнүүд ангилалгүй боллоо.');
  res.redirect('/admin/categories');
});

// ---------- Users ----------

router.get('/users', requireAdmin, (req, res) => {
  const users = db.prepare('SELECT id, username, name, role, created_at FROM users ORDER BY id').all();
  res.render('admin/users', { users });
});

router.post('/users', requireAdmin, checkCsrf, (req, res) => {
  const username = String(req.body.username || '').trim();
  const name = String(req.body.name || '').trim() || username;
  const password = String(req.body.password || '');
  const role = req.body.role === 'admin' ? 'admin' : 'editor';
  if (!username || password.length < 6) {
    flash(req, 'error', 'Нэвтрэх нэр болон дор хаяж 6 тэмдэгттэй нууц үг оруулна уу.');
  } else {
    try {
      db.prepare('INSERT INTO users (username, name, password_hash, role) VALUES (?, ?, ?, ?)').run(username, name, hashPassword(password), role);
      flash(req, 'success', 'Хэрэглэгч нэмэгдлээ.');
    } catch {
      flash(req, 'error', 'Ийм нэвтрэх нэртэй хэрэглэгч байна.');
    }
  }
  res.redirect('/admin/users');
});

router.post('/users/:id/delete', requireAdmin, checkCsrf, (req, res) => {
  const id = Number(req.params.id);
  if (id === req.session.user.id) flash(req, 'error', 'Өөрийгөө устгах боломжгүй.');
  else {
    db.prepare('DELETE FROM users WHERE id = ?').run(id);
    flash(req, 'success', 'Хэрэглэгч устгагдлаа.');
  }
  res.redirect('/admin/users');
});

router.get('/profile', (req, res) => res.render('admin/profile'));

router.post('/profile', checkCsrf, (req, res) => {
  const user = db.prepare('SELECT * FROM users WHERE id = ?').get(req.session.user.id);
  const { current, password, confirm } = req.body;
  if (!verifyPassword(String(current || ''), user.password_hash)) flash(req, 'error', 'Одоогийн нууц үг буруу байна.');
  else if (String(password || '').length < 6) flash(req, 'error', 'Шинэ нууц үг дор хаяж 6 тэмдэгт байна.');
  else if (password !== confirm) flash(req, 'error', 'Шинэ нууц үг таарахгүй байна.');
  else {
    db.prepare('UPDATE users SET password_hash = ? WHERE id = ?').run(hashPassword(password), user.id);
    flash(req, 'success', 'Нууц үг солигдлоо.');
  }
  res.redirect('/admin/profile');
});

// ---------- Settings ----------

const SETTING_KEYS = ['site_name', 'site_tagline', 'site_url', 'fb_page_url', 'fb_app_id', 'fb_page_id', 'fb_page_token'];

router.get('/settings', requireAdmin, (req, res) => res.render('admin/settings'));

router.post('/settings', requireAdmin, checkCsrf, (req, res) => {
  for (const key of SETTING_KEYS) {
    // An empty token field keeps the stored token so it never has to be shown in the page.
    if (key === 'fb_page_token' && !req.body[key]) continue;
    setSetting(key, String(req.body[key] || '').trim());
  }
  setSetting('fb_auto_post', req.body.fb_auto_post ? '1' : '0');
  setSetting('fb_comments', req.body.fb_comments ? '1' : '0');
  if (req.body.clear_token) setSetting('fb_page_token', '');
  flash(req, 'success', 'Тохиргоо хадгалагдлаа.');
  res.redirect('/admin/settings');
});

module.exports = router;

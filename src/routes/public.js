const express = require('express');
const { db, getSettings } = require('../db');
const { articleUrl } = require('../util');

const router = express.Router();

const PUBLISHED = "a.status = 'published' AND a.published_at <= datetime('now')";
const SELECT = `SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
  FROM articles a
  LEFT JOIN categories c ON c.id = a.category_id
  LEFT JOIN users u ON u.id = a.author_id`;
const PAGE_SIZE = 12;

function list(where, params, limit, offset = 0) {
  return db.prepare(`${SELECT} WHERE ${PUBLISHED}${where ? ' AND ' + where : ''}
    ORDER BY a.published_at DESC LIMIT ? OFFSET ?`).all(...params, limit, offset);
}

function count(where, params) {
  return db.prepare(`SELECT COUNT(*) AS n FROM articles a WHERE ${PUBLISHED}${where ? ' AND ' + where : ''}`)
    .get(...params).n;
}

router.use((req, res, next) => {
  res.locals.settings = getSettings();
  res.locals.categories = db.prepare('SELECT * FROM categories ORDER BY position, id').all();
  res.locals.popular = db.prepare(`${SELECT} WHERE ${PUBLISHED} AND a.published_at >= datetime('now', '-7 days')
    ORDER BY a.views DESC LIMIT 6`).all();
  res.locals.breaking = list('a.is_breaking = 1', [], 5);
  res.locals.currentPath = req.path;
  res.locals.meta = {};
  next();
});

router.get('/', (req, res) => {
  const featured = list('a.is_featured = 1', [], 5);
  const exclude = featured.map((a) => a.id);
  const latest = list(exclude.length ? `a.id NOT IN (${exclude.map(() => '?').join(',')})` : '', exclude, 12);
  const sections = res.locals.categories
    .map((c) => ({ category: c, articles: list('a.category_id = ?', [c.id], 4) }))
    .filter((s) => s.articles.length);
  res.render('index', { featured, latest, sections });
});

router.get('/news/:id{/:slug}', (req, res, next) => {
  const article = db.prepare(`${SELECT} WHERE ${PUBLISHED} AND a.id = ?`).get(Number(req.params.id));
  if (!article) return next();
  if (req.params.slug !== article.slug) return res.redirect(301, articleUrl(article));

  db.prepare('UPDATE articles SET views = views + 1 WHERE id = ?').run(article.id);
  article.views += 1;
  const related = article.category_id
    ? list('a.category_id = ? AND a.id != ?', [article.category_id, article.id], 4)
    : [];

  const s = res.locals.settings;
  const base = s.site_url.replace(/\/+$/, '');
  res.locals.meta = {
    title: article.title,
    description: article.excerpt,
    url: base + articleUrl(article),
    image: article.image ? base + article.image : null,
    type: 'article',
    published: article.published_at,
  };
  res.render('article', { article, related });
});

router.get('/category/:slug', (req, res, next) => {
  const category = db.prepare('SELECT * FROM categories WHERE slug = ?').get(req.params.slug);
  if (!category) return next();
  const page = Math.max(1, parseInt(req.query.page, 10) || 1);
  const total = count('a.category_id = ?', [category.id]);
  const articles = list('a.category_id = ?', [category.id], PAGE_SIZE, (page - 1) * PAGE_SIZE);
  res.locals.meta = { title: category.name };
  res.render('list', { heading: category.name, articles, page, pages: Math.ceil(total / PAGE_SIZE), query: '' });
});

router.get('/latest', (req, res) => {
  const page = Math.max(1, parseInt(req.query.page, 10) || 1);
  const total = count('', []);
  const articles = list('', [], PAGE_SIZE, (page - 1) * PAGE_SIZE);
  res.locals.meta = { title: 'Шинэ мэдээ' };
  res.render('list', { heading: 'Шинэ мэдээ', articles, page, pages: Math.ceil(total / PAGE_SIZE), query: '' });
});

router.get('/search', (req, res) => {
  const q = String(req.query.q || '').trim().slice(0, 100);
  const page = Math.max(1, parseInt(req.query.page, 10) || 1);
  let articles = [];
  let total = 0;
  if (q) {
    const like = `%${q.replace(/[%_\\]/g, '\\$&')}%`;
    const where = "(a.title LIKE ? ESCAPE '\\' OR a.excerpt LIKE ? ESCAPE '\\' OR a.content LIKE ? ESCAPE '\\')";
    total = count(where, [like, like, like]);
    articles = list(where, [like, like, like], PAGE_SIZE, (page - 1) * PAGE_SIZE);
  }
  res.locals.meta = { title: q ? `Хайлт: ${q}` : 'Хайлт' };
  res.render('list', { heading: q ? `"${q}" — ${total} үр дүн` : 'Хайлт', articles, page, pages: Math.ceil(total / PAGE_SIZE), query: q });
});

router.get('/rss.xml', (req, res) => {
  const s = res.locals.settings;
  const base = s.site_url.replace(/\/+$/, '');
  const esc = (v) => String(v || '').replace(/[<>&'"]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', "'": '&apos;', '"': '&quot;' }[c]));
  const items = list('', [], 30).map((a) => `<item><title>${esc(a.title)}</title><link>${base}${articleUrl(a)}</link>` +
    `<guid>${base}${articleUrl(a)}</guid><pubDate>${new Date(a.published_at.replace(' ', 'T') + 'Z').toUTCString()}</pubDate>` +
    `<description>${esc(a.excerpt)}</description></item>`).join('');
  res.type('application/rss+xml').send(`<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>` +
    `<title>${esc(s.site_name)}</title><link>${base}</link><description>${esc(s.site_tagline)}</description>${items}</channel></rss>`);
});

module.exports = router;

const path = require('node:path');
const fs = require('node:fs');
const { DatabaseSync } = require('node:sqlite');
const { hashPassword } = require('./auth');

const dataDir = process.env.DATA_DIR || path.join(__dirname, '..', 'data');
fs.mkdirSync(dataDir, { recursive: true });

const db = new DatabaseSync(path.join(dataDir, 'medee.db'));
db.exec('PRAGMA journal_mode = WAL; PRAGMA foreign_keys = ON;');

db.exec(`
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'editor',
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS categories (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  position INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS articles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  slug TEXT NOT NULL,
  excerpt TEXT NOT NULL DEFAULT '',
  content TEXT NOT NULL DEFAULT '',
  image TEXT,
  category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
  author_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
  status TEXT NOT NULL DEFAULT 'draft',
  is_featured INTEGER NOT NULL DEFAULT 0,
  is_breaking INTEGER NOT NULL DEFAULT 0,
  views INTEGER NOT NULL DEFAULT 0,
  fb_post_id TEXT,
  fb_error TEXT,
  published_at TEXT,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  updated_at TEXT NOT NULL DEFAULT (datetime('now'))
);
CREATE INDEX IF NOT EXISTS idx_articles_pub ON articles(status, published_at);
CREATE INDEX IF NOT EXISTS idx_articles_cat ON articles(category_id);
CREATE TABLE IF NOT EXISTS settings (
  key TEXT PRIMARY KEY,
  value TEXT NOT NULL DEFAULT ''
);
CREATE TABLE IF NOT EXISTS sessions (
  sid TEXT PRIMARY KEY,
  data TEXT NOT NULL,
  expires INTEGER NOT NULL
);
`);

const DEFAULT_SETTINGS = {
  site_name: 'Medee.mn',
  site_tagline: 'Шуурхай, бодит мэдээ',
  site_url: process.env.SITE_URL || 'http://localhost:3000',
  fb_page_url: '',
  fb_app_id: '',
  fb_page_id: '',
  fb_page_token: '',
  fb_auto_post: '1',
  fb_comments: '0',
};

const insertSetting = db.prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)');
for (const [k, v] of Object.entries(DEFAULT_SETTINGS)) insertSetting.run(k, v);

if (db.prepare('SELECT COUNT(*) AS n FROM categories').get().n === 0) {
  const cats = [
    ['Улс төр', 'uls-tur'],
    ['Эдийн засаг', 'ediin-zasag'],
    ['Нийгэм', 'niigem'],
    ['Дэлхий', 'delkhii'],
    ['Спорт', 'sport'],
    ['Технологи', 'tekhnologi'],
    ['Урлаг, соёл', 'urlag-soyol'],
  ];
  const ins = db.prepare('INSERT INTO categories (name, slug, position) VALUES (?, ?, ?)');
  cats.forEach(([name, slug], i) => ins.run(name, slug, i));
}

if (db.prepare('SELECT COUNT(*) AS n FROM users').get().n === 0) {
  const username = process.env.ADMIN_USER || 'admin';
  const password = process.env.ADMIN_PASSWORD || 'admin123';
  db.prepare('INSERT INTO users (username, name, password_hash, role) VALUES (?, ?, ?, ?)')
    .run(username, 'Админ', hashPassword(password), 'admin');
  console.log(`Анхны админ хэрэглэгч үүслээ: ${username} / ${password} — нэвтэрсний дараа нууц үгээ солино уу.`);
}

function getSettings() {
  const out = {};
  for (const row of db.prepare('SELECT key, value FROM settings').all()) out[row.key] = row.value;
  return out;
}

function setSetting(key, value) {
  db.prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value')
    .run(key, value);
}

module.exports = { db, getSettings, setSetting };

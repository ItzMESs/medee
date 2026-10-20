const { db, getSettings } = require('./db');
const { articleUrl } = require('./util');

const GRAPH = 'https://graph.facebook.com/v21.0';

function isConfigured(settings = getSettings()) {
  return Boolean(settings.fb_page_id && settings.fb_page_token);
}

// Publishes the article link to the configured Facebook Page feed.
// Facebook builds the preview card from the article's Open Graph tags,
// so site_url must be a public address Facebook can reach.
async function postArticle(article) {
  const s = getSettings();
  if (!isConfigured(s)) throw new Error('Facebook пэйжийн ID болон access token тохируулаагүй байна.');

  const link = s.site_url.replace(/\/+$/, '') + articleUrl(article);
  const message = article.excerpt ? `${article.title}\n\n${article.excerpt}` : article.title;

  const fail = (msg) => {
    db.prepare('UPDATE articles SET fb_error = ? WHERE id = ?').run(msg, article.id);
    return new Error(msg);
  };

  let res;
  try {
    res = await fetch(`${GRAPH}/${encodeURIComponent(s.fb_page_id)}/feed`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ message, link, access_token: s.fb_page_token }),
    });
  } catch (e) {
    throw fail(`Facebook-тэй холбогдож чадсангүй: ${e.message}`);
  }
  const data = await res.json().catch(() => ({}));
  if (!res.ok || !data.id) {
    const msg = (data.error && data.error.message) || `Facebook API алдаа (HTTP ${res.status})`;
    throw fail(msg);
  }
  db.prepare('UPDATE articles SET fb_post_id = ?, fb_error = NULL WHERE id = ?').run(data.id, article.id);
  return data.id;
}

module.exports = { postArticle, isConfigured };

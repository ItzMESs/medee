const sanitizeHtml = require('sanitize-html');

const TRANSLIT = {
  а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'yo', ж: 'j', з: 'z', и: 'i', й: 'i',
  к: 'k', л: 'l', м: 'm', н: 'n', о: 'o', ө: 'u', п: 'p', р: 'r', с: 's', т: 't', у: 'u',
  ү: 'u', ф: 'f', х: 'kh', ц: 'ts', ч: 'ch', ш: 'sh', щ: 'sh', ъ: '', ы: 'y', ь: '', э: 'e',
  ю: 'yu', я: 'ya',
};

function slugify(text) {
  const latin = String(text).toLowerCase().split('').map((c) => (c in TRANSLIT ? TRANSLIT[c] : c)).join('');
  return latin.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 80) || 'medee';
}

function cleanHtml(html) {
  return sanitizeHtml(html || '', {
    allowedTags: sanitizeHtml.defaults.allowedTags.concat(['img', 'h1', 'h2', 'iframe', 'figure', 'figcaption', 'u', 's']),
    allowedAttributes: {
      ...sanitizeHtml.defaults.allowedAttributes,
      img: ['src', 'alt', 'title', 'width', 'height'],
      iframe: ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder'],
      '*': ['class'],
    },
    allowedIframeHostnames: ['www.youtube.com', 'youtube.com', 'www.facebook.com', 'player.vimeo.com'],
  });
}

function stripTags(html) {
  return sanitizeHtml(html || '', { allowedTags: [], allowedAttributes: {} }).replace(/\s+/g, ' ').trim();
}

function excerptFrom(html, len = 200) {
  const text = stripTags(html);
  return text.length > len ? text.slice(0, len).replace(/\s+\S*$/, '') + '…' : text;
}

// SQLite stores UTC as "YYYY-MM-DD HH:MM:SS".
function parseDb(value) {
  if (!value) return null;
  return new Date(value.replace(' ', 'T') + 'Z');
}

function toDb(date) {
  return date.toISOString().slice(0, 19).replace('T', ' ');
}

const TZ = process.env.TZ_DISPLAY || 'Asia/Ulaanbaatar';

function formatDate(value) {
  const d = parseDb(value);
  if (!d) return '';
  const p = new Intl.DateTimeFormat('en-CA', {
    timeZone: TZ, year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false,
  }).formatToParts(d).reduce((o, x) => ((o[x.type] = x.value), o), {});
  return `${p.year}.${p.month}.${p.day} ${p.hour}:${p.minute}`;
}

function timeAgo(value) {
  const d = parseDb(value);
  if (!d) return '';
  const s = Math.floor((Date.now() - d.getTime()) / 1000);
  if (s < 60) return 'Саяхан';
  if (s < 3600) return `${Math.floor(s / 60)} минутын өмнө`;
  if (s < 86400) return `${Math.floor(s / 3600)} цагийн өмнө`;
  if (s < 7 * 86400) return `${Math.floor(s / 86400)} өдрийн өмнө`;
  return formatDate(value);
}

// Value for <input type="datetime-local"> in Ulaanbaatar time.
function toLocalInput(value) {
  const f = formatDate(value);
  return f ? f.replace(/\./g, '-').replace(' ', 'T') : '';
}

// Parses a datetime-local value entered in Ulaanbaatar time (UTC+8) into a DB timestamp.
function fromLocalInput(value) {
  if (!value) return null;
  const d = new Date(value + ':00+08:00');
  return isNaN(d) ? null : toDb(d);
}

function articleUrl(a) {
  return `/news/${a.id}/${a.slug}`;
}

module.exports = { slugify, cleanHtml, stripTags, excerptFrom, formatDate, timeAgo, toDb, toLocalInput, fromLocalInput, articleUrl };

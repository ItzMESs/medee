const crypto = require('node:crypto');

function hashPassword(password) {
  const salt = crypto.randomBytes(16).toString('hex');
  const hash = crypto.scryptSync(password, salt, 64).toString('hex');
  return `${salt}:${hash}`;
}

function verifyPassword(password, stored) {
  const [salt, hash] = String(stored).split(':');
  if (!salt || !hash) return false;
  const test = crypto.scryptSync(password, salt, 64);
  const expected = Buffer.from(hash, 'hex');
  return expected.length === test.length && crypto.timingSafeEqual(expected, test);
}

function requireAuth(req, res, next) {
  if (req.session.user) return next();
  res.redirect('/admin/login');
}

function requireAdmin(req, res, next) {
  if (req.session.user && req.session.user.role === 'admin') return next();
  res.status(403).send('Зөвхөн админ хандах эрхтэй.');
}

// Simple per-session CSRF token, checked on every non-GET admin request.
function csrf(req, res, next) {
  if (!req.session.csrf) req.session.csrf = crypto.randomBytes(24).toString('hex');
  res.locals.csrf = req.session.csrf;
  next();
}

function checkCsrf(req, res, next) {
  const token = (req.body && req.body._csrf) || req.get('x-csrf-token');
  if (token && token === req.session.csrf) return next();
  res.status(403).send('CSRF токен буруу байна. Хуудсаа сэргээгээд дахин оролдоно уу.');
}

module.exports = { hashPassword, verifyPassword, requireAuth, requireAdmin, csrf, checkCsrf };

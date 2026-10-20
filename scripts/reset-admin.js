// Resets (or creates) the admin account from ADMIN_USER / ADMIN_PASSWORD. Run: npm run reset-admin
const { db } = require('../src/db');
const { hashPassword } = require('../src/auth');

const username = process.env.ADMIN_USER;
const password = process.env.ADMIN_PASSWORD;
if (!username || !password || password.length < 6) {
  console.error('ADMIN_USER болон ADMIN_PASSWORD (6+ тэмдэгт) орчны хувьсагчийг тохируулна уу.');
  process.exit(1);
}

const existing = db.prepare('SELECT id FROM users WHERE username = ?').get(username);
if (existing) {
  db.prepare("UPDATE users SET password_hash = ?, role = 'admin' WHERE id = ?").run(hashPassword(password), existing.id);
  console.log(`"${username}" хэрэглэгчийн нууц үг шинэчлэгдлээ.`);
} else {
  db.prepare("INSERT INTO users (username, name, password_hash, role) VALUES (?, ?, ?, 'admin')").run(username, username, hashPassword(password));
  console.log(`"${username}" админ хэрэглэгч үүслээ.`);
}

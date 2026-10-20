const path = require('node:path');
const crypto = require('node:crypto');
const express = require('express');
const session = require('express-session');
const { db } = require('./src/db');
const SqliteStore = require('./src/session-store');
const util = require('./src/util');

const app = express();
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.set('trust proxy', 1);
app.disable('x-powered-by');

app.locals.formatDate = util.formatDate;
app.locals.timeAgo = util.timeAgo;
app.locals.articleUrl = util.articleUrl;

app.use('/static', express.static(path.join(__dirname, 'public'), { maxAge: '7d' }));
app.use('/vendor/quill', express.static(path.join(__dirname, 'node_modules', 'quill', 'dist'), { maxAge: '30d' }));
app.use('/uploads', express.static(process.env.UPLOAD_DIR || path.join(__dirname, 'uploads'), { maxAge: '30d' }));

app.use('/admin', session({
  store: new SqliteStore(db),
  secret: process.env.SESSION_SECRET || crypto.randomBytes(32).toString('hex'),
  resave: false,
  saveUninitialized: false,
  cookie: { httpOnly: true, sameSite: 'lax', secure: 'auto', maxAge: 7 * 86400e3 },
}), require('./src/routes/admin'));

app.use('/', require('./src/routes/public'));

app.use((req, res) => {
  res.status(404);
  if (req.path.startsWith('/admin')) return res.send('Хуудас олдсонгүй');
  res.render('404');
});

app.use((err, req, res, next) => {
  console.error(err);
  res.status(500).send('Серверийн алдаа гарлаа.');
});

const port = Number(process.env.PORT) || 3000;
app.listen(port, () => console.log(`Мэдээний сайт: http://localhost:${port}  |  Админ: http://localhost:${port}/admin`));

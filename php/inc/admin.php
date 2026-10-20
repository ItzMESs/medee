<?php
function admin_dispatch($path, $method)
{
    start_session();
    header('X-Frame-Options: SAMEORIGIN');
    header('Cache-Control: no-store');
    $isPost = $method === 'POST';
    if ($isPost) {
        check_csrf();
    }

    if ($path === '/login') {
        return $isPost ? admin_login_post() : admin_login();
    }
    if (!current_user()) {
        redirect('/admin/login');
    }
    if ($path === '/logout' && $isPost) {
        $_SESSION = [];
        session_destroy();
        redirect('/admin/login');
    }

    if ($path === '/') {
        return admin_dashboard();
    }
    if ($path === '/articles') {
        return $isPost ? admin_article_save(null) : admin_articles();
    }
    if ($path === '/articles/new') {
        return admin_article_form(blank_article());
    }
    if (preg_match('#^/articles/(\d+)(/edit|/delete|/facebook)?$#', $path, $m)) {
        $id = (int) $m[1];
        $action = isset($m[2]) ? $m[2] : '';
        if ($action === '/edit') {
            return admin_article_edit($id);
        }
        if ($isPost && $action === '') {
            return admin_article_save($id);
        }
        if ($isPost && $action === '/delete') {
            return admin_article_delete($id);
        }
        if ($isPost && $action === '/facebook') {
            return admin_article_facebook($id);
        }
    }
    if ($path === '/upload' && $isPost) {
        return admin_upload();
    }
    if ($path === '/categories') {
        return $isPost ? admin_category_create() : admin_categories();
    }
    if (preg_match('#^/categories/(\d+)(/delete)?$#', $path, $m) && $isPost) {
        return empty($m[2]) ? admin_category_update((int) $m[1]) : admin_category_delete((int) $m[1]);
    }
    if ($path === '/pages') {
        return admin_pages();
    }
    if ($path === '/pages/new') {
        return admin_page_form(['id' => null, 'title' => '', 'slug' => '', 'content' => '', 'in_footer' => 1, 'position' => 0]);
    }
    if (preg_match('#^/pages/(\d+)(/delete)?$#', $path, $m)) {
        if (!$isPost) {
            $pg = row('SELECT * FROM pages WHERE id = ?', [(int) $m[1]]);
            return $pg ? admin_page_form($pg) : redirect('/admin/pages');
        }
        return empty($m[2]) ? admin_page_save((int) $m[1]) : admin_page_delete((int) $m[1]);
    }
    if ($path === '/pages/save' && $isPost) {
        return admin_page_save(null);
    }
    if ($path === '/comments') {
        return admin_comments();
    }
    if (preg_match('#^/comments/(\d+)/(approve|delete)$#', $path, $m) && $isPost) {
        return admin_comment_action((int) $m[1], $m[2]);
    }
    if ($path === '/profile') {
        return $isPost ? admin_profile_post() : admin_render('profile');
    }
    if ($path === '/users' || strpos($path, '/users/') === 0 || $path === '/settings' || strpos($path, '/images/') === 0
        || $path === '/ads' || strpos($path, '/ads/') === 0) {
        if (!is_admin()) {
            http_response_code(403);
            exit('Зөвхөн админ хандах эрхтэй.');
        }
        if ($path === '/users') {
            return $isPost ? admin_user_create() : admin_users();
        }
        if (preg_match('#^/users/(\d+)/delete$#', $path, $m) && $isPost) {
            return admin_user_delete((int) $m[1]);
        }
        if ($path === '/settings') {
            return $isPost ? admin_settings_post() : admin_render('settings', ['imageStats' => image_stats()]);
        }
        if ($path === '/images/optimize' && $isPost) {
            return admin_optimize_images();
        }
        if ($path === '/images/watermark' && $isPost) {
            return admin_watermark_images();
        }
        if ($path === '/ads') {
            return admin_ads();
        }
        if ($path === '/ads/new') {
            return admin_ad_form(['id' => null, 'title' => '', 'slot' => 'sidebar', 'kind' => 'image', 'image' => null, 'url' => '',
                'code' => '', 'starts_at' => null, 'ends_at' => null, 'active' => 1]);
        }
        if ($path === '/ads/save' && $isPost) {
            return admin_ad_save(null);
        }
        if (preg_match('#^/ads/(\d+)(/delete)?$#', $path, $m)) {
            if (!$isPost) {
                $ad = row('SELECT * FROM ads WHERE id = ?', [(int) $m[1]]);
                return $ad ? admin_ad_form($ad) : redirect('/admin/ads');
            }
            return empty($m[2]) ? admin_ad_save((int) $m[1]) : admin_ad_delete((int) $m[1]);
        }
    }
    http_response_code(404);
    exit('Хуудас олдсонгүй');
}

function admin_render($template, array $vars = [])
{
    render('admin/' . $template, array_merge([
        'pendingComments' => pending_comment_count(),
        'user' => current_user(),
        'settings' => settings(),
        'flash' => take_flash(),
        'path' => $GLOBALS['route_path'],
    ], $vars));
}

// ---------- Auth ----------

function admin_login($error = null)
{
    if (current_user()) {
        redirect('/admin');
    }
    render('admin/login', ['settings' => settings(), 'error' => $error]);
}

function admin_login_post()
{
    $u = row('SELECT * FROM users WHERE username = ?', [post('username')]);
    if (!$u || !password_verify(isset($_POST['password']) ? (string) $_POST['password'] : '', $u['password_hash'])) {
        usleep(300000);
        http_response_code(401);
        return admin_login('Нэвтрэх нэр эсвэл нууц үг буруу байна.');
    }
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int) $u['id'], 'username' => $u['username'], 'name' => $u['name'], 'role' => $u['role']];
    redirect('/admin');
}

// ---------- Dashboard ----------

function admin_dashboard()
{
    $stats = row("SELECT COUNT(*) AS total,
        COALESCE(SUM(status = 'published'), 0) AS published,
        COALESCE(SUM(status = 'draft'), 0) AS drafts,
        COALESCE(SUM(views), 0) AS views,
        COALESCE(SUM(fb_post_id IS NOT NULL), 0) AS fb
        FROM articles");
    $stats['comments_today'] = (int) q('SELECT COUNT(*) FROM comments WHERE created_at >= ?', [date('Y-m-d 00:00:00')])->fetchColumn();
    $recent = rows('SELECT a.*, c.name AS category_name FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id ORDER BY a.updated_at DESC LIMIT 8');

    $range = get('range') === '7' ? 7 : 30;
    $from = date('Y-m-d', strtotime('-' . ($range - 1) . ' days'));
    $byDay = [];
    foreach (rows('SELECT day, page_views, article_views FROM stats_daily WHERE day >= ?', [$from]) as $r) {
        $byDay[$r['day']] = $r;
    }
    $daily = [];
    for ($i = $range - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $daily[] = [
            'day' => $d,
            'page_views' => isset($byDay[$d]) ? (int) $byDay[$d]['page_views'] : 0,
            'article_views' => isset($byDay[$d]) ? (int) $byDay[$d]['article_views'] : 0,
        ];
    }
    $today = end($daily);
    $yesterday = count($daily) > 1 ? $daily[count($daily) - 2] : ['page_views' => 0];
    $totals = [
        'today' => $today['page_views'],
        'yesterday' => $yesterday['page_views'],
        'range' => array_sum(array_column($daily, 'page_views')),
    ];
    $top = rows("SELECT a.id, a.title, a.slug, a.status, c.name AS category_name, SUM(v.views) AS period_views, a.views AS total_views
        FROM article_views_daily v JOIN articles a ON a.id = v.article_id
        LEFT JOIN categories c ON c.id = a.category_id
        WHERE v.day >= ? GROUP BY a.id, a.title, a.slug, a.status, c.name, a.views
        ORDER BY period_views DESC LIMIT 10", [$from]);
    $allTime = rows("SELECT a.id, a.title, a.slug, a.status, c.name AS category_name, a.views AS total_views
        FROM articles a LEFT JOIN categories c ON c.id = a.category_id
        WHERE a.status = 'published' ORDER BY a.views DESC LIMIT 10");

    admin_render('dashboard', [
        'stats' => $stats, 'recent' => $recent, 'fbReady' => fb_configured(),
        'range' => $range, 'daily' => $daily, 'totals' => $totals, 'top' => $top, 'allTime' => $allTime,
    ]);
}

// ---------- Articles ----------

function admin_articles()
{
    $q = get('q');
    $status = in_array(get('status'), ['published', 'draft'], true) ? get('status') : '';
    $category = (int) get('category', '0');
    $page = max(1, (int) get('page', '1'));
    $size = 20;

    $where = [];
    $params = [];
    if ($q !== '') {
        $where[] = 'a.title LIKE ?';
        $params[] = '%' . addcslashes($q, '%_\\') . '%';
    }
    if ($status !== '') {
        $where[] = 'a.status = ?';
        $params[] = $status;
    }
    if ($category) {
        $where[] = 'a.category_id = ?';
        $params[] = $category;
    }
    $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $total = (int) q("SELECT COUNT(*) FROM articles a $w", $params)->fetchColumn();
    $articles = rows("SELECT a.*, c.name AS category_name, u.name AS author_name FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id LEFT JOIN users u ON u.id = a.author_id
        $w ORDER BY COALESCE(a.published_at, a.created_at) DESC LIMIT $size OFFSET " . (($page - 1) * $size), $params);

    admin_render('articles', [
        'articles' => $articles, 'categories' => all_categories(), 'q' => $q, 'status' => $status,
        'category' => $category, 'page' => $page, 'pages' => (int) ceil($total / $size),
    ]);
}

function blank_article()
{
    return [
        'id' => null, 'title' => '', 'excerpt' => '', 'content' => '', 'status' => 'draft', 'category_id' => null,
        'is_featured' => 0, 'is_breaking' => 0, 'image' => null, 'published_at' => null, 'fb_post_id' => null,
        'fb_error' => null, 'slug' => '',
    ];
}

function admin_article_form(array $article, $error = null)
{
    admin_render('article-form', [
        'article' => $article, 'categories' => all_categories(), 'error' => $error, 'fbReady' => fb_configured(),
        'publishedLocal' => $article['published_at'] ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : '',
    ]);
}

function admin_article_edit($id)
{
    $a = row('SELECT * FROM articles WHERE id = ?', [$id]);
    if (!$a) {
        http_response_code(404);
        exit('Мэдээ олдсонгүй');
    }
    admin_article_form($a);
}

function admin_article_save($id)
{
    $existing = $id ? row('SELECT * FROM articles WHERE id = ?', [$id]) : null;
    if ($id && !$existing) {
        http_response_code(404);
        exit('Мэдээ олдсонгүй');
    }

    $content = clean_html(isset($_POST['content']) ? (string) $_POST['content'] : '');
    $status = post('status') === 'published' ? 'published' : 'draft';
    $pubInput = post('published_at');
    $pubAt = $pubInput !== '' && strtotime($pubInput) ? date('Y-m-d H:i:s', strtotime($pubInput)) : null;
    if (!$pubAt) {
        $pubAt = $existing && $existing['published_at'] ? $existing['published_at'] : ($status === 'published' ? now() : null);
    }
    $data = [
        'title' => mb_substr(post('title'), 0, 250),
        'excerpt' => post('excerpt') !== '' ? mb_substr(post('excerpt'), 0, 400) : excerpt_from($content),
        'content' => $content,
        'category_id' => (int) post('category_id') ?: null,
        'status' => $status,
        'is_featured' => post('is_featured') ? 1 : 0,
        'is_breaking' => post('is_breaking') ? 1 : 0,
        'published_at' => $pubAt,
        'image' => $existing ? $existing['image'] : null,
    ];
    $data['slug'] = slugify($data['title']);

    if ($data['title'] === '') {
        return admin_article_form(array_merge($existing ?: blank_article(), $data), 'Гарчиг заавал оруулна уу.');
    }
    try {
        $upload = save_upload('image');
    } catch (RuntimeException $ex) {
        return admin_article_form(array_merge($existing ?: blank_article(), $data), $ex->getMessage());
    }
    if ($upload) {
        if ($existing) {
            remove_upload($existing['image']);
        }
        $data['image'] = $upload;
    } elseif (post('remove_image') && $existing) {
        remove_upload($existing['image']);
        $data['image'] = null;
    }

    if ($existing) {
        q('UPDATE articles SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, category_id = ?, status = ?,
            is_featured = ?, is_breaking = ?, published_at = ?, updated_at = ? WHERE id = ?', [
            $data['title'], $data['slug'], $data['excerpt'], $data['content'], $data['image'], $data['category_id'],
            $data['status'], $data['is_featured'], $data['is_breaking'], $data['published_at'], now(), $id,
        ]);
    } else {
        q('INSERT INTO articles (title, slug, excerpt, content, image, category_id, author_id, status, is_featured,
            is_breaking, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $data['title'], $data['slug'], $data['excerpt'], $data['content'], $data['image'], $data['category_id'],
            current_user()['id'], $data['status'], $data['is_featured'], $data['is_breaking'], $data['published_at'], now(), now(),
        ]);
        $id = (int) db()->lastInsertId();
    }

    $article = row('SELECT * FROM articles WHERE id = ?', [$id]);
    $type = 'success';
    $message = 'Мэдээ хадгалагдлаа.';
    // Share to the Facebook Page once, the first time an article goes live.
    if (post('post_to_facebook') && $article['status'] === 'published' && !$article['fb_post_id'] && fb_configured()) {
        try {
            fb_post_article($article);
            $message .= ' Facebook пэйж дээр нийтлэгдлээ.';
        } catch (RuntimeException $ex) {
            $type = 'error';
            $message .= ' Гэхдээ Facebook-д нийтлэхэд алдаа гарлаа: ' . $ex->getMessage();
        }
    }
    flash($type, $message);
    redirect('/admin/articles/' . $id . '/edit');
}

function admin_article_delete($id)
{
    $a = row('SELECT * FROM articles WHERE id = ?', [$id]);
    if ($a) {
        remove_upload($a['image']);
        q('DELETE FROM articles WHERE id = ?', [$id]);
        flash('success', 'Мэдээ устгагдлаа.');
    }
    redirect('/admin/articles');
}

function admin_article_facebook($id)
{
    $a = row('SELECT * FROM articles WHERE id = ?', [$id]);
    if (!$a) {
        http_response_code(404);
        exit('Мэдээ олдсонгүй');
    }
    if ($a['status'] !== 'published') {
        flash('error', 'Эхлээд мэдээгээ нийтэлсэн төлөвт оруулна уу.');
    } else {
        try {
            fb_post_article($a);
            flash('success', 'Facebook пэйж дээр нийтлэгдлээ.');
        } catch (RuntimeException $ex) {
            flash('error', 'Facebook алдаа: ' . $ex->getMessage());
        }
    }
    redirect('/admin/articles/' . $id . '/edit');
}

// Images inserted inside the article body from the editor.
function admin_upload()
{
    header('Content-Type: application/json; charset=utf-8');
    try {
        $path = save_upload('image', false);
        if (!$path) {
            throw new RuntimeException('Зураг сонгоно уу.');
        }
        echo json_encode(['url' => media($path)]);
    } catch (RuntimeException $ex) {
        http_response_code(400);
        echo json_encode(['error' => $ex->getMessage()]);
    }
}

// ---------- Categories ----------

function admin_categories()
{
    $cats = rows('SELECT c.*, COUNT(a.id) AS count FROM categories c LEFT JOIN articles a ON a.category_id = c.id
        GROUP BY c.id, c.name, c.slug, c.position ORDER BY c.position, c.id');
    admin_render('categories', ['categories' => $cats]);
}

function admin_category_create()
{
    $name = post('name');
    if ($name !== '') {
        try {
            q('INSERT INTO categories (name, slug, position) VALUES (?, ?, ?)', [$name, slugify(post('slug') ?: $name), (int) post('position')]);
            flash('success', 'Ангилал нэмэгдлээ.');
        } catch (PDOException $ex) {
            flash('error', 'Ийм холбоостой (slug) ангилал аль хэдийн байна.');
        }
    }
    redirect('/admin/categories');
}

function admin_category_update($id)
{
    $name = post('name');
    if ($name !== '') {
        try {
            q('UPDATE categories SET name = ?, slug = ?, position = ? WHERE id = ?', [$name, slugify(post('slug') ?: $name), (int) post('position'), $id]);
            flash('success', 'Ангилал шинэчлэгдлээ.');
        } catch (PDOException $ex) {
            flash('error', 'Ийм холбоостой (slug) ангилал аль хэдийн байна.');
        }
    }
    redirect('/admin/categories');
}

function admin_category_delete($id)
{
    q('DELETE FROM categories WHERE id = ?', [$id]);
    flash('success', 'Ангилал устгагдлаа. Түүнд хамаарах мэдээнүүд ангилалгүй боллоо.');
    redirect('/admin/categories');
}

// ---------- Users ----------

function admin_users()
{
    admin_render('users', ['users' => rows('SELECT id, username, name, role, created_at FROM users ORDER BY id')]);
}

function admin_user_create()
{
    $username = post('username');
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    if ($username === '' || strlen($password) < 6) {
        flash('error', 'Нэвтрэх нэр болон дор хаяж 6 тэмдэгттэй нууц үг оруулна уу.');
    } else {
        try {
            q('INSERT INTO users (username, name, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?)', [
                $username, post('name') ?: $username, password_hash($password, PASSWORD_DEFAULT),
                post('role') === 'admin' ? 'admin' : 'editor', now(),
            ]);
            flash('success', 'Хэрэглэгч нэмэгдлээ.');
        } catch (PDOException $ex) {
            flash('error', 'Ийм нэвтрэх нэртэй хэрэглэгч байна.');
        }
    }
    redirect('/admin/users');
}

function admin_user_delete($id)
{
    if ($id === current_user()['id']) {
        flash('error', 'Өөрийгөө устгах боломжгүй.');
    } else {
        q('DELETE FROM users WHERE id = ?', [$id]);
        flash('success', 'Хэрэглэгч устгагдлаа.');
    }
    redirect('/admin/users');
}

function admin_profile_post()
{
    $u = row('SELECT * FROM users WHERE id = ?', [current_user()['id']]);
    $current = isset($_POST['current']) ? (string) $_POST['current'] : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $confirm = isset($_POST['confirm']) ? (string) $_POST['confirm'] : '';
    if (!password_verify($current, $u['password_hash'])) {
        flash('error', 'Одоогийн нууц үг буруу байна.');
    } elseif (strlen($password) < 6) {
        flash('error', 'Шинэ нууц үг дор хаяж 6 тэмдэгт байна.');
    } elseif ($password !== $confirm) {
        flash('error', 'Шинэ нууц үг таарахгүй байна.');
    } else {
        q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $u['id']]);
        flash('success', 'Нууц үг солигдлоо.');
    }
    redirect('/admin/profile');
}

// ---------- Settings ----------

function admin_settings_post()
{
    foreach (['site_name', 'site_tagline', 'site_url', 'fb_page_url', 'fb_app_id', 'fb_page_id', 'fb_page_token',
        'rate_usd', 'rate_cny', 'rate_rub', 'ga_id', 'google_verification',
        'brand_color', 'footer_about', 'contact_address', 'contact_phone', 'contact_email',
        'social_youtube', 'social_x', 'social_instagram'] as $key) {
        // An empty token field keeps the stored token so it never has to be shown in the page.
        if ($key === 'fb_page_token' && post($key) === '') {
            continue;
        }
        $value = post($key);
        if ($key === 'site_url') {
            $value = rtrim($value, '/');
        } elseif ($key === 'ga_id') {
            $value = strtoupper($value);
            if ($value !== '' && !preg_match('/^G-[A-Z0-9]{4,20}$/', $value)) {
                flash('error', 'Google Analytics ID буруу байна. G-XXXXXXXXXX хэлбэртэй байх ёстой.');
                redirect('/admin/settings');
            }
        } elseif ($key === 'brand_color' && !preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            $value = '#0a5cff';
        } elseif (in_array($key, ['fb_page_url', 'social_youtube', 'social_x', 'social_instagram'], true) && $value !== '' && !safe_url($value)) {
            $value = 'https://' . ltrim($value, '/');
        } elseif ($key === 'google_verification' && preg_match('/content="([^"]+)"/', $value, $m)) {
            // Accept the whole <meta> tag pasted from Search Console.
            $value = $m[1];
        }
        save_setting($key, $value);
    }
    save_setting('fb_auto_post', post('fb_auto_post') ? '1' : '0');
    save_setting('fb_comments', post('fb_comments') ? '1' : '0');
    save_setting('rates_mode', in_array(post('rates_mode'), ['auto', 'manual', 'off'], true) ? post('rates_mode') : 'auto');
    save_setting('weather_enabled', post('weather_enabled') ? '1' : '0');
    save_setting('comments_enabled', post('comments_enabled') ? '1' : '0');
    save_setting('ornaments', post('ornaments') ? '1' : '0');
    save_setting('watermark_enabled', post('watermark_enabled') ? '1' : '0');
    save_setting('watermark_position', in_array(post('watermark_position'), ['br', 'bl', 'tr', 'tl'], true) ? post('watermark_position') : 'br');
    save_setting('watermark_size', (string) max(8, min(40, (int) post('watermark_size'))));
    save_setting('watermark_opacity', (string) max(20, min(100, (int) post('watermark_opacity'))));
    save_setting('comments_moderation', post('comments_moderation') ? '1' : '0');
    if (post('clear_token')) {
        save_setting('fb_page_token', '');
    }
    try {
        $logo = save_upload('logo_file', false, false);
        if ($logo) {
            remove_upload(setting('logo'));
            save_setting('logo', $logo);
        }
    } catch (RuntimeException $ex) {
        flash('error', 'Лого: ' . $ex->getMessage());
        redirect('/admin/settings');
    }
    if (post('remove_logo')) {
        remove_upload(setting('logo'));
        save_setting('logo', '');
    }
    flash('success', 'Тохиргоо хадгалагдлаа.');
    redirect('/admin/settings');
}

// ---------- Images ----------

function image_stats()
{
    $total = 0;
    $missing = 0;
    foreach (rows("SELECT image FROM articles WHERE image IS NOT NULL AND image != ''") as $r) {
        $total++;
        if (!is_file(ROOT . thumb_path($r['image']))) {
            $missing++;
        }
    }
    return ['total' => $total, 'missing' => $missing, 'gd' => gd_available()];
}

// Creates small copies for cover images uploaded before resizing existed. Works in batches
// so a slow shared host never hits the PHP time limit.
function admin_optimize_images()
{
    @set_time_limit(60);
    $start = microtime(true);
    $done = 0;
    foreach (rows("SELECT image FROM articles WHERE image IS NOT NULL AND image != '' ORDER BY id DESC") as $r) {
        if (is_file(ROOT . thumb_path($r['image']))) {
            continue;
        }
        if (optimize_image($r['image'], true)) {
            $done++;
        }
        if (microtime(true) - $start > 20) {
            break;
        }
    }
    $left = image_stats()['missing'];
    flash('success', $done . ' зураг жижигрүүллээ.' . ($left ? ' ' . $left . ' зураг үлдсэн — товчийг дахин дарна уу.' : ''));
    redirect('/admin/settings');
}

// ---------- Comments ----------

function admin_comments()
{
    $status = in_array(get('status'), ['pending', 'approved'], true) ? get('status') : '';
    $page = max(1, (int) get('page', '1'));
    $size = 30;
    $w = $status ? 'WHERE c.status = ?' : '';
    $params = $status ? [$status] : [];
    $total = (int) q("SELECT COUNT(*) FROM comments c $w", $params)->fetchColumn();
    $comments = rows("SELECT c.*, a.title AS article_title, a.slug AS article_slug, a.id AS aid FROM comments c
        JOIN articles a ON a.id = c.article_id $w ORDER BY c.created_at DESC LIMIT $size OFFSET " . (($page - 1) * $size), $params);
    admin_render('comments', ['comments' => $comments, 'status' => $status, 'page' => $page, 'pages' => (int) ceil($total / $size)]);
}

function admin_comment_action($id, $action)
{
    if ($action === 'approve') {
        q("UPDATE comments SET status = 'approved' WHERE id = ?", [$id]);
        flash('success', 'Сэтгэгдэл нийтлэгдлээ.');
    } else {
        q('DELETE FROM comments WHERE id = ?', [$id]);
        flash('success', 'Сэтгэгдэл устгагдлаа.');
    }
    $back = get('status') !== '' ? '/admin/comments?status=' . rawurlencode(get('status')) : '/admin/comments';
    redirect($back);
}

// ---------- Pages (Бидний тухай, Холбоо барих ...) ----------

function admin_pages()
{
    admin_render('pages', ['pages' => rows('SELECT * FROM pages ORDER BY position, id')]);
}

function admin_page_form(array $page, $error = null)
{
    admin_render('page-form', ['page' => $page, 'error' => $error]);
}

function admin_page_save($id)
{
    $data = [
        'title' => mb_substr(post('title'), 0, 250),
        'slug' => slugify(post('slug') ?: post('title')),
        'content' => clean_html(isset($_POST['content']) ? (string) $_POST['content'] : ''),
        'in_footer' => post('in_footer') ? 1 : 0,
        'position' => (int) post('position'),
    ];
    if ($data['title'] === '') {
        return admin_page_form(array_merge(['id' => $id], $data), 'Гарчиг заавал оруулна уу.');
    }
    try {
        if ($id) {
            q('UPDATE pages SET title = ?, slug = ?, content = ?, in_footer = ?, position = ?, updated_at = ? WHERE id = ?',
                [$data['title'], $data['slug'], $data['content'], $data['in_footer'], $data['position'], now(), $id]);
        } else {
            q('INSERT INTO pages (title, slug, content, in_footer, position, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
                [$data['title'], $data['slug'], $data['content'], $data['in_footer'], $data['position'], now()]);
            $id = (int) db()->lastInsertId();
        }
    } catch (PDOException $ex) {
        return admin_page_form(array_merge(['id' => $id], $data), 'Ийм холбоостой (slug) хуудас аль хэдийн байна.');
    }
    flash('success', 'Хуудас хадгалагдлаа.');
    redirect('/admin/pages/' . $id);
}

function admin_page_delete($id)
{
    q('DELETE FROM pages WHERE id = ?', [$id]);
    flash('success', 'Хуудас устгагдлаа.');
    redirect('/admin/pages');
}

// Every uploaded picture used by articles: covers plus images inside the text.
function article_image_paths()
{
    $paths = [];
    foreach (rows('SELECT image, content FROM articles') as $r) {
        if ($r['image'] && strpos($r['image'], '/uploads/') === 0) {
            $paths[$r['image']] = true;
        }
        if (preg_match_all('#src="[^"]*?(/uploads/[^"?]+)"#', $r['content'], $m)) {
            foreach ($m[1] as $p) {
                $paths[$p] = true;
            }
        }
    }
    return array_keys($paths);
}

// Re-applies (or removes) the watermark on existing pictures with the current settings.
// Works from the clean "-orig" copies, so running it twice never doubles the logo.
function admin_watermark_images()
{
    @set_time_limit(90);
    $start = microtime(true);
    $todo = article_image_paths();
    $offset = max(0, (int) post('offset'));
    $on = setting('watermark_enabled') === '1';
    $done = 0;
    for ($i = $offset; $i < count($todo); $i++) {
        $on ? apply_watermark($todo[$i]) : remove_watermark($todo[$i]);
        $done++;
        if (microtime(true) - $start > 25) {
            $i++;
            break;
        }
    }
    $left = count($todo) - $i;
    $_SESSION['watermark_offset'] = $left > 0 ? $i : 0;
    flash('success', $done . ' зураг ' . ($on ? 'дээр лого тавилаа.' : 'дээрээс лого авлаа.')
        . ($left > 0 ? ' ' . $left . ' зураг үлдсэн — товчийг дахин дарна уу.' : ''));
    redirect('/admin/settings#watermark');
}

// ---------- Advertising ----------

function admin_ads()
{
    $ads = rows('SELECT * FROM ads ORDER BY active DESC, created_at DESC');
    admin_render('ads', ['ads' => $ads, 'slots' => ad_slots(), 'now' => now()]);
}

function admin_ad_form(array $ad, $error = null)
{
    $local = function ($v) { return $v ? date('Y-m-d\TH:i', strtotime($v)) : ''; };
    admin_render('ad-form', ['ad' => $ad, 'slots' => ad_slots(), 'error' => $error,
        'startsLocal' => $local($ad['starts_at']), 'endsLocal' => $local($ad['ends_at'])]);
}

function admin_ad_save($id)
{
    $existing = $id ? row('SELECT * FROM ads WHERE id = ?', [$id]) : null;
    $date = function ($key) {
        $v = post($key);
        return $v !== '' && strtotime($v) ? date('Y-m-d H:i:s', strtotime($v)) : null;
    };
    $data = [
        'title' => mb_substr(post('title'), 0, 250),
        'slot' => array_key_exists(post('slot'), ad_slots()) ? post('slot') : 'sidebar',
        'kind' => post('kind') === 'code' ? 'code' : 'image',
        'url' => post('url'),
        'code' => isset($_POST['code']) ? (string) $_POST['code'] : '',
        'starts_at' => $date('starts_at'),
        'ends_at' => $date('ends_at'),
        'active' => post('active') ? 1 : 0,
        'image' => $existing ? $existing['image'] : null,
    ];
    $back = array_merge($existing ?: ['id' => null], $data);
    if ($data['title'] === '') {
        return admin_ad_form($back, 'Нэр заавал оруулна уу.');
    }
    if ($data['url'] !== '' && !safe_url($data['url'])) {
        return admin_ad_form($back, 'Холбоос https:// эсвэл http://-ээр эхлэх ёстой.');
    }
    try {
        // Banners are shown as uploaded: no watermark, no thumbnail.
        $upload = save_upload('image', false, false);
    } catch (RuntimeException $ex) {
        return admin_ad_form($back, $ex->getMessage());
    }
    if ($upload) {
        if ($existing) {
            remove_upload($existing['image']);
        }
        $data['image'] = $upload;
    }
    if ($data['kind'] === 'image' && !$data['image']) {
        return admin_ad_form($back, 'Баннерын зураг хуулна уу.');
    }
    if ($data['kind'] === 'code' && trim($data['code']) === '') {
        return admin_ad_form($back, 'Сурталчилгааны кодоо оруулна уу.');
    }
    $cols = ['title', 'slot', 'kind', 'image', 'url', 'code', 'starts_at', 'ends_at', 'active'];
    $vals = array_map(function ($c) use ($data) { return $data[$c]; }, $cols);
    if ($existing) {
        q('UPDATE ads SET ' . implode(' = ?, ', $cols) . ' = ? WHERE id = ?', array_merge($vals, [$id]));
    } else {
        q('INSERT INTO ads (' . implode(', ', $cols) . ', created_at) VALUES (' . str_repeat('?, ', count($cols)) . '?)', array_merge($vals, [now()]));
        $id = (int) db()->lastInsertId();
    }
    flash('success', 'Сурталчилгаа хадгалагдлаа.');
    redirect('/admin/ads/' . $id);
}

function admin_ad_delete($id)
{
    $ad = row('SELECT * FROM ads WHERE id = ?', [$id]);
    if ($ad) {
        remove_upload($ad['image']);
        q('DELETE FROM ads WHERE id = ?', [$id]);
        flash('success', 'Сурталчилгаа устгагдлаа.');
    }
    redirect('/admin/ads');
}

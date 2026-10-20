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
    if ($path === '/profile') {
        return $isPost ? admin_profile_post() : admin_render('profile');
    }
    if ($path === '/users' || strpos($path, '/users/') === 0 || $path === '/settings') {
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
            return $isPost ? admin_settings_post() : admin_render('settings');
        }
    }
    http_response_code(404);
    exit('Хуудас олдсонгүй');
}

function admin_render($template, array $vars = [])
{
    render('admin/' . $template, array_merge([
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
    $recent = rows('SELECT a.*, c.name AS category_name FROM articles a
        LEFT JOIN categories c ON c.id = a.category_id ORDER BY a.updated_at DESC LIMIT 8');
    admin_render('dashboard', ['stats' => $stats, 'recent' => $recent, 'fbReady' => fb_configured()]);
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
        $path = save_upload('image');
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
    foreach (['site_name', 'site_tagline', 'site_url', 'fb_page_url', 'fb_app_id', 'fb_page_id', 'fb_page_token'] as $key) {
        // An empty token field keeps the stored token so it never has to be shown in the page.
        if ($key === 'fb_page_token' && post($key) === '') {
            continue;
        }
        save_setting($key, $key === 'site_url' ? rtrim(post($key), '/') : post($key));
    }
    save_setting('fb_auto_post', post('fb_auto_post') ? '1' : '0');
    save_setting('fb_comments', post('fb_comments') ? '1' : '0');
    if (post('clear_token')) {
        save_setting('fb_page_token', '');
    }
    flash('success', 'Тохиргоо хадгалагдлаа.');
    redirect('/admin/settings');
}

<?php
define('PAGE_SIZE', 12);

function public_dispatch($path)
{
    if ($path === '/') {
        track_view();
        return page_home();
    }
    if (preg_match('#^/news/(\d+)/react$#', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        return post_reaction((int) $m[1]);
    }
    if (preg_match('#^/news/(\d+)/comment$#', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        return post_comment((int) $m[1]);
    }
    if (preg_match('#^/news/(\d+)(?:/([^/]*))?$#', $path, $m)) {
        return page_article((int) $m[1], isset($m[2]) ? $m[2] : '');
    }
    if (preg_match('#^/category/([a-z0-9-]+)$#', $path, $m)) {
        track_view();
        return page_category($m[1]);
    }
    if ($path === '/latest') {
        track_view();
        return page_list('Шинэ мэдээ', '', []);
    }
    if ($path === '/search') {
        track_view();
        return page_search();
    }
    if ($path === '/rss.xml') {
        return page_rss();
    }
    if ($path === '/push/subscribe' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        return push_subscribe();
    }
    if ($path === '/push/unsubscribe' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        return push_unsubscribe();
    }
    if ($path === '/manifest.webmanifest') {
        return page_manifest();
    }
    if (preg_match('#^/ad/(\d+)$#', $path, $m)) {
        return ad_click((int) $m[1]);
    }
    if (preg_match('#^/p/([a-z0-9-]+)$#', $path, $m)) {
        return page_static($m[1]);
    }
    if ($path === '/sitemap.xml') {
        return page_sitemap();
    }
    if ($path === '/robots.txt') {
        return page_robots();
    }
    not_found();
}

function current_page()
{
    return max(1, (int) get('page', '1'));
}

function page_home()
{
    // Two big "Онцлох мэдээ" cards; topped up with the newest articles when fewer are featured.
    $featured = list_articles('a.is_featured = 1', [], 2);
    if (count($featured) < 2) {
        $have = array_map(function ($a) { return (int) $a['id']; }, $featured);
        $fill = list_articles($have ? 'a.id NOT IN (' . implode(',', $have) . ')' : '', [], 2 - count($featured));
        $featured = array_merge($featured, $fill);
    }
    $ids = array_map(function ($a) { return (int) $a['id']; }, $featured);
    $latest = list_articles($ids ? 'a.id NOT IN (' . implode(',', $ids) . ')' : '', [], 8);
    $sections = [];
    foreach (all_categories() as $c) {
        $list = list_articles('a.category_id = ?', [$c['id']], 5);
        // Thin categories are already covered by "Шинэ мэдээ"; a 1-2 story section looks empty.
        if (count($list) >= 3) {
            $sections[] = ['category' => $c, 'articles' => $list];
        }
    }
    render('index', public_context(compact('featured', 'latest', 'sections')));
}

function page_article($id, $slug)
{
    $article = row(article_select() . ' WHERE ' . published_where() . ' AND a.id = ?', [now(), $id]);
    if (!$article) {
        not_found();
    }
    if ($slug !== $article['slug']) {
        redirect(article_path($article), 301);
    }
    if (track_view($id)) {
        q('UPDATE articles SET views = views + 1 WHERE id = ?', [$id]);
        $article['views']++;
    }
    $related = $article['category_id']
        ? list_articles('a.category_id = ? AND a.id != ?', [$article['category_id'], $id], 3)
        : [];
    $meta = [
        'title' => $article['title'],
        'description' => $article['excerpt'],
        'url' => absolute_url(article_path($article)),
        'image' => $article['image'] ? absolute_url($article['image']) : null,
        'type' => 'article',
        'published' => date('c', strtotime($article['published_at'])),
    ];
    $meta['jsonld'] = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => mb_substr($article['title'], 0, 110),
        'description' => $article['excerpt'],
        'image' => $meta['image'] ? [$meta['image']] : [],
        'datePublished' => date('c', strtotime($article['published_at'])),
        'dateModified' => date('c', strtotime($article['updated_at'])),
        'mainEntityOfPage' => $meta['url'],
        'author' => ['@type' => 'Person', 'name' => $article['author_name'] ?: setting('site_name')],
        'publisher' => ['@type' => 'Organization', 'name' => setting('site_name'), 'url' => absolute_url('/')],
    ];
    $comments = [];
    if (setting('comments_enabled') === '1') {
        $comments = rows("SELECT name, body, created_at FROM comments WHERE article_id = ? AND status = 'approved'
            ORDER BY created_at DESC LIMIT 200", [$id]);
    }
    $commentNotice = get('c');
    // ~200 words a minute; Mongolian words are long, so this errs on the generous side.
    $words = count(preg_split('/\s+/u', strip_html($article['content']), -1, PREG_SPLIT_NO_EMPTY));
    $readMinutes = max(1, (int) ceil($words / 200));
    $isArticle = true;
    $reactions = reaction_counts($id);
    $myReaction = my_reaction($id);
    render('article', public_context(compact('article', 'related', 'meta', 'comments', 'commentNotice', 'readMinutes', 'isArticle', 'reactions', 'myReaction')));
}

function page_list($heading, $where, array $params, array $extra = [])
{
    $page = current_page();
    $total = count_articles($where, $params);
    $articles = list_articles($where, $params, PAGE_SIZE, ($page - 1) * PAGE_SIZE);
    $pages = (int) ceil($total / PAGE_SIZE);
    $meta = ['title' => $heading];
    render('list', public_context(array_merge(compact('heading', 'articles', 'page', 'pages', 'meta'), $extra)));
}

function page_category($slug)
{
    $c = row('SELECT * FROM categories WHERE slug = ?', [$slug]);
    if (!$c) {
        not_found();
    }
    page_list($c['name'], 'a.category_id = ?', [$c['id']]);
}

function page_search()
{
    $q = mb_substr(get('q'), 0, 100);
    if ($q === '') {
        return page_list('Хайлт', '1 = 0', [], ['query' => '']);
    }
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where = '(a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)';
    $params = [$like, $like, $like];
    $total = count_articles($where, $params);
    page_list('"' . $q . '" — ' . $total . ' үр дүн', $where, $params, ['query' => $q]);
}

function page_rss()
{
    $x = function ($v) { return htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8'); };
    header('Content-Type: application/rss+xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>';
    echo '<title>' . $x(setting('site_name')) . '</title><link>' . $x(absolute_url('/')) . '</link>';
    echo '<description>' . $x(setting('site_tagline')) . '</description>';
    foreach (list_articles('', [], 30) as $a) {
        $link = $x(absolute_url(article_path($a)));
        echo '<item><title>' . $x($a['title']) . '</title><link>' . $link . '</link><guid>' . $link . '</guid>';
        echo '<pubDate>' . date('r', strtotime($a['published_at'])) . '</pubDate>';
        echo '<description>' . $x($a['excerpt']) . '</description></item>';
    }
    echo '</channel></rss>';
}

function post_comment($id)
{
    $article = row('SELECT id, slug FROM articles a WHERE ' . published_where() . ' AND a.id = ?', [now(), $id]);
    if (!$article || setting('comments_enabled') !== '1') {
        not_found();
    }
    $back = function ($code) use ($article) {
        redirect(article_path($article) . '?c=' . $code . '#comments');
    };

    // Honeypot field is hidden from people; bots fill it in.
    if (post('website') !== '' || !comment_token_valid($id, post('token'))) {
        $back('spam');
    }
    $name = mb_substr(preg_replace('/\s+/u', ' ', post('name')), 0, 60);
    $body = trim(preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', isset($_POST['body']) ? (string) $_POST['body'] : '')));
    if (mb_strlen($body) < 2) {
        $back('empty');
    }
    if (mb_strlen($body) > 1000) {
        $back('long');
    }
    $ip = client_ip_hash();
    $recent = (int) q('SELECT COUNT(*) FROM comments WHERE ip_hash = ? AND created_at > ?',
        [$ip, date('Y-m-d H:i:s', time() - 30)])->fetchColumn();
    if ($recent > 0) {
        $back('wait');
    }
    // Comments with several links usually are spam, so they wait for a moderator.
    $links = preg_match_all('#https?://|www\.#i', $body);
    $status = setting('comments_moderation') === '1' || $links > 1 ? 'pending' : 'approved';
    q('INSERT INTO comments (article_id, name, body, status, ip_hash, created_at) VALUES (?, ?, ?, ?, ?, ?)',
        [$id, $name !== '' ? $name : 'Зочин', $body, $status, $ip, now()]);
    $back($status === 'pending' ? 'pending' : 'ok');
}

function page_sitemap()
{
    $x = function ($v) { return htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8'); };
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";
    echo '<url><loc>' . $x(absolute_url('/')) . '</loc><changefreq>hourly</changefreq><priority>1.0</priority></url>' . "\n";
    foreach (all_categories() as $c) {
        echo '<url><loc>' . $x(absolute_url('/category/' . $c['slug'])) . '</loc><changefreq>hourly</changefreq><priority>0.7</priority></url>' . "\n";
    }
    $newsCutoff = time() - 2 * 86400;
    foreach (list_articles('', [], 5000) as $a) {
        $ts = strtotime($a['updated_at'] > $a['published_at'] ? $a['updated_at'] : $a['published_at']);
        echo '<url><loc>' . $x(absolute_url(article_path($a))) . '</loc><lastmod>' . date('c', $ts) . '</lastmod>';
        // Google News only reads articles from the last two days.
        if (strtotime($a['published_at']) >= $newsCutoff) {
            echo '<news:news><news:publication><news:name>' . $x(setting('site_name')) . '</news:name><news:language>mn</news:language></news:publication>'
                . '<news:publication_date>' . date('c', strtotime($a['published_at'])) . '</news:publication_date>'
                . '<news:title>' . $x($a['title']) . '</news:title></news:news>';
        }
        echo "</url>\n";
    }
    echo '</urlset>';
}

function page_robots()
{
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\n";
    echo 'Disallow: ' . url('/admin') . "\n";
    echo 'Disallow: ' . url('/search') . "\n\n";
    echo 'Sitemap: ' . absolute_url('/sitemap.xml') . "\n";
}

function page_static($slug)
{
    $page = row('SELECT * FROM pages WHERE slug = ?', [$slug]);
    if (!$page) {
        not_found();
    }
    track_view();
    $meta = ['title' => $page['title'], 'description' => excerpt_from($page['content'], 160), 'url' => absolute_url('/p/' . $page['slug'])];
    render('page', public_context(compact('page', 'meta')));
}

// Lets phones add the site to the home screen (needed for push on iPhone).
function page_manifest()
{
    header('Content-Type: application/manifest+json; charset=utf-8');
    echo json_encode([
        'name' => setting('site_name'),
        'short_name' => mb_substr(setting('site_name'), 0, 12),
        'start_url' => url('/'),
        'scope' => url('/'),
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => brand_color(),
        'icons' => [
            ['src' => url('/assets/img/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => url('/assets/img/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

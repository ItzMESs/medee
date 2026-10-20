<?php
define('PAGE_SIZE', 12);

function public_dispatch($path)
{
    if ($path === '/') {
        return page_home();
    }
    if (preg_match('#^/news/(\d+)(?:/([^/]*))?$#', $path, $m)) {
        return page_article((int) $m[1], isset($m[2]) ? $m[2] : '');
    }
    if (preg_match('#^/category/([a-z0-9-]+)$#', $path, $m)) {
        return page_category($m[1]);
    }
    if ($path === '/latest') {
        return page_list('Шинэ мэдээ', '', []);
    }
    if ($path === '/search') {
        return page_search();
    }
    if ($path === '/rss.xml') {
        return page_rss();
    }
    not_found();
}

function current_page()
{
    return max(1, (int) get('page', '1'));
}

function page_home()
{
    $featured = list_articles('a.is_featured = 1', [], 5);
    $ids = array_map(function ($a) { return (int) $a['id']; }, $featured);
    $latest = list_articles($ids ? 'a.id NOT IN (' . implode(',', $ids) . ')' : '', [], 12);
    $sections = [];
    foreach (all_categories() as $c) {
        $list = list_articles('a.category_id = ?', [$c['id']], 4);
        if ($list) {
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
    q('UPDATE articles SET views = views + 1 WHERE id = ?', [$id]);
    $article['views']++;
    $related = $article['category_id']
        ? list_articles('a.category_id = ? AND a.id != ?', [$article['category_id'], $id], 4)
        : [];
    $meta = [
        'title' => $article['title'],
        'description' => $article['excerpt'],
        'url' => absolute_url(article_path($article)),
        'image' => $article['image'] ? absolute_url($article['image']) : null,
        'type' => 'article',
        'published' => date('c', strtotime($article['published_at'])),
    ];
    render('article', public_context(compact('article', 'related', 'meta')));
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

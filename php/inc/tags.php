<?php
// Tags: free-form topics ("Сонгууль", "Наадам") shared by many articles. Each tag gets its own
// page at /tag/{slug}, which Google indexes as a topic page.

define('MAX_TAGS_PER_ARTICLE', 10);

function tag_path(array $t)
{
    return '/tag/' . $t['slug'];
}

// "сонгууль, #Наадам ,  Ханш" → ['сонгууль', 'Наадам', 'Ханш'] (trimmed, de-duplicated, capped).
function parse_tag_input($input)
{
    $out = [];
    foreach (preg_split('/[,;\n]+/u', (string) $input) as $name) {
        $name = trim(preg_replace('/\s+/u', ' ', ltrim(trim($name), '#')));
        $name = mb_substr($name, 0, 40);
        if ($name === '' || isset($out[mb_strtolower($name)])) {
            continue;
        }
        $out[mb_strtolower($name)] = $name;
        if (count($out) >= MAX_TAGS_PER_ARTICLE) {
            break;
        }
    }
    return array_values($out);
}

// Finds a tag by name (case-insensitive) or creates it with a unique slug.
function tag_id_for($name)
{
    $found = row('SELECT id FROM tags WHERE LOWER(name) = ?', [mb_strtolower($name)]);
    if ($found) {
        return (int) $found['id'];
    }
    $base = slugify($name);
    $slug = $base;
    for ($i = 2; row('SELECT id FROM tags WHERE slug = ?', [$slug]); $i++) {
        $slug = $base . '-' . $i;
    }
    q('INSERT INTO tags (name, slug, created_at) VALUES (?, ?, ?)', [$name, $slug, now()]);
    return (int) db()->lastInsertId();
}

function save_article_tags($articleId, array $names)
{
    q('DELETE FROM article_tags WHERE article_id = ?', [$articleId]);
    foreach ($names as $name) {
        q('INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)', [$articleId, tag_id_for($name)]);
    }
}

function article_tags($articleId)
{
    return rows('SELECT t.* FROM tags t JOIN article_tags at ON at.tag_id = t.id WHERE at.article_id = ? ORDER BY t.name', [$articleId]);
}

// Tags with the most published articles, for the sidebar.
function popular_tags($limit = 15)
{
    static $cache = [];
    if (!isset($cache[$limit])) {
        $cache[$limit] = rows('SELECT t.name, t.slug, COUNT(*) AS cnt FROM tags t
            JOIN article_tags at ON at.tag_id = t.id
            JOIN articles a ON a.id = at.article_id
            WHERE ' . published_where() . '
            GROUP BY t.id, t.name, t.slug ORDER BY cnt DESC, t.name LIMIT ' . (int) $limit, [now()]);
    }
    return $cache[$limit];
}

// Articles sharing the most tags with this one, topped up from its category.
function related_articles(array $article, array $tags, $limit = 3)
{
    $found = [];
    if ($tags) {
        $ids = implode(',', array_map('intval', array_column($tags, 'id')));
        $found = rows(article_select() . ' JOIN (SELECT article_id, COUNT(*) AS shared FROM article_tags
            WHERE tag_id IN (' . $ids . ') GROUP BY article_id) r ON r.article_id = a.id
            WHERE ' . published_where() . ' AND a.id != ?
            ORDER BY r.shared DESC, a.published_at DESC LIMIT ' . (int) $limit, [now(), $article['id']]);
    }
    if (count($found) < $limit && $article['category_id']) {
        $skip = array_merge([(int) $article['id']], array_map('intval', array_column($found, 'id')));
        $found = array_merge($found, list_articles('a.category_id = ? AND a.id NOT IN (' . implode(',', $skip) . ')',
            [$article['category_id']], $limit - count($found)));
    }
    return $found;
}

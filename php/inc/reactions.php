<?php
// Emoji reactions under articles: one per visitor (cookie), which they can change or take back.

function reaction_types()
{
    return [
        'like' => ['👍', 'Таалагдлаа'],
        'love' => ['❤️', 'Хайртай'],
        'haha' => ['😂', 'Инээдтэй'],
        'wow' => ['😮', 'Гайхмаар'],
        'sad' => ['😢', 'Харамсалтай'],
        'angry' => ['😡', 'Уурлаж байна'],
    ];
}

function reaction_counts($articleId)
{
    $counts = array_fill_keys(array_keys(reaction_types()), 0);
    foreach (rows('SELECT type, COUNT(*) AS n FROM article_reactions WHERE article_id = ? GROUP BY type', [$articleId]) as $r) {
        if (isset($counts[$r['type']])) {
            $counts[$r['type']] = (int) $r['n'];
        }
    }
    return $counts;
}

function reaction_voter()
{
    return isset($_COOKIE['rv']) && preg_match('/^[a-f0-9]{32}$/', $_COOKIE['rv']) ? $_COOKIE['rv'] : null;
}

function my_reaction($articleId)
{
    $voter = reaction_voter();
    if (!$voter) {
        return null;
    }
    $r = row('SELECT type FROM article_reactions WHERE article_id = ? AND voter = ?', [$articleId, $voter]);
    return $r ? $r['type'] : null;
}

// POST /news/{id}/react  type=like|love|...  (same type again removes the reaction)
function post_reaction($articleId)
{
    header('Content-Type: application/json; charset=utf-8');
    $type = post('type');
    $exists = row('SELECT id FROM articles a WHERE ' . published_where() . ' AND a.id = ?', [now(), $articleId]);
    // Only accept votes sent from this site's own pages.
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST) : null;
    $sameSite = $origin === null || $origin === (isset($_SERVER['HTTP_HOST']) ? preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']) : '');
    if (!$exists || !isset(reaction_types()[$type]) || is_bot() || !$sameSite) {
        http_response_code(400);
        echo json_encode(['error' => 'bad request']);
        return;
    }
    $voter = reaction_voter();
    if (!$voter) {
        $voter = bin2hex(random_bytes(16));
        setcookie('rv', $voter, ['expires' => time() + 5 * 365 * 86400, 'path' => base_path() . '/', 'samesite' => 'Lax', 'httponly' => true]);
    }
    $current = row('SELECT type FROM article_reactions WHERE article_id = ? AND voter = ?', [$articleId, $voter]);
    if ($current && $current['type'] === $type) {
        q('DELETE FROM article_reactions WHERE article_id = ? AND voter = ?', [$articleId, $voter]);
        $mine = null;
    } else {
        q('INSERT INTO article_reactions (article_id, voter, type, created_at) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE type = VALUES(type), created_at = VALUES(created_at)', [$articleId, $voter, $type, now()]);
        $mine = $type;
    }
    echo json_encode(['counts' => reaction_counts($articleId), 'mine' => $mine]);
}

<?php
// Advertising slots. Each slot shows one live ad, picked at random when several share it.

function ad_slots()
{
    return [
        'top' => ['Толгойн доор (өргөн баннер)', '1200 × 150'],
        'home' => ['Нүүр хуудасны дунд', '1200 × 200'],
        'sidebar' => ['Хажуу талд', '300 × 250 эсвэл 300 × 600'],
        'article' => ['Мэдээн дотор (3-р догол мөрийн дараа)', '728 × 250'],
        'article_end' => ['Мэдээний төгсгөлд', '728 × 250'],
    ];
}

function live_ads($slot)
{
    static $cache = [];
    if (!isset($cache[$slot])) {
        $now = now();
        $cache[$slot] = rows('SELECT * FROM ads WHERE slot = ? AND active = 1
            AND (starts_at IS NULL OR starts_at <= ?) AND (ends_at IS NULL OR ends_at >= ?)', [$slot, $now, $now]);
    }
    return $cache[$slot];
}

// HTML for one ad in $slot ('' when none is running). Counts an impression for real visitors.
function ad_slot($slot)
{
    $ads = live_ads($slot);
    if (!$ads) {
        return '';
    }
    $ad = $ads[array_rand($ads)];
    if (!is_bot()) {
        q('UPDATE ads SET views = views + 1 WHERE id = ?', [$ad['id']]);
    }
    $inner = '';
    if ($ad['kind'] === 'code') {
        $inner = $ad['code'];
    } elseif ($ad['image']) {
        $img = '<img src="' . e(media($ad['image'])) . '" alt="' . e($ad['title']) . '" loading="lazy">';
        $inner = $ad['url'] !== ''
            ? '<a href="' . url('/ad/' . $ad['id']) . '" target="_blank" rel="sponsored noopener">' . $img . '</a>'
            : $img;
    }
    if ($inner === '') {
        return '';
    }
    return '<div class="ad ad-' . e($slot) . '"><span class="ad-label">Сурталчилгаа</span>' . $inner . '</div>';
}

// Counts the click, then sends the reader on to the advertiser.
function ad_click($id)
{
    $ad = row('SELECT id, url FROM ads WHERE id = ?', [$id]);
    if (!$ad || !safe_url($ad['url'])) {
        not_found();
    }
    if (!is_bot()) {
        q('UPDATE ads SET clicks = clicks + 1 WHERE id = ?', [$id]);
    }
    header('Location: ' . $ad['url'], true, 302);
    exit;
}

// Inserts $insert after the $n-th closing </p> of $html (or at the end when shorter).
function insert_after_paragraph($html, $n, $insert)
{
    if ($insert === '') {
        return $html;
    }
    $pos = 0;
    for ($i = 0; $i < $n; $i++) {
        $found = stripos($html, '</p>', $pos);
        if ($found === false) {
            return $html . $insert;
        }
        $pos = $found + 4;
    }
    return substr($html, 0, $pos) . $insert . substr($html, $pos);
}

<?php
// Advertising slots. Each slot shows one live ad, picked at random when several share it.

function ad_slots()
{
    return [
        'top' => ['Толгойн доор (өргөн баннер)', '1600 × 200 (8:1)'],
        'home' => ['Нүүр хуудасны дунд', '1600 × 270 (6:1)'],
        'sidebar' => ['Хажуу талд', '720 × 600 эсвэл 720 × 1440'],
        'article' => ['Мэдээн дотор (3-р догол мөрийн дараа)', '1456 × 500'],
        'article_end' => ['Мэдээний төгсгөлд', '1456 × 500'],
        'list' => ['Жагсаалтын дунд (ангилал, шошго, хайлт)', '1456 × 500'],
        'mobile_sticky' => ['Утасны доод наалттай баннер (зөвхөн утсанд)', '640 × 100 (6.4:1)'],
    ];
}

// Google AdSense publisher ID ("ca-pub-1234567890123456"), or '' when not set up.
function adsense_client()
{
    $id = setting('adsense_client');
    return preg_match('/^ca-pub-\d{10,20}$/', $id) ? $id : '';
}

// An AdSense ad unit for $slot, used where no direct (sold) ad is running.
function adsense_unit($slot)
{
    $client = adsense_client();
    $unit = setting('adsense_slot_' . $slot);
    if ($client === '' || !preg_match('/^\d{6,20}$/', (string) $unit)) {
        return '';
    }
    return '<ins class="adsbygoogle" style="display:block" data-ad-client="' . e($client) . '" data-ad-slot="' . e($unit) . '"'
        . ($slot === 'article' ? ' data-ad-layout="in-article" data-ad-format="fluid"' : ' data-ad-format="auto" data-full-width-responsive="true"')
        . '></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
}

// ads.txt: Google's line for the publisher ID plus any extra lines from other ad networks.
function ads_txt()
{
    $lines = [];
    if (($client = adsense_client()) !== '') {
        $lines[] = 'google.com, ' . substr($client, 3) . ', DIRECT, f08c47fec0942fa0';
    }
    foreach (preg_split('/\r?\n/', setting('ads_txt_extra')) as $line) {
        if (trim($line) !== '') {
            $lines[] = trim($line);
        }
    }
    return $lines;
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
        $unit = adsense_unit($slot);
        return $unit === '' ? '' : '<div class="ad ad-' . e($slot) . ' ad-adsense"><span class="ad-label">Сурталчилгаа</span>' . $unit . '</div>';
    }
    $ad = $ads[array_rand($ads)];
    if (!is_bot()) {
        q('UPDATE ads SET views = views + 1 WHERE id = ?', [$ad['id']]);
        ad_stat($ad['id'], 'views');
    }
    $inner = '';
    if ($ad['kind'] === 'code') {
        $inner = $ad['code'];
    } elseif ($ad['image']) {
        $img = ad_picture($ad, $slot === 'top' ? 'eager' : 'lazy');
        $inner = $ad['url'] !== ''
            ? '<a href="' . url('/ad/' . $ad['id']) . '" target="_blank" rel="sponsored noopener">' . $img . '</a>'
            : $img;
    }
    if ($inner === '') {
        return '';
    }
    return '<div class="ad ad-' . e($slot) . '"><span class="ad-label">Сурталчилгаа</span>' . $inner . '</div>';
}

// Banner <img>, with the phone-sized image (if any) swapped in on small screens. Width and
// height are set from the file so the page does not jump while the banner loads.
function ad_picture(array $ad, $loading = 'lazy')
{
    $size = function ($path) {
        $info = $path && is_file(ROOT . $path) ? @getimagesize(ROOT . $path) : false;
        return $info ? ' width="' . (int) $info[0] . '" height="' . (int) $info[1] . '"' : '';
    };
    $img = '<img src="' . e(media($ad['image'])) . '" alt="' . e($ad['title']) . '"' . $size($ad['image']) . ' loading="' . $loading . '">';
    if (empty($ad['image_mobile'])) {
        return $img;
    }
    return '<picture><source media="(max-width: 760px)" srcset="' . e(media($ad['image_mobile'])) . '"' . $size($ad['image_mobile']) . '>' . $img . '</picture>';
}

function ad_stat($id, $field)
{
    $field = $field === 'clicks' ? 'clicks' : 'views';
    q("INSERT INTO ad_stats_daily (ad_id, day, $field) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE $field = $field + 1", [$id, date('Y-m-d')]);
}

// The sticky phone banner is only rendered for phones, so desktop page views are not counted.
function is_mobile_request()
{
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    return (bool) preg_match('/Mobi|Android|iPhone|iPod/i', $ua) && !preg_match('/iPad|Tablet/i', $ua);
}

function sticky_ad()
{
    if (!is_mobile_request()) {
        return '';
    }
    $html = ad_slot('mobile_sticky');
    if ($html === '') {
        return '';
    }
    return '<div class="ad-sticky" hidden>' . $html . '<button type="button" class="ad-sticky-close" aria-label="Сурталчилгааг хаах">×</button></div>';
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
        ad_stat($id, 'clicks');
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

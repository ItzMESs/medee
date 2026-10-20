<?php
// Currency rates and Ulaanbaatar weather for the top bar. Remote data is cached in the
// settings table so a page view never waits on the remote API more than once per TTL.

function http_get_json($url, $timeout = 4)
{
    if (!function_exists('curl_init')) {
        return null;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_USERAGENT => 'Nationalrbc.com/1.0',
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $status !== 200) {
        return null;
    }
    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

// Returns cached data for $key, refreshing it via $fetch() when older than $ttl seconds.
// A failed refresh keeps the old data and waits 10 minutes before trying again.
function cached_remote($key, $ttl, callable $fetch)
{
    $raw = setting('cache_' . $key);
    $cache = $raw !== '' ? json_decode($raw, true) : null;
    if (!is_array($cache)) {
        $cache = ['at' => 0, 'tried' => 0, 'data' => null];
    }
    $fresh = $cache['data'] !== null && time() - $cache['at'] < $ttl;
    $recentlyTried = time() - $cache['tried'] < 600;
    if (!$fresh && !$recentlyTried) {
        $cache['tried'] = time();
        $data = $fetch();
        if ($data !== null) {
            $cache['data'] = $data;
            $cache['at'] = time();
        }
        save_setting('cache_' . $key, json_encode($cache));
    }
    return $cache['data'];
}

function parse_rates(array $json)
{
    if (empty($json['rates']['MNT']) || empty($json['rates']['CNY']) || empty($json['rates']['RUB'])) {
        return null;
    }
    $mnt = (float) $json['rates']['MNT'];
    return [
        'USD' => round($mnt, 2),
        'CNY' => round($mnt / (float) $json['rates']['CNY'], 2),
        'RUB' => round($mnt / (float) $json['rates']['RUB'], 2),
    ];
}

function currency_rates()
{
    $mode = setting('rates_mode');
    if ($mode === 'off') {
        return null;
    }
    if ($mode === 'manual') {
        $rates = [];
        foreach (['USD', 'CNY', 'RUB'] as $c) {
            $v = (float) str_replace([',', ' '], ['.', ''], setting('rate_' . strtolower($c)));
            if ($v > 0) {
                $rates[$c] = $v;
            }
        }
        return $rates ?: null;
    }
    return cached_remote('rates', 6 * 3600, function () {
        $json = http_get_json('https://open.er-api.com/v6/latest/USD');
        return $json ? parse_rates($json) : null;
    });
}

// WMO weather codes used by Open-Meteo.
function weather_label($code, $isDay = true)
{
    $c = (int) $code;
    if ($c === 0) return [$isDay ? '☀️' : '🌙', 'Цэлмэг'];
    if ($c <= 2) return [$isDay ? '🌤️' : '☁️', 'Багавтар үүлтэй'];
    if ($c === 3) return ['☁️', 'Үүлэрхэг'];
    if ($c === 45 || $c === 48) return ['🌫️', 'Манантай'];
    if ($c >= 51 && $c <= 57) return ['🌦️', 'Шиврээ бороо'];
    if (($c >= 61 && $c <= 67) || ($c >= 80 && $c <= 82)) return ['🌧️', 'Бороотой'];
    if (($c >= 71 && $c <= 77) || $c === 85 || $c === 86) return ['🌨️', 'Цастай'];
    if ($c >= 95) return ['⛈️', 'Аадар бороо'];
    return ['🌡️', ''];
}

// One-word description for the narrow header slot.
function weather_short($code)
{
    $c = (int) $code;
    if ($c === 0) return 'Цэлмэг';
    if ($c <= 2) return 'Үүлтэй';
    if ($c === 3) return 'Үүлэрхэг';
    if ($c === 45 || $c === 48) return 'Манан';
    if ($c >= 51 && $c <= 57) return 'Шиврээ';
    if (($c >= 61 && $c <= 67) || ($c >= 80 && $c <= 82)) return 'Бороо';
    if (($c >= 71 && $c <= 77) || $c === 85 || $c === 86) return 'Цас';
    if ($c >= 95) return 'Аадар';
    return '';
}

function parse_weather(array $json)
{
    if (!isset($json['current']['temperature_2m'])) {
        return null;
    }
    return [
        'temp' => (int) round((float) $json['current']['temperature_2m']),
        'code' => isset($json['current']['weather_code']) ? (int) $json['current']['weather_code'] : -1,
        'is_day' => !isset($json['current']['is_day']) || (int) $json['current']['is_day'] === 1,
    ];
}

function ub_weather()
{
    if (setting('weather_enabled') === '0') {
        return null;
    }
    return cached_remote('weather', 1800, function () {
        $json = http_get_json('https://api.open-meteo.com/v1/forecast?latitude=47.9184&longitude=106.9177'
            . '&current=temperature_2m,weather_code,is_day&timezone=Asia%2FUlaanbaatar');
        return $json ? parse_weather($json) : null;
    });
}

function format_rate($v)
{
    return $v >= 100 ? number_format($v, 0, '.', ',') : number_format($v, 2, '.', ',');
}

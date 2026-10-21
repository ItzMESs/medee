<?php
// Inline SVG icons (stroke = currentColor) so the theme colour applies everywhere.
function icon($name, $size = 22)
{
    $paths = [
        'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'cloud' => '<path d="M7 18h10a4 4 0 0 0 .6-7.96A6 6 0 0 0 6.1 9.5 4.25 4.25 0 0 0 7 18z"/>',
        'snow' => '<path d="M12 2v20M4.9 7l14.2 10M4.9 17L19.1 7"/>',
        'rain' => '<path d="M7 14h10a4 4 0 0 0 .6-7.96A6 6 0 0 0 6.1 5.5 4.25 4.25 0 0 0 7 14z"/><path d="M8 18l-1 3M12 18l-1 3M16 18l-1 3"/>',
        'coins' => '<circle cx="9" cy="9" r="6"/><path d="M15.5 9.5a6 6 0 1 1-6 6"/><path d="M9 6v6M7 8h3.5a1.5 1.5 0 0 1 0 3H7"/>',
        'up' => '<path d="M6 13l6-6 6 6M6 19l6-6 6 6"/>',
        'prev' => '<path d="M15 5l-7 7 7 7"/>',
        'next' => '<path d="M9 5l7 7-7 7"/>',
        'link' => '<path d="M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1"/><path d="M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1"/>',
        'chev' => '<path d="M9 6l6 6-6 6"/>',
        'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
    ];
    $fills = [
        'facebook' => '<path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.87.25-1.46 1.5-1.46h1.55V4.47A20 20 0 0 0 14.3 4.3c-2.2 0-3.8 1.35-3.8 3.8v2.4H8v3h2.5V21z"/>',
        'x' => '<path d="M17.6 3h3.1l-6.8 7.8L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L1.8 3h6.4l4.4 5.8zm-1.1 16.2h1.7L7.4 4.7H5.6z"/>',
        'youtube' => '<path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15V9l5.2 3z"/>',
        'instagram' => '<path d="M12 7.3A4.7 4.7 0 1 0 16.7 12 4.7 4.7 0 0 0 12 7.3zm0 7.7a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm6-7.9a1.1 1.1 0 1 1-1.1-1.1A1.1 1.1 0 0 1 18 7.1zM21.9 8.2a5.4 5.4 0 0 0-1.5-3.9 5.4 5.4 0 0 0-3.9-1.5C15 2.7 9 2.7 7.5 2.8a5.4 5.4 0 0 0-3.9 1.5 5.4 5.4 0 0 0-1.5 3.9C2 9.7 2 14.3 2.1 15.8a5.4 5.4 0 0 0 1.5 3.9 5.4 5.4 0 0 0 3.9 1.5c1.5.1 7.5.1 9 0a5.4 5.4 0 0 0 3.9-1.5 5.4 5.4 0 0 0 1.5-3.9c.1-1.5.1-6.1 0-7.6zm-2.1 9.4a3 3 0 0 1-1.7 1.7c-1.2.5-4 .4-5.3.4s-4.1.1-5.3-.4a3 3 0 0 1-1.7-1.7c-.5-1.2-.4-4-.4-5.3s-.1-4.1.4-5.3a3 3 0 0 1 1.7-1.7c1.2-.5 4-.4 5.3-.4s4.1-.1 5.3.4a3 3 0 0 1 1.7 1.7c.5 1.2.4 4 .4 5.3s.1 4.1-.4 5.3z"/>',
        'messenger' => '<path d="M12 2C6.4 2 2 6.1 2 11.7a9.6 9.6 0 0 0 3.3 7.3v3.4l3.1-1.7a10.9 10.9 0 0 0 3.6.6c5.6 0 10-4.1 10-9.6S17.6 2 12 2zm1 12.9l-2.5-2.7-5 2.7 5.5-5.8 2.6 2.7 4.9-2.7z"/>',
    ];
    if (isset($fills[$name])) {
        return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">' . $fills[$name] . '</svg>';
    }
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . (isset($paths[$name]) ? $paths[$name] : '') . '</svg>';
}

function weather_icon_name($code)
{
    $c = (int) $code;
    if ($c <= 1) return 'sun';
    if (($c >= 51 && $c <= 67) || ($c >= 80 && $c <= 82) || $c >= 95) return 'rain';
    if (($c >= 71 && $c <= 77) || $c === 85 || $c === 86) return 'snow';
    return 'cloud';
}

// Mongolian ornaments (өлзий, эвэр угалз) drawn in currentColor.
function ornament($name, $class = '')
{
    $svg = [
        'ulzii' => '<svg class="orn ' . $class . '" viewBox="0 0 48 48" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" transform="rotate(45 24 24)">'
            . '<path d="M13 17V13H17M31 13H35V17M35 31V35H31M17 35H13V31"/>'
            . '<path d="M17 13V35M24 13V35M31 13V35M13 17H35M13 24H35M13 31H35"/>'
            . '<path d="M13 17A5.6 5.6 0 1 1 17 13M31 13A5.6 5.6 0 1 1 35 17M35 31A5.6 5.6 0 1 1 31 35M17 35A5.6 5.6 0 1 1 13 31"/></g></svg>',
        'ugalz' => '<svg class="orn ' . $class . '" viewBox="0 0 200 100" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">'
            . '<path d="M100 92C100 62 84 40 62 40C42 40 32 56 40 68C47 78 62 72 60 62C58 55 50 56 50 61"/>'
            . '<path d="M100 92C100 62 116 40 138 40C158 40 168 56 160 68C153 78 138 72 140 62C142 55 150 56 150 61"/>'
            . '<path d="M100 78C96 50 74 22 46 26C24 30 14 50 22 62"/><path d="M100 78C104 50 126 22 154 26C176 30 186 50 178 62"/>'
            . '<path d="M100 92V60M100 60C100 44 92 36 100 26C108 36 100 44 100 60"/></g></svg>',
    ];
    return isset($svg[$name]) ? $svg[$name] : '';
}

function ornaments_enabled()
{
    return setting('ornaments') !== '0';
}

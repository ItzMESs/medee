<?php
// Image optimisation with GD: phone photos are rotated upright, shrunk to MAX_IMAGE_SIDE,
// and a small "-sm" copy is made for article cards. Without GD the original is kept.
define('MAX_IMAGE_SIDE', 1600);
define('THUMB_WIDTH', 480);

function gd_available()
{
    return function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled');
}

function thumb_path($path)
{
    return preg_replace('/(\.[a-z0-9]+)$/i', '-sm$1', (string) $path);
}

// URL of the small copy when it exists, otherwise of the original.
function thumb_url($path)
{
    if (!$path) {
        return '';
    }
    $small = thumb_path($path);
    return is_file(ROOT . $small) ? media($small) : media($path);
}

function load_image($file, $type)
{
    switch ($type) {
        case IMAGETYPE_JPEG:
            return @imagecreatefromjpeg($file);
        case IMAGETYPE_PNG:
            return @imagecreatefrompng($file);
        case IMAGETYPE_WEBP:
            return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false;
    }
    return false;
}

function save_image($img, $file, $type)
{
    switch ($type) {
        case IMAGETYPE_JPEG:
            imageinterlace($img, true);
            return imagejpeg($img, $file, 82);
        case IMAGETYPE_PNG:
            return imagepng($img, $file, 7);
        case IMAGETYPE_WEBP:
            return function_exists('imagewebp') ? imagewebp($img, $file, 80) : false;
    }
    return false;
}

function resized_copy($src, $w, $h, $type)
{
    $dst = imagecreatetruecolor($w, $h);
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
    return $dst;
}

// Rotates JPEGs taken on phones according to their EXIF orientation.
function apply_exif_orientation($img, $file)
{
    if (!function_exists('exif_read_data')) {
        return [$img, false];
    }
    $exif = @exif_read_data($file);
    $o = isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;
    $angles = [3 => 180, 6 => -90, 8 => 90];
    if (!isset($angles[$o])) {
        return [$img, false];
    }
    $rotated = imagerotate($img, $angles[$o], 0);
    if (!$rotated) {
        return [$img, false];
    }
    imagedestroy($img);
    return [$rotated, true];
}

// Optimises an uploaded image in place (by public path like /uploads/2026/10/x.jpg).
function optimize_image($path, $withThumb = true)
{
    $file = ROOT . $path;
    if (!gd_available() || !is_file($file)) {
        return false;
    }
    $info = @getimagesize($file);
    // GIFs may be animated; very large images could exhaust shared-hosting memory.
    if (!$info || $info[2] === IMAGETYPE_GIF || $info[0] * $info[1] > 40000000) {
        return false;
    }
    if ((int) ini_get('memory_limit') > 0 && (int) ini_get('memory_limit') < 256) {
        @ini_set('memory_limit', '256M');
    }
    $type = $info[2];
    $img = load_image($file, $type);
    if (!$img) {
        return false;
    }
    $changed = false;
    if ($type === IMAGETYPE_JPEG) {
        list($img, $changed) = apply_exif_orientation($img, $file);
    }

    $w = imagesx($img);
    $h = imagesy($img);
    $scale = min(1, MAX_IMAGE_SIDE / max($w, $h));
    if ($scale < 1) {
        $resized = resized_copy($img, (int) round($w * $scale), (int) round($h * $scale), $type);
        imagedestroy($img);
        $img = $resized;
        $changed = true;
    }
    // Re-encode large files even when the size is fine; phone JPEGs are often 3-5MB.
    if ($changed || filesize($file) > 400 * 1024) {
        $tmp = $file . '.tmp';
        if (save_image($img, $tmp, $type) && filesize($tmp) > 0 && ($changed || filesize($tmp) < filesize($file))) {
            rename($tmp, $file);
        } else {
            @unlink($tmp);
        }
    }

    if ($withThumb) {
        $w = imagesx($img);
        $h = imagesy($img);
        $tw = min(THUMB_WIDTH, $w);
        $thumb = resized_copy($img, $tw, (int) round($h * $tw / $w), $type);
        save_image($thumb, ROOT . thumb_path($path), $type);
        imagedestroy($thumb);
    }
    imagedestroy($img);
    clearstatcache();
    return true;
}

// ---------- Watermark ----------
// The clean picture is kept as "name-orig.ext" so the logo can be re-applied with new
// settings (or removed) without ever stamping it twice.

function orig_path($path)
{
    return preg_replace('/(\.[a-z0-9]+)$/i', '-orig$1', (string) $path);
}

// White logo on dark corners, the colour logo on bright ones; an uploaded logo is used as is.
function watermark_logo_file($brightCorner = false)
{
    $custom = setting('logo');
    if ($custom !== '' && is_file(ROOT . $custom)) {
        return ROOT . $custom;
    }
    $color = ROOT . '/assets/img/nationx-logo.png';
    $white = ROOT . '/assets/img/nationx-logo-white.png';
    if ($brightCorner && is_file($color)) {
        return $color;
    }
    return is_file($white) ? $white : null;
}

// Average brightness (0-255) of a rectangle, sampled on a coarse grid.
function region_brightness($img, $x, $y, $w, $h)
{
    $sum = 0;
    $n = 0;
    $step = max(1, (int) floor(min($w, $h) / 12));
    for ($j = $y; $j < $y + $h; $j += $step) {
        for ($i = $x; $i < $x + $w; $i += $step) {
            $c = imagecolorat($img, $i, $j);
            $sum += 0.299 * (($c >> 16) & 0xFF) + 0.587 * (($c >> 8) & 0xFF) + 0.114 * ($c & 0xFF);
            $n++;
        }
    }
    return $n ? $sum / $n : 0;
}

// Copy of $img with its alpha scaled by $opacity (0..1); GD's merge functions ignore alpha.
function fade_image($img, $opacity)
{
    $w = imagesx($img);
    $h = imagesy($img);
    $out = imagecreatetruecolor($w, $h);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $c = imagecolorat($img, $x, $y);
            $a = ($c >> 24) & 0x7F;
            $na = (int) round(127 - (127 - $a) * $opacity);
            imagesetpixel($out, $x, $y, ($na << 24) | ($c & 0xFFFFFF));
        }
    }
    return $out;
}

function apply_watermark($path)
{
    $file = ROOT . $path;
    if (!gd_available() || setting('watermark_enabled') !== '1' || !is_file($file)) {
        return false;
    }
    $info = @getimagesize($file);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true) || $info[0] < 300) {
        return false;
    }
    if (!watermark_logo_file()) {
        return false;
    }
    $orig = ROOT . orig_path($path);
    if (!is_file($orig) && !copy($file, $orig)) {
        return false;
    }
    $type = $info[2];
    $base = load_image($orig, $type);
    if (!$base) {
        return false;
    }
    imagealphablending($base, true);

    $W = imagesx($base);
    $H = imagesy($base);
    $size = max(8, min(40, (int) setting('watermark_size'))) / 100;
    $opacity = max(20, min(100, (int) setting('watermark_opacity'))) / 100;
    $lw = max(90, (int) round($W * $size));
    $m = (int) round(min($W, $H) * 0.035);
    $pos = (string) setting('watermark_position');
    $estH = (int) round($lw * 0.2);
    $x = strpos($pos, 'l') !== false ? $m : $W - $lw - $m;
    $y = substr($pos, 0, 1) === 't' ? $m : $H - $estH - $m;

    $bright = region_brightness($base, max(0, $x), max(0, $y), min($lw, $W - $x), min($estH, $H - $y)) > 165;
    $logo = @imagecreatefrompng(watermark_logo_file($bright));
    if (!$logo) {
        imagedestroy($base);
        return false;
    }
    $lh = (int) round(imagesy($logo) * $lw / imagesx($logo));
    $y = substr($pos, 0, 1) === 't' ? $m : $H - $lh - $m;
    $mark = resized_copy($logo, $lw, $lh, IMAGETYPE_PNG);
    imagedestroy($logo);

    // A soft dark shadow keeps the white logo readable; the colour logo needs none.
    if (!$bright) {
        $shadow = resized_copy($mark, $lw, $lh, IMAGETYPE_PNG);
        imagefilter($shadow, IMG_FILTER_BRIGHTNESS, -255);
        for ($i = 0; $i < 4; $i++) {
            imagefilter($shadow, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $shadow = fade_image($shadow, 0.55 * $opacity);
        $off = max(1, (int) round($lh * 0.04));
        imagecopy($base, $shadow, $x + $off, $y + $off, 0, 0, $lw, $lh);
        imagedestroy($shadow);
    }

    $mark = fade_image($mark, $opacity);
    imagecopy($base, $mark, $x, $y, 0, 0, $lw, $lh);
    imagedestroy($mark);

    $ok = save_image($base, $file, $type);
    imagedestroy($base);
    clearstatcache();
    return $ok;
}

// Puts the clean picture back (used when the watermark is switched off).
function remove_watermark($path)
{
    $orig = ROOT . orig_path($path);
    if (!is_file($orig)) {
        return false;
    }
    $ok = copy($orig, ROOT . $path);
    clearstatcache();
    return $ok;
}

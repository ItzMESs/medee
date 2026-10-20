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

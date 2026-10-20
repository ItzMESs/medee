<?php
// Sample articles (with generated cover images when GD is available) so a fresh site isn't empty.
function insert_demo_articles(PDO $pdo, $authorId)
{
    $samples = [
        ['uls-tur', 'УИХ-ын намрын чуулган өнөөдөр эхэллээ', 'Улсын Их Хурлын ээлжит бус чуулганаар төсвийн тодотгол, хууль тогтоомжийн шинэчлэлийн асуудлыг хэлэлцэнэ.', 1, 1],
        ['ediin-zasag', 'Төгрөгийн ханш сүүлийн долоо хоногт тогтвортой байлаа', 'Монголбанкны мэдээллээр гадаад валютын албан ханш өмнөх долоо хоногтой харьцуулахад бага зэрэг чангарсан байна.', 1, 0],
        ['niigem', 'Нийслэлд өвлийн бэлтгэл ажил 90 хувьтай байна', 'Дулаан, цахилгааны шугам сүлжээний засвар үйлчилгээний ажлыг энэ сарын эцэс гэхэд бүрэн дуусгахаар төлөвлөжээ.', 1, 0],
        ['sport', 'Монголын бөхчүүд дэлхийн аваргаас гурван медаль хүртлээ', 'Чөлөөт бөхийн дэлхийн аварга шалгаруулах тэмцээнд манай тамирчид алт, мөнгө, хүрэл медаль хүртэв.', 1, 1],
        ['tekhnologi', 'Улаанбаатарт 5G сүлжээний хамрах хүрээ өргөжлөө', 'Үүрэн холбооны операторууд нийслэлийн бүх дүүрэгт 5G сүлжээг нэвтрүүлэх ажлыг эрчимжүүлж байна.', 1, 0],
        ['delkhii', 'Дэлхийн цаг уурын бага хурал шинэ тохиролцоонд хүрлээ', 'Оролцогч орнууд хүлэмжийн хийн ялгарлыг бууруулах шинэ зорилтыг баталсан байна.', 0, 0],
        ['urlag-soyol', 'Улсын драмын театр шинэ улирлаа нээлээ', 'Энэ улиралд сонгодог болон орчин үеийн нийт 12 жүжгийг үзэгчдэд хүргэнэ.', 0, 0],
        ['ediin-zasag', 'Уул уурхайн экспорт өмнөх оноос 12 хувиар өслөө', 'Нүүрс, зэсийн баяжмалын экспортын хэмжээ нэмэгдсэн нь гадаад худалдааны тэнцэлд эерэг нөлөө үзүүлжээ.', 0, 0],
        ['niigem', 'Сургуулиудад хоёр ээлжийн хичээллэлтийг багасгана', 'Боловсролын яамнаас шинэ сургуулийн барилгуудыг ашиглалтад оруулснаар ачааллыг бууруулна гэж мэдээллээ.', 0, 0],
        ['sport', 'Сагсан бөмбөгийн дээд лигийн шинэ улирал эхэллээ', 'Энэ улиралд нийт 10 баг аваргын төлөө өрсөлдөнө.', 0, 0],
        ['tekhnologi', 'Монгол стартапууд олон улсын хөрөнгө оруулалт татлаа', 'Санхүүгийн технологийн чиглэлийн гурван компани нийт 5 сая ам.долларын хөрөнгө оруулалт авчээ.', 0, 0],
        ['delkhii', 'Азийн орнуудын эдийн засгийн өсөлт хүлээлтээс давлаа', 'Азийн хөгжлийн банкны тайланд бүс нутгийн эдийн засаг 4.8 хувиар өснө гэж таамаглажээ.', 0, 0],
    ];
    $colors = [[30, 58, 138, 59, 130, 246], [6, 95, 70, 16, 185, 129], [124, 45, 18, 249, 115, 22], [88, 28, 135, 168, 85, 247],
        [15, 23, 42, 71, 85, 105], [22, 78, 99, 6, 182, 212], [131, 24, 67, 236, 72, 153], [113, 63, 18, 234, 179, 8],
        [20, 83, 45, 132, 204, 22], [127, 29, 29, 239, 68, 68], [49, 46, 129, 99, 102, 241], [19, 78, 74, 20, 184, 166]];

    $cats = [];
    foreach ($pdo->query('SELECT id, slug FROM categories') as $c) {
        $cats[$c['slug']] = (int) $c['id'];
    }
    $ins = $pdo->prepare("INSERT INTO articles (title, slug, excerpt, content, image, category_id, author_id, status,
        is_featured, is_breaking, views, published_at, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'published', ?, ?, ?, ?, ?, ?)");

    foreach ($samples as $i => $s) {
        $image = demo_image($i + 1, $colors[$i % count($colors)]);
        $content = '<p>' . htmlspecialchars($s[2]) . '</p><p>Энэ бол жишээ мэдээ. Админ панелаас засаж, устгаж эсвэл шинэ мэдээ нэмж болно.</p>'
            . '<h2>Дэлгэрэнгүй</h2><p>Холбогдох албан тушаалтнууд энэ асуудлаар дараагийн долоо хоногт нэмэлт мэдээлэл өгөхөө мэдэгдлээ.</p>'
            . '<blockquote>"Бид иргэдэд бодит, шуурхай мэдээллийг хүргэхийг зорьж байна."</blockquote>';
        $at = date('Y-m-d H:i:s', time() - (int) ($i * 2.5 * 3600));
        $ins->execute([$s[1], demo_slug($s[1]), $s[2], $content, $image, isset($cats[$s[0]]) ? $cats[$s[0]] : null, $authorId,
            $s[3], $s[4], mt_rand(50, 3000), $at, $at, $at]);
    }
}

function demo_slug($title)
{
    if (function_exists('slugify')) {
        return slugify($title);
    }
    require_once __DIR__ . '/functions.php';
    return slugify($title);
}

function demo_image($n, array $c)
{
    if (!function_exists('imagecreatetruecolor') || !is_writable(UPLOAD_DIR)) {
        return null;
    }
    $w = 1200;
    $h = 630;
    $img = imagecreatetruecolor($w, $h);
    for ($x = 0; $x < $w; $x++) {
        $t = $x / $w;
        $col = imagecolorallocate($img, (int) ($c[0] + ($c[3] - $c[0]) * $t), (int) ($c[1] + ($c[4] - $c[1]) * $t), (int) ($c[2] + ($c[5] - $c[2]) * $t));
        imageline($img, $x, 0, $x, $h, $col);
    }
    $light = imagecolorallocatealpha($img, 255, 255, 255, 110);
    imagefilledellipse($img, 1050, 130, 520, 520, $light);
    imagefilledellipse($img, 230, 600, 320, 320, $light);
    $dark = imagecolorallocatealpha($img, 0, 0, 0, 105);
    for ($x = 0; $x < $w; $x += 90) {
        imagefilledrectangle($img, $x, 450, $x + 60, $h, $dark);
    }
    $dir = UPLOAD_DIR . '/demo';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $file = $dir . '/demo-' . $n . '.jpg';
    $ok = imagejpeg($img, $file, 80);
    imagedestroy($img);
    if (!$ok) {
        return null;
    }
    require_once __DIR__ . '/images.php';
    optimize_image('/uploads/demo/demo-' . $n . '.jpg', true);
    return '/uploads/demo/demo-' . $n . '.jpg';
}

<?php
// Сайтын агуулгыг data/ хавтсанд JSON хэлбэрээр хадгална (өгөгдлийн сан шаардлагагүй).
// Файлууд .php өргөтгөлтэй, эхэндээ exit-тэй тул вэбээс шууд уншигдахгүй.

const DATA_DIR = __DIR__ . '/../data';
const UPLOAD_DIR = __DIR__ . '/../uploads';

if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
}

// Мөр хоорондын текстийг догол мөр болгон хэвлэнэ
function paragraphs($text) {
    $out = '';
    foreach (preg_split('/\R{2,}/', trim((string) $text)) as $p) {
        if ($p !== '') $out .= '<p>' . nl2br(e($p)) . '</p>';
    }
    return $out;
}

function json_read($name, $default) {
    $file = DATA_DIR . "/$name.json.php";
    if (!is_file($file)) return $default;
    $raw = file_get_contents($file);
    $raw = substr($raw, strpos($raw, "\n") + 1);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $default;
}

function json_write($name, $data) {
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
    $file = DATA_DIR . "/$name.json.php";
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (file_put_contents($tmp, "<?php exit; ?>\n" . $json, LOCK_EX) === false) return false;
    return rename($tmp, $file);
}

function store_defaults() {
    return [
        'settings' => [
            'name'        => 'КОМПАНИЙН НЭР ХК',
            'logo_title'  => 'COMPANY NAME',
            'logo_sub'    => 'JOINT STOCK COMPANY',
            'logo'        => 'assets/img/logo.svg',
            'phone'       => '+976 7000-0000',
            'email'       => 'info@example.mn',
            'address'     => 'Монгол улс, Улаанбаатар хот, Сонгинохайрхан дүүрэг, 4-р хороо',
            'facebook'    => 'https://www.facebook.com/',
            'english_url' => '#',
            'mail_to'     => 'info@example.mn',
            'developer'   => '',
        ],
        'home' => [
            'about_title' => 'БИДНИЙ ТУХАЙ',
            'about_text'  => 'Манай компани нь Монгол орны баруун хязгаарт материаллаг бааз, тоног төхөөрөмж, техник хэрэгсэл, машин механизмын баазыг бүрдүүлж, дадлага туршлагатай боловсон хүчний нөөцийг бэхжүүлэн, оновчтой менежментээр ханган ажиллаж байна. Бүтээн байгуулалтынхаа хэмжээг жилээс жилд өсгөн нэмэгдүүлж авто замын салбар болон уурхайд хөрс хуулалт, эрдэс баялгийн олборлолт зэрэг чиглэлүүдээр үйл ажиллагаагаа өргөжүүлэн тэргүүлэх байр сууриа эзлэн ажиллаж байна.',
            'about_image' => 'assets/img/about.svg',
            'video_title' => 'КОМПАНИЙН ТАНИЛЦУУЛГА БИЧЛЭГ',
            'video_file'  => '',
            'video_url'   => '',
            'video_poster'=> 'assets/img/video-poster.svg',
        ],
        'about' => [
            'heading' => 'КОМПАНИЙН ТАНИЛЦУУЛГА',
            'image'   => 'assets/img/about.svg',
            'text'    => "Манай компани нь 1995 онд үүсгэн байгуулагдсан бөгөөд авто зам барилга, зам арчлалт, уул уурхайн гэрээт олборлолтын чиглэлээр үйл ажиллагаа явуулдаг үндэсний компани юм.\n\nБид орчин үеийн техник технологи, туршлагатай мэргэжилтнүүд, аюулгүй ажиллагааны өндөр стандартаар харилцагчдадаа чанартай үйлчилгээ үзүүлдэг.",
            'vision'  => 'Монгол улсын уул уурхайн үйлдвэрлэлийн салбарын шинэчлэлд манлайлагч байж, тэргүүлэх орон зайг эзэлнэ.',
            'mission' => 'Чанартай, аюулгүй, хариуцлагатай ажлаар харилцагч, ажилтан, нийгэмдээ үнэ цэнийг бүтээнэ.',
            'values'  => 'Аюулгүй ажиллагаа, багийн ажил, хариуцлага, тасралтгүй хөгжил.',
        ],
        'careers' => [
            'policy' => 'Бид ажилтнуудынхаа ур чадварыг тасралтгүй хөгжүүлж, аюулгүй, эрүүл ажлын байр, өрсөлдөхүйц цалин хөлс, нийгмийн баталгааг хангахыг эрхэмлэдэг.',
        ],
        'slides' => [
            ['id' => 's1', 'image' => 'assets/img/hero-1.svg', 'title' => 'АЛСЫН ХАРАА', 'text' => 'Монгол улсын уул уурхайн үйлдвэрлэлийн салбарын шинэчлэлд манлайлагч байж, тэргүүлэх орон зайг эзэлнэ.'],
            ['id' => 's2', 'image' => 'assets/img/hero-2.svg', 'title' => 'ЭРХЭМ ЗОРИЛГО', 'text' => 'Чанартай, аюулгүй, хариуцлагатай ажлаар харилцагч, ажилтан, нийгэмдээ үнэ цэнийг бүтээнэ.'],
            ['id' => 's3', 'image' => 'assets/img/hero-1.svg', 'title' => 'ҮНЭТ ЗҮЙЛС', 'text' => 'Аюулгүй ажиллагаа, багийн ажил, хариуцлага, тасралтгүй хөгжил.'],
        ],
        'services' => [
            ['id' => 'o1', 'title' => 'УУЛ УУРХАЙ', 'image' => 'assets/img/mining.svg', 'text' => 'Манай компани 2014 оноос эхлэн уурхайн хөрс хуулалт, нүүрс олборлолтын төслийг хэрэгжүүлэн ажиллаж байна. Орчин үеийн техник, туршлагатай боловсон хүчнээр үйлдвэрлэлийн өндөр бүтээмжийг хангадаг.'],
            ['id' => 'o2', 'title' => 'ЗАМ АРЧЛАЛТ', 'image' => 'assets/img/road-maintenance.svg', 'text' => 'Уурхайгаас боомт хүртэлх 300 гаруй км урттай авто замын засвар, арчлалтын ажлыг жилийн турш тасралтгүй гүйцэтгэж байна.'],
            ['id' => 'o3', 'title' => 'ЗАМ БАРИЛГА', 'image' => 'assets/img/road-construction.svg', 'text' => 'Аймаг, сумдын чиглэлийн 100 гаруй км хатуу хучилттай авто зам барилгын ажлыг өөрийн хөрөнгө, техникээр богино хугацаанд гүйцэтгэсэн туршлагатай.'],
        ],
        'news' => [
            ['id' => 'n1', 'date' => '2026-10-15', 'title' => 'Зам арчлалтын өвлийн бэлтгэл ажил эхэллээ', 'image' => 'assets/img/road-maintenance.svg', 'body' => 'Өвлийн улиралд замын хөдөлгөөний аюулгүй байдлыг хангах зорилгоор цас цэвэрлэгээний техник, материалын бэлтгэлийг бүрэн хангаж эхэллээ.'],
            ['id' => 'n2', 'date' => '2026-09-02', 'title' => 'Шинэ техник, тоног төхөөрөмж нэмэгдлээ', 'image' => 'assets/img/mining.svg', 'body' => 'Уурхайн үйлдвэрлэлийн хүчин чадлыг нэмэгдүүлэх зорилгоор шинэ техник, тоног төхөөрөмж ашиглалтад орууллаа.'],
            ['id' => 'n3', 'date' => '2026-07-20', 'title' => 'Хатуу хучилттай замын шинэ хэсгийг ашиглалтад орууллаа', 'image' => 'assets/img/road-construction.svg', 'body' => 'Төлөвлөсөн хугацаанаас өмнө замын шинэ хэсгийг ашиглалтад хүлээлгэн өглөө.'],
        ],
        'docs' => [],
        'jobs' => [
            ['id' => 'j1', 'title' => 'Хүнд машин механизмын оператор', 'location' => 'Уурхай', 'type' => 'Үндсэн', 'body' => ''],
            ['id' => 'j2', 'title' => 'Авто засварчин', 'location' => 'Уурхай', 'type' => 'Үндсэн', 'body' => ''],
            ['id' => 'j3', 'title' => 'Зам барилгын инженер', 'location' => 'Төсөл', 'type' => 'Үндсэн', 'body' => ''],
        ],
    ];
}

function store_load() {
    $defaults = store_defaults();
    $data = json_read('content', []);
    foreach ($defaults as $key => $value) {
        if (!array_key_exists($key, $data)) {
            $data[$key] = $value;
        } elseif (is_array($value) && !array_is_list_compat($value)) {
            $data[$key] = array_replace($value, (array) $data[$key]);
        }
    }
    return $data;
}

function store_save($data) {
    return json_write('content', $data);
}

// PHP 7.4-д array_is_list байхгүй
function array_is_list_compat($a) {
    return $a === [] || array_keys($a) === range(0, count($a) - 1);
}

function find_item($list, $id) {
    foreach ($list as $item) if (($item['id'] ?? '') === $id) return $item;
    return null;
}

// YouTube холбоосоос embed хаяг гаргана
function youtube_embed($url) {
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~', (string) $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return '';
}

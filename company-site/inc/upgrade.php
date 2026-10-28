<?php
// Хуучин (1-р хувилбарын) анхны агуулга. Админаас өөрчлөөгүй мөрүүдийг шинэ агуулгаар солиход ашиглана.

function store_defaults_v1() {
    return [
        'settings' => [
            'name'          => 'National Road Bridge Construction',
            'logo_title'    => 'NATIONAL ROAD BRIDGE',
            'logo_sub'      => 'CONSTRUCTION',
            'logo'          => 'assets/img/logo.png',
            'logo_header'   => 'assets/img/logo.png',
            'logo_wordmark' => 'assets/img/logo-wordmark.png',
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
            'vision'  => 'Монгол улсын зам, гүүрийн барилгын салбарын шинэчлэлд манлайлагч байж, тэргүүлэх орон зайг эзэлнэ.',
            'mission' => 'Чанартай, аюулгүй, хариуцлагатай ажлаар харилцагч, ажилтан, нийгэмдээ үнэ цэнийг бүтээнэ.',
            'values'  => 'Аюулгүй ажиллагаа, багийн ажил, хариуцлага, тасралтгүй хөгжил.',
        ],
        'careers' => [
            'policy' => 'Бид ажилтнуудынхаа ур чадварыг тасралтгүй хөгжүүлж, аюулгүй, эрүүл ажлын байр, өрсөлдөхүйц цалин хөлс, нийгмийн баталгааг хангахыг эрхэмлэдэг.',
        ],
        'slides' => [
            ['id' => 's1', 'image' => 'assets/img/hero-1.svg', 'title' => 'АЛСЫН ХАРАА', 'text' => 'Монгол улсын зам, гүүрийн барилгын салбарын шинэчлэлд манлайлагч байж, тэргүүлэх орон зайг эзэлнэ.'],
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

// Админаас өөрчлөгдөөгүй (анхны хэвээрээ) агуулгыг шинэ анхны агуулгаар солино.
// Хэрэглэгчийн өөрөө засварласан эсвэл нэмсэн зүйлд хүрэхгүй.
function content_upgrade($from) {
    if ($from < 3) db_create_tables();
    if ($from < 4) {
        // Слайдад "том гарчиг" багана нэмэгдсэн
        $has = db()->query("SHOW COLUMNS FROM " . t('slides') . " LIKE 'headline'")->fetch();
        if (!$has) db()->exec('ALTER TABLE ' . t('slides') . " ADD `headline` VARCHAR(255) NOT NULL DEFAULT '' AFTER `title`");
    }
    if ($from < 2) content_upgrade_v1();
    if ($from < 3) {
        // Мэдээ, тайлангийн оронд төсөл, зургийн цомог нэмэгдсэн
        if (!db()->query('SELECT COUNT(*) FROM ' . t('projects'))->fetchColumn()) {
            foreach (store_defaults()['projects'] as $it) store_save_item('projects', $it, true);
        }
    }
    if ($from < 4) {
        // Анхны слайдуудад том гарчиг, эхний слайдад гүүрийн зураг
        $st = db()->prepare('UPDATE ' . t('slides') . " SET `headline` = ? WHERE `id` = ? AND `title` = ? AND `headline` = ''");
        foreach (store_defaults()['slides'] as $s) $st->execute([$s['headline'], $s['id'], $s['title']]);
        db()->exec('UPDATE ' . t('slides') . " SET `image` = 'assets/img/bridge.svg' WHERE `id` = 's1' AND `image` = 'assets/img/hero-1.svg'");
    }
    content_version_save();
}

function content_upgrade_v1() {
    $old = store_defaults_v1();
    $new = store_defaults();
    // Анхны хувилбарын "Алсын хараа" уул уурхайн тухай байсан
    $norm = function ($s) use ($old) {
        return str_replace('Монгол улсын уул уурхайн үйлдвэрлэлийн салбарын шинэчлэлд манлайлагч байж, тэргүүлэх орон зайг эзэлнэ.', $old['about']['vision'], (string) $s);
    };

    $cur = [];
    foreach (db()->query('SELECT `section`, `field`, `value` FROM ' . t('settings')) as $r) $cur[$r['section']][$r['field']] = $r['value'];
    $st = db()->prepare('UPDATE ' . t('settings') . ' SET `value` = ? WHERE `section` = ? AND `field` = ?');
    foreach (PAGE_SECTIONS as $section) {
        foreach ($new[$section] as $field => $value) {
            if (!isset($cur[$section][$field], $old[$section][$field])) continue;
            if ($norm($cur[$section][$field]) === (string) $old[$section][$field]) $st->execute([$value, $section, $field]);
        }
    }

    foreach (LIST_COLUMNS as $list => $cols) {
        $oldRows = array_column($old[$list] ?? [], null, 'id');
        $newRows = array_column($new[$list], null, 'id');
        foreach (db()->query('SELECT * FROM ' . t($list))->fetchAll() as $row) {
            if (!isset($oldRows[$row['id']])) continue;
            foreach ($cols as $c) {
                if ($norm($row[$c]) !== (string) ($oldRows[$row['id']][$c] ?? '')) continue 2;
            }
            if (isset($newRows[$row['id']])) store_save_item($list, $newRows[$row['id']], false);
            else store_delete_item($list, $row['id']);
        }
    }
}

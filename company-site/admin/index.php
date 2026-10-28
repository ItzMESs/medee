<?php
// Админ панел: сайтын бүх агуулгыг нэмэх, засах, устгах.
require __DIR__ . '/../inc/store.php';

session_name('cs_admin');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// ---------- Агуулгын бүтэц ----------
// Талбар: [нэр, төрөл, сонголтууд/тайлбар]
$LISTS = [
    'slides' => ['label' => 'Слайдер', 'icon' => '🖼', 'fields' => [
        'image' => ['Зураг (хэвтээ, 1920×1080 орчим)', 'image'],
        'title' => ['Гарчиг', 'text', 'required'],
        'text'  => ['Текст', 'textarea'],
    ]],
    'services' => ['label' => 'Үйл ажиллагаа', 'icon' => '🏗', 'fields' => [
        'title' => ['Нэр', 'text', 'required'],
        'image' => ['Зураг', 'image'],
        'text'  => ['Тайлбар', 'textarea'],
    ]],
    'news' => ['label' => 'Мэдээ', 'icon' => '📰', 'fields' => [
        'date'  => ['Огноо', 'date'],
        'title' => ['Гарчиг', 'text', 'required'],
        'image' => ['Нүүр зураг', 'image'],
        'body'  => ['Агуулга', 'textarea_big'],
    ]],
    'docs' => ['label' => 'Тайлан, худалдан авалт', 'icon' => '📄', 'fields' => [
        'category' => ['Ангилал', 'select', ['tailan' => 'Тайлан', 'hudaldan-avalt' => 'Худалдан авалт']],
        'date'     => ['Огноо', 'date'],
        'title'    => ['Гарчиг', 'text', 'required'],
        'body'     => ['Тайлбар', 'textarea'],
        'file'     => ['Файл (PDF, Word, Excel, PowerPoint, ZIP)', 'file'],
    ]],
    'jobs' => ['label' => 'Ажлын байр', 'icon' => '👷', 'fields' => [
        'title'    => ['Албан тушаал', 'text', 'required'],
        'location' => ['Байршил', 'text'],
        'type'     => ['Төрөл (үндсэн, гэрээт...)', 'text'],
        'body'     => ['Тавигдах шаардлага, тайлбар', 'textarea_big'],
    ]],
];
$PAGES = [
    'home' => ['label' => 'Нүүр хуудас', 'icon' => '🏠', 'fields' => [
        'about_title'  => ['"Бидний тухай" хэсгийн гарчиг', 'text'],
        'about_text'   => ['"Бидний тухай" текст', 'textarea_big'],
        'about_image'  => ['"Бидний тухай" зураг', 'image'],
        'video_title'  => ['Бичлэгийн гарчиг', 'text'],
        'video_url'    => ['YouTube холбоос (байвал үүнийг харуулна)', 'url'],
        'video_file'   => ['Эсвэл бичлэгийн файл (MP4)', 'video'],
        'video_poster' => ['Бичлэгийн нүүр зураг', 'image'],
    ]],
    'about' => ['label' => 'Бидний тухай', 'icon' => 'ℹ️', 'fields' => [
        'heading' => ['Гарчиг', 'text'],
        'image'   => ['Зураг', 'image'],
        'text'    => ['Танилцуулга', 'textarea_big'],
        'vision'  => ['Алсын хараа', 'textarea'],
        'mission' => ['Эрхэм зорилго', 'textarea'],
        'values'  => ['Үнэт зүйлс', 'textarea'],
    ]],
    'careers' => ['label' => 'Хүний нөөцийн бодлого', 'icon' => '🤝', 'fields' => [
        'policy' => ['Хүний нөөцийн бодлого', 'textarea_big'],
    ]],
    'settings' => ['label' => 'Ерөнхий тохиргоо', 'icon' => '⚙️', 'fields' => [
        'name'        => ['Компанийн нэр (footer, хөтчийн гарчиг)', 'text', 'required'],
        'logo'          => ['Лого (өнгөт — хөтчийн таб, админд)', 'image'],
        'logo_header'   => ['Толгой хэсгийн лого', 'image'],
        'logo_wordmark' => ['Логоны бичиг — зураг. Устгавал доорх текст харагдана', 'image'],
        'logo_title'    => ['Логоны бичиг — текст (том)', 'text'],
        'logo_sub'      => ['Логоны бичиг — текст (жижиг)', 'text'],
        'phone'       => ['Утас', 'text'],
        'email'       => ['И-мэйл', 'text'],
        'address'     => ['Хаяг', 'textarea'],
        'facebook'    => ['Facebook хуудас', 'url'],
        'english_url' => ['Англи хувилбарын холбоос', 'url'],
        'mail_to'     => ['Санал хүсэлт хүлээн авах и-мэйл', 'text'],
        'developer'   => ['Вэб хөгжүүлэгч (footer-т харагдана, хоосон байж болно)', 'text'],
    ]],
];
$UPLOAD_TYPES = [
    'image' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
    'file'  => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip'],
    'video' => ['mp4', 'webm'],
];

// ---------- Туслах функцууд ----------
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

function redirect($query = '') {
    header('Location: index.php' . ($query !== '' ? '?' . $query : ''));
    exit;
}
function flash($msg, $type = 'ok') { $_SESSION['flash'] = [$msg, $type]; }
function preview_url($path) { return preg_match('~^(https?:)?//~', $path) ? $path : '../' . $path; }
function client_ip() { return $_SERVER['REMOTE_ADDR'] ?? '0'; }

function delete_upload($path) {
    if (!is_string($path) || !preg_match('~^uploads/[0-9/]+[a-f0-9]+\.[a-z0-9]+$~', $path)) return;
    $file = __DIR__ . '/../' . $path;
    if (is_file($file)) @unlink($file);
}

function handle_upload($input, $type) {
    global $UPLOAD_TYPES;
    $f = $_FILES[$input] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('Файл хэт том байна. Хостингийн хязгаар: ' . ini_get('upload_max_filesize'));
    }
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) throw new RuntimeException('Файл хуулахад алдаа гарлаа.');

    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $UPLOAD_TYPES[$type], true)) {
        throw new RuntimeException('Зөвшөөрөгдөх файл: ' . implode(', ', $UPLOAD_TYPES[$type]));
    }
    if ($type === 'image') {
        $info = @getimagesize($f['tmp_name']);
        $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
        if (!$info || !isset($types[$info[2]])) throw new RuntimeException('Зургийн файл биш байна.');
        $ext = $types[$info[2]];
    }

    $sub = date('Y/m');
    $dir = UPLOAD_DIR . '/' . $sub;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) throw new RuntimeException('uploads хавтас руу бичих эрхгүй байна.');
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) throw new RuntimeException('Файл хадгалахад алдаа гарлаа.');
    @chmod("$dir/$name", 0644);
    return "uploads/$sub/$name";
}

// Формын утгуудыг уншина. $old — өмнөх утга. Солигдсон файлуудыг $replaced-д буцаана.
function collect_fields($fields, $old, &$replaced) {
    foreach ($fields as $key => $def) {
        if (($def[2] ?? '') === 'required' && trim((string) ($_POST["f_$key"] ?? '')) === '') {
            throw new RuntimeException('"' . $def[0] . '" талбарыг бөглөнө үү.');
        }
    }
    $new = $old;
    $uploaded = [];
    try {
        foreach ($fields as $key => [$label, $type]) {
            $opts = $fields[$key][2] ?? null;
            if (in_array($type, ['image', 'file', 'video'], true)) {
                $val = $old[$key] ?? '';
                $up = handle_upload("f_$key", $type);
                if ($up !== null) {
                    $uploaded[] = $up;
                    $replaced[] = $val;
                    $val = $up;
                } elseif (!empty($_POST["rm_$key"])) {
                    $replaced[] = $val;
                    $val = '';
                }
                $new[$key] = $val;
                continue;
            }
            $v = str_replace("\r\n", "\n", trim((string) ($_POST["f_$key"] ?? '')));
            if ($type === 'select' && !isset($opts[$v])) $v = array_key_first($opts);
            if ($type === 'date' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) $v = date('Y-m-d');
            if ($type === 'url' && $v !== '' && !preg_match('~^(https?://|#|/)~i', $v)) $v = 'https://' . $v;
            $new[$key] = $v;
        }
    } catch (RuntimeException $ex) {
        foreach ($uploaded as $u) delete_upload($u);
        throw $ex;
    }
    return $new;
}

// ---------- Хүсэлт боловсруулах ----------
if (!is_installed()) {
    header('Location: ../install.php');
    exit;
}
try {
    $auth = admin_get();
} catch (PDOException $ex) {
    http_response_code(503);
    exit('Өгөгдлийн сантай холбогдож чадсангүй. config.php доторх тохиргоог шалгана уу.');
}
if (!$auth) {
    header('Location: ../install.php');
    exit;
}
$p = $_GET['p'] ?? 'dash';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$_POST && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        flash('Файл хэт том байна. Хостингийн хязгаар: ' . ini_get('post_max_size'), 'err');
        redirect($_SERVER['QUERY_STRING'] ?? '');
    }
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Хүсэлт хүчингүй боллоо. Хуудсаа дахин ачаалаад оролдоно уу.');
    }
}

$me = !empty($_SESSION['admin_id']) ? db()->query('SELECT * FROM ' . t('admins') . ' WHERE `id` = ' . (int) $_SESSION['admin_id'])->fetch() : null;

if (!$me) {
    $p = 'login';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = admin_find((string) ($_POST['user'] ?? ''));
        if (login_blocked(client_ip())) {
            $error = 'Хэт олон удаа буруу оролдлоо. 15 минутын дараа дахин оролдоно уу.';
        } elseif ($user && password_verify((string) ($_POST['pass'] ?? ''), $user['password_hash'])) {
            login_record(client_ip(), false);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            redirect();
        } else {
            login_record(client_ip(), true);
            sleep(1);
            $error = 'Нэвтрэх нэр эсвэл нууц үг буруу байна.';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $l = (string) ($_POST['l'] ?? '');
    $id = (string) ($_POST['id'] ?? '');

    if ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        redirect();
    }

    if (in_array($action, ['save_item', 'delete_item', 'move_item'], true) && !isset($LISTS[$l])) redirect();

    if ($action === 'save_item') {
        $old = $id !== '' ? store_get_item($l, $id) : null;
        $replaced = [];
        try {
            $item = collect_fields($LISTS[$l]['fields'], $old ?: ['id' => bin2hex(random_bytes(4))], $replaced);
            store_save_item($l, $item, !$old);
            foreach ($replaced as $r) delete_upload($r);
            flash('Хадгалагдлаа.');
            redirect('p=list&l=' . $l);
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
            $p = 'edit';
            $_GET['l'] = $l;
            $_GET['id'] = $old ? $id : '';
            $formValues = $_POST;
        }
    }

    if ($action === 'delete_item') {
        if ($it = store_get_item($l, $id)) {
            store_delete_item($l, $id);
            foreach ($LISTS[$l]['fields'] as $key => $def) {
                if (in_array($def[1], ['image', 'file', 'video'], true)) delete_upload($it[$key] ?? '');
            }
            flash('Устгагдлаа.');
        }
        redirect('p=list&l=' . $l);
    }

    if ($action === 'move_item') {
        store_move_item($l, $id, ($_POST['dir'] ?? '') === 'up');
        redirect('p=list&l=' . $l);
    }

    if ($action === 'save_page' && isset($PAGES[$_POST['k'] ?? ''])) {
        $k = $_POST['k'];
        $replaced = [];
        try {
            $values = collect_fields($PAGES[$k]['fields'], store_load()[$k], $replaced);
            store_save_page($k, $values);
            foreach ($replaced as $r) delete_upload($r);
            flash('Хадгалагдлаа.');
            redirect('p=page&k=' . $k);
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
            $p = 'page';
            $_GET['k'] = $k;
            $formValues = $_POST;
        }
    }

    if ($action === 'msg_read' || $action === 'msg_delete') {
        if ($action === 'msg_delete') message_delete($id); else message_toggle($id);
        redirect('p=messages');
    }

    if ($action === 'password') {
        if (!password_verify((string) ($_POST['old'] ?? ''), $me['password_hash'])) {
            $error = 'Одоогийн нууц үг буруу байна.';
        } elseif (strlen((string) ($_POST['pass'] ?? '')) < 8) {
            $error = 'Шинэ нууц үг хамгийн багадаа 8 тэмдэгт байна.';
        } elseif ($_POST['pass'] !== ($_POST['pass2'] ?? '')) {
            $error = 'Шинэ нууц үг таарахгүй байна.';
        } else {
            admin_set_password($me['id'], $_POST['pass']);
            flash('Нууц үг солигдлоо.');
            redirect('p=password');
        }
        $p = 'password';
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$content = store_load();
$messages = $me ? messages_all() : [];
$unread = count(array_filter($messages, fn($m) => !$m['read']));

// ---------- Харагдац ----------
function field_html($key, $def, $value) {
    [$label, $type] = $def;
    $opts = $def[2] ?? null;
    $name = "f_$key";
    $req = $opts === 'required' ? ' required' : '';
    $h = '<div class="field"><label for="' . $name . '">' . e($label) . ($req ? ' <b>*</b>' : '') . '</label>';
    switch ($type) {
        case 'textarea':
        case 'textarea_big':
            $h .= '<textarea id="' . $name . '" name="' . $name . '" rows="' . ($type === 'textarea' ? 4 : 12) . '"' . $req . '>' . e($value) . '</textarea>';
            $h .= '<small>Догол мөр тусгаарлахдаа хоосон мөр үлдээнэ.</small>';
            break;
        case 'select':
            $h .= '<select id="' . $name . '" name="' . $name . '">';
            foreach ($opts as $k => $v) $h .= '<option value="' . e($k) . '"' . ($k === $value ? ' selected' : '') . '>' . e($v) . '</option>';
            $h .= '</select>';
            break;
        case 'image':
        case 'file':
        case 'video':
            if ($value) {
                $h .= '<div class="current">';
                $h .= $type === 'image'
                    ? '<img src="' . e(preview_url($value)) . '" alt="">'
                    : '<a href="' . e(preview_url($value)) . '" target="_blank">' . e(basename($value)) . '</a>';
                $h .= '<label class="check"><input type="checkbox" name="rm_' . $key . '" value="1"> Устгах</label></div>';
            }
            $accept = ['image' => 'image/jpeg,image/png,image/webp,image/gif', 'file' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip', 'video' => 'video/mp4,video/webm'][$type];
            $h .= '<input type="file" id="' . $name . '" name="' . $name . '" accept="' . $accept . '">';
            $h .= '<small>' . ($value ? 'Шинэ файл сонговол хуучныг нь солино. ' : '') . 'Хамгийн ихдээ ' . e(ini_get('upload_max_filesize')) . '</small>';
            break;
        default:
            $t = $type === 'date' ? 'date' : 'text';
            $h .= '<input type="' . $t . '" id="' . $name . '" name="' . $name . '" value="' . e($value) . '"' . $req . '>';
    }
    return $h . '</div>';
}

function form_value($key, $item) {
    global $formValues;
    if (isset($formValues["f_$key"])) return $formValues["f_$key"];
    return $item[$key] ?? '';
}

$csrfField = '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
?><!doctype html>
<html lang="mn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Админ — <?= e($content['settings']['name']) ?></title>
<link rel="stylesheet" href="<?= e('../' . asset('admin/admin.css')) ?>">
</head>
<body>
<?php if ($p === 'login'): ?>
<main class="auth">
  <form method="post" class="card">
    <img src="<?= e(preview_url($content['settings']['logo'])) ?>" alt="" class="auth-logo">
    <h1>Админ нэвтрэх</h1>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
    <?= $csrfField ?>
    <div class="field"><label>Нэвтрэх нэр</label><input name="user" required autofocus autocomplete="username" value="<?= e($_POST['user'] ?? '') ?>"></div>
    <div class="field"><label>Нууц үг</label><input type="password" name="pass" required autocomplete="current-password"></div>
    <button class="btn primary wide">Нэвтрэх</button>
    <a class="muted back" href="../">← Сайт руу буцах</a>
  </form>
</main>
<?php else: ?>
<div class="layout">
  <aside class="side">
    <a class="side-brand" href="index.php"><img src="<?= e(preview_url($content['settings']['logo'])) ?>" alt=""><span>Админ</span></a>
    <nav>
      <a href="index.php" class="<?= $p === 'dash' ? 'on' : '' ?>">📊 Хянах самбар</a>
      <span class="group">Агуулга</span>
      <?php foreach ($LISTS as $key => $def): ?>
      <a href="?p=list&l=<?= $key ?>" class="<?= ($_GET['l'] ?? '') === $key ? 'on' : '' ?>"><?= $def['icon'] ?> <?= e($def['label']) ?></a>
      <?php endforeach; ?>
      <span class="group">Хуудас</span>
      <?php foreach ($PAGES as $key => $def): ?>
      <a href="?p=page&k=<?= $key ?>" class="<?= ($_GET['k'] ?? '') === $key ? 'on' : '' ?>"><?= $def['icon'] ?> <?= e($def['label']) ?></a>
      <?php endforeach; ?>
      <span class="group">Бусад</span>
      <a href="?p=messages" class="<?= $p === 'messages' ? 'on' : '' ?>">✉️ Санал хүсэлт<?php if ($unread): ?> <em class="badge"><?= $unread ?></em><?php endif; ?></a>
      <a href="?p=password" class="<?= $p === 'password' ? 'on' : '' ?>">🔑 Нууц үг солих</a>
      <a href="../" target="_blank">↗ Сайтыг үзэх</a>
      <form method="post"><?= $csrfField ?><button name="action" value="logout" class="linklike">⏻ Гарах</button></form>
    </nav>
  </aside>
  <main class="main">
    <?php if ($flash): ?><div class="alert <?= $flash[1] ?>"><?= e($flash[0]) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

    <?php if ($p === 'list' && isset($LISTS[$_GET['l'] ?? ''])):
      $l = $_GET['l']; $def = $LISTS[$l]; $items = $content[$l];
      $imgKey = isset($def['fields']['image']); ?>
    <div class="head"><h1><?= e($def['label']) ?></h1><a class="btn primary" href="?p=edit&l=<?= $l ?>">+ Шинэ нэмэх</a></div>
    <?php if ($l === 'news'): ?><p class="muted">Сайт дээр мэдээ огноогоор (шинэ нь эхэндээ) эрэмбэлэгдэнэ.</p><?php endif; ?>
    <?php if (!$items): ?><div class="card empty">Одоогоор хоосон байна. "+ Шинэ нэмэх" товчоор нэмнэ үү.</div><?php endif; ?>
    <div class="rows">
      <?php foreach ($items as $i => $it): ?>
      <div class="row card">
        <?php if ($imgKey): ?><div class="thumb"><?php if ($it['image']): ?><img src="<?= e(preview_url($it['image'])) ?>" alt=""><?php endif; ?></div><?php endif; ?>
        <div class="row-main">
          <strong><?= e($it['title']) ?></strong>
          <span class="muted"><?= e(implode(' · ', array_filter([
              $it['date'] ?? '',
              isset($it['category']) ? ($def['fields']['category'][2][$it['category']] ?? '') : '',
              $it['location'] ?? '',
              !empty($it['file']) ? '📎 ' . basename($it['file']) : '',
          ]))) ?></span>
        </div>
        <div class="row-actions">
          <form method="post"><?= $csrfField ?><input type="hidden" name="action" value="move_item"><input type="hidden" name="l" value="<?= $l ?>"><input type="hidden" name="id" value="<?= e($it['id']) ?>">
            <button class="btn small" name="dir" value="up" title="Дээш" <?= $i === 0 ? 'disabled' : '' ?>>↑</button>
            <button class="btn small" name="dir" value="down" title="Доош" <?= $i === count($items) - 1 ? 'disabled' : '' ?>>↓</button>
          </form>
          <a class="btn small" href="?p=edit&l=<?= $l ?>&id=<?= e($it['id']) ?>">Засах</a>
          <form method="post" onsubmit="return confirm('Устгах уу?')"><?= $csrfField ?><input type="hidden" name="action" value="delete_item"><input type="hidden" name="l" value="<?= $l ?>"><input type="hidden" name="id" value="<?= e($it['id']) ?>"><button class="btn small danger">Устгах</button></form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php elseif ($p === 'edit' && isset($LISTS[$_GET['l'] ?? ''])):
      $l = $_GET['l']; $def = $LISTS[$l];
      $item = ($_GET['id'] ?? '') !== '' ? find_item($content[$l], (string) $_GET['id']) : null; ?>
    <div class="head"><h1><?= e($def['label']) ?> — <?= $item ? 'засах' : 'шинэ' ?></h1><a class="btn" href="?p=list&l=<?= $l ?>">← Буцах</a></div>
    <form method="post" enctype="multipart/form-data" class="card form">
      <?= $csrfField ?>
      <input type="hidden" name="action" value="save_item"><input type="hidden" name="l" value="<?= $l ?>"><input type="hidden" name="id" value="<?= e($item['id'] ?? '') ?>">
      <?php foreach ($def['fields'] as $key => $f) echo field_html($key, $f, form_value($key, $item ?? ['date' => date('Y-m-d')])); ?>
      <div class="actions"><button class="btn primary">Хадгалах</button><a class="btn" href="?p=list&l=<?= $l ?>">Болих</a></div>
    </form>

    <?php elseif ($p === 'page' && isset($PAGES[$_GET['k'] ?? ''])):
      $k = $_GET['k']; $def = $PAGES[$k]; ?>
    <div class="head"><h1><?= e($def['label']) ?></h1></div>
    <form method="post" enctype="multipart/form-data" class="card form">
      <?= $csrfField ?>
      <input type="hidden" name="action" value="save_page"><input type="hidden" name="k" value="<?= $k ?>">
      <?php foreach ($def['fields'] as $key => $f) echo field_html($key, $f, form_value($key, $content[$k])); ?>
      <div class="actions"><button class="btn primary">Хадгалах</button></div>
    </form>

    <?php elseif ($p === 'messages'): ?>
    <div class="head"><h1>Санал хүсэлт</h1></div>
    <?php if (!$messages): ?><div class="card empty">Санал хүсэлт ирээгүй байна.</div><?php endif; ?>
    <div class="rows">
      <?php foreach ($messages as $m): ?>
      <div class="card msg<?= $m['read'] ? '' : ' unread' ?>">
        <div class="msg-head">
          <div><strong><?= e($m['name']) ?></strong> <span class="muted"><?= e($m['date']) ?></span><br>
            <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a><?php if ($m['phone']): ?> · <a href="tel:<?= e($m['phone']) ?>"><?= e($m['phone']) ?></a><?php endif; ?></div>
          <div class="row-actions">
            <form method="post"><?= $csrfField ?><input type="hidden" name="id" value="<?= e($m['id']) ?>"><button class="btn small" name="action" value="msg_read"><?= $m['read'] ? 'Уншаагүй болгох' : 'Уншсан' ?></button></form>
            <form method="post" onsubmit="return confirm('Устгах уу?')"><?= $csrfField ?><input type="hidden" name="id" value="<?= e($m['id']) ?>"><button class="btn small danger" name="action" value="msg_delete">Устгах</button></form>
          </div>
        </div>
        <p><?= nl2br(e($m['message'])) ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <?php elseif ($p === 'password'): ?>
    <div class="head"><h1>Нууц үг солих</h1></div>
    <form method="post" class="card form narrow">
      <?= $csrfField ?><input type="hidden" name="action" value="password">
      <div class="field"><label>Одоогийн нууц үг</label><input type="password" name="old" required autocomplete="current-password"></div>
      <div class="field"><label>Шинэ нууц үг (8+ тэмдэгт)</label><input type="password" name="pass" required autocomplete="new-password"></div>
      <div class="field"><label>Шинэ нууц үг давтах</label><input type="password" name="pass2" required autocomplete="new-password"></div>
      <div class="actions"><button class="btn primary">Солих</button></div>
    </form>

    <?php else: ?>
    <div class="head"><h1>Хянах самбар</h1><a class="btn" href="../" target="_blank">↗ Сайтыг үзэх</a></div>
    <div class="tiles">
      <?php foreach ($LISTS as $key => $def): ?>
      <a class="card tile" href="?p=list&l=<?= $key ?>"><span><?= $def['icon'] ?></span><b><?= count($content[$key]) ?></b><?= e($def['label']) ?></a>
      <?php endforeach; ?>
      <a class="card tile<?= $unread ? ' hot' : '' ?>" href="?p=messages"><span>✉️</span><b><?= count($messages) ?></b>Санал хүсэлт<?= $unread ? " ($unread шинэ)" : '' ?></a>
    </div>
    <div class="card help">
      <h2>Хурдан үйлдэл</h2>
      <p><a class="btn primary" href="?p=edit&l=news">+ Мэдээ нэмэх</a> <a class="btn" href="?p=edit&l=slides">+ Слайд нэмэх</a> <a class="btn" href="?p=edit&l=jobs">+ Ажлын байр нэмэх</a> <a class="btn" href="?p=edit&l=docs">+ Тайлан нэмэх</a></p>
      <p class="muted">Компанийн нэр, утас, лого зэргийг <a href="?p=page&k=settings">Ерөнхий тохиргоо</a> хэсгээс солино.</p>
    </div>
    <?php endif; ?>
  </main>
</div>
<?php endif; ?>
</body>
</html>

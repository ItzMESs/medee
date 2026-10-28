<?php
// Санал хүсэлтийн форм — и-мэйлээр илгээнэ.
$site = require __DIR__ . '/config.php';
$back = basename($_POST['back'] ?? 'index.php');
if (!preg_match('/^[a-z]+\.php$/', $back) || !is_file(__DIR__ . '/' . $back)) $back = 'index.php';

function done($back, $ok) {
    header('Location: ' . $back . '?sent=' . ($ok ? '1' : '0') . '#footer');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') done($back, false);
if (!empty($_POST['website'])) done($back, true); // спам бот

$clean = fn($s, $n) => mb_substr(trim(str_replace(["\r", "\n"], ' ', (string) $s)), 0, $n);
$name    = $clean($_POST['name'] ?? '', 100);
$email   = trim((string) ($_POST['email'] ?? ''));
$phone   = $clean($_POST['phone'] ?? '', 30);
$message = mb_substr(trim((string) ($_POST['message'] ?? '')), 0, 3000);

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) done($back, false);

$body = "Нэр: $name\nИ-мэйл: $email\nУтас: $phone\n\n$message\n";
$subject = '=?UTF-8?B?' . base64_encode('Санал хүсэлт: ' . $name) . '?=';
$host = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
$headers = "From: noreply@$host\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8\r\n";

done($back, @mail($site['mail_to'], $subject, $body, $headers));

<?php
// Энэ файлыг config.php гэж нэрийг нь сольж, доорх утгуудыг өөрийнхөөрөө бөглөнө.
// Дараа нь install.php-г нээж админ хэрэглэгчээ үүсгэнэ.
return [
    'db_host' => 'localhost',
    'db_name' => 'cpanelner_medee',     // cPanel → MySQL Databases дахь бүтэн нэр
    'db_user' => 'cpanelner_medeeuser', // өгөгдлийн сангийн хэрэглэгчийн бүтэн нэр
    'db_pass' => 'ENDE_NUUTS_UG',       // тэр хэрэглэгчийн нууц үг
    'debug' => false,
];

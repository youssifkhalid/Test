<?php
$host = '127.0.0.1'; // استخدم هذا بدلاً من localhost
$username = 'root';     // اسم المستخدم الخاص بك
$password = 'root';        // كلمة المرور الخاصة بك
$database = 'school_website_db';

// إضافة خيارات إضافية للاتصال
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

if (!$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
    die("Setting MYSQLI_OPT_CONNECT_TIMEOUT failed");
}

if (!$conn->real_connect($host, $username, $password, $database)) {
    die("Connect Error: " . mysqli_connect_error());
}

$conn->set_charset("utf8mb4");
?>
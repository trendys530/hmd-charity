<?php
// إعدادات اتصال قاعدة البيانات
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'hmd_charity');

// محاولة الاتصال بقاعدة البيانات
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// التحقق من الاتصال
if($conn === false){
    die("خطأ في الاتصال: " . mysqli_connect_error());
}

// تعيين ترميز الأحرف
mysqli_set_charset($conn, "utf8");
?>

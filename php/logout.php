<?php
// بدء الجلسة
session_start();

// تضمين ملف الإعدادات
require_once 'config.php';

// التحقق من وجود معرف الجلسة
if (isset($_SESSION['session_id'])) {
    $session_id = $_SESSION['session_id'];
    
    // حذف جلسة المستخدم من قاعدة البيانات
    $sql = "DELETE FROM user_sessions WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $session_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// حذف جميع متغيرات الجلسة
$_SESSION = array();

// إذا كنت تريد حذف ملف تعريف الارتباط الخاص بالجلسة، قم بإلغاء التعليق من السطر التالي
// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(session_name(), '', time() - 42000,
//         $params["path"], $params["domain"],
//         $params["secure"], $params["httponly"]
//     );
// }

// تدمير الجلسة
session_destroy();

// حذف ملف تعريف الارتباط "تذكرني" إذا كان موجوداً
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/', '', true, true);
}

// إعادة التوجيه إلى صفحة تسجيل الدخول
header('Location: ../login.html?logged_out=1');
exit();
?>

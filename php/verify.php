<?php
// بدء الجلسة
session_start();

// تضمين ملف الإعدادات
require_once 'config.php';

// التحقق من وجود رمز التحقق
if (isset($_GET['code'])) {
    $verification_code = trim($_GET['code']);
    
    // البحث عن المستخدم باستخدام رمز التحقق
    $sql = "SELECT id, email, fullname FROM users WHERE verification_code = ? AND email_verified = 0";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $verification_code);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                
                // تحديث حالة البريد الإلكتروني إلى مؤكد
                $sql_update = "UPDATE users SET email_verified = 1, verification_code = NULL, status = 'active' WHERE id = ?";
                
                if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                    mysqli_stmt_bind_param($stmt_update, "i", $user['id']);
                    
                    if (mysqli_stmt_execute($stmt_update)) {
                        // تسجيل الدخول التلقائي
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['fullname'] = $user['fullname'];
                        $_SESSION['email'] = $user['email'];
                        
                        // إعادة التوجيه إلى الصفحة الرئيسية مع رسالة نجاح
                        header('Location: ../index.html?verified=1');
                        exit();
                    }
                    mysqli_stmt_close($stmt_update);
                }
            }
        }
        
        mysqli_stmt_close($stmt);
    }
}

// إذا وصلنا إلى هنا، فهناك خطأ ما
header('Location: ../index.html?verification_error=1');
exit();
?>

<?php
// بدء الجلسة
session_start();

// تضمين ملف الإعدادات
require_once 'config.php';

// تهيئة المصفوفة للاستجابة
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // الحصول على البيانات المدخلة
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // التحقق من صحة البيانات المدخلة
    if (empty($username) || empty($password)) {
        $response['message'] = 'الرجاء إدخال اسم المستخدم وكلمة المرور';
    } else {
        // البحث عن المستخدم في قاعدة البيانات
        $sql = "SELECT id, fullname, email, password, user_type, status, email_verified FROM users WHERE email = ? OR phone = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // ربط المتغيرات بالاستعلام
            mysqli_stmt_bind_param($stmt, "ss", $username, $username);
            
            // تنفيذ الاستعلام
            if (mysqli_stmt_execute($stmt)) {
                // تخزين النتيجة
                mysqli_stmt_store_result($stmt);
                
                // التحقق من وجود المستخدم
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // ربط المتغيرات بالنتيجة
                    mysqli_stmt_bind_result($stmt, $id, $fullname, $email, $hashed_password, $user_type, $status, $email_verified);
                    
                    if (mysqli_stmt_fetch($stmt)) {
                        // التحقق من حالة الحساب
                        if ($status === 'inactive') {
                            $response['message'] = 'حسابك غير مفعل. يرجى التواصل مع الإدارة.';
                        } elseif ($status === 'suspended') {
                            $response['message'] = 'حسابك موقوف. يرجى التواصل مع الإدارة.';
                        } elseif ($email_verified == 0) {
                            $response['message'] = 'الرجاء تأكيد بريدك الإلكتروني أولاً';
                        } elseif (password_verify($password, $hashed_password)) {
                            // كلمة المرور صحيحة، إنشاء جلسة جديدة
                            
                            // إنشاء معرف جلسة فريد
                            $session_id = bin2hex(random_bytes(32));
                            
                            // تعيين وقت انتهاء الجلسة (30 دقيقة من الآن)
                            $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                            
                            // تخزين معلومات الجلسة في قاعدة البيانات
                            $ip_address = $_SERVER['REMOTE_ADDR'];
                            $user_agent = $_SERVER['HTTP_USER_AGENT'];
                            
                            $sql_session = "INSERT INTO user_sessions (id, user_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)";
                            
                            if ($stmt_session = mysqli_prepare($conn, $sql_session)) {
                                mysqli_stmt_bind_param($stmt_session, "siss", $session_id, $id, $ip_address, $user_agent, $expires_at);
                                
                                if (mysqli_stmt_execute($stmt_session)) {
                                    // تعيين متغيرات الجلسة
                                    $_SESSION['user_id'] = $id;
                                    $_SESSION['fullname'] = $fullname;
                                    $_SESSION['email'] = $email;
                                    $_SESSION['user_type'] = $user_type;
                                    $_SESSION['session_id'] = $session_id;
                                    
                                    // إذا اختار المستخدم "تذكرني"، قم بإنشاء ملف تعريف ارتباط آمن
                                    if ($remember) {
                                        $token = bin2hex(random_bytes(32));
                                        $expires = time() + (30 * 24 * 60 * 60); // 30 يومًا
                                        
                                        // تخزين الرمز في قاعدة البيانات
                                        $sql_token = "UPDATE users SET remember_token = ?, remember_token_expires = ? WHERE id = ?";
                                        if ($stmt_token = mysqli_prepare($conn, $sql_token)) {
                                            $expires_date = date('Y-m-d H:i:s', $expires);
                                            mysqli_stmt_bind_param($stmt_token, "ssi", $token, $expires_date, $id);
                                            
                                            if (mysqli_stmt_execute($stmt_token)) {
                                                setcookie('remember_me', $token, $expires, '/', '', true, true);
                                            }
                                            mysqli_stmt_close($stmt_token);
                                        }
                                    }
                                    
                                    // تحديث وقت آخر تسجيل دخول
                                    $sql_update = "UPDATE users SET last_login = NOW() WHERE id = ?";
                                    if ($stmt_update = mysqli_prepare($conn, $sql_update)) {
                                        mysqli_stmt_bind_param($stmt_update, "i", $id);
                                        mysqli_stmt_execute($stmt_update);
                                        mysqli_stmt_close($stmt_update);
                                    }
                                    
                                    // تحديد الصفحة الهدف بناءً على نوع المستخدم
                                    $redirect = 'index.html';
                                    if ($user_type === 'admin' || $user_type === 'employee') {
                                        $redirect = 'admin/dashboard.php';
                                    } elseif ($user_type === 'volunteer') {
                                        $redirect = 'volunteer/dashboard.php';
                                    } elseif ($user_type === 'donor') {
                                        $redirect = 'donor/dashboard.php';
                                    } elseif ($user_type === 'beneficiary') {
                                        $redirect = 'beneficiary/dashboard.php';
                                    }
                                    
                                    $response['success'] = true;
                                    $response['message'] = 'تم تسجيل الدخول بنجاح';
                                    $response['redirect'] = $redirect;
                                } else {
                                    $response['message'] = 'حدث خطأ أثناء تسجيل الدخول. يرجى المحاولة مرة أخرى.';
                                }
                                mysqli_stmt_close($stmt_session);
                            } else {
                                $response['message'] = 'حدث خطأ أثناء تسجيل الدخول. يرجى المحاولة مرة أخرى.';
                            }
                        } else {
                            // كلمة المرور غير صحيحة
                            $response['message'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
                        }
                    }
                } else {
                    // المستخدم غير موجود
                    $response['message'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
                }
            } else {
                $response['message'] = 'حدث خطأ. يرجى المحاولة مرة أخرى لاحقاً.';
            }
            
            // إغلاق البيان
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = 'حدث خطأ. يرجى المحاولة مرة أخرى لاحقاً.';
        }
    }
} else {
    $response['message'] = 'طريقة الطلب غير صالحة';
}

// إغلاق الاتصال بقاعدة البيانات
mysqli_close($conn);

// إرجاع الاستجابة بتنسيق JSON
header('Content-Type: application/json');
echo json_encode($response);
?>

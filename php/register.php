<?php
// بدء الجلسة
session_start();

// تضمين ملف الإعدادات
require_once 'config.php';

// تهيئة المصفوفة للاستجابة
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// التحقق من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // الحصول على البيانات المدخلة
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $user_type = $_POST['user-type'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    
    // مصفوفة الأخطاء
    $errors = [];
    
    // التحقق من صحة البيانات المدخلة
    if (empty($fullname)) {
        $errors['fullname'] = 'الاسم الكامل مطلوب';
    } elseif (strlen($fullname) < 3) {
        $errors['fullname'] = 'يجب أن يكون الاسم 3 أحرف على الأقل';
    }
    
    if (empty($email)) {
        $errors['email'] = 'البريد الإلكتروني مطلوب';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'البريد الإلكتروني غير صالح';
    }
    
    if (empty($phone)) {
        $errors['phone'] = 'رقم الجوال مطلوب';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors['phone'] = 'رقم الجوال غير صالح';
    }
    
    $allowed_types = ['volunteer', 'donor', 'beneficiary'];
    if (empty($user_type) || !in_array($user_type, $allowed_types)) {
        $errors['user-type'] = 'نوع الحساب غير صالح';
    }
    
    if (empty($password)) {
        $errors['password'] = 'كلمة المرور مطلوبة';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'يجب أن تكون كلمة المرور 8 أحرف على الأقل';
    }
    
    if (empty($confirm_password)) {
        $errors['confirm-password'] = 'يرجى تأكيد كلمة المرور';
    } elseif ($password !== $confirm_password) {
        $errors['confirm-password'] = 'كلمتا المرور غير متطابقتين';
    }
    
    // إذا لم تكن هناك أخطاء في التحقق من الصحة
    if (empty($errors)) {
        // التحقق من عدم وجود بريد إلكتروني أو هاتف مستخدم مسبقاً
        $sql_check = "SELECT id FROM users WHERE email = ? OR phone = ?";
        
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "ss", $email, $phone);
            
            if (mysqli_stmt_execute($stmt_check)) {
                mysqli_stmt_store_result($stmt_check);
                
                if (mysqli_stmt_num_rows($stmt_check) > 0) {
                    // التحقق مما إذا كان البريد الإلكتروني مستخدماً
                    $sql_email = "SELECT id FROM users WHERE email = ?";
                    if ($stmt_email = mysqli_prepare($conn, $sql_email)) {
                        mysqli_stmt_bind_param($stmt_email, "s", $email);
                        mysqli_stmt_execute($stmt_email);
                        mysqli_stmt_store_result($stmt_email);
                        
                        if (mysqli_stmt_num_rows($stmt_email) > 0) {
                            $errors['email'] = 'البريد الإلكتروني مستخدم مسبقاً';
                        }
                        mysqli_stmt_close($stmt_email);
                    }
                    
                    // التحقق مما إذا كان رقم الهاتف مستخدماً
                    $sql_phone = "SELECT id FROM users WHERE phone = ?";
                    if ($stmt_phone = mysqli_prepare($conn, $sql_phone)) {
                        mysqli_stmt_bind_param($stmt_phone, "s", $phone);
                        mysqli_stmt_execute($stmt_phone);
                        mysqli_stmt_store_result($stmt_phone);
                        
                        if (mysqli_stmt_num_rows($stmt_phone) > 0) {
                            $errors['phone'] = 'رقم الجوال مستخدم مسبقاً';
                        }
                        mysqli_stmt_close($stmt_phone);
                    }
                }
                
                // إذا لم تكن هناك أخطاء، قم بإنشاء الحساب
                if (empty($errors)) {
                    // إنشاء رمز التحقق
                    $verification_code = bin2hex(random_bytes(16));
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // إدراج المستخدم الجديد في قاعدة البيانات
                    $sql_insert = "INSERT INTO users (fullname, email, phone, password, user_type, verification_code) VALUES (?, ?, ?, ?, ?, ?)";
                    
                    if ($stmt_insert = mysqli_prepare($conn, $sql_insert)) {
                        mysqli_stmt_bind_param($stmt_insert, "ssssss", $fullname, $email, $phone, $hashed_password, $user_type, $verification_code);
                        
                        if (mysqli_stmt_execute($stmt_insert)) {
                            // إرسال بريد التحقق (يجب تنفيذه لاحقاً)
                            $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/verify.php?code=" . $verification_code;
                            
                            // هنا يمكنك إضافة كود إرسال البريد الإلكتروني
                            // mail($email, 'تأكيد البريد الإلكتروني', 'الرجاء النقر على الرابط التالي لتأكيد بريدك الإلكتروني: ' . $verification_link);
                            
                            $response['success'] = true;
                            $response['message'] = 'تم إنشاء الحساب بنجاح! يرجى التحقق من بريدك الإلكتروني لتأكيد الحساب.';
                            $response['verification_link'] = $verification_link; // للإغراءات فقط، يجب إزالته في الإنتاج
                        } else {
                            $response['message'] = 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة مرة أخرى.';
                        }
                        
                        mysqli_stmt_close($stmt_insert);
                    } else {
                        $response['message'] = 'حدث خطأ. يرجى المحاولة مرة أخرى لاحقاً.';
                    }
                } else {
                    $response['errors'] = $errors;
                }
            } else {
                $response['message'] = 'حدث خطأ. يرجى المحاولة مرة أخرى لاحقاً.';
            }
            
            mysqli_stmt_close($stmt_check);
        } else {
            $response['message'] = 'حدث خطأ. يرجى المحاولة مرة أخرى لاحقاً.';
        }
    } else {
        $response['errors'] = $errors;
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

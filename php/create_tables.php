<?php
// تضمين ملف الإعدادات
require_once 'config.php';

// استعلام إنشاء جدول المستخدمين
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'employee', 'volunteer', 'donor', 'beneficiary') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive',
    email_verified BOOLEAN DEFAULT 0,
    verification_code VARCHAR(100),
    reset_token VARCHAR(100),
    reset_token_expires DATETIME,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// استعلام إنشاء جدول الموظفين
$sql_employees = "CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    hire_date DATE,
    salary DECIMAL(10,2),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// استعلام إنشاء جدول الصلاحيات
$sql_permissions = "CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// استعلام إنشاء جدول صلاحيات المستخدمين
$sql_user_permissions = "CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    granted_by INT,
    PRIMARY KEY (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

// استعلام إنشاء جدول جلسات المستخدمين
$sql_sessions = "CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// تنفيذ الاستعلامات
$queries = [
    'users' => $sql_users,
    'employees' => $sql_employees,
    'permissions' => $sql_permissions,
    'user_permissions' => $sql_user_permissions,
    'sessions' => $sql_sessions
];

$success = true;
$results = [];

foreach ($queries as $table => $sql) {
    if (mysqli_query($conn, $sql)) {
        $results[$table] = "تم إنشاء الجدول $table بنجاح";
    } else {
        $results[$table] = "خطأ في إنشاء الجدول $table: " . mysqli_error($conn);
        $success = false;
    }
}

// إغلاق الاتصال
mysqli_close($conn);

// عرض النتائج
echo "<h2>نتيجة إنشاء الجداول</h2>";
echo "<ul>";
foreach ($results as $table => $message) {
    echo "<li>$message</li>";
}
echo "</ul>";

if ($success) {
    echo "<p style='color: green;'>تم إنشاء جميع الجداول بنجاح!</p>";
    echo "<p>الرجاء حذف هذا الملف بعد إنشاء الجداول لأسباب أمنية.</p>";
} else {
    echo "<p style='color: red;'>حدثت أخطاء أثناء إنشاء الجداول. يرجى مراجعة الرسائل أعلاه.</p>";
}
?>

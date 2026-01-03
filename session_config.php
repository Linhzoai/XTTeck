<?php
// Cấu hình session - phải được gọi TRƯỚC session_start()
ini_set('session.cookie_lifetime', 86400); // 24 giờ
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

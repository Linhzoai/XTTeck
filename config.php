
<?php
// Thiết lập thông tin kết nối
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "xttech";

// Tạo kết nối
$conn = new mysqli($host, $user, $pass, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập charset
$conn->set_charset("utf8mb4");
?>
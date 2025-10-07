<?php
session_start();

// Tạo session_id nếu chưa có
if (!isset($_SESSION['cart_session_id'])) {
    $_SESSION['cart_session_id'] = session_id();
}

$conn = new mysqli("localhost", "root", "", "xttech");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Nếu có POST từ form đặt hàng, thêm vào giỏ
if (isset($_POST['sanpham_id'])) {
    $session_id = $_SESSION['cart_session_id'];
    $sanpham_id = (int)$_POST['sanpham_id'];
    $so_luong = 1; // Mặc định 1
    
    // Kiểm tra xem sản phẩm đã có trong giỏ chưa
    $check = $conn->query("SELECT * FROM giohang WHERE session_id='$session_id' AND sanpham_id=$sanpham_id");
    
    if ($check->num_rows > 0) {
        // Cập nhật số lượng
        $conn->query("UPDATE giohang SET so_luong = so_luong + $so_luong WHERE session_id='$session_id' AND sanpham_id=$sanpham_id");
    } else {
        // Thêm mới
        $conn->query("INSERT INTO giohang (session_id, sanpham_id, so_luong) VALUES ('$session_id', $sanpham_id, $so_luong)");
    }
}

$conn->close();

// Chuyển hướng đến trang giỏ hàng
header("Location: giohang.php");
exit;
?>
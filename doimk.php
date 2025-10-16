<?php
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 1) {
    header("Location: login.php");
    exit;
}

// Kết nối database (sửa lại theo cấu hình của bạn)
require_once 'config.php';
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['admin_id'];
    $new_pass = $_POST['password'];
    $confirm_pass = $_POST['confirmPassword'];

    if (empty($new_pass) || empty($confirm_pass)) {
        $message = "Vui lòng nhập đầy đủ thông tin.";
    } elseif ($new_pass !== $confirm_pass) {
        $message = "Mật khẩu xác nhận không khớp.";
    } else {
        // Cập nhật vào database
        $sql = "UPDATE user SET mk = ? WHERE id_admin = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $confirm_pass, $admin_id);

        if ($stmt->execute()) {
            $message = "Đổi mật khẩu thành công!";
        } else {
            $message = "Có lỗi xảy ra, vui lòng thử lại.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/doimk.css">
    <title>Đổi mật khẩu</title>
</head>
<body>
    <div class="mainDiv">
        <div class="cardStyle">
            <form action="" method="post" name="signupForm" id="signupForm">        
                <h2 class="formTitle">
                    Đổi mật khẩu Admin
                </h2>

                <?php if (!empty($message)): ?>
                    <p style="color:red; text-align:center;"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
                
                <div class="inputDiv">
                    <label class="inputLabel" for="password">Mật khẩu mới</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="inputDiv">
                    <label class="inputLabel" for="confirmPassword">Xác nhận mật khẩu</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                
                <div class="buttonWrapper">
                    <button type="submit" id="submitButton" class="submitButton pure-button pure-button-primary">
                        <span>Đổi mật khẩu</span>
                    </button>
                </div>           
            </form>
        </div>
    </div>
</body>
</html>

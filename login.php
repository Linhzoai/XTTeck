<?php
session_start();

// Kết nối CSDL
require_once 'config.php';

$errors = '';
$success = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nếu là đăng ký
    if (isset($_POST['register'])) {
        $username = trim($_POST['reg_username'] ?? '');
        $password = trim($_POST['reg_password'] ?? '');
        $email = trim($_POST['reg_email'] ?? '');

        if ($username === '' || $password === '' || $email === '') {
            $errors = 'Vui lòng nhập đầy đủ thông tin đăng ký.';
        } else {
            // Kiểm tra trùng tài khoản
            $check = $conn->prepare("SELECT id FROM user WHERE user = ? LIMIT 1");
            $check->bind_param("s", $username);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $errors = 'Tên tài khoản đã tồn tại.';
            } else {
                // Mã hóa mật khẩu
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Thêm người dùng mới (role = 0)
                $insert = $conn->prepare("INSERT INTO user (user, mk, email, role) VALUES (?, ?, ?, 0)");
                $insert->bind_param("sss", $username, $hash, $email);

                if ($insert->execute()) {
                    $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                } else {
                    $errors = 'Đăng ký thất bại: ' . $conn->error;
                }
                $insert->close();
            }

            $check->close();
        }
    }

    // Nếu là đăng nhập
    if (isset($_POST['login'])) {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if ($username === '' || $password === '') {
            $errors = 'Vui lòng nhập tài khoản và mật khẩu.';
        } else {
            $stmt = $conn->prepare("SELECT id, user, mk, role FROM user WHERE user = ? LIMIT 1");
            if (!$stmt) {
                die("Lỗi prepare: " . $conn->error);
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $hash = $row['mk'];

                if (password_verify($password, $hash) || $password === $hash) {
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_user'] = $row['user'];
                    $_SESSION['admin_role'] = $row['role'];

                    if ((int)$row['role'] === 1) {
                        header("Location: master.php");
                    } else {
                        header("Location: index_kh.php");
                    }
                    exit;
                } else {
                    $errors = 'Sai mật khẩu.';
                }
            } else {
                $errors = 'Tài khoản không tồn tại.';
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="js/login.js" defer></script>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Đăng nhập</title>
</head>
<body>
    <div class="container" id="container">

        <!-- Đăng ký -->
        <div class="form-container sign-up-container">
            <form action="login.php" method="POST">
                <h1>Tạo tài khoản</h1>

                <?php if ($errors && isset($_POST['register'])): ?>
                    <p style="color:red; font-weight:600;"><?= htmlspecialchars($errors) ?></p>
                <?php elseif ($success): ?>
                    <p style="color:green; font-weight:600;"><?= htmlspecialchars($success) ?></p>
                <?php endif; ?>

                <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <span>hoặc dùng email của bạn để đăng ký</span>

                <input type="text" name="reg_username" placeholder="Tên tài khoản" required />
                <input type="email" name="reg_email" placeholder="Email" required />
                <input type="password" name="reg_password" placeholder="Mật khẩu" required />
                <button type="submit" name="register">Đăng ký</button>
            </form>
        </div>

        <!-- Đăng nhập -->
        <div class="form-container sign-in-container">
            <form action="login.php" method="POST">
                <h1>Đăng nhập</h1>

                <?php if ($errors && isset($_POST['login'])): ?>
                    <p style="color:red; font-weight:600;"><?= htmlspecialchars($errors) ?></p>
                <?php endif; ?>

                <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <span>hoặc sử dụng tài khoản của bạn</span>

                <input type="text" name="username" placeholder="Tài khoản" value="<?= htmlspecialchars($username) ?>" required />
                <input type="password" name="password" placeholder="Mật khẩu" required />
                <a href="#">Quên mật khẩu?</a>
                <button type="submit" name="login">Đăng nhập</button>
            </form>
        </div>

        <!-- Overlay -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Chào mừng trở lại!</h1>
                    <p>Để tiếp tục, vui lòng đăng nhập bằng tài khoản của bạn</p>
                    <button class="ghost" id="signIn">Đăng nhập</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Xin chào!</h1>
                    <p>Nhập thông tin của bạn để bắt đầu hành trình cùng chúng tôi</p>
                    <button class="ghost" id="signUp">Đăng ký</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

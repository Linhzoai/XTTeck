<?php
require_once 'session_config.php';
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: khoahoc.php');
    exit;
}

$id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Lấy thông tin khóa học
$sql = "SELECT kh.*, nd.ho_ten as ten_giang_vien
        FROM khoa_hoc kh 
        LEFT JOIN nguoi_dung nd ON kh.id_giang_vien = nd.id
        WHERE kh.id = ? AND kh.trang_thai_khoa_hoc = 'publish'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: khoahoc.php');
    exit;
}

$course = $result->fetch_assoc();

// Kiểm tra đã đăng ký chưa
$check = $conn->query("SELECT id FROM dang_ky WHERE id_nguoi_dung = $user_id AND id_khoa_hoc = $id");
if ($check->num_rows > 0) {
    header('Location: khoahoc_cua_toi.php');
    exit;
}

// Xử lý đăng ký
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ma_giam_gia = $_POST['ma_giam_gia'] ?? '';
    $giam_gia = 0;
    $gia_cuoi = $course['gia'];

    // Kiểm tra mã giảm giá
    if ($ma_giam_gia) {
        $sql = 'SELECT * FROM ma_giam_gia 
                WHERE ma = ? 
                AND ngay_bat_dau <= NOW() 
                AND ngay_ket_thuc >= NOW() 
                AND so_luong > 0';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $ma_giam_gia);
        $stmt->execute();
        $discount_result = $stmt->get_result();

        if ($discount_result->num_rows > 0) {
            $discount = $discount_result->fetch_assoc();
            $giam_gia = $discount['giam_gia'];
            $gia_cuoi = $course['gia'] * (100 - $giam_gia) / 100;

            // Giảm số lượng mã
            $conn->query("UPDATE ma_giam_gia SET so_luong = so_luong - 1 WHERE id = {$discount['id']}");
        }
    }

    // Tạo đăng ký
    $sql = "INSERT INTO dang_ky (id_nguoi_dung, id_khoa_hoc, ngay_dang_ky, trang_thai) 
            VALUES (?, ?, NOW(), 'dang_ky')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $id);

    if ($stmt->execute()) {
        $dang_ky_id = $conn->insert_id;

        // Tạo thanh toán
        $sql = "INSERT INTO thanh_toan (id_dang_ky, so_tien, phuong_thuc, trang_thai, ngay_thanh_toan) 
                VALUES (?, ?, 'chuyen_khoan', 'cho_xu_ly', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('id', $dang_ky_id, $gia_cuoi);
        $stmt->execute();

        $success = true;
    } else {
        $error = 'Có lỗi xảy ra. Vui lòng thử lại!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký khóa học - CODE4Fun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        .course-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .price-display {
            font-size: 2rem;
            font-weight: 800;
            color: #d40000;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            background: linear-gradient(45deg, #2f74d5, #1a5bb8);
            border: none;
            border-radius: 10px;
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(47,116,213,0.4);
        }
    </style>
</head>
<body>

<div class="register-container">
    <?php if ($success) { ?>
        <div class="text-center">
            <i class="fa-solid fa-check-circle text-success" style="font-size: 80px;"></i>
            <h2 class="mt-4 mb-3">Đăng ký thành công!</h2>
            <p class="lead">Cảm ơn bạn đã đăng ký khóa học <strong><?php echo htmlspecialchars($course['ten']); ?></strong></p>
            <div class="alert alert-info mt-4">
                <i class="fa-solid fa-info-circle"></i>
                Vui lòng chuyển khoản để hoàn tất đăng ký. Chúng tôi sẽ xác nhận trong vòng 24h.
            </div>
            <div class="mt-4">
                <a href="khoahoc_cua_toi.php" class="btn btn-primary me-2">
                    <i class="fa-solid fa-book"></i> Khóa học của tôi
                </a>
                <a href="khoahoc.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Tiếp tục khám phá
                </a>
            </div>
        </div>
    <?php } else { ?>
        <h2 class="text-center mb-4">
            <i class="fa-solid fa-shopping-cart"></i> Đăng ký khóa học
        </h2>

        <?php if ($error) { ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php } ?>

        <div class="course-info">
            <h4><?php echo htmlspecialchars($course['ten']); ?></h4>
            <p class="text-muted mb-2">
                <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($course['ten_giang_vien']); ?>
            </p>
            <div class="price-display"><?php echo number_format($course['gia']); ?>₫</div>
        </div>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Mã giảm giá (nếu có)</label>
                <input type="text" name="ma_giam_gia" class="form-control" placeholder="Nhập mã giảm giá">
            </div>

            <div class="alert alert-info">
                <strong><i class="fa-solid fa-info-circle"></i> Thông tin thanh toán:</strong><br>
                Ngân hàng: Vietcombank<br>
                Số tài khoản: 1234567890<br>
                Chủ tài khoản: CODE4Fun<br>
                Nội dung: DK<?php echo $id; ?>_<?php echo $user_id; ?>
            </div>

            <button type="submit" class="btn btn-submit">
                <i class="fa-solid fa-check"></i> Xác nhận đăng ký
            </button>

            <div class="text-center mt-3">
                <a href="chitietkhoahoc.php?id=<?php echo $id; ?>" class="text-muted">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </form>
    <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>


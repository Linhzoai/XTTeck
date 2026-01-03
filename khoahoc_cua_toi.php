<?php
require_once 'session_config.php';
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy khóa học đã đăng ký
$sql = 'SELECT kh.*, dk.ngay_dang_ky, dk.trang_thai as trang_thai_dk, 
               nd.ho_ten as ten_giang_vien,
               tt.trang_thai as trang_thai_tt
        FROM dang_ky dk
        JOIN khoa_hoc kh ON dk.id_khoa_hoc = kh.id
        LEFT JOIN nguoi_dung nd ON kh.id_giang_vien = nd.id
        LEFT JOIN thanh_toan tt ON dk.id = tt.id_dang_ky
        WHERE dk.id_nguoi_dung = ?
        ORDER BY dk.ngay_dang_ky DESC';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khóa học của tôi - CODE4Fun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(90deg, #2f74d5, #1a5bb8);
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .page-header {
            background: linear-gradient(135deg, #2f74d5 0%, #1a5bb8 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .course-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 30px;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        }

        .course-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .course-body {
            padding: 20px;
        }

        .course-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2f74d5;
            margin-bottom: 10px;
        }

        .btn-learn {
            width: 100%;
            background: linear-gradient(45deg, #2f74d5, #1a5bb8);
            border: none;
            padding: 10px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
        }

        .btn-learn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(47,116,213,0.3);
            color: white;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 80px;
            color: #ccc;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fa-solid fa-code"></i> CODE4Fun
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="khoahoc.php">Khóa học</a></li>
                <li class="nav-item"><a class="nav-link active" href="khoahoc_cua_toi.php">Khóa học của tôi</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Đăng xuất</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><i class="fa-solid fa-book-reader"></i> Khóa học của tôi</h1>
        <p class="lead">Quản lý và học tập các khóa học đã đăng ký</p>
    </div>
</div>

<!-- Main Content -->
<div class="container mb-5">
    <div class="row">
        <?php if ($result && $result->num_rows > 0) { ?>
            <?php while ($course = $result->fetch_assoc()) { ?>
                <div class="col-md-4">
                    <div class="course-card position-relative">
                        <?php
                        $badge_class = 'bg-warning';
                $badge_text = 'Chờ xác nhận';
                if ($course['trang_thai_tt'] == 'thanh_cong') {
                    $badge_class = 'bg-success';
                    $badge_text = 'Đã kích hoạt';
                }
                ?>
                        <span class="status-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>

                        <img src="<?php echo htmlspecialchars($course['hinh_anh'] ?? 'img/default-course.jpg'); ?>"
                             alt="<?php echo htmlspecialchars($course['ten']); ?>"
                             class="course-img">

                        <div class="course-body">
                            <h5 class="course-title"><?php echo htmlspecialchars($course['ten']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($course['ten_giang_vien']); ?>
                            </p>
                            <p class="text-muted mb-3">
                                <i class="fa-solid fa-calendar"></i>
                                Đăng ký: <?php echo date('d/m/Y', strtotime($course['ngay_dang_ky'])); ?>
                            </p>

                            <?php if ($course['trang_thai_tt'] == 'thanh_cong') { ?>
                                <a href="hoc.php?id=<?php echo $course['id']; ?>" class="btn btn-learn">
                                    <i class="fa-solid fa-play"></i> Tiếp tục học
                                </a>
                            <?php } else { ?>
                                <button class="btn btn-learn" disabled>
                                    <i class="fa-solid fa-clock"></i> Chờ xác nhận thanh toán
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="fa-solid fa-book-open"></i>
                    <h3>Bạn chưa đăng ký khóa học nào</h3>
                    <p class="text-muted">Khám phá các khóa học thú vị và bắt đầu hành trình học tập của bạn!</p>
                    <a href="khoahoc.php" class="btn btn-primary btn-lg mt-3">
                        <i class="fa-solid fa-search"></i> Khám phá khóa học
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>


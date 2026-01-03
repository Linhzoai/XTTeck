<?php
require_once 'session_config.php';
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: khoahoc.php');
    exit;
}

$id = (int) $_GET['id'];

// Xử lý thêm đánh giá
$review_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $review_message = '<div class="alert alert-warning">Bạn cần đăng nhập để đánh giá!</div>';
    } else {
        $user_id = $_SESSION['user_id'];

        // Kiểm tra đã đăng ký khóa học chưa
        $check_enrolled = $conn->query("SELECT id FROM dang_ky WHERE id_nguoi_dung = $user_id AND id_khoa_hoc = $id");
        if (!$check_enrolled || $check_enrolled->num_rows == 0) {
            $review_message = '<div class="alert alert-warning">Bạn cần đăng ký khóa học trước khi đánh giá!</div>';
        } else {
            // Kiểm tra đã đánh giá chưa
            $check_review = $conn->query("SELECT id FROM danh_gia WHERE id_nguoi_dung = $user_id AND id_khoa_hoc = $id");
            if ($check_review && $check_review->num_rows > 0) {
                $review_message = '<div class="alert alert-warning">Bạn đã đánh giá khóa học này rồi!</div>';
            } else {
                $diem = (int) $_POST['diem'];
                $binh_luan = $conn->real_escape_string($_POST['binh_luan']);

                if ($diem < 1 || $diem > 5) {
                    $review_message = '<div class="alert alert-danger">Điểm đánh giá phải từ 1 đến 5 sao!</div>';
                } else {
                    $sql = "INSERT INTO danh_gia (id_nguoi_dung, id_khoa_hoc, diem, binh_luan, ngay_danh_gia)
                            VALUES ($user_id, $id, $diem, '$binh_luan', NOW())";
                    if ($conn->query($sql)) {
                        $review_message = '<div class="alert alert-success">Cảm ơn bạn đã đánh giá khóa học!</div>';
                        // Reload để hiển thị đánh giá mới
                        header("Location: chitietkhoahoc.php?id=$id&review=success");
                        exit;
                    } else {
                        $review_message = '<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại!</div>';
                    }
                }
            }
        }
    }
}

// Hiển thị thông báo từ URL
if (isset($_GET['review']) && $_GET['review'] == 'success') {
    $review_message = '<div class="alert alert-success">Cảm ơn bạn đã đánh giá khóa học!</div>';
}

// Lấy thông tin khóa học
$sql = "SELECT kh.*, dm.ten as ten_danh_muc, nd.ho_ten as ten_giang_vien, nd.email as email_giang_vien
        FROM khoa_hoc kh 
        LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
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

// Lấy số lượng học viên đã đăng ký
$enrolled_result = $conn->query("SELECT COUNT(*) as total FROM dang_ky WHERE id_khoa_hoc = $id");
$enrolled = $enrolled_result ? $enrolled_result->fetch_assoc()['total'] : 0;

// Lấy đánh giá
$reviews = $conn->query("SELECT dg.*, nd.ho_ten
                         FROM danh_gia dg
                         JOIN nguoi_dung nd ON dg.id_nguoi_dung = nd.id
                         WHERE dg.id_khoa_hoc = $id
                         ORDER BY dg.ngay_danh_gia DESC
                         LIMIT 5");

// Tính điểm trung bình
$avg_result = $conn->query("SELECT AVG(diem) as avg FROM danh_gia WHERE id_khoa_hoc = $id");
$avg_rating = 0;
if ($avg_result && $avg_result->num_rows > 0) {
    $avg_data = $avg_result->fetch_assoc();
    $avg_rating = $avg_data['avg'] ?? 0;
}

// Kiểm tra đã đăng ký chưa
$is_enrolled = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check = $conn->query("SELECT id FROM dang_ky WHERE id_nguoi_dung = $user_id AND id_khoa_hoc = $id");
    $is_enrolled = $check && $check->num_rows > 0;
}

// Lấy bài học
$lessons = $conn->query("SELECT * FROM bai_hoc WHERE id_khoa_hoc = $id ORDER BY thu_tu ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['ten']); ?> - CODE4Fun</title>
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

        .course-header {
            background: linear-gradient(135deg, #2f74d5 0%, #1a5bb8 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .course-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .course-meta {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .price-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }

        .price {
            font-size: 2.5rem;
            font-weight: 800;
            color: #d40000;
            margin-bottom: 20px;
        }

        .btn-enroll {
            width: 100%;
            padding: 15px;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(45deg, #2f74d5, #1a5bb8);
            border: none;
            border-radius: 10px;
            color: white;
            transition: all 0.3s;
        }

        .btn-enroll:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(47,116,213,0.4);
            color: white;
        }

        .content-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2f74d5;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #2f74d5;
        }

        .lesson-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lesson-item:last-child {
            border-bottom: none;
        }

        .review-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .stars {
            color: #ffc107;
        }

        .instructor-box {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .instructor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
        }

        /* Rating Input */
        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
            font-size: 30px;
        }

        .rating-input input[type="radio"] {
            display: none;
        }

        .rating-input label {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }

        .rating-input label:hover,
        .rating-input label:hover ~ label,
        .rating-input input[type="radio"]:checked ~ label {
            color: #ffc107;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .course-header {
                padding: 30px 0;
            }
            .course-title {
                font-size: 24px;
            }
            .sidebar-card {
                margin-top: 20px;
            }
            .content-box {
                padding: 15px;
            }
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
                <?php if (isset($_SESSION['user_id'])) { ?>
                <li class="nav-item"><a class="nav-link" href="khoahoc_cua_toi.php">Khóa học của tôi</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Đăng xuất</a></li>
                <?php } else { ?>
                <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Course Header -->
<div class="course-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="badge bg-danger mb-3"><?php echo htmlspecialchars($course['ten_danh_muc']); ?></div>
                <h1 class="course-title"><?php echo htmlspecialchars($course['ten']); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($course['mo_ta']); ?></p>
                <div class="course-meta">
                    <div class="meta-item">
                        <i class="fa-solid fa-user"></i>
                        <span><?php echo htmlspecialchars($course['ten_giang_vien']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-users"></i>
                        <span><?php echo number_format($enrolled); ?> học viên</span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-star"></i>
                        <span><?php echo number_format($avg_rating, 1); ?> / 5.0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mb-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Hình ảnh khóa học -->
            <?php if (!empty($course['hinh_anh'])) { ?>
            <div class="content-box">
                <img src="<?php echo htmlspecialchars($course['hinh_anh']); ?>"
                     alt="<?php echo htmlspecialchars($course['ten']); ?>"
                     class="img-fluid rounded"
                     style="width: 100%; max-height: 400px; object-fit: cover;">
            </div>
            <?php } ?>

            <!-- Video Demo -->
            <?php if (!empty($course['video_demo'])) { ?>
            <div class="content-box">
                <h3 class="section-title"><i class="fa-solid fa-play-circle"></i> Video giới thiệu</h3>
                <div class="ratio ratio-16x9">
                    <iframe src="<?php echo htmlspecialchars($course['video_demo']); ?>"
                            allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                </div>
                <p class="text-muted mt-2 small">
                    <i class="fa-brands fa-youtube"></i>
                    <a href="<?php echo htmlspecialchars($course['video_demo']); ?>" target="_blank">Xem trên YouTube</a>
                </p>
            </div>
            <?php } ?>

            <!-- Mô tả chi tiết -->
            <div class="content-box">
                <h3 class="section-title"><i class="fa-solid fa-info-circle"></i> Mô tả khóa học</h3>
                <div><?php echo nl2br(htmlspecialchars($course['mo_ta_chi_tiet'] ?? $course['mo_ta'])); ?></div>
            </div>

            <!-- Nội dung khóa học -->
            <div class="content-box">
                <h3 class="section-title"><i class="fa-solid fa-list"></i> Nội dung khóa học</h3>
                <?php if ($lessons && $lessons->num_rows > 0) { ?>
                    <?php while ($lesson = $lessons->fetch_assoc()) { ?>
                    <div class="lesson-item">
                        <div>
                            <i class="fa-solid fa-play-circle text-primary"></i>
                            <strong><?php echo htmlspecialchars($lesson['ten']); ?></strong>
                        </div>
                        <span class="text-muted">Bài <?php echo $lesson['thu_tu'] ?? ''; ?></span>
                    </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="text-muted">Nội dung đang được cập nhật...</p>
                <?php } ?>
            </div>

            <!-- Giảng viên -->
            <div class="content-box">
                <h3 class="section-title"><i class="fa-solid fa-chalkboard-teacher"></i> Giảng viên</h3>
                <div class="instructor-box">
                    <div class="instructor-avatar">
                        <?php echo strtoupper(substr($course['ten_giang_vien'], 0, 1)); ?>
                    </div>
                    <div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($course['ten_giang_vien']); ?></h5>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($course['email_giang_vien']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Đánh giá -->
            <div class="content-box">
                <h3 class="section-title"><i class="fa-solid fa-star"></i> Đánh giá từ học viên</h3>

                <!-- Thông báo -->
                <?php echo $review_message; ?>

                <!-- Form thêm đánh giá -->
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <?php
                    // Kiểm tra đã đánh giá chưa
                    $user_id = $_SESSION['user_id'];
                    $check_review = $conn->query("SELECT id FROM danh_gia WHERE id_nguoi_dung = $user_id AND id_khoa_hoc = $id");
                    $has_reviewed = $check_review && $check_review->num_rows > 0;

                    // Kiểm tra đã đăng ký chưa
                    $check_enrolled = $conn->query("SELECT id FROM dang_ky WHERE id_nguoi_dung = $user_id AND id_khoa_hoc = $id");
                    $is_enrolled_check = $check_enrolled && $check_enrolled->num_rows > 0;
                    ?>

                    <?php if ($is_enrolled_check && !$has_reviewed) { ?>
                    <div class="card mb-4" style="border: 2px solid #2f74d5; border-radius: 10px;">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="fa-solid fa-pen"></i> Viết đánh giá của bạn</h5>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Đánh giá của bạn:</strong></label>
                                    <div class="rating-input">
                                        <input type="radio" name="diem" value="5" id="star5" required>
                                        <label for="star5" title="5 sao"><i class="fa-solid fa-star"></i></label>

                                        <input type="radio" name="diem" value="4" id="star4">
                                        <label for="star4" title="4 sao"><i class="fa-solid fa-star"></i></label>

                                        <input type="radio" name="diem" value="3" id="star3">
                                        <label for="star3" title="3 sao"><i class="fa-solid fa-star"></i></label>

                                        <input type="radio" name="diem" value="2" id="star2">
                                        <label for="star2" title="2 sao"><i class="fa-solid fa-star"></i></label>

                                        <input type="radio" name="diem" value="1" id="star1">
                                        <label for="star1" title="1 sao"><i class="fa-solid fa-star"></i></label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="binh_luan" class="form-label"><strong>Nhận xét:</strong></label>
                                    <textarea name="binh_luan" id="binh_luan" class="form-control" rows="4"
                                              placeholder="Chia sẻ trải nghiệm của bạn về khóa học này..." required></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-primary">
                                    <i class="fa-solid fa-paper-plane"></i> Gửi đánh giá
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php } elseif (!$is_enrolled_check) { ?>
                    <div class="alert alert-info mb-4">
                        <i class="fa-solid fa-info-circle"></i> Bạn cần đăng ký khóa học để có thể đánh giá.
                    </div>
                    <?php } ?>
                <?php } else { ?>
                <div class="alert alert-info mb-4">
                    <i class="fa-solid fa-info-circle"></i> Vui lòng <a href="login.php" class="alert-link">đăng nhập</a> để đánh giá khóa học.
                </div>
                <?php } ?>

                <!-- Danh sách đánh giá -->
                <h5 class="mt-4 mb-3">Đánh giá từ học viên khác:</h5>
                <?php if ($reviews && $reviews->num_rows > 0) { ?>
                    <?php while ($review = $reviews->fetch_assoc()) { ?>
                    <div class="review-item">
                        <div class="d-flex justify-content-between mb-2">
                            <strong><?php echo htmlspecialchars($review['ho_ten']); ?></strong>
                            <div class="stars">
                                <?php for ($i = 0; $i < 5; ++$i) { ?>
                                    <i class="fa-solid fa-star<?php echo $i < ($review['diem'] ?? 0) ? '' : '-o'; ?>"></i>
                                <?php } ?>
                            </div>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($review['binh_luan'] ?? 'Không có nhận xét'); ?></p>
                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($review['ngay_danh_gia'])); ?></small>
                    </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="text-muted">Chưa có đánh giá nào.</p>
                <?php } ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="price-box">
                <div class="price"><?php echo number_format($course['gia']); ?>₫</div>
                <?php if ($is_enrolled) { ?>
                    <a href="khoahoc_cua_toi.php" class="btn btn-success btn-enroll">
                        <i class="fa-solid fa-check-circle"></i> Đã đăng ký
                    </a>
                <?php } elseif (isset($_SESSION['user_id'])) { ?>
                    <a href="dangky_khoahoc.php?id=<?php echo $course['id']; ?>" class="btn btn-enroll">
                        <i class="fa-solid fa-shopping-cart"></i> Đăng ký ngay
                    </a>
                <?php } else { ?>
                    <a href="login.php?redirect=chitietkhoahoc.php?id=<?php echo $course['id']; ?>" class="btn btn-enroll">
                        <i class="fa-solid fa-sign-in-alt"></i> Đăng nhập để đăng ký
                    </a>
                <?php } ?>

                <hr>

                <div class="mb-3">
                    <i class="fa-solid fa-users text-primary"></i>
                    <strong><?php echo number_format($enrolled); ?></strong> học viên đã đăng ký
                </div>
                <div class="mb-3">
                    <i class="fa-solid fa-clock text-primary"></i>
                    Học mọi lúc, mọi nơi
                </div>
                <div class="mb-3">
                    <i class="fa-solid fa-certificate text-primary"></i>
                    Cấp chứng chỉ hoàn thành
                </div>
                <div>
                    <i class="fa-solid fa-infinity text-primary"></i>
                    Truy cập trọn đời
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>


<?php
// Khởi tạo session
require_once 'session_config.php';

// Kết nối CSDL
require_once 'config.php';

// Lấy danh mục cho bộ lọc
$dm_result = $conn->query('SELECT * FROM danh_muc');

// Xử lý tìm kiếm và lọc
$where = "kh.trang_thai_khoa_hoc = 'publish'";
$keyword = '';
$danh_muc_id = '';

if (isset($_GET['keyword']) && $_GET['keyword'] !== '') {
    $keyword = $conn->real_escape_string($_GET['keyword']);
    $where .= " AND kh.ten LIKE '%$keyword%'";
}
if (isset($_GET['danh_muc']) && $_GET['danh_muc'] !== '') {
    $danh_muc_id = (int) $_GET['danh_muc'];
    $where .= " AND kh.id_danh_muc = $danh_muc_id";
}

// Lấy khóa học
$sql = "SELECT kh.*, dm.ten as ten_danh_muc, nd.ho_ten as ten_giang_vien 
        FROM khoa_hoc kh 
        LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
        LEFT JOIN nguoi_dung nd ON kh.id_giang_vien = nd.id
        WHERE $where
        ORDER BY kh.ngay_tao DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Khóa học - CODE4Fun</title>
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

      .navbar-brand {
          font-size: 1.5rem;
          font-weight: 700;
      }

      .search-bar {
          background: #fff;
          padding: 20px;
          border-radius: 12px;
          box-shadow: 0 3px 10px rgba(0,0,0,0.05);
          margin-top: 30px;
      }

      .form-control {
          border-radius: 10px;
          box-shadow: none;
      }

      .btn-primary {
          border-radius: 10px;
          background: linear-gradient(45deg, #2f74d5, #1a5bb8);
          border: none;
          transition: transform 0.2s ease, box-shadow 0.2s ease;
      }

      .btn-primary:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 10px rgba(47,116,213,0.4);
      }

      .course-card {
          border: none;
          border-radius: 15px;
          overflow: hidden;
          background: #fff;
          box-shadow: 0 3px 10px rgba(0,0,0,0.08);
          transition: transform 0.25s ease, box-shadow 0.25s ease;
          margin-bottom: 30px;
      }

      .course-card:hover {
          transform: translateY(-6px);
          box-shadow: 0 6px 18px rgba(0,0,0,0.15);
      }

      .course-img {
          height: 220px;
          object-fit: cover;
          width: 100%;
          border-bottom: 1px solid #eee;
      }

      .card-body {
          padding: 20px;
      }

      .course-title {
          font-size: 1.1rem;
          font-weight: 700;
          color: #2f74d5;
          margin-bottom: 10px;
          min-height: 50px;
      }

      .course-instructor {
          color: #666;
          font-size: 0.9rem;
          margin-bottom: 10px;
      }

      .course-price {
          font-size: 1.4rem;
          font-weight: 700;
          color: #d40000;
          margin-bottom: 15px;
      }

      .btn-view {
          width: 100%;
          background: linear-gradient(45deg, #2f74d5, #1a5bb8);
          border: none;
          padding: 10px;
          border-radius: 8px;
          color: white;
          font-weight: 600;
          transition: all 0.3s ease;
      }

      .btn-view:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 12px rgba(47,116,213,0.3);
          color: white;
      }

      .badge-category {
          position: absolute;
          top: 15px;
          right: 15px;
          background: linear-gradient(135deg, #d40000 0%, #a00000 100%);
          color: white;
          padding: 6px 12px;
          border-radius: 20px;
          font-size: 12px;
          font-weight: 600;
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
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="khoahoc.php">Khóa học</a>
                </li>
                <?php if (isset($_SESSION['user_id'])) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="khoahoc_cua_toi.php">Khóa học của tôi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Đăng xuất</a>
                </li>
                <?php } else { ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Đăng nhập</a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container my-5">
    <h1 class="text-center mb-4" style="color: #2f74d5; font-weight: 800;">
        <i class="fa-solid fa-book"></i> KHÓA HỌC LẬP TRÌNH
    </h1>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="get" action="khoahoc.php">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm khóa học..." value="<?php echo htmlspecialchars($keyword); ?>">
                </div>
                <div class="col-md-4">
                    <select name="danh_muc" class="form-select">
                        <option value="">-- Tất cả danh mục --</option>
                        <?php
                        if ($dm_result && $dm_result->num_rows > 0) {
                            $dm_result->data_seek(0);
                            while ($dm = $dm_result->fetch_assoc()) {
                                $selected = ($danh_muc_id == $dm['id']) ? 'selected' : '';
                                echo "<option value='{$dm['id']}' $selected>".htmlspecialchars($dm['ten']).'</option>';
                            }
                        }
?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Course List -->
    <div class="row mt-5">
        <?php if ($result && $result->num_rows > 0) { ?>
            <?php while ($course = $result->fetch_assoc()) { ?>
                <div class="col-md-4 col-lg-3">
                    <div class="course-card position-relative">
                        <?php if ($course['ten_danh_muc']) { ?>
                            <span class="badge-category"><?php echo htmlspecialchars($course['ten_danh_muc']); ?></span>
                        <?php } ?>
                        <img src="<?php echo htmlspecialchars($course['hinh_anh'] ?? 'img/default-course.svg'); ?>"
                             alt="<?php echo htmlspecialchars($course['ten']); ?>"
                             class="course-img">
                        <div class="card-body">
                            <h5 class="course-title"><?php echo htmlspecialchars($course['ten']); ?></h5>
                            <p class="course-instructor">
                                <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($course['ten_giang_vien'] ?? 'Giảng viên'); ?>
                            </p>
                            <p class="course-price"><?php echo number_format($course['gia']); ?>₫</p>
                            <a href="chitietkhoahoc.php?id=<?php echo $course['id']; ?>" class="btn btn-view">
                                <i class="fa-solid fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fa-solid fa-info-circle"></i> Không tìm thấy khóa học nào phù hợp.
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4 mt-5">
    <p class="mb-0">© 2025 CODE4Fun. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>

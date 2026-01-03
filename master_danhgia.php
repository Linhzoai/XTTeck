<?php
require_once 'session_config.php';
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'config.php';

// Xử lý xóa
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM danh_gia WHERE id = $id");
    header('Location: master_danhgia.php?msg=deleted');
    exit;
}

$reviews = $conn->query('SELECT dg.*, kh.ten as ten_khoa_hoc, nd.ho_ten
                         FROM danh_gia dg
                         JOIN khoa_hoc kh ON dg.id_khoa_hoc = kh.id
                         JOIN nguoi_dung nd ON dg.id_nguoi_dung = nd.id
                         ORDER BY dg.ngay_danh_gia DESC');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá - CODE4Fun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin-responsive.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 260px; 
                   background: linear-gradient(180deg, #2f74d5 0%, #1a5bb8 100%); padding: 20px 0; z-index: 1000; }
        .sidebar-brand { padding: 0 20px 30px; text-align: center; color: white; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { list-style: none; padding: 20px 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 15px 25px; color: rgba(255,255,255,0.8); 
                          text-decoration: none; transition: all 0.3s; border-left: 3px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; border-left-color: #ffc107; }
        .sidebar-menu a i { width: 25px; font-size: 18px; margin-right: 12px; }
        .main-content { margin-left: 260px; padding: 20px; }
        .top-navbar { background: white; padding: 15px 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .stars { color: #ffc107; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <h3><i class="fas fa-code"></i> CODE4Fun</h3>
        <p style="font-size: 12px; opacity: 0.8;">Hệ thống quản trị</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="master.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="master_khoahoc.php"><i class="fas fa-book"></i> Quản lý khóa học</a></li>
        <li><a href="master_dangky.php"><i class="fas fa-user-graduate"></i> Quản lý đăng ký</a></li>
        <li><a href="master_hocvien.php"><i class="fas fa-users"></i> Quản lý học viên</a></li>
        <li><a href="master_dm.php"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
        <li><a href="master_danhgia.php" class="active"><i class="fas fa-star"></i> Quản lý đánh giá</a></li>
        <li><a href="master_magiamgia.php"><i class="fas fa-tags"></i> Mã giảm giá</a></li>
        <li><a href="index.php" target="_blank"><i class="fas fa-globe"></i> Xem website</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="top-navbar">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-star"></i> Quản lý đánh giá</h4>
            <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])) { ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> Xóa thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách đánh giá</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Học viên</th>
                            <th>Khóa học</th>
                            <th>Đánh giá</th>
                            <th>Nội dung</th>
                            <th>Ngày</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($review = $reviews->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $review['id']; ?></td>
                                <td><?php echo htmlspecialchars($review['ho_ten']); ?></td>
                                <td><?php echo htmlspecialchars($review['ten_khoa_hoc']); ?></td>
                                <td>
                                    <span class="stars">
                                        <?php for ($i = 0; $i < ($review['diem'] ?? 0); ++$i) { ?>
                                            <i class="fas fa-star"></i>
                                        <?php } ?>
                                        <?php for ($i = ($review['diem'] ?? 0); $i < 5; ++$i) { ?>
                                            <i class="far fa-star"></i>
                                        <?php } ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(substr($review['binh_luan'] ?? '', 0, 50)).'...'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($review['ngay_danh_gia'])); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $review['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Xác nhận xóa?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin-mobile.js"></script>
</body>
</html>

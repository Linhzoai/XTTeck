<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 1) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "xttech");
if ($conn->connect_error) die("Kết nối thất bại");
$conn->set_charset("utf8mb4");

$message = '';

// Xử lý xóa bình luận
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM binhluan WHERE id=$id")) {
        $message = '<div class="alert alert-success">Đã xóa bình luận!</div>';
    }
}

// Lấy danh sách bình luận
$comments = $conn->query("SELECT bl.*, sp.ten_sanpham, sp.hinh_anh
                         FROM binhluan bl
                         JOIN sanpham sp ON bl.sanpham_id = sp.id
                         ORDER BY bl.ngay_binh_luan DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bình luận - XTTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh; width: 260px;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px 0; z-index: 1000;
        }
        .sidebar-brand { padding: 0 20px 30px; text-align: center; color: white; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { list-style: none; padding: 20px 0; }
        .sidebar-menu a {
            display: flex; align-items: center; padding: 15px 25px;
            color: rgba(255,255,255,0.8); text-decoration: none;
            transition: all 0.3s; border-left: 3px solid transparent;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1); color: white; border-left-color: #ffc107;
        }
        .sidebar-menu a i { width: 25px; margin-right: 12px; }
        .main-content { margin-left: 260px; padding: 20px; }
        .content-card {
            background: white; border-radius: 12px; padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px;
        }
        .comment-item {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .comment-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <h3><i class="fas fa-tools"></i> XTTech</h3>
        <p>Hệ thống quản trị</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="master.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="master_sp.php"><i class="fas fa-box"></i> Quản lý sản phẩm</a></li>
        <li><a href="master_dh.php"><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</a></li>
        <li><a href="master_kh.php"><i class="fas fa-users"></i> Quản lý khách hàng</a></li>
        <li><a href="master_dm.php"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
        <li><a href="master_bl.php" class="active"><i class="fas fa-comments"></i> Quản lý bình luận</a></li>
        <li><a href="master_tk.php"><i class="fas fa-chart-bar"></i> Báo cáo thống kê</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-comments"></i> Quản lý Bình luận</h2>
        <a href="master.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>

    <?php echo $message; ?>

    <div class="content-card">
        <h5 class="mb-4"><i class="fas fa-list"></i> Danh sách bình luận (<?php echo $comments->num_rows; ?>)</h5>

        <?php if ($comments->num_rows > 0): ?>
            <?php while ($row = $comments->fetch_assoc()): ?>
            <div class="comment-item">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img src="<?php echo htmlspecialchars($row['hinh_anh']); ?>" 
                             class="img-fluid rounded" 
                             style="max-height: 80px; object-fit: cover;">
                    </div>
                    <div class="col-md-8">
                        <div class="mb-2">
                            <strong class="text-primary"><?php echo htmlspecialchars($row['ten_nguoi_dung']); ?></strong>
                            <small class="text-muted ms-2">
                                <i class="fas fa-clock"></i> 
                                <?php echo date('d/m/Y H:i', strtotime($row['ngay_binh_luan'])); ?>
                            </small>
                        </div>
                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($row['noi_dung'])); ?></p>
                        <small class="text-muted">
                            <i class="fas fa-box"></i> 
                            Sản phẩm: <strong><?php echo htmlspecialchars($row['ten_sanpham']); ?></strong>
                        </small>
                    </div>
                    <div class="col-md-2 text-end">
                        <a href="thongtinsanpham.php?id=<?php echo $row['sanpham_id']; ?>" 
                           class="btn btn-sm btn-info mb-2" target="_blank" title="Xem sản phẩm">
                            <i class="fas fa-eye"></i> Xem
                        </a>
                        <a href="?delete=<?php echo $row['id']; ?>" 
                           class="btn btn-sm btn-danger" title="Xóa"
                           onclick="return confirm('Bạn có chắc muốn xóa bình luận này?')">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                <p class="text-muted">Chưa có bình luận nào</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
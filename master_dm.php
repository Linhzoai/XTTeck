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

// Xử lý thêm/sửa danh mục
if (isset($_POST['save_category'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $ten_danhmuc = $conn->real_escape_string($_POST['ten_danhmuc']);
    
    if ($id > 0) {
        $conn->query("UPDATE danhmuc SET ten_danhmuc='$ten_danhmuc' WHERE id=$id");
        $message = '<div class="alert alert-success">Cập nhật danh mục thành công!</div>';
    } else {
        $conn->query("INSERT INTO danhmuc (ten_danhmuc) VALUES ('$ten_danhmuc')");
        $message = '<div class="alert alert-success">Thêm danh mục mới thành công!</div>';
    }
}

// Xử lý xóa danh mục
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Kiểm tra xem danh mục có sản phẩm không
    $check = $conn->query("SELECT COUNT(*) as c FROM sanpham WHERE danhmuc_id=$id")->fetch_assoc()['c'];
    
    if ($check > 0) {
        $message = '<div class="alert alert-warning">Không thể xóa danh mục này vì còn '.$check.' sản phẩm!</div>';
    } else {
        $conn->query("DELETE FROM danhmuc WHERE id=$id");
        $message = '<div class="alert alert-success">Đã xóa danh mục!</div>';
    }
}

// Lấy danh sách danh mục
$categories = $conn->query("SELECT dm.*, COUNT(sp.id) as so_sanpham
                           FROM danhmuc dm
                           LEFT JOIN sanpham sp ON dm.id = sp.danhmuc_id
                           GROUP BY dm.id
                           ORDER BY dm.id ASC");

// Lấy danh mục để sửa
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_category = $conn->query("SELECT * FROM danhmuc WHERE id=$edit_id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục - XTTech</title>
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
        .category-icon {
            width: 50px; height: 50px; border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 20px;
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
        <li><a href="master_dm.php" class="active"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
        <li><a href="master_bl.php"><i class="fas fa-comments"></i> Quản lý bình luận</a></li>
        <li><a href="master_tk.php"><i class="fas fa-chart-bar"></i> Báo cáo thống kê</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-list"></i> Quản lý Danh mục</h2>
        <a href="master.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>

    <?php echo $message; ?>

    <div class="row">
        <!-- Form thêm/sửa -->
        <div class="col-md-4">
            <div class="content-card">
                <h5 class="mb-4">
                    <i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus-circle'; ?>"></i> 
                    <?php echo $edit_category ? 'Sửa danh mục' : 'Thêm danh mục'; ?>
                </h5>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_category['id'] ?? ''; ?>">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="ten_danhmuc" class="form-control" 
                               value="<?php echo htmlspecialchars($edit_category['ten_danhmuc'] ?? ''); ?>" 
                               placeholder="Nhập tên danh mục..." required>
                    </div>
                    <button type="submit" name="save_category" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> <?php echo $edit_category ? 'Cập nhật' : 'Thêm mới'; ?>
                    </button>
                    <?php if ($edit_category): ?>
                    <a href="master_dm.php" class="btn btn-secondary w-100 mt-2">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Danh sách danh mục -->
        <div class="col-md-8">
            <div class="content-card">
                <h5 class="mb-4">
                    <i class="fas fa-list"></i> Danh sách danh mục (<?php echo $categories->num_rows; ?>)
                </h5>

                <div class="row g-3">
                    <?php if ($categories->num_rows > 0): ?>
                        <?php while ($row = $categories->fetch_assoc()): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="category-icon">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($row['ten_danhmuc']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-box"></i> <?php echo $row['so_sanpham']; ?> sản phẩm
                                        </small>
                                    </div>
                                    <div class="btn-group-vertical">
                                        <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có danh mục nào</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
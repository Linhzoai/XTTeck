<?php
require_once 'session_config.php';
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

$message = '';

// Xử lý thêm/sửa danh mục
if (isset($_POST['save_category'])) {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $ten = $conn->real_escape_string($_POST['ten']);

    if ($id > 0) {
        $conn->query("UPDATE danh_muc SET ten='$ten' WHERE id=$id");
        $message = '<div class="alert alert-success">Cập nhật danh mục thành công!</div>';
    } else {
        $conn->query("INSERT INTO danh_muc (ten) VALUES ('$ten')");
        $message = '<div class="alert alert-success">Thêm danh mục mới thành công!</div>';
    }
}

// Xử lý xóa danh mục
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    // Kiểm tra xem danh mục có khóa học không
    $check_result = $conn->query("SELECT COUNT(*) as c FROM khoa_hoc WHERE id_danh_muc=$id");
    $check = $check_result ? $check_result->fetch_assoc()['c'] : 0;

    if ($check > 0) {
        $message = '<div class="alert alert-warning">Không thể xóa danh mục này vì còn '.$check.' khóa học!</div>';
    } else {
        $conn->query("DELETE FROM danh_muc WHERE id=$id");
        $message = '<div class="alert alert-success">Đã xóa danh mục!</div>';
    }
}

// Lấy danh sách danh mục
$categories = $conn->query('SELECT dm.*, COUNT(kh.id) as so_khoa_hoc
                           FROM danh_muc dm
                           LEFT JOIN khoa_hoc kh ON dm.id = kh.id_danh_muc
                           GROUP BY dm.id
                           ORDER BY dm.id ASC');

// Lấy danh mục để sửa
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $result = $conn->query("SELECT * FROM danh_muc WHERE id=$edit_id");
    $edit_category = $result ? $result->fetch_assoc() : null;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục - CODE4Fun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin-responsive.css">
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
        <h3><i class="fas fa-code"></i> CODE4Fun</h3>
        <p>Hệ thống quản trị</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="master.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="master_khoahoc.php"><i class="fas fa-book"></i> Quản lý khóa học</a></li>
        <li><a href="master_dangky.php"><i class="fas fa-user-graduate"></i> Quản lý đăng ký</a></li>
        <li><a href="master_hocvien.php"><i class="fas fa-users"></i> Quản lý học viên</a></li>
        <li><a href="master_dm.php" class="active"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
        <li><a href="master_danhgia.php"><i class="fas fa-star"></i> Quản lý đánh giá</a></li>
        <li><a href="master_magiamgia.php"><i class="fas fa-tags"></i> Mã giảm giá</a></li>
        <li><a href="index.php" target="_blank"><i class="fas fa-globe"></i> Xem website</a></li>
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
                        <input type="text" name="ten" class="form-control"
                               value="<?php echo htmlspecialchars($edit_category['ten'] ?? ''); ?>"
                               placeholder="Nhập tên danh mục..." required>
                    </div>
                    <button type="submit" name="save_category" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> <?php echo $edit_category ? 'Cập nhật' : 'Thêm mới'; ?>
                    </button>
                    <?php if ($edit_category) { ?>
                    <a href="master_dm.php" class="btn btn-secondary w-100 mt-2">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                    <?php } ?>
                </form>
            </div>
        </div>

        <!-- Danh sách danh mục -->
        <div class="col-md-8">
            <div class="content-card">
                <h5 class="mb-4">
                    <i class="fas fa-list"></i> Danh sách danh mục (<?php echo $categories ? $categories->num_rows : 0; ?>)
                </h5>

                <div class="row g-3">
                    <?php if ($categories && $categories->num_rows > 0) { ?>
                        <?php while ($row = $categories->fetch_assoc()) { ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="category-icon">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($row['ten']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-book"></i> <?php echo $row['so_khoa_hoc']; ?> khóa học
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
                        <?php } ?>
                    <?php } else { ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có danh mục nào</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin-mobile.js"></script>
</body>
</html>

<?php $conn->close(); ?>
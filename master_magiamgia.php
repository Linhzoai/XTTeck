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
    $conn->query("DELETE FROM ma_giam_gia WHERE id = $id");
    header('Location: master_magiamgia.php?msg=deleted');
    exit;
}

// Xử lý thêm/sửa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 0;
    $ma = $conn->real_escape_string($_POST['ma']);
    $giam_gia = (int) $_POST['giam_gia'];
    $so_luong = (int) $_POST['so_luong'];
    $ngay_bat_dau = $conn->real_escape_string($_POST['ngay_bat_dau']);
    $ngay_ket_thuc = $conn->real_escape_string($_POST['ngay_ket_thuc']);

    if ($id > 0) {
        $sql = "UPDATE ma_giam_gia SET ma='$ma', giam_gia=$giam_gia, so_luong=$so_luong, 
                ngay_bat_dau='$ngay_bat_dau', ngay_ket_thuc='$ngay_ket_thuc' WHERE id=$id";
    } else {
        $sql = "INSERT INTO ma_giam_gia (ma, giam_gia, so_luong, ngay_bat_dau, ngay_ket_thuc) 
                VALUES ('$ma', $giam_gia, $so_luong, '$ngay_bat_dau', '$ngay_ket_thuc')";
    }
    $conn->query($sql);
    header('Location: master_magiamgia.php?msg=success');
    exit;
}

$coupons = $conn->query('SELECT * FROM ma_giam_gia ORDER BY ngay_tao DESC');

$edit_coupon = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_coupon = $conn->query("SELECT * FROM ma_giam_gia WHERE id = $edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý mã giảm giá - CODE4Fun</title>
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
        <li><a href="master_danhgia.php"><i class="fas fa-star"></i> Quản lý đánh giá</a></li>
        <li><a href="master_magiamgia.php" class="active"><i class="fas fa-tags"></i> Mã giảm giá</a></li>
        <li><a href="index.php" target="_blank"><i class="fas fa-globe"></i> Xem website</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="top-navbar">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-tags"></i> Quản lý mã giảm giá</h4>
            <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])) { ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> Thao tác thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <!-- Form thêm/sửa -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-edit"></i> <?php echo $edit_coupon ? 'Sửa mã giảm giá' : 'Thêm mã giảm giá mới'; ?></h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $edit_coupon['id'] ?? 0; ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mã giảm giá *</label>
                        <input type="text" name="ma" class="form-control" value="<?php echo htmlspecialchars($edit_coupon['ma'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Giảm giá (%) *</label>
                        <input type="number" name="giam_gia" class="form-control" min="1" max="100" value="<?php echo $edit_coupon['giam_gia'] ?? 10; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Số lượng *</label>
                        <input type="number" name="so_luong" class="form-control" min="1" value="<?php echo $edit_coupon['so_luong'] ?? 100; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ngày bắt đầu *</label>
                        <input type="datetime-local" name="ngay_bat_dau" class="form-control" 
                               value="<?php echo isset($edit_coupon['ngay_bat_dau']) ? date('Y-m-d\TH:i', strtotime($edit_coupon['ngay_bat_dau'])) : ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ngày kết thúc *</label>
                        <input type="datetime-local" name="ngay_ket_thuc" class="form-control" 
                               value="<?php echo isset($edit_coupon['ngay_ket_thuc']) ? date('Y-m-d\TH:i', strtotime($edit_coupon['ngay_ket_thuc'])) : ''; ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                <?php if ($edit_coupon) { ?>
                    <a href="master_magiamgia.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
                <?php } ?>
            </form>
        </div>
    </div>

    <!-- Danh sách mã giảm giá -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách mã giảm giá</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mã</th>
                            <th>Giảm giá</th>
                            <th>Số lượng</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($coupon = $coupons->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $coupon['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($coupon['ma']); ?></strong></td>
                                <td><span class="badge bg-success"><?php echo $coupon['giam_gia']; ?>%</span></td>
                                <td><?php echo $coupon['so_luong']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($coupon['ngay_bat_dau'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($coupon['ngay_ket_thuc'])); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="?delete=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i></a>
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


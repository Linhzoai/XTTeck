<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 1) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';
$message = '';

// Xử lý xóa khách hàng
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM khachhang WHERE id=$id")) {
        $message = '<div class="alert alert-success">Đã xóa khách hàng!</div>';
    }
}

// Tìm kiếm
$where = "1";
if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = $conn->real_escape_string($_GET['search']);
    $where .= " AND (ten_khachhang LIKE '%$search%' OR sdt LIKE '%$search%' OR email LIKE '%$search%')";
}

$customers = $conn->query("SELECT kh.*, 
                          COUNT(DISTINCT dh.id) as tong_don,
                          SUM(dh.tong_tien) as tong_chi_tieu
                          FROM khachhang kh
                          LEFT JOIN donhang dh ON kh.id = dh.khachhang_id
                          WHERE $where
                          GROUP BY kh.id
                          ORDER BY kh.id DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khách hàng - XTTech</title>
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
        .customer-avatar {
            width: 50px; height: 50px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 18px;
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
        <li><a href="master_kh.php" class="active"><i class="fas fa-users"></i> Quản lý khách hàng</a></li>
        <li><a href="master_dm.php"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
        <li><a href="master_bl.php"><i class="fas fa-comments"></i> Quản lý bình luận</a></li>
        <li><a href="master_tk.php"><i class="fas fa-chart-bar"></i> Báo cáo thống kê</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users"></i> Quản lý Khách hàng</h2>
        <a href="master.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>

    <?php echo $message; ?>

    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5><i class="fas fa-list"></i> Danh sách khách hàng (<?php echo $customers->num_rows; ?>)</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." 
                       value="<?php echo $_GET['search'] ?? ''; ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Địa chỉ</th>
                        <th>Tổng đơn</th>
                        <th>Tổng chi tiêu</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($customers->num_rows > 0): ?>
                        <?php while ($row = $customers->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="customer-avatar">
                                        <?php echo strtoupper(substr($row['ten_khachhang'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($row['ten_khachhang']); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td><i class="fas fa-phone text-primary"></i> <?php echo htmlspecialchars($row['sdt']); ?></td>
                            <td>
                                <?php if ($row['email']): ?>
                                    <i class="fas fa-envelope text-success"></i> <?php echo htmlspecialchars($row['email']); ?>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['dia_chi']); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo $row['tong_don']; ?> đơn</span>
                            </td>
                            <td>
                                <strong class="text-success">
                                    <?php echo number_format($row['tong_chi_tieu'] ?? 0, 0, ',', '.'); ?>₫
                                </strong>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="master_dh.php?customer=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-info" title="Xem đơn hàng">
                                        <i class="fas fa-shopping-cart"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" title="Xóa"
                                       onclick="return confirm('Bạn có chắc muốn xóa khách hàng này? Tất cả đơn hàng liên quan sẽ bị xóa!')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-user-slash fa-3x text-muted mb-3 d-block"></i>
                                Không có khách hàng nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
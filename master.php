<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 1) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "xttech");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

// Thống kê tổng quan
$total_products = $conn->query("SELECT COUNT(*) as total FROM sanpham")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) as total FROM donhang")->fetch_assoc()['total'];
$total_customers = $conn->query("SELECT COUNT(*) as total FROM khachhang")->fetch_assoc()['total'];
$total_revenue = $conn->query("SELECT SUM(tong_tien) as total FROM donhang WHERE trang_thai != 'Hủy'")->fetch_assoc()['total'] ?? 0;

// Đơn hàng chờ xử lý
$pending_orders = $conn->query("SELECT COUNT(*) as total FROM donhang WHERE trang_thai = 'Chờ xử lý'")->fetch_assoc()['total'];

// Sản phẩm sắp hết hàng (tồn kho < 10)
$low_stock = $conn->query("SELECT COUNT(*) as total FROM sanpham WHERE ton_kho < 10")->fetch_assoc()['total'];

// Đơn hàng gần đây
$recent_orders = $conn->query("SELECT dh.*, kh.ten_khachhang, kh.sdt 
                               FROM donhang dh 
                               JOIN khachhang kh ON dh.khachhang_id = kh.id 
                               ORDER BY dh.ngay_dat DESC 
                               LIMIT 5");

// Doanh thu theo tháng (6 tháng gần nhất)
$revenue_by_month = $conn->query("SELECT 
    DATE_FORMAT(ngay_dat, '%Y-%m') as thang,
    SUM(tong_tien) as doanh_thu
    FROM donhang 
    WHERE ngay_dat >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    AND trang_thai != 'Hủy'
    GROUP BY DATE_FORMAT(ngay_dat, '%Y-%m')
    ORDER BY thang ASC");

$months = [];
$revenues = [];
while ($row = $revenue_by_month->fetch_assoc()) {
    $months[] = $row['thang'];
    $revenues[] = $row['doanh_thu'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Trị - XTTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-brand {
            padding: 0 20px 30px;
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-brand p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #ffc107;
        }

        .sidebar-menu a i {
            width: 25px;
            font-size: 18px;
            margin-right: 12px;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
        }

        .top-navbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-card.blue { border-left-color: #4e73df; }
        .stat-card.green { border-left-color: #1cc88a; }
        .stat-card.orange { border-left-color: #f6c23e; }
        .stat-card.red { border-left-color: #e74a3b; }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin-bottom: 15px;
        }

        .stat-icon.blue { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
        .stat-icon.green { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
        .stat-icon.orange { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
        .stat-icon.red { background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 30px;
        }

        .recent-orders {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 30px;
        }

        .table-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .logout-btn {
            background: #e74a3b;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #c9302c;
            transform: translateY(-2px);
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
        <li><a href="master.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="master_sp.php"><i class="fas fa-box"></i> Quản lý sản phẩm</a></li>
        <li><a href="master_dh.php"><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</a></li>
        <li><a href="master_kh.php"><i class="fas fa-users"></i> Quản lý khách hàng</a></li>
        <li><a href="master_dm.php"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
        <li><a href="master_bl.php"><i class="fas fa-comments"></i> Quản lý bình luận</a></li>
        <li><a href="master_tk.php"><i class="fas fa-chart-bar"></i> Báo cáo thống kê</a></li>
        <li><a href="index.php" target="_blank"><i class="fas fa-globe"></i> Xem website</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div>
            <h4 class="mb-0">Dashboard Tổng Quan</h4>
            <small class="text-muted">Chào mừng trở lại, <?php echo htmlspecialchars($_SESSION['admin_user']); ?>!</small>
        </div>
        <div class="user-info">
            <div>
                <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin_user']); ?></div>
                <small class="text-muted">Quản trị viên</small>
            </div>
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['admin_user'], 0, 1)); ?>
            </div>
            <a href="doimk.php" class="btn btn-primary">
                <i class="fas fa-key me-2"></i> Đổi mật khẩu
            </a>

            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card blue">
                <div class="stat-icon blue">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_products); ?></div>
                <div class="stat-label">Tổng sản phẩm</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card green">
                <div class="stat-icon green">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                <div class="stat-label">Tổng đơn hàng</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card orange">
                <div class="stat-icon orange">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_customers); ?></div>
                <div class="stat-label">Khách hàng</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card red">
                <div class="stat-icon red">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_revenue / 1000000, 1); ?>M</div>
                <div class="stat-label">Doanh thu (VNĐ)</div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <div class="row mt-4">
        <?php if ($pending_orders > 0): ?>
        <div class="col-md-6">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <strong>Thông báo:</strong> Có <strong><?php echo $pending_orders; ?></strong> đơn hàng đang chờ xử lý!
                    <a href="master_dh.php" class="alert-link ms-2">Xem ngay →</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($low_stock > 0): ?>
        <div class="col-md-6">
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-box-open fa-2x me-3"></i>
                <div>
                    <strong>Cảnh báo:</strong> <strong><?php echo $low_stock; ?></strong> sản phẩm sắp hết hàng!
                    <a href="master_sp.php?filter=low_stock" class="alert-link ms-2">Kiểm tra →</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Revenue Chart -->
    <div class="chart-container">
        <h5 class="table-header"><i class="fas fa-chart-line"></i> Biểu đồ doanh thu 6 tháng gần nhất</h5>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

    <!-- Recent Orders -->
    <div class="recent-orders">
        <h5 class="table-header"><i class="fas fa-receipt"></i> Đơn hàng gần đây</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($order['ten_khachhang']); ?></td>
                        <td><?php echo htmlspecialchars($order['sdt']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($order['ngay_dat'])); ?></td>
                        <td><strong><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>₫</strong></td>
                        <td>
                            <?php
                            $badge_class = 'secondary';
                            switch($order['trang_thai']) {
                                case 'Chờ xử lý': $badge_class = 'warning'; break;
                                case 'Đang giao': $badge_class = 'info'; break;
                                case 'Hoàn tất': $badge_class = 'success'; break;
                                case 'Hủy': $badge_class = 'danger'; break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $badge_class; ?> badge-status">
                                <?php echo $order['trang_thai']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="master_dh.php?view=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?php echo json_encode($revenues); ?>,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#4e73df',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: ' + context.parsed.y.toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return (value / 1000000).toFixed(0) + 'M';
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>

<?php $conn->close(); ?>
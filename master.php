<?php
require_once 'session_config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

// Thống kê tổng quan
$result = $conn->query('SELECT COUNT(*) as total FROM khoa_hoc');
$total_courses = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query('SELECT COUNT(DISTINCT id_nguoi_dung) as total FROM dang_ky');
$total_students = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query('SELECT COUNT(*) as total FROM dang_ky');
$total_enrollments = $result ? $result->fetch_assoc()['total'] : 0;

$result = $conn->query("SELECT SUM(so_tien) as total FROM thanh_toan WHERE trang_thai = 'thanh_cong'");
$total_revenue = ($result && $row = $result->fetch_assoc()) ? ($row['total'] ?? 0) : 0;

// Đăng ký chờ xử lý
$result = $conn->query("SELECT COUNT(*) as total FROM dang_ky WHERE trang_thai = 'dang_ky'");
$pending_enrollments = $result ? $result->fetch_assoc()['total'] : 0;

// Khóa học chưa publish
$result = $conn->query("SELECT COUNT(*) as total FROM khoa_hoc WHERE trang_thai_khoa_hoc = 'draf'");
$draft_courses = $result ? $result->fetch_assoc()['total'] : 0;

// Khóa học mới nhất
$recent_courses = $conn->query('SELECT kh.*, nd.ho_ten as ten_giang_vien
                               FROM khoa_hoc kh
                               LEFT JOIN nguoi_dung nd ON kh.id_giang_vien = nd.id
                               ORDER BY kh.ngay_tao DESC
                               LIMIT 5');

// Doanh thu theo tháng (6 tháng gần nhất)
$revenue_by_month = $conn->query("SELECT
    DATE_FORMAT(ngay_thanh_toan, '%Y-%m') as thang,
    SUM(so_tien) as doanh_thu
    FROM thanh_toan
    WHERE ngay_thanh_toan >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    AND trang_thai = 'thanh_cong'
    GROUP BY DATE_FORMAT(ngay_thanh_toan, '%Y-%m')
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
    <title>Quản Trị - CODE4Fun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin-responsive.css">
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
            background: linear-gradient(180deg, #2f74d5 0%, #1a5bb8 100%);
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
        <h3><i class="fas fa-code"></i> CODE4Fun</h3>
        <p>Hệ thống quản trị</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="master.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="master_khoahoc.php"><i class="fas fa-book"></i> Quản lý khóa học</a></li>
        <li><a href="master_dangky.php"><i class="fas fa-user-graduate"></i> Quản lý đăng ký</a></li>
        <li><a href="master_hocvien.php"><i class="fas fa-users"></i> Quản lý học viên</a></li>
        <li><a href="master_dm.php"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
        <li><a href="master_danhgia.php"><i class="fas fa-star"></i> Quản lý đánh giá</a></li>
        <li><a href="master_magiamgia.php"><i class="fas fa-tags"></i> Mã giảm giá</a></li>
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
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_courses); ?></div>
                <div class="stat-label">Tổng khóa học</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card green">
                <div class="stat-icon green">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_students); ?></div>
                <div class="stat-label">Tổng học viên</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card orange">
                <div class="stat-icon orange">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-value"><?php echo number_format($total_enrollments); ?></div>
                <div class="stat-label">Tổng đăng ký</div>
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
        <?php if ($pending_enrollments > 0) { ?>
        <div class="col-md-6">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <strong>Thông báo:</strong> Có <strong><?php echo $pending_enrollments; ?></strong> đăng ký đang chờ xử lý!
                    <a href="master_dangky.php" class="alert-link ms-2">Xem ngay →</a>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($draft_courses > 0) { ?>
        <div class="col-md-6">
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="fas fa-file-alt fa-2x me-3"></i>
                <div>
                    <strong>Thông báo:</strong> <strong><?php echo $draft_courses; ?></strong> khóa học chưa publish!
                    <a href="master_khoahoc.php?filter=draft" class="alert-link ms-2">Kiểm tra →</a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Revenue Chart -->
    <div class="chart-container">
        <h5 class="table-header"><i class="fas fa-chart-line"></i> Biểu đồ doanh thu 6 tháng gần nhất</h5>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

    <!-- Recent Courses -->
    <div class="recent-orders">
        <h5 class="table-header"><i class="fas fa-book"></i> Khóa học mới nhất</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên khóa học</th>
                        <th>Giảng viên</th>
                        <th>Ngày tạo</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($course = $recent_courses->fetch_assoc()) { ?>
                    <tr>
                        <td><strong>#<?php echo $course['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($course['ten']); ?></td>
                        <td><?php echo htmlspecialchars($course['ten_giang_vien'] ?? 'Chưa có'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($course['ngay_tao'])); ?></td>
                        <td><strong><?php echo number_format($course['gia'], 0, ',', '.'); ?>₫</strong></td>
                        <td>
                            <?php
                            $badge_class = 'secondary';
                        switch ($course['trang_thai_khoa_hoc']) {
                            case 'draf': $badge_class = 'warning';
                                break;
                            case 'publish': $badge_class = 'success';
                                break;
                            case 'archive': $badge_class = 'danger';
                                break;
                        }
                        ?>
                            <span class="badge bg-<?php echo $badge_class; ?> badge-status">
                                <?php echo ucfirst($course['trang_thai_khoa_hoc']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="master_khoahoc.php?view=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
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

<script src="js/admin-mobile.js"></script>
</body>
</html>

<?php $conn->close(); ?>
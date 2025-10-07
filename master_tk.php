<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 1) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "xttech");
if ($conn->connect_error) die("Kết nối thất bại");
$conn->set_charset("utf8mb4");

// Doanh thu theo tháng (12 tháng gần nhất)
$revenue_monthly = $conn->query("SELECT 
    DATE_FORMAT(ngay_dat, '%Y-%m') as thang,
    DATE_FORMAT(ngay_dat, '%m/%Y') as thang_hienthi,
    SUM(tong_tien) as doanh_thu,
    COUNT(*) as so_don
    FROM donhang 
    WHERE ngay_dat >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    AND trang_thai != 'Hủy'
    GROUP BY DATE_FORMAT(ngay_dat, '%Y-%m')
    ORDER BY thang ASC");

$months = [];
$revenues = [];
$orders_count = [];
while ($row = $revenue_monthly->fetch_assoc()) {
    $months[] = $row['thang_hienthi'];
    $revenues[] = $row['doanh_thu'];
    $orders_count[] = $row['so_don'];
}

// Top sản phẩm bán chạy
$top_products = $conn->query("SELECT sp.ten_sanpham, sp.hinh_anh,
    SUM(ct.so_luong) as tong_ban,
    SUM(ct.so_luong * ct.don_gia) as doanh_thu
    FROM chitiet_donhang ct
    JOIN sanpham sp ON ct.sanpham_id = sp.id
    JOIN donhang dh ON ct.donhang_id = dh.id
    WHERE dh.trang_thai != 'Hủy'
    GROUP BY ct.sanpham_id
    ORDER BY tong_ban DESC
    LIMIT 10");

// Thống kê theo danh mục
$category_stats = $conn->query("SELECT dm.ten_danhmuc,
    COUNT(DISTINCT sp.id) as so_sanpham,
    COALESCE(SUM(ct.so_luong), 0) as tong_ban,
    COALESCE(SUM(ct.so_luong * ct.don_gia), 0) as doanh_thu
    FROM danhmuc dm
    LEFT JOIN sanpham sp ON dm.id = sp.danhmuc_id
    LEFT JOIN chitiet_donhang ct ON sp.id = ct.sanpham_id
    LEFT JOIN donhang dh ON ct.donhang_id = dh.id AND dh.trang_thai != 'Hủy'
    GROUP BY dm.id
    ORDER BY doanh_thu DESC");

// Thống kê tổng quan
$total_revenue = $conn->query("SELECT SUM(tong_tien) as total FROM donhang WHERE trang_thai != 'Hủy'")->fetch_assoc()['total'] ?? 0;
$total_orders_completed = $conn->query("SELECT COUNT(*) as total FROM donhang WHERE trang_thai = 'Hoàn tất'")->fetch_assoc()['total'];
$avg_order_value = $total_orders_completed > 0 ? $total_revenue / $total_orders_completed : 0;

// Doanh thu hôm nay
$today_revenue = $conn->query("SELECT COALESCE(SUM(tong_tien), 0) as total 
                               FROM donhang 
                               WHERE DATE(ngay_dat) = CURDATE() 
                               AND trang_thai != 'Hủy'")->fetch_assoc()['total'];

// Doanh thu tháng này
$month_revenue = $conn->query("SELECT COALESCE(SUM(tong_tien), 0) as total 
                               FROM donhang 
                               WHERE MONTH(ngay_dat) = MONTH(CURDATE()) 
                               AND YEAR(ngay_dat) = YEAR(CURDATE())
                               AND trang_thai != 'Hủy'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo Thống kê - XTTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card-mini {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid;
        }
        .stat-card-mini.blue { border-left-color: #4e73df; }
        .stat-card-mini.green { border-left-color: #1cc88a; }
        .stat-card-mini.orange { border-left-color: #f6c23e; }
        .product-rank {
            width: 35px; height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        .rank-1 { background: #ffd700; }
        .rank-2 { background: #c0c0c0; }
        .rank-3 { background: #cd7f32; }
        .rank-other { background: #6c757d; }
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
        <li><a href="master_bl.php"><i class="fas fa-comments"></i> Quản lý bình luận</a></li>
        <li><a href="master_tk.php" class="active"><i class="fas fa-chart-bar"></i> Báo cáo thống kê</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar"></i> Báo cáo Thống kê</h2>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> In báo cáo
            </button>
            <a href="master.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Thống kê nhanh -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card-mini blue">
                <small class="text-muted d-block mb-2">Doanh thu hôm nay</small>
                <h4 class="mb-0 text-primary"><?php echo number_format($today_revenue / 1000000, 2); ?>M VNĐ</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-mini green">
                <small class="text-muted d-block mb-2">Doanh thu tháng này</small>
                <h4 class="mb-0 text-success"><?php echo number_format($month_revenue / 1000000, 2); ?>M VNĐ</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-mini orange">
                <small class="text-muted d-block mb-2">Giá trị đơn hàng TB</small>
                <h4 class="mb-0 text-warning"><?php echo number_format($avg_order_value / 1000, 0); ?>K VNĐ</h4>
            </div>
        </div>
    </div>

    <!-- Biểu đồ doanh thu -->
    <div class="content-card">
        <h5 class="mb-4"><i class="fas fa-chart-line"></i> Biểu đồ doanh thu 12 tháng gần nhất</h5>
        <canvas id="revenueChart" height="80"></canvas>
    </div>

    <div class="row">
        <!-- Top sản phẩm bán chạy -->
        <div class="col-lg-7">
            <div class="content-card">
                <h5 class="mb-4"><i class="fas fa-trophy"></i> Top 10 sản phẩm bán chạy</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Hạng</th>
                                <th>Sản phẩm</th>
                                <th>Đã bán</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            while ($row = $top_products->fetch_assoc()): 
                                $rank_class = $rank <= 3 ? "rank-$rank" : "rank-other";
                            ?>
                            <tr>
                                <td>
                                    <div class="product-rank <?php echo $rank_class; ?>">
                                        <?php echo $rank; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo htmlspecialchars($row['hinh_anh']); ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                        <strong><?php echo htmlspecialchars($row['ten_sanpham']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $row['tong_ban']; ?> sp</span>
                                </td>
                                <td>
                                    <strong class="text-success">
                                        <?php echo number_format($row['doanh_thu'], 0, ',', '.'); ?>₫
                                    </strong>
                                </td>
                            </tr>
                            <?php 
                            $rank++;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Thống kê theo danh mục -->
        <div class="col-lg-5">
            <div class="content-card">
                <h5 class="mb-4"><i class="fas fa-chart-pie"></i> Thống kê theo danh mục</h5>
                <canvas id="categoryChart" height="200"></canvas>
                
                <div class="mt-4">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Danh mục</th>
                                <th>Sản phẩm</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $category_stats->data_seek(0);
                            while ($row = $category_stats->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['ten_danhmuc']); ?></td>
                                <td><?php echo $row['so_sanpham']; ?></td>
                                <td class="text-success">
                                    <strong><?php echo number_format($row['doanh_thu'] / 1000, 0); ?>K</strong>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

        <!-- Bảng chi tiết doanh thu theo tháng -->
    <div class="content-card">
        <h5 class="mb-4"><i class="fas fa-table"></i> Chi tiết doanh thu theo tháng</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Tháng</th>
                        <?php foreach ($months as $m): ?>
                        <th><?php echo $m; ?></th>
                        <?php endforeach; ?>
                        <th>Tổng</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Doanh thu</th>
                        <?php 
                        $total = 0;
                        foreach ($revenues as $r): 
                            $total += $r;
                        ?>
                        <td><?php echo number_format($r / 1000000, 1); ?>M</td>
                        <?php endforeach; ?>
                        <th class="text-danger"><?php echo number_format($total / 1000000, 1); ?>M</th>
                    </tr>
                    <tr>
                        <th>Số đơn</th>
                        <?php 
                        $total_orders = 0;
                        foreach ($orders_count as $o): 
                            $total_orders += $o;
                        ?>
                        <td><?php echo $o; ?></td>
                        <?php endforeach; ?>
                        <th class="text-info"><?php echo $total_orders; ?></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Biểu đồ doanh thu
const ctx1 = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?php echo json_encode($revenues); ?>,
            backgroundColor: 'rgba(78, 115, 223, 0.8)',
            borderColor: '#4e73df',
            borderWidth: 2
        }, {
            label: 'Số đơn hàng',
            data: <?php echo json_encode($orders_count); ?>,
            type: 'line',
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            borderWidth: 3,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                position: 'left',
                ticks: {
                    callback: function(value) {
                        return (value / 1000000).toFixed(0) + 'M';
                    }
                }
            },
            y1: {
                beginAtZero: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});

// Biểu đồ tròn danh mục
const ctx2 = document.getElementById('categoryChart').getContext('2d');
const categoryData = <?php 
    $category_stats->data_seek(0);
    $cat_labels = [];
    $cat_values = [];
    while ($row = $category_stats->fetch_assoc()) {
        $cat_labels[] = $row['ten_danhmuc'];
        $cat_values[] = $row['doanh_thu'];
    }
    echo json_encode(['labels' => $cat_labels, 'values' => $cat_values]);
?>;

new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: categoryData.labels,
        datasets: [{
            data: categoryData.values,
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', 
                '#e74a3b', '#858796', '#5a5c69'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

</body>
</html>

<?php $conn->close(); ?>
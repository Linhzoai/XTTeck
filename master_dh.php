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

    // Xử lý cập nhật trạng thái đơn hàng
    if (isset($_POST['update_status'])) {
        $id = (int)$_POST['order_id'];
        $status = $conn->real_escape_string($_POST['trang_thai']);
        
        if ($conn->query("UPDATE donhang SET trang_thai='$status' WHERE id=$id")) {
            $message = '<div class="alert alert-success">Đã cập nhật trạng thái đơn hàng!</div>';
        }
    }

    // Xử lý xóa đơn hàng
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        if ($conn->query("DELETE FROM donhang WHERE id=$id")) {
            $message = '<div class="alert alert-success">Đã xóa đơn hàng!</div>';
        }
    }

    // Lọc đơn hàng
    $where = "1";
    if (isset($_GET['status']) && $_GET['status'] != '') {
        $status = $conn->real_escape_string($_GET['status']);
        $where .= " AND dh.trang_thai = '$status'";
    }

    $orders = $conn->query("SELECT dh.*, kh.ten_khachhang, kh.sdt, kh.dia_chi, kh.email
                            FROM donhang dh
                            JOIN khachhang kh ON dh.khachhang_id = kh.id
                            WHERE $where
                            ORDER BY dh.ngay_dat DESC");

    // Xem chi tiết đơn hàng
    $view_order = null;
    $order_details = null;
    if (isset($_GET['view'])) {
        $view_id = (int)$_GET['view'];
        $view_order = $conn->query("SELECT dh.*, kh.* 
                                    FROM donhang dh
                                    JOIN khachhang kh ON dh.khachhang_id = kh.id
                                    WHERE dh.id=$view_id")->fetch_assoc();
        
        if ($view_order) {
            $order_details = $conn->query("SELECT ct.*, sp.ten_sanpham, sp.hinh_anh
                                        FROM chitiet_donhang ct
                                        JOIN sanpham sp ON ct.sanpham_id = sp.id
                                        WHERE ct.donhang_id=$view_id");
        }
    }

    // Thống kê đơn hàng
    $stats = [
        'Chờ xử lý' => $conn->query("SELECT COUNT(*) as c FROM donhang WHERE trang_thai='Chờ xử lý'")->fetch_assoc()['c'],
        'Đang giao' => $conn->query("SELECT COUNT(*) as c FROM donhang WHERE trang_thai='Đang giao'")->fetch_assoc()['c'],
        'Hoàn tất' => $conn->query("SELECT COUNT(*) as c FROM donhang WHERE trang_thai='Hoàn tất'")->fetch_assoc()['c'],
        'Hủy' => $conn->query("SELECT COUNT(*) as c FROM donhang WHERE trang_thai='Hủy'")->fetch_assoc()['c']
    ];
    ?>

    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quản lý Đơn hàng - XTTech</title>
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
            .stat-box {
                background: white; border-radius: 10px; padding: 20px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                border-left: 4px solid;
            }
            .stat-box.warning { border-left-color: #ffc107; }
            .stat-box.info { border-left-color: #17a2b8; }
            .stat-box.success { border-left-color: #28a745; }
            .stat-box.danger { border-left-color: #dc3545; }
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
            <li><a href="master_dh.php" class="active"><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</a></li>
            <li><a href="master_kh.php"><i class="fas fa-users"></i> Quản lý khách hàng</a></li>
            <li><a href="master_dm.php"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
            <li><a href="master_bl.php"><i class="fas fa-comments"></i> Quản lý bình luận</a></li>
            <li><a href="master_tk.php"><i class="fas fa-chart-bar"></i> Báo cáo thống kê</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</h2>
            <a href="master.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>

        <?php echo $message; ?>

        <!-- Thống kê nhanh -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-box warning">
                    <h3 class="mb-0"><?php echo $stats['Chờ xử lý']; ?></h3>
                    <p class="mb-0 text-muted">Chờ xử lý</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box info">
                    <h3 class="mb-0"><?php echo $stats['Đang giao']; ?></h3>
                    <p class="mb-0 text-muted">Đang giao</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box success">
                    <h3 class="mb-0"><?php echo $stats['Hoàn tất']; ?></h3>
                    <p class="mb-0 text-muted">Hoàn tất</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box danger">
                    <h3 class="mb-0"><?php echo $stats['Hủy']; ?></h3>
                    <p class="mb-0 text-muted">Đã hủy</p>
                </div>
            </div>
        </div>

        <?php if ($view_order): ?>
        <!-- Chi tiết đơn hàng -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5><i class="fas fa-file-invoice"></i> Chi tiết đơn hàng #<?php echo $view_order['id']; ?></h5>
                <a href="master_dh.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Đóng</a>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3"><i class="fas fa-user"></i> Thông tin khách hàng</h6>
                    <table class="table table-sm">
                        <tr><th width="150">Họ tên:</th><td><?php echo htmlspecialchars($view_order['ten_khachhang']); ?></td></tr>
                        <tr><th>Số điện thoại:</th><td><?php echo htmlspecialchars($view_order['sdt']); ?></td></tr>
                        <tr><th>Email:</th><td><?php echo htmlspecialchars($view_order['email']); ?></td></tr>
                        <tr><th>Địa chỉ:</th><td><?php echo htmlspecialchars($view_order['dia_chi']); ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-3"><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h6>
                    <table class="table table-sm">
                        <tr><th width="150">Mã đơn:</th><td><strong>#<?php echo $view_order['id']; ?></strong></td></tr>
                        <tr><th>Ngày đặt:</th><td><?php echo date('d/m/Y H:i', strtotime($view_order['ngay_dat'])); ?></td></tr>
                        <tr><th>Tổng tiền:</th><td class="text-danger"><strong><?php echo number_format($view_order['tong_tien'], 0, ',', '.'); ?>₫</strong></td></tr>
                        <tr>
                            <th>Trạng thái:</th>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?php echo $view_order['id']; ?>">
                                    <select name="trang_thai" class="form-select form-select-sm d-inline w-auto">
                                        <option value="Chờ xử lý" <?php echo $view_order['trang_thai']=='Chờ xử lý'?'selected':''; ?>>Chờ xử lý</option>
                                        <option value="Đang giao" <?php echo $view_order['trang_thai']=='Đang giao'?'selected':''; ?>>Đang giao</option>
                                        <option value="Hoàn tất" <?php echo $view_order['trang_thai']=='Hoàn tất'?'selected':''; ?>>Hoàn tất</option>
                                        <option value="Hủy" <?php echo $view_order['trang_thai']=='Hủy'?'selected':''; ?>>Hủy</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm"><i class="fas fa-save"></i></button>
                                </form>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <h6 class="text-primary mt-4 mb-3"><i class="fas fa-shopping-bag"></i> Chi tiết sản phẩm</h6>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Hình</th>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($detail = $order_details->fetch_assoc()): ?>
                    <tr>
                        <td><img src="<?php echo $detail['hinh_anh']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                        <td><?php echo htmlspecialchars($detail['ten_sanpham']); ?></td>
                        <td><?php echo number_format($detail['don_gia'], 0, ',', '.'); ?>₫</td>
                        <td><?php echo $detail['so_luong']; ?></td>
                        <td><strong><?php echo number_format($detail['don_gia'] * $detail['so_luong'], 0, ',', '.'); ?>₫</strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="4" class="text-end">Tổng cộng:</th>
                        <th class="text-danger"><?php echo number_format($view_order['tong_tien'], 0, ',', '.'); ?>₫</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>

        <!-- Danh sách đơn hàng -->
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5><i class="fas fa-list"></i> Danh sách đơn hàng (<?php echo $orders->num_rows; ?>)</h5>
                <div class="btn-group">
                    <a href="master_dh.php" class="btn btn-outline-primary <?php echo !isset($_GET['status'])?'active':''; ?>">Tất cả</a>
                    <a href="?status=Chờ xử lý" class="btn btn-outline-warning <?php echo ($_GET['status']??'')=='Chờ xử lý'?'active':''; ?>">Chờ xử lý</a>
                    <a href="?status=Đang giao" class="btn btn-outline-info <?php echo ($_GET['status']??'')=='Đang giao'?'active':''; ?>">Đang giao</a>
                    <a href="?status=Hoàn tất" class="btn btn-outline-success <?php echo ($_GET['status']??'')=='Hoàn tất'?'active':''; ?>">Hoàn tất</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>SĐT</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while ($row = $orders->fetch_assoc()): 
                                $badge = ['Chờ xử lý'=>'warning', 'Đang giao'=>'info', 'Hoàn tất'=>'success', 'Hủy'=>'danger'];
                            ?>
                            <tr>
                                <td><strong>#<?php echo $row['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($row['ten_khachhang']); ?></td>
                                <td><?php echo htmlspecialchars($row['sdt']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['ngay_dat'])); ?></td>
                                <td><strong><?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>₫</strong></td>
                                <td>
                                    <span class="badge bg-<?php echo $badge[$row['trang_thai']]; ?>">
                                        <?php echo $row['trang_thai']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?view=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Xóa"
                                        onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4">Không có đơn hàng nào</td></tr>
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
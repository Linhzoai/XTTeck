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

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sp = $conn->query("SELECT hinh_anh FROM sanpham WHERE id=$id")->fetch_assoc();
    
    if ($conn->query("DELETE FROM sanpham WHERE id=$id")) {
        if (!empty($sp['hinh_anh']) && file_exists($sp['hinh_anh'])) {
            unlink($sp['hinh_anh']);
        }
        $message = '<div class="alert alert-success">Đã xóa sản phẩm thành công!</div>';
    }
}

// Xử lý thêm/sửa sản phẩm
if (isset($_POST['save_product'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $ten = $conn->real_escape_string($_POST['ten_sanpham']);
    $danhmuc_id = (int)$_POST['danhmuc_id'];
    $gia = (float)$_POST['gia'];
    $mo_ta = $conn->real_escape_string($_POST['mo_ta']);
    $ton_kho = (int)$_POST['ton_kho'];
    
    $hinh_anh = '';
    if (!empty($_FILES['hinh_anh']['name'])) {
        $targetDir = "img/";
        $fileName = time() . "_" . basename($_FILES["hinh_anh"]["name"]);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $targetFile);
        $hinh_anh = $targetFile;
    }
    
    if ($id > 0) {
        // Cập nhật
        $sql = "UPDATE sanpham SET ten_sanpham='$ten', danhmuc_id=$danhmuc_id, gia=$gia, mo_ta='$mo_ta', ton_kho=$ton_kho";
        if ($hinh_anh) $sql .= ", hinh_anh='$hinh_anh'";
        $sql .= " WHERE id=$id";
        $conn->query($sql);
        $message = '<div class="alert alert-success">Cập nhật sản phẩm thành công!</div>';
    } else {
        // Thêm mới
        $conn->query("INSERT INTO sanpham (ten_sanpham, danhmuc_id, gia, mo_ta, hinh_anh, ton_kho) 
                      VALUES ('$ten', $danhmuc_id, $gia, '$mo_ta', '$hinh_anh', $ton_kho)");
        $message = '<div class="alert alert-success">Thêm sản phẩm mới thành công!</div>';
    }
}

// Lấy danh sách sản phẩm
$where = "1";
if (isset($_GET['filter']) && $_GET['filter'] == 'low_stock') {
    $where .= " AND sp.ton_kho < 10";
}
if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = $conn->real_escape_string($_GET['search']);
    $where .= " AND sp.ten_sanpham LIKE '%$search%'";
}

$products = $conn->query("SELECT sp.*, dm.ten_danhmuc 
                          FROM sanpham sp 
                          LEFT JOIN danhmuc dm ON sp.danhmuc_id = dm.id 
                          WHERE $where
                          ORDER BY sp.id DESC");

$categories = $conn->query("SELECT * FROM danhmuc");

// Lấy sản phẩm để sửa
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_product = $conn->query("SELECT * FROM sanpham WHERE id=$edit_id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm - XTTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px 0;
            z-index: 1000;
        }
        .sidebar-brand {
            padding: 0 20px 30px;
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
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
            margin-right: 12px;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .product-img-small {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .stock-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .stock-low { background: #fff3cd; color: #856404; }
        .stock-ok { background: #d1e7dd; color: #0f5132; }
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
        <li><a href="master_sp.php" class="active"><i class="fas fa-box"></i> Quản lý sản phẩm</a></li>
        <li><a href="master_dh.php"><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</a></li>
        <li><a href="master_kh.php"><i class="fas fa-users"></i> Quản lý khách hàng</a></li>
        <li><a href="master_dm.php"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
        <li><a href="master_bl.php"><i class="fas fa-comments"></i> Quản lý bình luận</a></li>
        <li><a href="master_tk.php"><i class="fas fa-chart-bar"></i> Báo cáo thống kê</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-box"></i> Quản lý Sản phẩm</h2>
        <a href="master.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php echo $message; ?>

    <!-- Form thêm/sửa -->
    <div class="content-card">
        <h5 class="mb-4">
            <i class="fas fa-<?php echo $edit_product ? 'edit' : 'plus-circle'; ?>"></i> 
            <?php echo $edit_product ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới'; ?>
        </h5>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $edit_product['id'] ?? ''; ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="ten_sanpham" class="form-control" 
                           value="<?php echo htmlspecialchars($edit_product['ten_sanpham'] ?? ''); ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                    <select name="danhmuc_id" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php 
                        $categories->data_seek(0);
                        while ($cat = $categories->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo (isset($edit_product['danhmuc_id']) && $edit_product['danhmuc_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['ten_danhmuc']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" name="gia" class="form-control" 
                           value="<?php echo $edit_product['gia'] ?? ''; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tồn kho <span class="text-danger">*</span></label>
                    <input type="number" name="ton_kho" class="form-control" 
                           value="<?php echo $edit_product['ton_kho'] ?? 0; ?>" required>
                </div>
                <div class="col-md-9 mb-3">
                    <label class="form-label">Hình ảnh</label>
                    <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                    <?php if ($edit_product && $edit_product['hinh_anh']): ?>
                        <img src="<?php echo $edit_product['hinh_anh']; ?>" class="mt-2" style="height: 80px; border-radius: 8px;">
                    <?php endif; ?>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="mo_ta" class="form-control" rows="3"><?php echo htmlspecialchars($edit_product['mo_ta'] ?? ''); ?></textarea>
                </div>
            </div>
            <button type="submit" name="save_product" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $edit_product ? 'Cập nhật' : 'Thêm mới'; ?>
            </button>
            <?php if ($edit_product): ?>
            <a href="master_sp.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5><i class="fas fa-list"></i> Danh sách sản phẩm (<?php echo $products->num_rows; ?>)</h5>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." 
                           value="<?php echo $_GET['search'] ?? ''; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <a href="?filter=low_stock" class="btn btn-warning">
                    <i class="fas fa-exclamation-triangle"></i> Sắp hết hàng
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products->num_rows > 0): ?>
                        <?php while ($row = $products->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($row['hinh_anh']); ?>" 
                                     class="product-img-small" alt="">
                            </td>
                            <td><?php echo htmlspecialchars($row['ten_sanpham']); ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($row['ten_danhmuc'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td><strong><?php echo number_format($row['gia'], 0, ',', '.'); ?>₫</strong></td>
                            <td>
                                <span class="stock-badge <?php echo $row['ton_kho'] < 10 ? 'stock-low' : 'stock-ok'; ?>">
                                    <?php echo $row['ton_kho']; ?> sp
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="chitietsanpham.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" title="Xóa"
                                       onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                Không có sản phẩm nào
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
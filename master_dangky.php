<?php
require_once 'session_config.php';
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'config.php';

// Xử lý thêm mới đăng ký
if (isset($_POST['add_enrollment'])) {
    $id_nguoi_dung = (int) $_POST['id_nguoi_dung'];
    $id_khoa_hoc = (int) $_POST['id_khoa_hoc'];
    $id_ma_giam_gia = !empty($_POST['id_ma_giam_gia']) ? (int) $_POST['id_ma_giam_gia'] : 'NULL';
    $giam_gia_ap_dung = (float) $_POST['giam_gia_ap_dung'];
    $trang_thai = $_POST['trang_thai'];

    // Kiểm tra đã đăng ký chưa
    $check = $conn->query("SELECT id FROM dang_ky WHERE id_nguoi_dung = $id_nguoi_dung AND id_khoa_hoc = $id_khoa_hoc");
    if ($check->num_rows > 0) {
        header('Location: master_dangky.php?msg=duplicate');
        exit;
    }

    $sql = "INSERT INTO dang_ky (id_nguoi_dung, id_khoa_hoc, id_ma_giam_gia, giam_gia_ap_dung, trang_thai, ngay_dang_ky)
            VALUES ($id_nguoi_dung, $id_khoa_hoc, $id_ma_giam_gia, $giam_gia_ap_dung, '$trang_thai', NOW())";

    if ($conn->query($sql)) {
        header('Location: master_dangky.php?msg=added');
    } else {
        header('Location: master_dangky.php?msg=error');
    }
    exit;
}

// Xử lý cập nhật thông tin đăng ký
if (isset($_POST['edit_enrollment'])) {
    $id = (int) $_POST['id'];
    $id_khoa_hoc = (int) $_POST['id_khoa_hoc'];
    $id_ma_giam_gia = !empty($_POST['id_ma_giam_gia']) ? (int) $_POST['id_ma_giam_gia'] : 'NULL';
    $giam_gia_ap_dung = (float) $_POST['giam_gia_ap_dung'];
    $trang_thai = $_POST['trang_thai'];

    $sql = "UPDATE dang_ky SET
            id_khoa_hoc = $id_khoa_hoc,
            id_ma_giam_gia = $id_ma_giam_gia,
            giam_gia_ap_dung = $giam_gia_ap_dung,
            trang_thai = '$trang_thai'
            WHERE id = $id";

    if ($conn->query($sql)) {
        header('Location: master_dangky.php?msg=updated');
    } else {
        header('Location: master_dangky.php?msg=error');
    }
    exit;
}

// Xử lý cập nhật trạng thái
if (isset($_GET['update_status'])) {
    $id = (int) $_GET['update_status'];
    $status = $_GET['status'] ?? 'dang_ky';
    $conn->query("UPDATE dang_ky SET trang_thai = '$status' WHERE id = $id");
    header('Location: master_dangky.php?msg=updated');
    exit;
}

// Xử lý cập nhật trạng thái thanh toán
if (isset($_GET['update_payment'])) {
    $id = (int) $_GET['update_payment'];
    $status = $_GET['payment_status'] ?? 'cho_xu_ly';
    $conn->query("UPDATE thanh_toan SET trang_thai = '$status' WHERE id_dang_ky = $id");
    header('Location: master_dangky.php?msg=updated');
    exit;
}

// Lấy danh sách khóa học, mã giảm giá và học viên cho form
$courses = $conn->query('SELECT id, ten FROM khoa_hoc ORDER BY ten');
$discounts = $conn->query('SELECT id, ma, giam_gia FROM ma_giam_gia ORDER BY ma');
$students = $conn->query("SELECT id, ho_ten, email FROM nguoi_dung WHERE vai_tro = 'hoc_vien' ORDER BY ho_ten");

$enrollments = $conn->query('SELECT dk.*, kh.ten as ten_khoa_hoc, nd.ho_ten, nd.email,
                                    tt.trang_thai as trang_thai_tt, tt.so_tien,
                                    mg.ma as ma_giam_gia_code
                             FROM dang_ky dk
                             JOIN khoa_hoc kh ON dk.id_khoa_hoc = kh.id
                             JOIN nguoi_dung nd ON dk.id_nguoi_dung = nd.id
                             LEFT JOIN thanh_toan tt ON dk.id = tt.id_dang_ky
                             LEFT JOIN ma_giam_gia mg ON dk.id_ma_giam_gia = mg.id
                             ORDER BY dk.ngay_dang_ky DESC');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đăng ký - CODE4Fun</title>
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
        <li><a href="master_dangky.php" class="active"><i class="fas fa-user-graduate"></i> Quản lý đăng ký</a></li>
        <li><a href="master_hocvien.php"><i class="fas fa-users"></i> Quản lý học viên</a></li>
        <li><a href="master_dm.php"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
        <li><a href="master_danhgia.php"><i class="fas fa-star"></i> Quản lý đánh giá</a></li>
        <li><a href="master_magiamgia.php"><i class="fas fa-tags"></i> Mã giảm giá</a></li>
        <li><a href="index.php" target="_blank"><i class="fas fa-globe"></i> Xem website</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="top-navbar">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-user-graduate"></i> Quản lý đăng ký</h4>
            <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])) {
        $msg = $_GET['msg'];
        $alert_class = 'success';
        $alert_text = 'Cập nhật thành công!';

        if ($msg == 'added') {
            $alert_text = 'Thêm đăng ký mới thành công!';
        } elseif ($msg == 'duplicate') {
            $alert_class = 'warning';
            $alert_text = 'Học viên đã đăng ký khóa học này rồi!';
        } elseif ($msg == 'error') {
            $alert_class = 'danger';
            $alert_text = 'Có lỗi xảy ra. Vui lòng thử lại!';
        }
        ?>
        <div class="alert alert-<?php echo $alert_class; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $alert_class == 'success' ? 'check-circle' : ($alert_class == 'warning' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
            <?php echo $alert_text; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách đăng ký</h5>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus"></i> Thêm đăng ký mới
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Học viên</th>
                            <th>Email</th>
                            <th>Khóa học</th>
                            <th>Mã giảm giá</th>
                            <th>Giảm giá</th>
                            <th>Số tiền</th>
                            <th>Ngày ĐK</th>
                            <th>TT Đăng ký</th>
                            <th>TT Thanh toán</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($enroll = $enrollments->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $enroll['id']; ?></td>
                                <td><?php echo htmlspecialchars($enroll['ho_ten']); ?></td>
                                <td><?php echo htmlspecialchars($enroll['email']); ?></td>
                                <td><?php echo htmlspecialchars($enroll['ten_khoa_hoc']); ?></td>
                                <td>
                                    <?php echo $enroll['ma_giam_gia_code'] ? '<span class="badge bg-info">'.htmlspecialchars($enroll['ma_giam_gia_code']).'</span>' : '<span class="text-muted">-</span>'; ?>
                                </td>
                                <td>
                                    <?php
                                        if ($enroll['giam_gia_ap_dung'] > 0) {
                                            echo number_format($enroll['giam_gia_ap_dung']).($enroll['giam_gia_ap_dung'] < 100 ? '%' : '₫');
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                            ?>
                                </td>
                                <td><?php echo number_format($enroll['so_tien'] ?? 0); ?>₫</td>
                                <td><?php echo date('d/m/Y', strtotime($enroll['ngay_dang_ky'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $enroll['trang_thai'] == 'hoan_thanh' ? 'success' : 'warning'; ?>">
                                        <?php echo $enroll['trang_thai']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $enroll['trang_thai_tt'] == 'thanh_cong' ? 'success' : 'secondary'; ?>">
                                        <?php echo $enroll['trang_thai_tt'] ?? 'N/A'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-warning"
                                                onclick="editEnrollment(<?php echo htmlspecialchars(json_encode($enroll)); ?>)"
                                                title="Sửa thông tin">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?update_payment=<?php echo $enroll['id']; ?>&payment_status=thanh_cong"
                                           class="btn btn-success" title="Xác nhận thanh toán">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="?update_status=<?php echo $enroll['id']; ?>&status=hoan_thanh"
                                           class="btn btn-primary" title="Hoàn thành">
                                            <i class="fas fa-check-double"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Thêm đăng ký mới -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Thêm đăng ký mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="add_enrollment" value="1">

                        <div class="mb-3">
                            <label class="form-label">Học viên <span class="text-danger">*</span></label>
                            <select name="id_nguoi_dung" class="form-select" required>
                                <option value="">-- Chọn học viên --</option>
                                <?php
                                $students->data_seek(0);
while ($student = $students->fetch_assoc()) {
    ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars($student['ho_ten']); ?> (<?php echo htmlspecialchars($student['email']); ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Khóa học <span class="text-danger">*</span></label>
                            <select name="id_khoa_hoc" class="form-select" required>
                                <option value="">-- Chọn khóa học --</option>
                                <?php
    $courses->data_seek(0);
while ($course = $courses->fetch_assoc()) {
    ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['ten']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mã giảm giá</label>
                            <select name="id_ma_giam_gia" class="form-select">
                                <option value="">-- Không áp dụng --</option>
                                <?php
    $discounts->data_seek(0);
while ($discount = $discounts->fetch_assoc()) {
    ?>
                                    <option value="<?php echo $discount['id']; ?>">
                                        <?php echo htmlspecialchars($discount['ma']); ?> (<?php echo $discount['giam_gia']; ?>%)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giảm giá áp dụng</label>
                            <input type="number" name="giam_gia_ap_dung" class="form-control"
                                   step="0.01" min="0" value="0">
                            <small class="text-muted">Nhập % (0-100) hoặc số tiền cụ thể</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select name="trang_thai" class="form-select" required>
                                <option value="dang_ky">dang_ky</option>
                                <option value="hoan_thanh">hoan_thanh</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Thêm đăng ký
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa thông tin đăng ký -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Sửa thông tin đăng ký</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="edit_enrollment" value="1">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label">Học viên</label>
                            <input type="text" class="form-control" id="edit_ho_ten" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Khóa học <span class="text-danger">*</span></label>
                            <select name="id_khoa_hoc" id="edit_id_khoa_hoc" class="form-select" required>
                                <?php
    $courses->data_seek(0);
while ($course = $courses->fetch_assoc()) {
    ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['ten']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mã giảm giá</label>
                            <select name="id_ma_giam_gia" id="edit_id_ma_giam_gia" class="form-select">
                                <option value="">-- Không áp dụng --</option>
                                <?php
    $discounts->data_seek(0);
while ($discount = $discounts->fetch_assoc()) {
    ?>
                                    <option value="<?php echo $discount['id']; ?>" data-discount="<?php echo $discount['giam_gia']; ?>">
                                        <?php echo htmlspecialchars($discount['ma']); ?> (<?php echo $discount['giam_gia']; ?>%)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giảm giá áp dụng</label>
                            <input type="number" name="giam_gia_ap_dung" id="edit_giam_gia_ap_dung"
                                   class="form-control" step="0.01" min="0" value="0">
                            <small class="text-muted">Nhập % (0-100) hoặc số tiền cụ thể</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select name="trang_thai" id="edit_trang_thai" class="form-select" required>
                                <option value="dang_ky">dang_ky</option>
                                <option value="hoan_thanh">hoan_thanh</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin-mobile.js"></script>
<script>
function editEnrollment(data) {
    // Điền dữ liệu vào form
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_ho_ten').value = data.ho_ten;
    document.getElementById('edit_id_khoa_hoc').value = data.id_khoa_hoc;
    document.getElementById('edit_id_ma_giam_gia').value = data.id_ma_giam_gia || '';
    document.getElementById('edit_giam_gia_ap_dung').value = data.giam_gia_ap_dung || 0;
    document.getElementById('edit_trang_thai').value = data.trang_thai;

    // Hiển thị modal
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>
</body>
</html>

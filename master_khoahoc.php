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
    $conn->query("DELETE FROM khoa_hoc WHERE id = $id");
    header('Location: master_khoahoc.php?msg=deleted');
    exit;
}

// Xử lý thêm/sửa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 0;
    $ten = $conn->real_escape_string($_POST['ten']);
    $mo_ta = $conn->real_escape_string($_POST['mo_ta']);
    $gia = (float) $_POST['gia'];
    $id_danh_muc = (int) $_POST['id_danh_muc'];
    $id_giang_vien = (int) $_POST['id_giang_vien'];
    $trang_thai = $conn->real_escape_string($_POST['trang_thai']);

    // Xử lý upload hình ảnh
    $hinh_anh = $_POST['hinh_anh_old'] ?? '';
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $file = $_FILES['hinh_anh'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed)) {
            $filename = time().'_'.basename($file['name']);
            $upload_path = 'img/'.$filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $hinh_anh = $upload_path;
            }
        }
    }

    // Xử lý video demo (URL YouTube)
    $video_demo = $conn->real_escape_string($_POST['video_demo'] ?? '');
    // Chuyển đổi YouTube URL sang embed format
    if (!empty($video_demo)) {
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $video_demo, $matches)) {
            $video_demo = 'https://www.youtube.com/embed/'.$matches[1];
        } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $video_demo, $matches)) {
            $video_demo = 'https://www.youtube.com/embed/'.$matches[1];
        }
    }

    if ($id > 0) {
        $sql = "UPDATE khoa_hoc SET ten='$ten', mo_ta='$mo_ta', gia=$gia, id_danh_muc=$id_danh_muc,
                id_giang_vien=$id_giang_vien, hinh_anh='$hinh_anh', video_demo='$video_demo',
                trang_thai_khoa_hoc='$trang_thai' WHERE id=$id";
    } else {
        $sql = "INSERT INTO khoa_hoc (ten, mo_ta, gia, id_danh_muc, id_giang_vien, hinh_anh, video_demo, trang_thai_khoa_hoc, ngay_tao)
                VALUES ('$ten', '$mo_ta', $gia, $id_danh_muc, $id_giang_vien, '$hinh_anh', '$video_demo', '$trang_thai', NOW())";
    }
    $conn->query($sql);
    header('Location: master_khoahoc.php?msg=success');
    exit;
}

$courses = $conn->query('SELECT kh.*, dm.ten as ten_danh_muc, nd.ho_ten as ten_giang_vien 
                         FROM khoa_hoc kh 
                         LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
                         LEFT JOIN nguoi_dung nd ON kh.id_giang_vien = nd.id
                         ORDER BY kh.ngay_tao DESC');
$categories = $conn->query('SELECT * FROM danh_muc ORDER BY ten');
$instructors = $conn->query("SELECT * FROM nguoi_dung WHERE vai_tro = 'giang_vien' ORDER BY ho_ten");

$edit_course = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_course = $conn->query("SELECT * FROM khoa_hoc WHERE id = $edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khóa học - CODE4Fun</title>
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
        <li><a href="master_khoahoc.php" class="active"><i class="fas fa-book"></i> Quản lý khóa học</a></li>
        <li><a href="master_dangky.php"><i class="fas fa-user-graduate"></i> Quản lý đăng ký</a></li>
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
            <h4 class="mb-0"><i class="fas fa-book"></i> Quản lý khóa học</h4>
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
            <h5 class="mb-0"><i class="fas fa-edit"></i> <?php echo $edit_course ? 'Sửa khóa học' : 'Thêm khóa học mới'; ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $edit_course['id'] ?? 0; ?>">
                <input type="hidden" name="hinh_anh_old" value="<?php echo htmlspecialchars($edit_course['hinh_anh'] ?? ''); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên khóa học *</label>
                        <input type="text" name="ten" class="form-control" value="<?php echo htmlspecialchars($edit_course['ten'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Giá (VNĐ) *</label>
                        <input type="number" name="gia" class="form-control" value="<?php echo $edit_course['gia'] ?? 0; ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Trạng thái *</label>
                        <select name="trang_thai" class="form-select" required>
                            <option value="draf" <?php echo ($edit_course['trang_thai_khoa_hoc'] ?? '') == 'draf' ? 'selected' : ''; ?>>Draf</option>
                            <option value="publish" <?php echo ($edit_course['trang_thai_khoa_hoc'] ?? 'publish') == 'publish' ? 'selected' : ''; ?>>Publish</option>
                            <option value="archive" <?php echo ($edit_course['trang_thai_khoa_hoc'] ?? '') == 'archive' ? 'selected' : ''; ?>>Archive</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Danh mục *</label>
                        <select name="id_danh_muc" class="form-select" required>
                            <?php while ($cat = $categories->fetch_assoc()) { ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_course['id_danh_muc'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['ten']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Giảng viên *</label>
                        <select name="id_giang_vien" class="form-select" required>
                            <?php while ($ins = $instructors->fetch_assoc()) { ?>
                                <option value="<?php echo $ins['id']; ?>" <?php echo ($edit_course['id_giang_vien'] ?? '') == $ins['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ins['ho_ten']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Hình ảnh khóa học</label>
                        <input type="file" name="hinh_anh" class="form-control" accept="image/*" onchange="previewImage(event)">
                        <small class="text-muted">Chọn file ảnh từ máy tính (JPG, PNG, GIF, WEBP)</small>
                        <?php if (!empty($edit_course['hinh_anh'])) { ?>
                            <div class="mt-2">
                                <img id="preview" src="<?php echo htmlspecialchars($edit_course['hinh_anh']); ?>" style="max-width: 200px; border-radius: 8px;">
                                <p class="text-muted small mt-1">Ảnh hiện tại: <?php echo htmlspecialchars($edit_course['hinh_anh']); ?></p>
                            </div>
                        <?php } else { ?>
                            <div class="mt-2">
                                <img id="preview" src="" style="max-width: 200px; border-radius: 8px; display: none;">
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Video demo (Link YouTube)</label>
                        <input type="text" name="video_demo" class="form-control" value="<?php echo htmlspecialchars($edit_course['video_demo'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                        <small class="text-muted">Nhập link YouTube (VD: https://www.youtube.com/watch?v=dQw4w9WgXcQ hoặc https://youtu.be/dQw4w9WgXcQ)</small>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="mo_ta" class="form-control" rows="4"><?php echo htmlspecialchars($edit_course['mo_ta'] ?? ''); ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                <?php if ($edit_course) { ?>
                    <a href="master_khoahoc.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
                <?php } ?>
            </form>
        </div>
    </div>

    <!-- Danh sách khóa học -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách khóa học</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên khóa học</th>
                            <th>Danh mục</th>
                            <th>Giảng viên</th>
                            <th>Giá</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($course = $courses->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['ten']); ?></td>
                                <td><?php echo htmlspecialchars($course['ten_danh_muc']); ?></td>
                                <td><?php echo htmlspecialchars($course['ten_giang_vien']); ?></td>
                                <td><?php echo number_format($course['gia']); ?>₫</td>
                                <td>
                                    <?php
                                    $badge = 'secondary';
                            if ($course['trang_thai_khoa_hoc'] == 'publish') {
                                $badge = 'success';
                            }
                            if ($course['trang_thai_khoa_hoc'] == 'archive') {
                                $badge = 'warning';
                            }
                            ?>
                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo $course['trang_thai_khoa_hoc']; ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($course['ngay_tao'])); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $course['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="?delete=<?php echo $course['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xác nhận xóa?')"><i class="fas fa-trash"></i></a>
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

<script>
function previewImage(event) {
    const preview = document.getElementById('preview');
    const file = event.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}
</script>

<script src="js/admin-mobile.js"></script>
</body>
</html>


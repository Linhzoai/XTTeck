<?php
$conn = new mysqli("localhost", "root", "", "xttech");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sp = $conn->query("SELECT * FROM sanpham WHERE id=$id")->fetch_assoc();

if (!$sp) {
    die("<div class='alert alert-danger text-center mt-5'>❌ Sản phẩm không tồn tại!</div>");
}

$danhmuc = $conn->query("SELECT * FROM danhmuc");

if (isset($_POST['update'])) {
    $ten = $conn->real_escape_string($_POST['ten']);
    $gia = (float)$_POST['gia'];
    $mota = $conn->real_escape_string($_POST['mota']);
    $dm_id = (int)$_POST['danhmuc_id'];
    $anh = $sp['hinh_anh']; // giữ ảnh cũ mặc định

    // Nếu có upload ảnh mới
    if (!empty($_FILES['anh']['name'])) {
        $targetDir = "img/";
        $fileName = basename($_FILES["anh"]["name"]);
        $targetFile = $targetDir . time() . "_" . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES["anh"]["tmp_name"], $targetFile)) {
                $anh = $targetFile;
            } else {
                echo "<div class='alert alert-danger'>Lỗi khi tải ảnh lên!</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)</div>";
        }
    }

    // ✅ Đổi 'mota' thành 'mo_ta'
    $sql = "UPDATE sanpham 
            SET ten_sanpham='$ten', gia=$gia, mo_ta='$mota', danhmuc_id=$dm_id, hinh_anh='$anh' 
            WHERE id=$id";

    if ($conn->query($sql)) {
        header("Location: chitietsanpham.php?id=$id&updated=1");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Cập nhật thất bại: {$conn->error}</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sửa sản phẩm</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">✏ Sửa thông tin sản phẩm</h2>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="ten" class="form-control" value="<?= htmlspecialchars($sp['ten_sanpham']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Giá (VNĐ)</label>
            <input type="number" name="gia" class="form-control" value="<?= $sp['gia']; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Danh mục</label>
            <select name="danhmuc_id" class="form-select">
                <?php while ($row = $danhmuc->fetch_assoc()) { ?>
                    <option value="<?= $row['id']; ?>" <?= $row['id'] == $sp['danhmuc_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($row['ten_danhmuc']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="mota" rows="5" class="form-control"><?= htmlspecialchars($sp['mo_ta']); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh sản phẩm</label><br>
            <?php if (!empty($sp['hinh_anh'])) { ?>
                <img src="<?= $sp['hinh_anh']; ?>" alt="Ảnh hiện tại" width="150" class="mb-2 rounded border"><br>
            <?php } ?>
            <input type="file" name="anh" class="form-control">
        </div>

        <button type="submit" name="update" class="btn btn-success">💾 Lưu thay đổi</button>
        <a href="chitietsanpham.php?id=<?= $id; ?>" class="btn btn-secondary">Hủy</a>
    </form>
</div>
</body>
</html>

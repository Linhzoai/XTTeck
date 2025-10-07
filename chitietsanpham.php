<?php
// ====== KẾT NỐI DATABASE ======
$conn = new mysqli("localhost", "root", "", "xttech");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ====== LẤY SẢN PHẨM THEO ID ======
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT sp.*, dm.ten_danhmuc 
        FROM sanpham sp 
        LEFT JOIN danhmuc dm ON sp.danhmuc_id = dm.id 
        WHERE sp.id = $id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("<div style='padding:30px;text-align:center;'>❌ Sản phẩm không tồn tại.</div>");
}
$sp = $result->fetch_assoc();

// ====== XỬ LÝ XÓA SẢN PHẨM ======
if (isset($_POST['delete'])) {
    // Lấy đường dẫn ảnh trước khi xóa
    $imgPath = $sp['hinh_anh'];

    $delete_sql = "DELETE FROM sanpham WHERE id = $id";
    if ($conn->query($delete_sql)) {
        // ✅ Xóa ảnh khỏi thư mục nếu có tồn tại
        if (!empty($imgPath) && file_exists($imgPath)) {
            unlink($imgPath);
        }

        header("Location: sanpham.php?deleted=1");
        exit;
    } else {
        echo "<script>alert('Không thể xóa sản phẩm!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chi tiết sản phẩm - <?= htmlspecialchars($sp['ten_sanpham']); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
        background-color: #f7f7f7;
    }
    .product-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
        padding: 20px;
    }
    .product-img {
        width: 100%;
        border-radius: 10px;
        max-height: 400px;
        object-fit: cover;
    }
    .price {
        font-size: 1.6rem;
        color: #dc3545;
        font-weight: bold;
    }
    footer {
        background: #222;
        color: #ccc;
        text-align: center;
        padding: 15px;
        margin-top: 40px;
    }
    .btn-action {
        min-width: 120px;
    }
  </style>
</head>
<body>

<!-- HEADER -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a href="index.php" class="navbar-brand fw-bold">Cửa Nhôm Group</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<!-- MAIN -->
<div class="container mt-5 mb-5">
  <div class="product-card">
    <div class="row align-items-center">
      <div class="col-md-5 text-center mb-3 mb-md-0">
        <img src="<?= htmlspecialchars($sp['hinh_anh']); ?>" alt="<?= htmlspecialchars($sp['ten_sanpham']); ?>" class="product-img shadow-sm">
      </div>

      <div class="col-md-7">
        <h2 class="fw-bold"><?= htmlspecialchars($sp['ten_sanpham']); ?></h2>
        <p class="text-muted mb-1"><strong>Danh mục:</strong> <?= htmlspecialchars($sp['ten_danhmuc'] ?? 'Chưa phân loại'); ?></p>
        <p class="price mb-3"><?= number_format($sp['gia'], 0, ',', '.'); ?> VNĐ</p>
        <p><?= nl2br(htmlspecialchars($sp['mo_ta'])); ?></p>

        <div class="mt-4">
          <a href="sanpham.php" class="btn btn-secondary btn-action me-2">⬅ Quay lại</a>
          <a href="suasanpham.php?id=<?= $sp['id']; ?>" class="btn btn-primary btn-action me-2">✏ Sửa</a>
          
          <!-- Nút XÓA có xác nhận -->
          <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này không?');">
            <button type="submit" name="delete" class="btn btn-danger btn-action">🗑 Xóa</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<footer>
  © 2025 Cửa Nhôm Group — Trang quản lý sản phẩm.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>

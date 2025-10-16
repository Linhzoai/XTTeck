<?php
// Kết nối CSDL
require_once 'config.php';

// Lấy danh mục cho bộ lọc
$dm_result = $conn->query("SELECT * FROM danhmuc");

// Xử lý tìm kiếm và lọc
$where = "1";
$keyword = "";
$danhmuc_id = "";

if (isset($_GET['keyword']) && $_GET['keyword'] !== "") {
    $keyword = $conn->real_escape_string($_GET['keyword']);
    $where .= " AND ten_sanpham LIKE '%$keyword%'";
}
if (isset($_GET['danhmuc_id']) && $_GET['danhmuc_id'] !== "") {
    $danhmuc_id = (int)$_GET['danhmuc_id'];
    $where .= " AND danhmuc_id = $danhmuc_id";
}

// Lấy sản phẩm
$sql = "SELECT sp.*, dm.ten_danhmuc 
        FROM sanpham sp 
        LEFT JOIN danhmuc dm ON sp.danhmuc_id = dm.id
        WHERE $where
        ORDER BY sp.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sản phẩm - XT Teck</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
      body {
          background-color: #f4f6f8;
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }

      .navbar {
          background: linear-gradient(90deg, #0d6efd, #6610f2);
          box-shadow: 0 2px 10px rgba(0,0,0,0.15);
      }

      .navbar-brand {
          font-size: 1.5rem;
          font-weight: 700;
      }

      .search-bar {
          background: #fff;
          padding: 20px;
          border-radius: 12px;
          box-shadow: 0 3px 10px rgba(0,0,0,0.05);
          margin-top: 30px;
      }

      .form-control {
          border-radius: 10px;
          box-shadow: none;
      }

      .btn-primary {
          border-radius: 10px;
          background: linear-gradient(45deg, #0d6efd, #6610f2);
          border: none;
          transition: transform 0.2s ease, box-shadow 0.2s ease;
      }

      .btn-primary:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 10px rgba(13,110,253,0.4);
      }

      .product-card {
          border: none;
          border-radius: 15px;
          overflow: hidden;
          background: #fff;
          box-shadow: 0 3px 10px rgba(0,0,0,0.08);
          transition: transform 0.25s ease, box-shadow 0.25s ease;
      }

      .product-card:hover {
          transform: translateY(-6px);
          box-shadow: 0 6px 18px rgba(0,0,0,0.15);
      }

      .product-img {
          height: 220px;
          object-fit: cover;
          width: 100%;
          border-bottom: 1px solid #eee;
      }

      .card-body {
          padding: 15px 18px;
      }

      .card-title {
          font-size: 1.05rem;
          font-weight: 600;
          color: #333;
          height: 45px;
          overflow: hidden;
      }

      .price {
          color: #e63946;
          font-weight: 700;
          font-size: 1.1rem;
      }

      .btn-outline-primary {
          border-radius: 10px;
          border-color: #0d6efd;
          color: #0d6efd;
          transition: all 0.2s ease;
      }

      .btn-outline-primary:hover {
          background-color: #0d6efd;
          color: #fff;
      }

      footer {
          background: #111;
          color: #ccc;
          padding: 20px 10px;
          text-align: center;
          margin-top: 60px;
          font-size: 0.9rem;
      }

      footer span {
          color: #0d6efd;
          font-weight: 600;
      }

      @media (max-width: 576px) {
          .product-img {
              height: 180px;
          }
      }
  </style>
</head>
<body>

<!-- Thanh điều hướng -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="fa-solid fa-microchip me-2"></i>XT Teck</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<div class="container mt-4">

  <!-- Thanh tìm kiếm & lọc -->
  <div class="search-bar">
    <div class="row align-items-center g-3">
      <div class="col-md-8">
        <form class="d-flex" method="GET">
          <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" class="form-control me-2" placeholder="🔍 Nhập tên sản phẩm...">
          <button class="btn btn-primary px-4" type="submit">Tìm kiếm</button>
        </form>
      </div>
      <div class="col-md-4">
        <form method="GET">
          <select name="danhmuc_id" class="form-select" onchange="this.form.submit()">
            <option value="">Lọc theo danh mục</option>
            <?php while ($dm = $dm_result->fetch_assoc()) { ?>
              <option value="<?= $dm['id']; ?>" <?= ($dm['id'] == $danhmuc_id) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($dm['ten_danhmuc']); ?>
              </option>
            <?php } ?>
          </select>
        </form>
      </div>
    </div>
  </div>

  <!-- Danh sách sản phẩm -->
  <div class="row mt-4">
    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
          <div class="card product-card h-100">
            <?php 
              $imgPath = 'img/' . basename($row['hinh_anh']);
            ?>
            <img src="<?= htmlspecialchars($imgPath); ?>" class="product-img" alt="<?= htmlspecialchars($row['ten_sanpham']); ?>">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($row['ten_sanpham']); ?></h5>
              <p class="price mb-1"><?= number_format($row['gia'], 0, ',', '.'); ?> VNĐ</p>
              <p class="text-muted small mb-3">📁 <?= htmlspecialchars($row['ten_danhmuc']); ?></p>
              <a href="thongtinsanpham.php?id=<?= $row['id']; ?>" class="btn btn-outline-primary mt-auto w-100">
                Xem chi tiết
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <div class="alert alert-warning mt-4">Không tìm thấy sản phẩm nào phù hợp!</div>
      </div>
    <?php endif; ?>
  </div>

</div>

<footer>
  © 2025 <span>XT Teck</span> — Nền tảng bán hàng chuyên nghiệp và tối ưu.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>

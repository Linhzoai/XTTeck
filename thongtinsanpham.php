<?php
session_start();

// Tạo session_id nếu chưa có
if (!isset($_SESSION['cart_session_id'])) {
    $_SESSION['cart_session_id'] = session_id();
}

$conn = new mysqli("localhost", "root", "", "xttech");
$conn->set_charset("utf8mb4");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin sản phẩm
$sql = "SELECT sanpham.*, danhmuc.ten_danhmuc AS ten_danhmuc
        FROM sanpham 
        LEFT JOIN danhmuc ON sanpham.danhmuc_id = danhmuc.id
        WHERE sanpham.id = $id";
$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}

$sp = $result->fetch_assoc();
if (!$sp) {
    echo "<p style='color:red;text-align:center;margin-top:50px;'>Sản phẩm không tồn tại!</p>";
    exit;
}

// Đếm số lượng giỏ hàng
$session_id = $_SESSION['cart_session_id'];
$cart_count = $conn->query("SELECT SUM(so_luong) as total FROM giohang WHERE session_id='$session_id'")->fetch_assoc()['total'] ?? 0;

// Xử lý thêm vào giỏ hàng
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $sanpham_id = (int)$_POST['sanpham_id'];
    $so_luong = isset($_POST['so_luong']) ? (int)$_POST['so_luong'] : 1;
    
    $check = $conn->query("SELECT * FROM giohang WHERE session_id='$session_id' AND sanpham_id=$sanpham_id");
    
    if ($check->num_rows > 0) {
        $conn->query("UPDATE giohang SET so_luong = so_luong + $so_luong WHERE session_id='$session_id' AND sanpham_id=$sanpham_id");
    } else {
        $conn->query("INSERT INTO giohang (session_id, sanpham_id, so_luong) VALUES ('$session_id', $sanpham_id, $so_luong)");
    }
    
    $success_msg = "Đã thêm sản phẩm vào giỏ hàng!";
    $cart_count += $so_luong;
}

// Xử lý thêm bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['noi_dung'])) {
    $ten = $conn->real_escape_string($_POST['ten']);
    $noi_dung = $conn->real_escape_string($_POST['noi_dung']);
    $conn->query("INSERT INTO binhluan (sanpham_id, ten_nguoi_dung, noi_dung)
                  VALUES ($id, '$ten', '$noi_dung')");
}

// Lấy danh sách bình luận
$binhluan = $conn->query("SELECT * FROM binhluan WHERE sanpham_id = $id ORDER BY ngay_binh_luan DESC");

// Lấy sản phẩm liên quan
$related_products = $conn->query("SELECT * FROM sanpham WHERE danhmuc_id = {$sp['danhmuc_id']} AND id != $id LIMIT 4");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sp['ten_sanpham']); ?> - XTTech</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ========== BREADCRUMB ========== */
        .breadcrumb {
            background: #f8f9fa;
            padding: 15px 0;
            margin-top: 20px;
        }

        .breadcrumb-list {
            display: flex;
            align-items: center;
            gap: 10px;
            list-style: none;
            font-size: 14px;
        }

        .breadcrumb-item a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: #2f74d5;
        }

        .breadcrumb-item.active {
            color: #2f74d5;
            font-weight: 600;
        }

        /* ========== PRODUCT DETAIL ========== */
        .product-detail {
            padding: 60px 0;
            background: white;
        }

        .product-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-bottom: 60px;
        }

        .product-gallery {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .product-main-img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .product-main-img:hover {
            transform: scale(1.02);
        }

        .product-info {
            padding: 20px 0;
        }

        .product-category {
            display: inline-block;
            background: linear-gradient(135deg, #eaf3ff 0%, #d4e7ff 100%);
            color: #2f74d5;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .product-title {
            font-size: 36px;
            color: #333;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }

        .stars {
            color: #ffc107;
            font-size: 18px;
        }

        .rating-count {
            color: #666;
            font-size: 14px;
        }

        .product-price {
            display: flex;
            align-items: baseline;
            gap: 15px;
            margin-bottom: 30px;
        }

        .price-current {
            font-size: 42px;
            color: #d40000;
            font-weight: 800;
        }

        .price-old {
            font-size: 24px;
            color: #999;
            text-decoration: line-through;
        }

        .product-desc {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            line-height: 1.8;
            color: #555;
        }

        .product-features {
            margin-bottom: 30px;
        }

        .product-features h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .feature-list {
            list-style: none;
        }

        .feature-list li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: #555;
        }

        .feature-list li::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #2ecc71;
        }

        /* ========== ORDER FORM ========== */
        .order-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .stock-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 12px 20px;
            background: white;
            border-radius: 8px;
            font-weight: 600;
        }

        .in-stock {
            color: #2ecc71;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .quantity-label {
            font-weight: 600;
            color: #333;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .qty-btn {
            background: white;
            border: none;
            width: 40px;
            height: 40px;
            cursor: pointer;
            font-size: 18px;
            color: #2f74d5;
            transition: all 0.3s ease;
        }

        .qty-btn:hover {
            background: #2f74d5;
            color: white;
        }

        .qty-input {
            width: 60px;
            height: 40px;
            border: none;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        .btn{
            flex: 1;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46,204,113,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-add-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(46,204,113,0.4);
        }

        .btn-buy-now {
            flex: 1;
            background: linear-gradient(135deg, #2f74d5 0%, #1a5bb8 100%);
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(47,116,213,0.3);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-buy-now:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(47,116,213,0.4);
        }

        /* ========== SUCCESS MESSAGE ========== */
        .success-alert {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #28a745;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-alert i {
            font-size: 24px;
        }

        .success-alert a {
            color: #155724;
            font-weight: 700;
            text-decoration: underline;
            margin-left: 10px;
        }

        /* ========== TABS ========== */
        .product-tabs {
            margin: 60px 0;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid #eee;
            margin-bottom: 30px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: #2f74d5;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #2f74d5;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* ========== COMMENTS ========== */
        .comment-section {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 16px;
        }

        .comment-section h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .comment-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .comment-form input,
        .comment-form textarea {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }

        .comment-form input:focus,
        .comment-form textarea:focus {
            outline: none;
            border-color: #2f74d5;
        }

        .comment-form textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn-submit-comment {
            background: linear-gradient(135deg, #2f74d5 0%, #1a5bb8 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit-comment:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(47,116,213,0.3);
        }

        .comment-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .comment-item {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .comment-item:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .comment-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .comment-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2f74d5 0%, #1a5bb8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }

        .comment-meta {
            flex: 1;
        }

        .comment-name {
            font-weight: 700;
            color: #333;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .comment-time {
            font-size: 13px;
            color: #999;
        }

        .comment-content {
            color: #555;
            line-height: 1.8;
        }

        /* ========== RELATED PRODUCTS ========== */
        .related-products {
            margin-top: 80px;
            padding-top: 60px;
            border-top: 2px solid #eee;
        }

        .related-title {
            font-size: 32px;
            color: #333;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1024px) {
            .product-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .product-gallery {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .product-title {
                font-size: 28px;
            }

            .price-current {
                font-size: 32px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .tab-buttons {
                overflow-x: auto;
            }

            .related-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container_main">
        <ul class="breadcrumb-list">
            <li class="breadcrumb-item"><a href="index.php"><i class="fa-solid fa-house"></i> Trang chủ</a></li>
            <li class="breadcrumb-item"><i class="fa-solid fa-chevron-right"></i></li>
            <li class="breadcrumb-item"><a href="sanpham.php">Sản phẩm</a></li>
            <li class="breadcrumb-item"><i class="fa-solid fa-chevron-right"></i></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($sp['ten_sanpham']); ?></li>
        </ul>
    </div>
</div>

<!-- Product Detail -->
<div class="product-detail">
    <div class="container_main">
        <div class="product-container">
            <!-- Gallery -->
            <div class="product-gallery">
                <img src="<?php echo htmlspecialchars($sp['hinh_anh']); ?>" 
                     alt="<?php echo htmlspecialchars($sp['ten_sanpham']); ?>" 
                     class="product-main-img">
            </div>

            <!-- Info -->
            <div class="product-info">
                <span class="product-category">
                    <i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($sp['ten_danhmuc'] ?? 'Chưa phân loại'); ?>
                </span>
                
                <h1 class="product-title"><?php echo htmlspecialchars($sp['ten_sanpham']); ?></h1>
                
                <div class="product-rating">
                    <div class="stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <span class="rating-count">(<?php echo $binhluan->num_rows; ?> đánh giá)</span>
                </div>

                <div class="product-price">
                    <span class="price-current"><?php echo number_format($sp['gia'], 0, ',', '.'); ?>₫</span>
                    <span class="price-old"><?php echo number_format($sp['gia'] * 1.2, 0, ',', '.'); ?>₫</span>
                </div>

                <?php if ($sp['mo_ta']): ?>
                    <div class="product-desc">
                        <strong><i class="fa-solid fa-circle-info"></i> Mô tả sản phẩm:</strong><br>
                        <?php echo nl2br(htmlspecialchars($sp['mo_ta'])); ?>
                    </div>
                <?php endif; ?>

                <div class="product-features">
                    <h3><i class="fa-solid fa-clipboard-check"></i> Đặc điểm nổi bật</h3>
                    <ul class="feature-list">
                        <li>Chất liệu cao cấp, bền đẹp theo thời gian</li>
                        <li>Bảo hành chính hãng 12 tháng</li>
                        <li>Lắp đặt miễn phí tại nhà</li>
                        <li>Hỗ trợ tư vấn 24/7</li>
                    </ul>
                </div>

                <!-- Order Section -->
                <div class="order-section">
                    <?php if ($success_msg): ?>
                        <div class="success-alert">
                            <i class="fa-solid fa-circle-check"></i>
                            <div>
                                <strong><?php echo $success_msg; ?></strong>
                                <a href="giohang.php">Xem giỏ hàng <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="stock-status in-stock">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Còn hàng - Sẵn sàng giao</span>
                    </div>

                    <form method="post" id="orderForm">
                        <input type="hidden" name="sanpham_id" value="<?php echo $sp['id']; ?>">
                        
                        <div class="quantity-selector">
                            <span class="quantity-label">Số lượng:</span>
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn" onclick="decreaseQty()">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" name="so_luong" id="quantity" class="qty-input" value="1" min="1" max="<?php echo $sp['ton_kho'] ?? 99; ?>" readonly>
                                <button type="button" class="qty-btn" onclick="increaseQty()">
                                    <i class="fa-solid fa-plus"></i>
                                </button>   
                            </div>
                        </div>

                        <div class="action-buttons">
                        <button type="submit" name="add_to_cart" class="btn btn-buy w-100">
                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
                        </button>

                        <a href="giohang.php" class="btn-buy-now">
                            <i class="fa-solid fa-bolt"></i> Xem giỏ hàng
                        </a>
                    </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="product-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="openTab('description')">
                    <i class="fa-solid fa-circle-info"></i> Mô tả chi tiết
                </button>
                <button class="tab-btn" onclick="openTab('reviews')">
                    <i class="fa-solid fa-comments"></i> Đánh giá (<?php echo $binhluan->num_rows; ?>)
                </button>
                <button class="tab-btn" onclick="openTab('delivery')">
                    <i class="fa-solid fa-truck"></i> Vận chuyển
                </button>
            </div>

            <div id="description" class="tab-content active">
                <div class="product-desc">
                    <h3 style="margin-bottom: 20px; color: #2f74d5;">Thông tin chi tiết</h3>
                    <?php echo nl2br(htmlspecialchars($sp['mo_ta'] ?? 'Đang cập nhật thông tin chi tiết...')); ?>
                </div>
            </div>

            <div id="reviews" class="tab-content">
                <div class="comment-section">
                    <h3><i class="fa-solid fa-comments"></i> Đánh giá từ khách hàng</h3>

                    <!-- Comment Form -->
                    <form class="comment-form" method="post">
                        <input type="text" name="ten" placeholder="Họ và tên của bạn" required>
                        <textarea name="noi_dung" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..." required></textarea>
                        <button type="submit" class="btn-submit-comment">
                            <i class="fa-solid fa-paper-plane"></i> Gửi đánh giá
                        </button>
                    </form>

                    <!-- Comment List -->
                    <div class="comment-list">
                        <?php if ($binhluan->num_rows > 0): ?>
                            <?php while ($bl = $binhluan->fetch_assoc()): ?>
                                <div class="comment-item">
                                    <div class="comment-header">
                                        <div class="comment-avatar">
                                            <?php echo strtoupper(substr($bl['ten_nguoi_dung'], 0, 1)); ?>
                                        </div>
                                        <div class="comment-meta">
                                            <div class="comment-name"><?php echo htmlspecialchars($bl['ten_nguoi_dung']); ?></div>
                                            <div class="comment-time">
                                                <i class="fa-regular fa-clock"></i> 
                                                <?php echo date('d/m/Y H:i', strtotime($bl['ngay_binh_luan'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="comment-content">
                                        <?php echo nl2br(htmlspecialchars($bl['noi_dung'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="text-align: center; color: #999; padding: 40px 0;">
                                <i class="fa-regular fa-comment-dots" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                                Chưa có đánh giá nào. Hãy là người đầu tiên!
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="delivery" class="tab-content">
                <div class="product-desc">
                    <h3 style="margin-bottom: 20px; color: #2f74d5;"><i class="fa-solid fa-truck-fast"></i> Chính sách vận chuyển</h3>
                    <ul class="feature-list">
                        <li>Giao hàng toàn quốc, nhận hàng trong vòng 2-7 ngày</li>
                        <li>Miễn phí vận chuyển cho đơn hàng trên 5 triệu đồng</li>
                        <li>Kiểm tra hàng trước khi thanh toán</li>
                        <li>Đổi trả trong vòng 7 ngày nếu có lỗi từ nhà sản xuất</li>
                        <li>Hỗ trợ lắp đặt tận nơi</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if ($related_products && $related_products->num_rows > 0): ?>
            <div class="related-products">
                <h2 class="related-title">
                    <i class="fa-solid fa-layer-group"></i> Sản phẩm liên quan
                </h2>
                <div class="related-grid">
                    <?php while ($related = $related_products->fetch_assoc()): ?>
                        <a href="thongtinsanpham.php?id=<?php echo $related['id']; ?>" class="produce_link">
                            <div class="produce_item">
                                <figure>
                                    <img src="<?php echo htmlspecialchars($related['hinh_anh']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['ten_sanpham']); ?>" 
                                         class="produce_img"
                                         loading="lazy">
                                </figure>
                                <h3 class="produce_title"><?php echo htmlspecialchars($related['ten_sanpham']); ?></h3>
                                <p class="produce_price"><?php echo number_format($related['gia'], 0, ',', '.'); ?>₫</p>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- Floating Icons -->
<div class="list_icon">
    <a href="https://www.facebook.com/nguyen.linh.757040" class="icon_link" target="_blank" rel="noopener" aria-label="Facebook">
        <img src="img/fb.svg" class="icon_img" alt="Facebook">
    </a>
    <a href="https://zalo.me/your-zalo" class="icon_link" target="_blank" rel="noopener" aria-label="Zalo">
        <img src="img/zalo.png" class="icon_img" alt="Zalo">
    </a>
    <a href="https://www.instagram.com/your-instagram" class="icon_link" target="_blank" rel="noopener" aria-label="Instagram">
        <img src="img/insta.png" class="icon_img" alt="Instagram">
    </a>
</div>

<script>
    // Quantity Controls
    function increaseQty() {
        const input = document.getElementById('quantity');
        const max = parseInt(input.max);
        const current = parseInt(input.value);
        if (current < max) {
            input.value = current + 1;
        }
    }

    function decreaseQty() {
        const input = document.getElementById('quantity');
        const min = parseInt(input.min);
        const current = parseInt(input.value);
        if (current > min) {
            input.value = current - 1;
        }
    }

    // Tab Switching
    function openTab(tabName) {
        // Hide all tabs
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => tab.classList.remove('active'));

        // Remove active class from all buttons
        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));

        // Show selected tab
        document.getElementById(tabName).classList.add('active');
        
        // Add active class to clicked button
        event.target.classList.add('active');
    }

    // Add to cart animation
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        const button = this.querySelector('.btn-add-cart');
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang thêm...';
        button.disabled = true;
        
        // Re-enable after form submission
        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ';
        }, 2000);
    });

    // Image zoom effect
    const mainImg = document.querySelector('.product-main-img');
    if (mainImg) {
        mainImg.addEventListener('click', function() {
            if (this.style.transform === 'scale(1.5)') {
                this.style.transform = 'scale(1)';
                this.style.cursor = 'zoom-in';
            } else {
                this.style.transform = 'scale(1.5)';
                this.style.cursor = 'zoom-out';
            }
        });
    }

    // Smooth scroll to comments when clicking review tab
    const reviewBtn = document.querySelector('.tab-btn:nth-child(2)');
    if (reviewBtn) {
        reviewBtn.addEventListener('click', function() {
            setTimeout(() => {
                document.querySelector('.comment-section').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 100);
        });
    }

    // Form validation
    const commentForm = document.querySelector('.comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            const name = this.querySelector('input[name="ten"]').value.trim();
            const content = this.querySelector('textarea[name="noi_dung"]').value.trim();
            
            if (name === '' || content === '') {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin!');
                return false;
            }
            
            if (content.length < 10) {
                e.preventDefault();
                alert('Nội dung đánh giá quá ngắn. Vui lòng nhập ít nhất 10 ký tự!');
                return false;
            }
        });
    }

    // Auto-hide success message after 5 seconds
    const successAlert = document.querySelector('.success-alert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'all 0.5s ease';
            successAlert.style.opacity = '0';
            successAlert.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 500);
        }, 5000);
    }

    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Add animation on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.comment-item, .produce_item').forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(item);
    });

    // Copy product link
    function copyProductLink() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('Đã copy link sản phẩm!');
        });
    }

    // Share on social media
    function shareProduct(platform) {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.querySelector('.product-title').textContent);
        
        let shareUrl = '';
        switch(platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                break;
            case 'zalo':
                shareUrl = `https://zalo.me/share?url=${url}`;
                break;
        }
        
        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    }

    // Print product info
    function printProduct() {
        window.print();
    }

    console.log('%cXTTech Product Page', 'color: #2f74d5; font-size: 20px; font-weight: bold;');
    console.log('%cGiao diện chi tiết sản phẩm đã được tối ưu', 'color: #555; font-size: 14px;');
</script>

</body>
</html>

<?php
$conn->close();
?>
<?php
session_start();

// Tạo session_id nếu chưa có
if (!isset($_SESSION['cart_session_id'])) {
    $_SESSION['cart_session_id'] = session_id();
}

require_once 'config.php';

$session_id = $_SESSION['cart_session_id'];
$message = '';

// Xử lý cập nhật số lượng
if (isset($_POST['update_quantity'])) {
    $cart_id = (int)$_POST['cart_id'];
    $so_luong = (int)$_POST['so_luong'];
    
    if ($so_luong > 0) {
        $conn->query("UPDATE giohang SET so_luong = $so_luong WHERE id = $cart_id AND session_id = '$session_id'");
        $message = "Đã cập nhật số lượng!";
    }
}

// Xử lý xóa sản phẩm khỏi giỏ
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $conn->query("DELETE FROM giohang WHERE id = $cart_id AND session_id = '$session_id'");
    $message = "Đã xóa sản phẩm khỏi giỏ hàng!";
    header("Location: giohang.php");
    exit;
}

// Xử lý đặt hàng
if (isset($_POST['checkout'])) {
    // Lấy thông tin khách hàng
    $ten_kh = $conn->real_escape_string($_POST['ten_khachhang']);
    $sdt = $conn->real_escape_string($_POST['sdt']);
    $dia_chi = $conn->real_escape_string($_POST['dia_chi']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Thêm khách hàng mới
    $conn->query("INSERT INTO khachhang (ten_khachhang, sdt, dia_chi, email) 
                  VALUES ('$ten_kh', '$sdt', '$dia_chi', '$email')");
    $khachhang_id = $conn->insert_id;
    
    // Tính tổng tiền
    $cart_items = $conn->query("SELECT gh.*, sp.gia 
                                FROM giohang gh 
                                JOIN sanpham sp ON gh.sanpham_id = sp.id 
                                WHERE gh.session_id = '$session_id'");
    
    $tong_tien = 0;
    while ($item = $cart_items->fetch_assoc()) {
        $tong_tien += $item['gia'] * $item['so_luong'];
    }
    
    // Tạo đơn hàng
    $conn->query("INSERT INTO donhang (khachhang_id, tong_tien) 
                  VALUES ($khachhang_id, $tong_tien)");
    $donhang_id = $conn->insert_id;
    
    // Thêm chi tiết đơn hàng
    $cart_items->data_seek(0); // Reset pointer
    while ($item = $cart_items->fetch_assoc()) {
        $sp_id = $item['sanpham_id'];
        $sl = $item['so_luong'];
        $gia = $item['gia'];
        $conn->query("INSERT INTO chitiet_donhang (donhang_id, sanpham_id, so_luong, don_gia) 
                      VALUES ($donhang_id, $sp_id, $sl, $gia)");
    }
    
    // Xóa giỏ hàng
    $conn->query("DELETE FROM giohang WHERE session_id = '$session_id'");
    
    $message = "Đặt hàng thành công! Mã đơn hàng: #$donhang_id";
}

// Lấy sản phẩm trong giỏ hàng
$cart_query = "SELECT gh.*, sp.ten_sanpham, sp.gia, sp.hinh_anh 
               FROM giohang gh 
               JOIN sanpham sp ON gh.sanpham_id = sp.id 
               WHERE gh.session_id = '$session_id'
               ORDER BY gh.ngay_them DESC";
$cart_result = $conn->query($cart_query);

// Tính tổng tiền
$tong_tien = 0;
$cart_items_array = [];
if ($cart_result->num_rows > 0) {
    while ($row = $cart_result->fetch_assoc()) {
        $cart_items_array[] = $row;
        $tong_tien += $row['gia'] * $row['so_luong'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - XTTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .cart-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .cart-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .cart-item-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .cart-item-price {
            color: #dc3545;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-input {
            width: 70px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-remove:hover {
            background: #c82333;
        }
        .cart-summary {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .total-price {
            font-size: 1.8rem;
            color: #dc3545;
            font-weight: bold;
        }
        .checkout-form {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-cart i {
            font-size: 80px;
            color: #ccc;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fa-solid fa-home"></i> XTTech
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Trang chủ</a>
            <a class="nav-link" href="sanpham.php">Sản phẩm</a>
        </div>
    </div>
</nav>

<div class="cart-container">
    <h1 class="mb-4"><i class="fa-solid fa-shopping-cart"></i> Giỏ hàng của bạn</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items_array)): ?>
        <div class="empty-cart">
            <i class="fa-solid fa-cart-shopping"></i>
            <h3>Giỏ hàng của bạn đang trống</h3>
            <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm!</p>
            <a href="sanpham.php" class="btn btn-primary btn-lg mt-3">
                <i class="fa-solid fa-shopping-bag"></i> Mua sắm ngay
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Danh sách sản phẩm -->
            <div class="col-lg-8">
                <?php foreach ($cart_items_array as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['hinh_anh']); ?>" 
                             alt="<?php echo htmlspecialchars($item['ten_sanpham']); ?>" 
                             class="cart-item-img">
                        
                        <div class="cart-item-info">
                            <h5 class="cart-item-title"><?php echo htmlspecialchars($item['ten_sanpham']); ?></h5>
                            <p class="cart-item-price"><?php echo number_format($item['gia'], 0, ',', '.'); ?> ₫</p>
                            
                            <form method="POST" class="quantity-control">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <label>Số lượng:</label>
                                <input type="number" name="so_luong" class="quantity-input" 
                                       value="<?php echo $item['so_luong']; ?>" min="1">
                                <button type="submit" name="update_quantity" class="btn btn-sm btn-primary">
                                    Cập nhật
                                </button>
                            </form>
                            
                            <p class="mt-2">
                                <strong>Thành tiền:</strong> 
                                <span class="text-danger">
                                    <?php echo number_format($item['gia'] * $item['so_luong'], 0, ',', '.'); ?> ₫
                                </span>
                            </p>
                        </div>
                        
                        <div>
                            <a href="?remove=<?php echo $item['id']; ?>" 
                               class="btn-remove"
                               onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                <i class="fa-solid fa-trash"></i> Xóa
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Tổng kết & Form đặt hàng -->
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h4>Tổng đơn hàng</h4>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($tong_tien, 0, ',', '.'); ?> ₫</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Phí vận chuyển:</span>
                        <span class="text-success">Miễn phí</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Tổng cộng:</strong>
                        <span class="total-price"><?php echo number_format($tong_tien, 0, ',', '.'); ?> ₫</span>
                    </div>
                </div>

                <!-- Form đặt hàng -->
                <div class="checkout-form">
                    <h4>Thông tin đặt hàng</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" name="ten_khachhang" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="sdt" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea name="dia_chi" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="checkout" class="btn btn-success w-100 btn-lg">
                            <i class="fa-solid fa-check-circle"></i> Đặt hàng ngay
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
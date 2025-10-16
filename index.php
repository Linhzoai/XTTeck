<?php
session_start();

// Tạo session_id nếu chưa có
if (!isset($_SESSION['cart_session_id'])) {
    $_SESSION['cart_session_id'] = session_id();
}

// Kết nối database
require_once 'config.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Đếm số lượng sản phẩm trong giỏ hàng
$session_id = $_SESSION['cart_session_id'];
$cart_count = $conn->query("SELECT SUM(so_luong) as total FROM giohang WHERE session_id='$session_id'")->fetch_assoc()['total'] ?? 0;

// Lấy tối đa 8 sản phẩm
$sql = "SELECT * FROM sanpham LIMIT 8";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XTTech - Trang chủ | Cửa nhôm, uPVC, Cửa kính cao cấp</title>
    <meta name="description" content="Chuyên cung cấp và thi công cửa nhôm, cửa uPVC, cửa kính cao cấp với chất lượng tốt nhất">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo.png">
</head>
<body>
    <!-- ========== HEADER ========== -->
    <header>
        <div class="container_main">  
            <nav class="tasbar">
                <figure>
                    <img src="img/logo.png" alt="XTTech Logo" class="logo">
                </figure>
                <ul class="nav_list">
                    <li class="nav_item">
                        <a href="index.php"><i class="fa-solid fa-house"></i> Trang chủ</a>
                    </li>

                    <li class="nav_item">
                        <a href="gioithieu.php">Giới thiệu</a>
                    </li>

                    <li class="nav_item">
                        <a href="sanpham.php">Sản phẩm <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i></a>
                        <ul class="nav_produte">
                            <li class="nav_produte-item"><a href="sanpham.php?danhmuc_id=1">Cửa nhôm</a></li>
                            <li class="nav_produte-item"><a href="sanpham.php?danhmuc_id=2">Cửa uPVC</a></li>
                            <li class="nav_produte-item"><a href="sanpham.php?danhmuc_id=3">Cửa gỗ</a></li>
                            <li class="nav_produte-item"><a href="sanpham.php?danhmuc_id=4">Cửa cuốn</a></li>
                            <li class="nav_produte-item"><a href="sanpham.php?danhmuc_id=5">Cửa tự động</a></li>
                            <li class="nav_produte-item"><a href="sanpham.php?danhmuc_id=6">Sản phẩm kính</a></li>
                            <li class="nav_produte-item"><a href="sanpham.php?danhmuc_id=7">Hệ thống thông minh</a></li>
                        </ul>
                    </li>

                    <li class="nav_item">
                        <a href="tintuc.php">Tin tức <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i></a>
                        <ul class="nav_produte">
                            <li class="nav_produte-item"><a href="tintuc.php?cat=thitruong">Tin tức thị trường</a></li>
                            <li class="nav_produte-item"><a href="tintuc.php?cat=tuvan">Góc tư vấn</a></li>
                        </ul>
                    </li>

                    <li class="nav_item"><a href="video.php">Video</a></li>
                    <li class="nav_item"><a href="duan.php">Dự án</a></li>
                    <li class="nav_item"><a href="lienhe.php">Liên hệ báo giá</a></li>

                    <li class="nav_item search_box">
                        <form action="sanpham.php" method="get">
                            <input type="text" name="keyword" placeholder="Nhập từ khóa..." aria-label="Tìm kiếm sản phẩm">
                        </form>
                    </li>

                    <!-- Icon giỏ hàng -->
                    <li class="nav_item cart-icon">
                        <a href="giohang.php" title="Giỏ hàng">
                            <i class="fa-solid fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-badge"><?= $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- ========== MAIN CONTENT ========== -->
    <main>
        <!-- Slider -->
        <div class="preview">
                <div class="slider-container">
                    <button class="slider-button prev-button" aria-label="Previous slide">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <div class="preview_img">
                        <img src="img/cua1.jpg" alt="Cửa nhôm cao cấp XTTech" class="preview_img-item active">
                        <img src="img/cua2.jpg" alt="Cửa uPVC chất lượng cao" class="preview_img-item">
                        <img src="img/cua3.jpg" alt="Sản phẩm cửa kính hiện đại" class="preview_img-item">
                    </div>
                    <button class="slider-button next-button" aria-label="Next slide">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
        </div>

        <!-- Hotline -->
        <h1 class="Title_heading">Hotline kinh doanh: <strong>+84 012 345 678</strong></h1>

        <!-- Sản phẩm -->
        <div class="produce">
            <div class="container_main">
                <div class="produce_body">
                    <h2 class="produce_heading">SẢN PHẨM CỦA CHÚNG TÔI</h2>

                    <div class="produce_list">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <a href="thongtinsanpham.php?id=<?= $row['id']; ?>" class="produce_link">
                                    <div class="produce_item">
                                        <figure>
                                            <img src="<?= htmlspecialchars($row['hinh_anh']); ?>" 
                                                 alt="<?= htmlspecialchars($row['ten_sanpham']); ?>" 
                                                 class="produce_img"
                                                 loading="lazy">
                                        </figure>
                                        <h3 class="produce_title"><?= htmlspecialchars($row['ten_sanpham']); ?></h3>
                                        <p class="produce_price"><?= number_format($row['gia']); ?>₫</p>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="text-align: center; width: 100%; padding: 40px 0; color: #666;">Hiện chưa có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>

                    <div class="see_all">
                        <a href="sanpham.php" class="btn_see_all">Xem tất cả <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Về chúng tôi -->
        <div class="about" id="about">
            <div class="container_main">
                <div class="about_body">
                    <h2 class="produce_heading">VỀ CHÚNG TÔI</h2>
                    <p>Chúng tôi tự hào là nhà Tư vấn – Thiết kế – Thi công Nội thất nhôm, Cửa kỹ thuật, Sàn cuộn Vinyl, Cửa ngăn cháy, Thanh tay vịn cho hành lang bệnh viện, Ốp nhôm Aluminum. Chúng tôi luôn nỗ lực hết mình để làm hài lòng khách hàng, để mang đến cho khách hàng những sản phẩm tốt nhất. Sức mạnh của công ty thể hiện ở đội ngũ nhân lực với các kỹ sư giàu kinh nghiệm và công nhân lắp đặt có trình độ tay nghề cao, kết hợp với sự chính xác của máy móc trong quá trình cắt xẻ nhôm.</p>
                    
                    <div class="About_list">
                        <!-- Cột 1 -->
                        <div class="about_item">
                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-gift"></i>
                                </figure>
                                <h3 class="section_heading-lv2">CHƯƠNG TRÌNH KHUYẾN MÃI</h3>
                                <p class="section_desc">Chương trình "Đầu tư xứng tầm – Ưu đãi cực phẩm" dành cho khách hàng sử dụng sản phẩm XTTech. Nhận ngay ưu đãi hấp dẫn khi đặt hàng trong thời gian khuyến mãi.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-regular fa-hourglass-half"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Tiết Kiệm Thời Gian</h3>
                                <p class="section_desc">Khách hàng sẽ không phải chờ đợi quá lâu. Chúng tôi sẽ ngay lập tức đo đạc và tiến hành thi công ngay lập tức, không làm mất thời gian của khách hàng.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-handshake"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Đảm Bảo Uy Tín</h3>
                                <p class="section_desc">Với phương châm uy tín, chất lượng và đặt lợi ích của khách hàng lên trên hết, quý khách có thể an tâm về chất lượng dịch vụ của Công ty chúng tôi.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-headset"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Hỗ Trợ 24/7</h3>
                                <p class="section_desc">Luôn sẵn sàng hỗ trợ quý khách hàng tại mọi nơi mọi lúc, giúp khách hàng chủ động được thời gian quý báu.</p>
                            </div>
                        </div>

                        <!-- Cột 2 -->
                        <div class="about_item">
                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-ranking-star"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Chất Lượng Sản Phẩm</h3>
                                <p class="section_desc">Tiên phong đưa cửa hiện đại uPVC tiêu chuẩn Châu Âu vào thị trường trong nước, làm nên cuộc cách mạng về cửa và mở ra "kỷ nguyên mới" cho ngôi nhà của bạn.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-piggy-bank"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Tiết Kiệm Chi Phí Hợp Lý</h3>
                                <p class="section_desc">Cam kết là một trong những đơn vị cung cấp và thi công với giá cả cạnh tranh nhất. Chất lượng cao - Giá thành tốt - Dịch vụ chuyên nghiệp.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-person"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Nhân Viên Chuyên Nghiệp</h3>
                                <p class="section_desc">Đội ngũ kỹ sư có độ chuyên môn cao và áp dụng máy móc tiên tiến nhất hiện nay vào thiết kế, thi công công trình.</p>
                            </div>
                        </div>
                    </div>      
                </div>
            </div>
        </div>
    </main>

    <!-- ========== FOOTER ========== -->
    <footer>
        <div class="container_main">
            <div class="footer__top">
                <div class="footer__column">
                    <div class="company">
                        <img src="img/logo.png" alt="XTTech Logo" class="company--logo">
                        <h3 style="font-size: 24px; font-weight: 800; color: white;">XTTech</h3>
                    </div>
                    <p class="footer__desc">
                        Chúng tôi tự hào mang đến những sản phẩm cửa chất lượng cao, thiết kế hiện đại và dịch vụ chuyên nghiệp cho mọi công trình.
                    </p>
                </div>
                
                <div class="footer__column">
                    <h3 class="footer__heading">Hỗ trợ</h3>
                    <ul class="footer__list">
                        <li class="footer__item">
                            <a href="trogiup.php" class="footer__link">Trung tâm trợ giúp</a>
                        </li>
                        <li class="footer__item">
                            <a href="taikhoan.php" class="footer__link">Thông tin tài khoản</a>
                        </li>
                        <li class="footer__item">
                            <a href="#about" class="footer__link">Về chúng tôi</a>
                        </li>
                        <li class="footer__item">
                            <a href="lienhe.php" class="footer__link">Liên hệ</a>
                        </li>
                    </ul>
                    <h3 class="footer__heading">Chính sách</h3>
                    <ul class="footer__list">
                        <li class="footer__item">
                            <a href="baohanh.php" class="footer__link">Bảo hành</a>
                        </li>
                        <li class="footer__item">
                            <a href="doitra.php" class="footer__link">Đổi trả</a>
                        </li>
                    </ul>
                </div>

                <div class="footer__column">
                    <h3 class="footer__heading">Sản phẩm</h3>
                    <ul class="footer__list">
                        <li class="footer__item">
                            <a href="sanpham.php?danhmuc_id=1" class="footer__link">Cửa nhôm</a>
                        </li>
                        <li class="footer__item">
                            <a href="sanpham.php?danhmuc_id=2" class="footer__link">Cửa uPVC</a>
                        </li>
                        <li class="footer__item">
                            <a href="sanpham.php?danhmuc_id=3" class="footer__link">Cửa gỗ</a>
                        </li>
                        <li class="footer__item">
                            <a href="sanpham.php?danhmuc_id=4" class="footer__link">Cửa cuốn</a>
                        </li>
                    </ul>
                    <h3 class="footer__heading">Dự án</h3>
                    <ul class="footer__list">
                        <li class="footer__item">
                            <a href="duan.php" class="footer__link">Dự án tiêu biểu</a>
                        </li>
                    </ul>
                </div>

                <div class="footer__column">
                    <h3 class="footer__heading">Kết nối với chúng tôi</h3>
                    <div class="footer__social">
                        <a href="https://facebook.com" class="footer__social-btn" target="_blank" rel="noopener" aria-label="Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com" class="footer__social-btn" target="_blank" rel="noopener" aria-label="Twitter">
                            <i class="fa-brands fa-twitter"></i>
                        </a>
                        <a href="https://linkedin.com" class="footer__social-btn" target="_blank" rel="noopener" aria-label="LinkedIn">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                        <a href="https://instagram.com" class="footer__social-btn" target="_blank" rel="noopener" aria-label="Instagram">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                    </div>
                    <h3 class="footer__heading">Đăng ký nhận tin</h3>
                    <p class="footer__desc">
                        Đăng ký để nhận thông tin cập nhật mới nhất về sản phẩm và chương trình khuyến mãi
                    </p>
                    <form class="footer__form" action="#!">
                        <input type="email" class="footer__form-input" placeholder="Nhập email của bạn..." required aria-label="Email">
                        <button type="submit" class="footer__form-submit">Gửi</button>
                    </form>
                </div>
            </div>
            
            <div class="footer__copy">
                <p class="footer__copy-desc">© 2025 - XTTech. Bản quyền thuộc về Công ty. Thiết kế bởi Nguyễn Quang Linh</p>
            </div>
        </div>
    </footer>

    <!-- ========== FLOATING SOCIAL ICONS ========== -->
    <div class="list_icon">
        <a href="https://www.facebook.com/nguyen.linh.757040" class="icon_link" target="_blank" rel="noopener" aria-label="Facebook">
            <img src="img/fb.svg" class="icon_img" alt="Facebook">
        </a>
        <a href="https://zalo.me" class="icon_link" target="_blank" rel="noopener" aria-label="Zalo">
            <img src="img/zalo.png" class="icon_img" alt="Zalo">
        </a>
        <a href="https://www.instagram.com" class="icon_link" target="_blank" rel="noopener" aria-label="Instagram">
            <img src="img/insta.png" class="icon_img" alt="Instagram">
        </a>
    </div>

    <!-- ========== JAVASCRIPT ========== -->
    <script src="temp.js"></script>
</body>
</html>

<?php
$conn->close();
?>
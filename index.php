<?php
// Khởi tạo session
require_once 'session_config.php';

// Kết nối database
require_once 'config.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    exit('Kết nối thất bại: '.$conn->connect_error);
}

// Lấy tối đa 8 khóa học nổi bật (đã publish)
$sql = "SELECT kh.*, dm.ten as ten_danh_muc, nd.ho_ten as ten_giang_vien 
        FROM khoa_hoc kh 
        LEFT JOIN danh_muc dm ON kh.id_danh_muc = dm.id
        LEFT JOIN nguoi_dung nd ON kh.id_giang_vien = nd.id
        WHERE kh.trang_thai_khoa_hoc = 'publish'
        ORDER BY kh.ngay_tao DESC
        LIMIT 8";
$result = $conn->query($sql);

// Đếm số học viên đã đăng ký
$students_result = $conn->query('SELECT COUNT(DISTINCT id_nguoi_dung) as total FROM dang_ky');
$total_students = $students_result ? $students_result->fetch_assoc()['total'] : 0;

$courses_result = $conn->query("SELECT COUNT(*) as total FROM khoa_hoc WHERE trang_thai_khoa_hoc = 'publish'");
$total_courses = $courses_result ? $courses_result->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>CODE4Fun - Học lập trình thật vui | Khóa học lập trình online chất lượng cao</title>
    <meta name="description" content="Nền tảng học lập trình online hàng đầu với các khóa học chất lượng cao, giảng viên giàu kinh nghiệm">

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
                    <img src="img/logo.png" alt="CODE4Fun Logo" class="logo">
                </figure>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <ul class="nav_list">
                    <li class="nav_item">
                        <a href="index.php"><i class="fa-solid fa-house"></i> Trang chủ</a>
                    </li>

                    <li class="nav_item">
                        <a href="gioithieu.php">Giới thiệu</a>
                    </li>

                    <li class="nav_item">
                        <a href="khoahoc.php">Khóa học <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i></a>
                        <ul class="nav_produte">
                            <li class="nav_produte-item"><a href="khoahoc.php?danh_muc=1">Lập trình Web</a></li>
                            <li class="nav_produte-item"><a href="khoahoc.php?danh_muc=2">Lập trình Mobile</a></li>
                            <li class="nav_produte-item"><a href="khoahoc.php?danh_muc=3">Lập trình Backend</a></li>
                            <li class="nav_produte-item"><a href="khoahoc.php?danh_muc=4">Data Science</a></li>
                            <li class="nav_produte-item"><a href="khoahoc.php?danh_muc=5">DevOps</a></li>
                        </ul>
                    </li>

                    <li class="nav_item">
                        <a href="blog.php">Blog <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i></a>
                        <ul class="nav_produte">
                            <li class="nav_produte-item"><a href="blog.php?cat=tips">Tips & Tricks</a></li>
                            <li class="nav_produte-item"><a href="blog.php?cat=career">Lộ trình sự nghiệp</a></li>
                        </ul>
                    </li>

                    <li class="nav_item"><a href="lienhe.php">Liên hệ</a></li>

                    <li class="nav_item search_box">
                        <button type="button" class="search_toggle" aria-label="Mở tìm kiếm">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        <form action="khoahoc.php" method="get" class="search_form">
                            <input type="text" name="keyword" placeholder="Tìm khóa học..." aria-label="Tìm kiếm khóa học">
                        </form>
                    </li>

                    <!-- User Icon -->
                    <?php if (isset($_SESSION['user_id'])) { ?>
                    <li class="nav_item">
                        <a href="khoahoc_cua_toi.php" title="Khóa học của tôi">
                            <i class="fa-solid fa-user"></i>
                        </a>
                    </li>
                    <li class="nav_item">
                        <a href="logout.php" title="Đăng xuất">
                            <i class="fa-solid fa-sign-out-alt"></i>
                        </a>
                    </li>
                    <?php } else { ?>
                    <li class="nav_item">
                        <a href="login.php" title="Đăng nhập">
                            <i class="fa-solid fa-sign-in-alt"></i> Đăng nhập
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- ========== MAIN CONTENT ========== -->
    <main>
        <!-- Hero Slider -->
        <div class="preview">
            <div class="slider-container">
                <button class="slider-button prev-button" aria-label="Previous slide">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="preview_img">
                    <img src="img/1759758142_guy-lesson 1.png" alt="Học lập trình cùng CODE4Fun" class="preview_img-item active">
                    <img src="img/1759758154_study2.jpg" alt="Khóa học chất lượng cao" class="preview_img-item">
                    <img src="img/1759758164_study3.jpg" alt="Giảng viên giàu kinh nghiệm" class="preview_img-item">
                </div>
                <button class="slider-button next-button" aria-label="Next slide">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <!-- Hero Content Overlay -->
                <div class="hero-content">
                    <h1 class="hero-title">Học Lập Trình Thật Vui Cùng CODE4Fun</h1>
                    <p class="hero-subtitle">Khóa học chất lượng cao - Giảng viên giàu kinh nghiệm - Học phí hợp lý</p>
                    <div class="hero-buttons">
                        <a href="khoahoc.php" class="hero-btn primary">Khám phá khóa học</a>
                        <a href="lienhe.php" class="hero-btn secondary">Liên hệ tư vấn</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container_main">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <h3 class="stat-number"><?php echo $total_students; ?>+</h3>
                        <p class="stat-label">Học viên</p>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <h3 class="stat-number"><?php echo $total_courses; ?>+</h3>
                        <p class="stat-label">Khóa học</p>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fa-solid fa-chalkboard-teacher"></i>
                        </div>
                        <h3 class="stat-number">50+</h3>
                        <p class="stat-label">Giảng viên</p>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <h3 class="stat-number">4.8/5</h3>
                        <p class="stat-label">Đánh giá trung bình</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hotline -->
        <div class="hotline-banner">
            <div class="container_main">
                <div class="hotline-content">
                    <i class="fa-solid fa-phone-volume"></i>
                    <span>Hotline tư vấn:</span>
                    <strong>+84 012 345 678</strong>
                    <a href="tel:+84012345678" class="hotline-btn">Gọi ngay</a>
                </div>
            </div>
        </div>

        <!-- Khóa học nổi bật -->
        <div class="produce">
            <div class="container_main">
                <div class="produce_body">
                    <h2 class="produce_heading">KHÓA HỌC NỔI BẬT</h2>
                    <p class="section-subtitle">Khám phá các khóa học lập trình chất lượng cao với giảng viên giàu kinh nghiệm</p>

                    <div class="produce_list">
                        <?php if ($result && $result->num_rows > 0) { ?>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <a href="chitietkhoahoc.php?id=<?php echo $row['id']; ?>" class="produce_link">
                                    <div class="produce_item">
                                        <div class="product-badge">Mới</div>
                                        <figure>
                                            <img src="<?php echo htmlspecialchars($row['hinh_anh'] ?? 'img/default-course.svg'); ?>"
                                                 alt="<?php echo htmlspecialchars($row['ten']); ?>"
                                                 class="produce_img"
                                                 loading="lazy">
                                        </figure>
                                        <h3 class="produce_title"><?php echo htmlspecialchars($row['ten']); ?></h3>
                                        <p class="course-instructor">
                                            <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($row['ten_giang_vien'] ?? 'Giảng viên'); ?>
                                        </p>
                                        <p class="produce_price"><?php echo number_format($row['gia']); ?>₫</p>
                                        <div class="product-actions">
                                            <button class="quick-view-btn">
                                                <i class="fa-solid fa-eye"></i> Xem chi tiết
                                            </button>
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>
                        <?php } else { ?>
                            <p style="text-align: center; width: 100%; padding: 40px 0; color: #666;">Hiện chưa có khóa học nào.</p>
                        <?php } ?>
                    </div>

                    <div class="see_all">
                        <a href="khoahoc.php" class="btn_see_all">Xem tất cả khóa học <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Why Choose Us -->
        <section class="why-choose">
            <div class="container_main">
                <h2 class="produce_heading">TẠI SAO CHỌN CODE4FUN?</h2>
                <p class="section-subtitle">Cam kết mang đến trải nghiệm học tập tốt nhất cho học viên</p>

                <div class="why-grid">
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fa-solid fa-laptop-code"></i>
                        </div>
                        <h3>Học mọi lúc mọi nơi</h3>
                        <p>Truy cập khóa học 24/7, học theo tiến độ của bạn</p>
                    </div>
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fa-solid fa-certificate"></i>
                        </div>
                        <h3>Chứng chỉ hoàn thành</h3>
                        <p>Nhận chứng chỉ sau khi hoàn thành khóa học</p>
                    </div>
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h3>Cộng đồng hỗ trợ</h3>
                        <p>Tham gia cộng đồng học viên sôi động</p>
                    </div>
                    <div class="why-item">
                        <div class="why-icon">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <h3>Giá cả hợp lý</h3>
                        <p>Học phí phải chăng, nhiều ưu đãi hấp dẫn</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Về chúng tôi -->
        <div class="about" id="about">
            <div class="container_main">
                <div class="about_body">
                    <h2 class="produce_heading">VỀ CODE4FUN</h2>
                    <p>CODE4Fun là nền tảng học lập trình online hàng đầu Việt Nam, cung cấp các khóa học chất lượng cao với giảng viên giàu kinh nghiệm. Chúng tôi cam kết mang đến cho học viên những kiến thức thực tế nhất, giúp bạn tự tin bước vào thế giới lập trình và phát triển sự nghiệp IT.</p>

                    <div class="About_list">
                        <!-- Cột 1 -->
                        <div class="about_item">
                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-book-open"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Nội dung cập nhật</h3>
                                <p class="section_desc">Khóa học được cập nhật liên tục theo xu hướng công nghệ mới nhất, đảm bảo kiến thức luôn thực tế và hữu ích.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-chalkboard-teacher"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Giảng viên chuyên nghiệp</h3>
                                <p class="section_desc">Đội ngũ giảng viên giàu kinh nghiệm thực tế, nhiệt tình hướng dẫn và hỗ trợ học viên.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-project-diagram"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Dự án thực tế</h3>
                                <p class="section_desc">Học qua các dự án thực tế, giúp bạn áp dụng kiến thức vào công việc ngay lập tức.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-headset"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Hỗ trợ 24/7</h3>
                                <p class="section_desc">Đội ngũ hỗ trợ luôn sẵn sàng giải đáp thắc mắc của bạn mọi lúc mọi nơi.</p>
                            </div>
                        </div>

                        <!-- Cột 2 -->
                        <div class="about_item">
                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-code"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Thực hành nhiều</h3>
                                <p class="section_desc">Tập trung vào thực hành, coding thực tế để nắm vững kiến thức và kỹ năng lập trình.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-trophy"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Chứng chỉ uy tín</h3>
                                <p class="section_desc">Chứng chỉ hoàn thành khóa học được công nhận, giúp bạn nâng cao giá trị bản thân.</p>
                            </div>

                            <div class="about_col">
                                <figure>
                                    <i class="fa-solid fa-users-gear"></i>
                                </figure>
                                <h3 class="section_heading-lv2">Cộng đồng sôi động</h3>
                                <p class="section_desc">Tham gia cộng đồng học viên đông đảo, cùng nhau học hỏi và phát triển.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <div class="container_main">
                <h2 class="produce_heading">HỌC VIÊN NÓI GÌ VỀ CHÚNG TÔI</h2>
                <p class="section-subtitle">Những đánh giá chân thực từ học viên</p>

                <div class="testimonial-grid">
                    <div class="testimonial-item">
                        <div class="testimonial-stars">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Khóa học rất chất lượng, giảng viên nhiệt tình. Sau khóa học tôi đã tự tin xin được việc làm lập trình viên."</p>
                        <div class="testimonial-author">
                            <strong>Nguyễn Văn A</strong>
                            <span>Học viên khóa Web Development</span>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-stars">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Nội dung cập nhật, bài tập thực tế. Tôi rất hài lòng với CODE4Fun và sẽ tiếp tục học các khóa khác."</p>
                        <div class="testimonial-author">
                            <strong>Trần Thị B</strong>
                            <span>Học viên khóa Mobile Development</span>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-stars">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="testimonial-text">"Giá cả hợp lý, chất lượng tuyệt vời. Đội ngũ hỗ trợ rất nhiệt tình, giải đáp mọi thắc mắc của tôi."</p>
                        <div class="testimonial-author">
                            <strong>Lê Văn C</strong>
                            <span>Học viên khóa Backend Development</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container_main">
                <div class="cta-content">
                    <h2>Bạn Muốn Bắt Đầu Học Lập Trình?</h2>
                    <p>Đội ngũ tư vấn của chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
                    <div class="cta-buttons">
                        <a href="khoahoc.php" class="cta-btn primary">Khám phá khóa học</a>
                        <a href="tel:+84012345678" class="cta-btn secondary">
                            <i class="fa-solid fa-phone"></i> +84 012 345 678
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- ========== FOOTER ========== -->
    <footer>
        <div class="container_main">
            <div class="footer__top">
                <div class="footer__column">
                    <div class="company">
                        <img src="img/logo.png" alt="CODE4Fun Logo" class="company--logo">
                        <h3 style="font-size: 24px; font-weight: 800; color: white;">CODE4Fun</h3>
                    </div>
                    <p class="footer__desc">
                        Nền tảng học lập trình online hàng đầu Việt Nam, cung cấp các khóa học chất lượng cao với giảng viên giàu kinh nghiệm.
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
                            <a href="chinhsach.php" class="footer__link">Chính sách hoàn tiền</a>
                        </li>
                        <li class="footer__item">
                            <a href="dieukho an.php" class="footer__link">Điều khoản sử dụng</a>
                        </li>
                    </ul>
                </div>

                <div class="footer__column">
                    <h3 class="footer__heading">Khóa học</h3>
                    <ul class="footer__list">
                        <li class="footer__item">
                            <a href="khoahoc.php?danh_muc=1" class="footer__link">Lập trình Web</a>
                        </li>
                        <li class="footer__item">
                            <a href="khoahoc.php?danh_muc=2" class="footer__link">Lập trình Mobile</a>
                        </li>
                        <li class="footer__item">
                            <a href="khoahoc.php?danh_muc=3" class="footer__link">Lập trình Backend</a>
                        </li>
                        <li class="footer__item">
                            <a href="khoahoc.php?danh_muc=4" class="footer__link">Data Science</a>
                        </li>
                    </ul>
                    <h3 class="footer__heading">Tài nguyên</h3>
                    <ul class="footer__list">
                        <li class="footer__item">
                            <a href="blog.php" class="footer__link">Blog</a>
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
                        Đăng ký để nhận thông tin cập nhật mới nhất về khóa học và chương trình khuyến mãi
                    </p>
                    <form class="footer__form" action="#!">
                        <input type="email" class="footer__form-input" placeholder="Nhập email của bạn..." required aria-label="Email">
                        <button type="submit" class="footer__form-submit">Gửi</button>
                    </form>
                </div>
            </div>

            <div class="footer__copy">
                <p class="footer__copy-desc">© 2025 - CODE4Fun. Bản quyền thuộc về Công ty. Thiết kế bởi Nguyễn Quang Linh</p>
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

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 07, 2025 lúc 04:53 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `xttech`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `ho_ten` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `binhluan`
--

CREATE TABLE `binhluan` (
  `id` int(11) NOT NULL,
  `sanpham_id` int(11) NOT NULL,
  `ten_nguoi_dung` varchar(100) NOT NULL,
  `noi_dung` text NOT NULL,
  `ngay_binh_luan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `binhluan`
--

INSERT INTO `binhluan` (`id`, `sanpham_id`, `ten_nguoi_dung`, `noi_dung`, `ngay_binh_luan`) VALUES
(1, 3, 'Trần Hoàng Long', 'Cửa xịn xò con bò quá', '2025-10-07 09:18:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitiet_donhang`
--

CREATE TABLE `chitiet_donhang` (
  `id` int(11) NOT NULL,
  `donhang_id` int(11) DEFAULT NULL,
  `sanpham_id` int(11) DEFAULT NULL,
  `so_luong` int(11) DEFAULT NULL,
  `don_gia` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitiet_donhang`
--

INSERT INTO `chitiet_donhang` (`id`, `donhang_id`, `sanpham_id`, `so_luong`, `don_gia`) VALUES
(1, 1, 8, 13, 8900000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc`
--

CREATE TABLE `danhmuc` (
  `id` int(11) NOT NULL,
  `ten_danhmuc` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danhmuc`
--

INSERT INTO `danhmuc` (`id`, `ten_danhmuc`) VALUES
(1, 'Cửa nhôm'),
(2, 'Cửa uPVC'),
(3, 'Cửa gỗ'),
(4, 'Cửa cuốn'),
(5, 'Cửa tự động'),
(6, 'Sản phẩm kính'),
(7, 'Cửa hệ thống thông minh');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `id` int(11) NOT NULL,
  `khachhang_id` int(11) DEFAULT NULL,
  `ngay_dat` date DEFAULT curdate(),
  `tong_tien` decimal(15,2) DEFAULT NULL,
  `trang_thai` enum('Chờ xử lý','Đang giao','Hoàn tất','Hủy') DEFAULT 'Chờ xử lý'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`id`, `khachhang_id`, `ngay_dat`, `tong_tien`, `trang_thai`) VALUES
(1, 1, '2025-10-07', 115700000.00, 'Chờ xử lý');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `sanpham_id` int(11) NOT NULL,
  `so_luong` int(11) DEFAULT 1,
  `ngay_them` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `id` int(11) NOT NULL,
  `ten_khachhang` varchar(100) NOT NULL,
  `sdt` varchar(15) DEFAULT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`id`, `ten_khachhang`, `sdt`, `dia_chi`, `email`) VALUES
(1, 'Nguyễn Quang Linh', '0762457422', '9/2213231', 'Linhbarao@gmail.com');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `id` int(11) NOT NULL,
  `ten_sanpham` varchar(150) NOT NULL,
  `danhmuc_id` int(11) DEFAULT NULL,
  `gia` decimal(15,2) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `ton_kho` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`id`, `ten_sanpham`, `danhmuc_id`, `gia`, `mo_ta`, `hinh_anh`, `ton_kho`) VALUES
(2, 'Cửa nhôm Việt Pháp 2 cánh mở trượt', 1, 6200000.00, 'Cửa nhôm Việt Pháp thiết kế đơn giản, bền đẹp, phù hợp nhà phố.', 'img/1759758328_anh3.jpg', 20),
(3, 'Cửa uPVC 2 cánh mở quay', 2, 5600000.00, 'Cửa nhựa lõi thép uPVC, chống ồn, chống nước, độ bền cao.', 'img/1759758315_anh2.jpg', 15),
(4, 'Cửa uPVC trượt lùa 4 cánh', 2, 7200000.00, 'Thiết kế hiện đại, thích hợp cho văn phòng và nhà ở.', 'img/1759758306_anh1.jpg', 10),
(5, 'Cửa gỗ công nghiệp MDF phủ melamine', 3, 4500000.00, 'Cửa gỗ công nghiệp, màu vân gỗ tự nhiên, chống cong vênh.', 'img/1759758174_windows-Pmu6-i4iyNE-unsplash.jpg', 8),
(6, 'Cửa gỗ tự nhiên lim Nam Phi', 3, 9500000.00, 'Cửa gỗ lim tự nhiên, sang trọng, chắc chắn, bền bỉ theo thời gian.', 'img/1759758164_study3.jpg', 5),
(7, 'Cửa cuốn Austdoor khe thoáng', 4, 10500000.00, 'Cửa cuốn khe thoáng công nghệ Đức, vận hành êm, bảo mật cao.', 'img/1759758154_study2.jpg', 7),
(8, 'Cửa cuốn tấm liền Mitadoor', 4, 8900000.00, 'Cửa cuốn tấm liền sơn tĩnh điện, an toàn và bền bỉ.', 'img/1759758142_guy-lesson 1.png', 9),
(9, 'Cửa trượt tự động Panasonic', 5, 15500000.00, 'Cửa tự động cảm biến chuyển động, thích hợp cho trung tâm thương mại.', 'img/1759758133_anh2.jpg', 4),
(10, 'Cửa mở tự động Nabco', 5, 17800000.00, 'Cửa tự động cao cấp, vận hành êm ái, tiết kiệm điện năng.', 'img/1759758125_anh1.jpg', 3),
(11, 'Vách kính cường lực 12mm', 6, 780000.00, 'Vách kính cường lực an toàn, trong suốt, dễ lau chùi.', 'img/1759758073_download (1).jpg', 30),
(12, 'Lan can kính tay vịn inox', 6, 2400000.00, 'Lan can kính kết hợp inox, hiện đại và an toàn.', 'img/1759758051_download.jpg', 12),
(13, 'Cửa nhôm thông minh điều khiển từ xa', 7, 19800000.00, 'Cửa nhôm tích hợp mô-tơ và cảm biến, điều khiển qua smartphone.', 'img/1759757783_Mèo Đẹp Ngầu.jpg', 5),
(14, 'Cửa cuốn thông minh tích hợp camera', 7, 22500000.00, 'Cửa cuốn tự động tích hợp camera giám sát và cảm biến an toàn.', 'img/1759757733_Rectangle 4449.png', 10);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `user` varchar(255) NOT NULL,
  `mk` varchar(255) NOT NULL,
  `role` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `user`
--

INSERT INTO `user` (`id`, `id_admin`, `user`, `mk`, `role`, `email`) VALUES
(1, 1, 'linh123', '123', 1, ''),
(2, 0, 'admin01', '$2y$10$ITpd2uA28HBBAG.xWAuVNulZqAkJzVAMpvGsktytYmM0ed6yIaiGi', 0, 'linhbarao@gmail.com');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `binhluan`
--
ALTER TABLE `binhluan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sanpham_id` (`sanpham_id`);

--
-- Chỉ mục cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donhang_id` (`donhang_id`),
  ADD KEY `sanpham_id` (`sanpham_id`);

--
-- Chỉ mục cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `khachhang_id` (`khachhang_id`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sanpham_id` (`sanpham_id`),
  ADD KEY `idx_session` (`session_id`);

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `danhmuc_id` (`danhmuc_id`);

--
-- Chỉ mục cho bảng `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `binhluan`
--
ALTER TABLE `binhluan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `giohang`
--
ALTER TABLE `giohang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `binhluan`
--
ALTER TABLE `binhluan`
  ADD CONSTRAINT `binhluan_ibfk_1` FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chitiet_donhang`
--
ALTER TABLE `chitiet_donhang`
  ADD CONSTRAINT `chitiet_donhang_ibfk_1` FOREIGN KEY (`donhang_id`) REFERENCES `donhang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitiet_donhang_ibfk_2` FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`khachhang_id`) REFERENCES `khachhang` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `giohang_ibfk_1` FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`danhmuc_id`) REFERENCES `danhmuc` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

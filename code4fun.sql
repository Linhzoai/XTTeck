-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 12, 2025 lúc 10:21 AM
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
-- Cơ sở dữ liệu: `code4fun`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bai_hoc`
--

CREATE TABLE `bai_hoc` (
  `id` int(11) NOT NULL,
  `ten` varchar(200) NOT NULL,
  `noi_dung` text DEFAULT NULL,
  `thu_tu` int(11) DEFAULT 1,
  `id_khoa_hoc` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bai_hoc`
--

INSERT INTO `bai_hoc` (`id`, `ten`, `noi_dung`, `thu_tu`, `id_khoa_hoc`) VALUES
(1, 'Giới thiệu HTML', 'Nội dung về cấu trúc HTML.', 1, 1),
(2, 'Thẻ cơ bản', 'Các thẻ HTML phổ biến.', 2, 1),
(3, 'CSS cơ bản', 'Học cách định dạng trang web.', 3, 1),
(4, 'Biến trong JS', 'Giới thiệu biến, kiểu dữ liệu.', 1, 2),
(5, 'Hàm trong JS', 'Cách định nghĩa và sử dụng hàm.', 2, 2),
(6, 'DOM Manipulation', 'Tương tác với phần tử HTML.', 3, 2),
(7, 'Component trong React', 'Cách tạo và quản lý component.', 1, 3),
(8, 'State & Props', 'Truyền dữ liệu trong React.', 2, 3),
(9, 'Hook useEffect', 'Tìm hiểu về side effect trong React.', 3, 3),
(10, 'Giới thiệu Python', 'Làm quen với cú pháp Python.', 1, 4),
(11, 'Cấu trúc điều kiện', 'if, else trong Python.', 2, 4),
(12, 'Hàm trong Python', 'Viết hàm hiệu quả.', 3, 4),
(13, 'OOP trong Python', 'Lập trình hướng đối tượng.', 1, 5),
(14, 'Class nâng cao', 'Kế thừa và đa hình.', 2, 5),
(15, 'Tổng quan ML', 'Giới thiệu Machine Learning.', 1, 6),
(16, 'Linear Regression', 'Thuật toán cơ bản ML.', 2, 6),
(17, 'Xử lý dữ liệu', 'Tiền xử lý dữ liệu với Pandas.', 3, 6),
(18, 'API cơ bản', 'Giới thiệu Node.js và Express.', 1, 7),
(19, 'CRUD API', 'Xây dựng API CRUD hoàn chỉnh.', 2, 7),
(20, 'Middleware', 'Xử lý request nâng cao.', 3, 7);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chung_chi`
--

CREATE TABLE `chung_chi` (
  `id` int(11) NOT NULL,
  `id_dang_ky` int(11) NOT NULL,
  `ma_chung_chi` varchar(50) NOT NULL,
  `ngay_cap` date NOT NULL,
  `duong_dan_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chung_chi`
--

INSERT INTO `chung_chi` (`id`, `id_dang_ky`, `ma_chung_chi`, `ngay_cap`, `duong_dan_pdf`) VALUES
(1, 1, 'CC-HTML-001', '2025-03-01', '/certs/CC-HTML-001.pdf'),
(2, 3, 'CC-JS-002', '2025-05-01', '/certs/CC-JS-002.pdf'),
(3, 8, 'CC-PY-003', '2025-06-15', '/certs/CC-PY-003.pdf');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dang_ky`
--

CREATE TABLE `dang_ky` (
  `id` int(11) NOT NULL,
  `id_nguoi_dung` int(11) NOT NULL,
  `id_khoa_hoc` int(11) NOT NULL,
  `ngay_dang_ky` timestamp NOT NULL DEFAULT current_timestamp(),
  `trang_thai` enum('dang_ky','hoan_thanh') DEFAULT 'dang_ky',
  `id_ma_giam_gia` int(11) DEFAULT NULL,
  `giam_gia_ap_dung` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `dang_ky`
--

INSERT INTO `dang_ky` (`id`, `id_nguoi_dung`, `id_khoa_hoc`, `ngay_dang_ky`, `trang_thai`, `id_ma_giam_gia`, `giam_gia_ap_dung`) VALUES
(1, 4, 1, '2025-11-12 08:13:34', 'hoan_thanh', 1, 10.00),
(2, 4, 3, '2025-11-12 08:13:34', 'dang_ky', NULL, 0.00),
(3, 5, 2, '2025-11-12 08:13:34', 'hoan_thanh', 1, 10.00),
(4, 5, 4, '2025-11-12 08:13:34', 'dang_ky', 2, 50000.00),
(5, 6, 1, '2025-11-12 08:13:34', 'hoan_thanh', NULL, 0.00),
(6, 6, 3, '2025-11-12 08:13:34', 'dang_ky', NULL, 0.00),
(7, 7, 7, '2025-11-12 08:13:34', 'hoan_thanh', 3, 20.00),
(8, 8, 4, '2025-11-12 08:13:34', 'hoan_thanh', NULL, 0.00),
(9, 9, 6, '2025-11-12 08:13:34', 'dang_ky', NULL, 0.00),
(10, 10, 5, '2025-11-12 08:13:34', 'dang_ky', NULL, 0.00),
(11, 5, 3, '2025-11-12 08:26:01', 'dang_ky', NULL, 0.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_gia`
--

CREATE TABLE `danh_gia` (
  `id` int(11) NOT NULL,
  `id_nguoi_dung` int(11) NOT NULL,
  `id_khoa_hoc` int(11) NOT NULL,
  `diem` int(11) DEFAULT NULL CHECK (`diem` between 1 and 5),
  `binh_luan` text DEFAULT NULL,
  `ngay_danh_gia` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_gia`
--

INSERT INTO `danh_gia` (`id`, `id_nguoi_dung`, `id_khoa_hoc`, `diem`, `binh_luan`, `ngay_danh_gia`) VALUES
(1, 4, 1, 5, 'Khóa học dễ hiểu và thực tế!', '2025-11-12 08:13:34'),
(2, 5, 2, 4, 'Giảng viên dạy hay, dễ hiểu.', '2025-11-12 08:13:34'),
(3, 6, 3, 5, 'ReactJS rất chi tiết.', '2025-11-12 08:13:34'),
(4, 8, 4, 4, 'Khóa học Python hữu ích.', '2025-11-12 08:13:34'),
(5, 10, 5, 5, 'OOP Python rất hay.', '2025-11-12 08:13:34'),
(6, 7, 7, 5, 'Node.js dễ học hơn mình nghĩ.', '2025-11-12 08:13:34'),
(7, 9, 6, 5, 'ML cơ bản rất rõ ràng.', '2025-11-12 08:13:34'),
(8, 5, 1, 4, 'Cần thêm ví dụ thực hành.', '2025-11-12 08:13:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `id` int(11) NOT NULL,
  `ten` varchar(100) NOT NULL,
  `mo_ta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`id`, `ten`, `mo_ta`) VALUES
(1, 'Lập trình Web', 'Các khóa học HTML, CSS, JavaScript, React,...'),
(2, 'Lập trình Python', 'Khóa học Python cơ bản và nâng cao'),
(3, 'Lập trình Mobile', 'Android, iOS, React Native'),
(4, 'Lập trình Game', 'Unity, Godot, Unreal Engine cơ bản'),
(5, 'Khoa học dữ liệu', 'Data Science, Machine Learning'),
(6, 'AI & Deep Learning', 'Trí tuệ nhân tạo, học sâu với TensorFlow'),
(7, 'Cơ sở lập trình', 'Ngôn ngữ C, C++, Java cho người mới'),
(8, 'Lập trình Backend', 'Node.js, PHP, Laravel,...'),
(9, 'Frontend nâng cao', 'ReactJS, NextJS, VueJS'),
(10, 'Công cụ DevOps', 'Docker, CI/CD, Cloud,...');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `file_dinh_kem`
--

CREATE TABLE `file_dinh_kem` (
  `id` int(11) NOT NULL,
  `ten_file` varchar(200) NOT NULL,
  `duong_dan` varchar(255) NOT NULL,
  `id_bai_hoc` int(11) NOT NULL,
  `ngay_tai_len` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `file_dinh_kem`
--

INSERT INTO `file_dinh_kem` (`id`, `ten_file`, `duong_dan`, `id_bai_hoc`, `ngay_tai_len`) VALUES
(1, 'html_guide.pdf', '/files/html_guide.pdf', 1, '2025-11-12 08:13:34'),
(2, 'css_cheatsheet.pdf', '/files/css_cheatsheet.pdf', 3, '2025-11-12 08:13:34'),
(3, 'js_examples.zip', '/files/js_examples.zip', 4, '2025-11-12 08:13:34'),
(4, 'react_starter.zip', '/files/react_starter.zip', 7, '2025-11-12 08:13:34'),
(5, 'python_intro.pdf', '/files/python_intro.pdf', 10, '2025-11-12 08:13:34'),
(6, 'oop_python.pdf', '/files/oop_python.pdf', 13, '2025-11-12 08:13:34'),
(7, 'ml_dataset.csv', '/files/ml_dataset.csv', 16, '2025-11-12 08:13:34'),
(8, 'node_api_example.zip', '/files/node_api_example.zip', 18, '2025-11-12 08:13:34'),
(9, 'git_cheatsheet.pdf', '/files/git_cheatsheet.pdf', 10, '2025-11-12 08:13:34'),
(10, 'rn_template.zip', '/files/rn_template.zip', 8, '2025-11-12 08:13:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khoa_hoc`
--

CREATE TABLE `khoa_hoc` (
  `id` int(11) NOT NULL,
  `ten` varchar(200) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `gia` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_danh_muc` int(11) DEFAULT NULL,
  `id_giang_vien` int(11) NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `hinh_anh` varchar(255) DEFAULT NULL,
  `video_demo` varchar(255) DEFAULT NULL,
  `trang_thai_khoa_hoc` enum('draf','publish','archive') DEFAULT 'draf'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khoa_hoc`
--

INSERT INTO `khoa_hoc` (`id`, `ten`, `mo_ta`, `gia`, `id_danh_muc`, `id_giang_vien`, `ngay_tao`, `hinh_anh`, `video_demo`, `trang_thai_khoa_hoc`) VALUES
(1, 'HTML & CSS Cơ Bản', 'Khóa học nền tảng cho người mới bắt đầu lập trình web.', 199000.00, 1, 2, '2025-11-12 08:13:34', 'img/1762937341_download.jpg', 'htmlcss.mp4', 'publish'),
(2, 'JavaScript Nâng Cao', 'Học cách làm việc với JS hiện đại, DOM, ES6.', 299000.00, 1, 2, '2025-11-12 08:13:34', 'img/1762937602_jsnangcao.jpg', 'jsdemo.mp4', 'publish'),
(3, 'ReactJS Từ Cơ Bản Đến Nâng Cao', 'Xây dựng ứng dụng thực tế với React.', 499000.00, 9, 2, '2025-11-12 08:13:34', 'img/1762937638_react.jpg', 'reactdemo.mp4', 'publish'),
(4, 'Python Cho Người Mới Bắt Đầu', 'Hiểu rõ cú pháp và ứng dụng thực tế Python.', 299000.00, 2, 3, '2025-11-12 08:13:34', 'img/1762937677_python.jpg', 'pyintro.mp4', 'publish'),
(5, 'Python Nâng Cao', 'Học các kỹ thuật nâng cao, xử lý file, OOP.', 399000.00, 2, 3, '2025-11-12 08:13:34', 'img/1762937753_py.jpg', 'pyadv.mp4', 'publish'),
(6, 'Machine Learning Cơ Bản', 'Áp dụng Python vào học máy cơ bản.', 699000.00, 5, 3, '2025-11-12 08:13:34', 'img/1762937777_mc.jpg', 'ml.mp4', 'publish'),
(7, 'Node.js Express Backend', 'Tạo REST API với Node.js và Express.', 499000.00, 8, 2, '2025-11-12 08:13:34', 'img/1762937807_nodejs.jpg', 'node.mp4', 'publish'),
(8, 'React Native App', 'Phát triển ứng dụng di động bằng React Native.', 599000.00, 3, 3, '2025-11-12 08:13:34', 'img/1762937850_reactapp.jpg', 'rn.mp4', 'publish'),
(9, 'C++ Cơ Bản', 'Nắm vững cú pháp và kỹ năng lập trình hướng đối tượng với C++.', 199000.00, 7, 2, '2025-11-12 08:13:34', 'img/1762937867_c.jpg', 'cpp.mp4', 'publish'),
(10, 'Git & GitHub Cho Developer', 'Quản lý mã nguồn chuyên nghiệp với Git.', 149000.00, 10, 3, '2025-11-12 08:13:34', 'img/1762937886_gt.jpg', 'gitdemo.mp4', 'publish');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ma_giam_gia`
--

CREATE TABLE `ma_giam_gia` (
  `id` int(11) NOT NULL,
  `ma` varchar(20) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `loai_giam_gia` enum('phan_tram','so_tien') NOT NULL,
  `gia_tri` decimal(10,2) NOT NULL,
  `gia_toi_thieu` decimal(10,2) DEFAULT 0.00,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date NOT NULL,
  `gioi_han_su_dung` int(11) DEFAULT NULL,
  `da_su_dung` int(11) DEFAULT 0,
  `trang_thai` enum('hoat_dong','het_han','vo_hieu_hoa') DEFAULT 'hoat_dong',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ma_giam_gia`
--

INSERT INTO `ma_giam_gia` (`id`, `ma`, `mo_ta`, `loai_giam_gia`, `gia_tri`, `gia_toi_thieu`, `ngay_bat_dau`, `ngay_ket_thuc`, `gioi_han_su_dung`, `da_su_dung`, `trang_thai`, `ngay_tao`) VALUES
(1, 'WELCOME10', 'Giảm 10% cho đơn đầu tiên', 'phan_tram', 10.00, 0.00, '2025-01-01', '2025-12-31', 100, 5, 'hoat_dong', '2025-11-12 08:13:34'),
(2, 'PYTHON50', 'Giảm 50k cho khóa Python', 'so_tien', 50000.00, 199000.00, '2025-01-01', '2025-06-30', 50, 10, 'hoat_dong', '2025-11-12 08:13:34'),
(3, 'REACT20', 'Giảm 20% cho khóa React', 'phan_tram', 20.00, 0.00, '2025-03-01', '2025-12-31', 30, 8, 'hoat_dong', '2025-11-12 08:13:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ho_ten` varchar(100) DEFAULT NULL,
  `vai_tro` enum('hoc_vien','giang_vien','admin') NOT NULL DEFAULT 'hoc_vien',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ten_dang_nhap`, `mat_khau`, `email`, `ho_ten`, `vai_tro`, `ngay_tao`) VALUES
(1, 'admin', '$2y$10$abcdefgh1234567890qwertyuiopasdfghjklzxcvbnm', 'admin@code4fun.com', 'Quản trị viên', 'admin', '2025-11-12 08:13:34'),
(2, 'teacher1', '123456', 'teacher1@code4fun.com', 'Nguyễn Văn Giảng', 'giang_vien', '2025-11-12 08:13:34'),
(3, 'teacher2', '123456', 'teacher2@code4fun.com', 'Trần Thị Hướng Dẫn', 'giang_vien', '2025-11-12 08:13:34'),
(4, 'student1', '123456', 'student1@gmail.com', 'Lê Minh Học', 'admin', '2025-11-12 08:13:34'),
(5, 'student2', '123456', 'student2@gmail.com', 'Nguyễn Hà', 'hoc_vien', '2025-11-12 08:13:34'),
(6, 'student3', '123456', 'student3@gmail.com', 'Phạm Tuấn', 'hoc_vien', '2025-11-12 08:13:34'),
(7, 'student4', '123456', 'student4@gmail.com', 'Trần An', 'hoc_vien', '2025-11-12 08:13:34'),
(8, 'student5', '123456', 'student5@gmail.com', 'Hoàng Khang', 'hoc_vien', '2025-11-12 08:13:34'),
(9, 'student6', '123456', 'student6@gmail.com', 'Lý Hương', 'hoc_vien', '2025-11-12 08:13:34'),
(10, 'student7', '123456', 'student7@gmail.com', 'Phan Kiên', 'hoc_vien', '2025-11-12 08:13:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanh_toan`
--

CREATE TABLE `thanh_toan` (
  `id` int(11) NOT NULL,
  `id_dang_ky` int(11) NOT NULL,
  `so_tien` decimal(10,2) NOT NULL,
  `so_tien_thuc_te` decimal(10,2) DEFAULT NULL,
  `ngay_thanh_toan` timestamp NOT NULL DEFAULT current_timestamp(),
  `phuong_thuc` varchar(50) DEFAULT NULL,
  `trang_thai` enum('thanh_cong','that_bai') DEFAULT 'thanh_cong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thanh_toan`
--

INSERT INTO `thanh_toan` (`id`, `id_dang_ky`, `so_tien`, `so_tien_thuc_te`, `ngay_thanh_toan`, `phuong_thuc`, `trang_thai`) VALUES
(1, 1, 199000.00, 179100.00, '2025-11-12 08:13:34', 'Momo', 'thanh_cong'),
(2, 3, 299000.00, 269100.00, '2025-11-12 08:13:34', 'VNPAY', 'thanh_cong'),
(3, 4, 299000.00, 249000.00, '2025-11-12 08:13:34', 'ZaloPay', 'thanh_cong'),
(4, 5, 199000.00, 199000.00, '2025-11-12 08:13:34', 'Momo', 'thanh_cong'),
(5, 8, 299000.00, 299000.00, '2025-11-12 08:13:34', 'VNPAY', 'thanh_cong'),
(6, 11, 499000.00, NULL, '2025-11-12 08:26:01', 'chuyen_khoan', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tien_do_hoc_tap`
--

CREATE TABLE `tien_do_hoc_tap` (
  `id` int(11) NOT NULL,
  `id_dang_ky` int(11) NOT NULL,
  `id_bai_hoc` int(11) NOT NULL,
  `trang_thai` enum('chua_hoc','dang_hoc','hoan_thanh') DEFAULT 'chua_hoc',
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tien_do_hoc_tap`
--

INSERT INTO `tien_do_hoc_tap` (`id`, `id_dang_ky`, `id_bai_hoc`, `trang_thai`, `ngay_cap_nhat`) VALUES
(1, 1, 1, 'hoan_thanh', '2025-11-12 08:13:34'),
(2, 1, 2, 'hoan_thanh', '2025-11-12 08:13:34'),
(3, 1, 3, 'hoan_thanh', '2025-11-12 08:13:34'),
(4, 3, 4, 'dang_hoc', '2025-11-12 08:13:34'),
(5, 3, 5, 'chua_hoc', '2025-11-12 08:13:34'),
(6, 4, 10, 'hoan_thanh', '2025-11-12 08:13:34'),
(7, 4, 11, 'dang_hoc', '2025-11-12 08:13:34'),
(8, 4, 12, 'chua_hoc', '2025-11-12 08:13:34'),
(9, 8, 10, 'hoan_thanh', '2025-11-12 08:13:34'),
(10, 8, 11, 'hoan_thanh', '2025-11-12 08:13:34');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bai_hoc`
--
ALTER TABLE `bai_hoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_khoa_hoc` (`id_khoa_hoc`);

--
-- Chỉ mục cho bảng `chung_chi`
--
ALTER TABLE `chung_chi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_chung_chi` (`ma_chung_chi`),
  ADD KEY `id_dang_ky` (`id_dang_ky`),
  ADD KEY `idx_ma_chung_chi` (`ma_chung_chi`);

--
-- Chỉ mục cho bảng `dang_ky`
--
ALTER TABLE `dang_ky`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_nguoi_dung` (`id_nguoi_dung`,`id_khoa_hoc`),
  ADD KEY `id_khoa_hoc` (`id_khoa_hoc`),
  ADD KEY `id_ma_giam_gia` (`id_ma_giam_gia`);

--
-- Chỉ mục cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_nguoi_dung` (`id_nguoi_dung`,`id_khoa_hoc`),
  ADD KEY `id_khoa_hoc` (`id_khoa_hoc`);

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `file_dinh_kem`
--
ALTER TABLE `file_dinh_kem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_bai_hoc` (`id_bai_hoc`);

--
-- Chỉ mục cho bảng `khoa_hoc`
--
ALTER TABLE `khoa_hoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_danh_muc` (`id_danh_muc`),
  ADD KEY `id_giang_vien` (`id_giang_vien`);

--
-- Chỉ mục cho bảng `ma_giam_gia`
--
ALTER TABLE `ma_giam_gia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma` (`ma`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_dang_ky` (`id_dang_ky`);

--
-- Chỉ mục cho bảng `tien_do_hoc_tap`
--
ALTER TABLE `tien_do_hoc_tap`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tien_do` (`id_dang_ky`,`id_bai_hoc`),
  ADD KEY `id_bai_hoc` (`id_bai_hoc`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bai_hoc`
--
ALTER TABLE `bai_hoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `chung_chi`
--
ALTER TABLE `chung_chi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `dang_ky`
--
ALTER TABLE `dang_ky`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `file_dinh_kem`
--
ALTER TABLE `file_dinh_kem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `khoa_hoc`
--
ALTER TABLE `khoa_hoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `ma_giam_gia`
--
ALTER TABLE `ma_giam_gia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `tien_do_hoc_tap`
--
ALTER TABLE `tien_do_hoc_tap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bai_hoc`
--
ALTER TABLE `bai_hoc`
  ADD CONSTRAINT `bai_hoc_ibfk_1` FOREIGN KEY (`id_khoa_hoc`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chung_chi`
--
ALTER TABLE `chung_chi`
  ADD CONSTRAINT `chung_chi_ibfk_1` FOREIGN KEY (`id_dang_ky`) REFERENCES `dang_ky` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `dang_ky`
--
ALTER TABLE `dang_ky`
  ADD CONSTRAINT `dang_ky_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dang_ky_ibfk_2` FOREIGN KEY (`id_khoa_hoc`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dang_ky_ibfk_3` FOREIGN KEY (`id_ma_giam_gia`) REFERENCES `ma_giam_gia` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `danh_gia`
--
ALTER TABLE `danh_gia`
  ADD CONSTRAINT `danh_gia_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `danh_gia_ibfk_2` FOREIGN KEY (`id_khoa_hoc`) REFERENCES `khoa_hoc` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `file_dinh_kem`
--
ALTER TABLE `file_dinh_kem`
  ADD CONSTRAINT `file_dinh_kem_ibfk_1` FOREIGN KEY (`id_bai_hoc`) REFERENCES `bai_hoc` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `khoa_hoc`
--
ALTER TABLE `khoa_hoc`
  ADD CONSTRAINT `khoa_hoc_ibfk_1` FOREIGN KEY (`id_danh_muc`) REFERENCES `danh_muc` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `khoa_hoc_ibfk_2` FOREIGN KEY (`id_giang_vien`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD CONSTRAINT `thanh_toan_ibfk_1` FOREIGN KEY (`id_dang_ky`) REFERENCES `dang_ky` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tien_do_hoc_tap`
--
ALTER TABLE `tien_do_hoc_tap`
  ADD CONSTRAINT `tien_do_hoc_tap_ibfk_1` FOREIGN KEY (`id_dang_ky`) REFERENCES `dang_ky` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tien_do_hoc_tap_ibfk_2` FOREIGN KEY (`id_bai_hoc`) REFERENCES `bai_hoc` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

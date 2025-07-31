-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 31, 2025 lúc 03:58 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `library_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id_user` int(11) NOT NULL,
  `tai_khoan` varchar(50) NOT NULL,
  `mat_khau` text NOT NULL,
  `vai_tro` enum('Quan_ly','Nguoi_dung') DEFAULT NULL,
  `ho_ten` varchar(255) DEFAULT NULL,
  `so_dt` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id_user`, `tai_khoan`, `mat_khau`, `vai_tro`, `ho_ten`, `so_dt`, `email`) VALUES
(1, 'admin', 'admin', 'Quan_ly', 'Nguyen Van A', '0123456789', 'a@gmail.com');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sach`
--

CREATE TABLE `sach` (
  `id_sach` int(11) NOT NULL,
  `id_sach_chung` int(11) NOT NULL,
  `id_thuvien` int(11) NOT NULL,
  `id_theloai` int(11) DEFAULT NULL,
  `tongsl` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sach`
--

INSERT INTO `sach` (`id_sach`, `id_sach_chung`, `id_thuvien`, `id_theloai`, `tongsl`) VALUES
(1, 1, 1, 1, 14),
(2, 1, 2, 1, 10),
(3, 2, 1, 1, 15),
(4, 3, 2, 1, 21),
(5, 4, 2, 1, 20),
(6, 5, 1, 2, 12),
(7, 6, 2, 2, 8),
(8, 7, 3, 3, 20),
(9, 8, 3, 3, 30),
(10, 9, 4, 4, 25),
(11, 10, 4, 4, 12),
(12, 11, 5, 5, 5),
(13, 12, 5, 5, 10);

--
-- Bẫy `sach`
--
DELIMITER $$
CREATE TRIGGER `check_tongsl_before_insert` BEFORE INSERT ON `sach` FOR EACH ROW BEGIN
    IF NEW.tongsl < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tổng số lượng sách không được âm';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `check_tongsl_before_update` BEFORE UPDATE ON `sach` FOR EACH ROW BEGIN
    IF NEW.tongsl < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tổng số lượng sách không được âm';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sach_chung`
--

CREATE TABLE `sach_chung` (
  `id_sach_chung` int(11) NOT NULL,
  `ten_sach` varchar(255) NOT NULL,
  `tac_gia` varchar(255) DEFAULT NULL,
  `nam_xuat_ban` smallint(6) DEFAULT NULL,
  `anh_sach` varchar(255) DEFAULT NULL,
  `id_theloai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sach_chung`
--

INSERT INTO `sach_chung` (`id_sach_chung`, `ten_sach`, `tac_gia`, `nam_xuat_ban`, `anh_sach`, `id_theloai`) VALUES
(1, 'Khoa học 1', 'Tác giả A', 2020, './public/images/anhsach/anhsach_1753722325.png', 1),
(2, 'Khoa học 2', 'Tác giả B', 2021, './public/images/anhsach/anhsach_1753723324.png', 1),
(3, 'Khoa học 3', 'Tác giả B', 2000, './public/images/anhsach/anhsach_1753723335.png', 1),
(4, 'Khoa học 4', 'Tác giả B', 2000, './public/images/anhsach/anhsach_1753723345.png', 1),
(5, 'Văn học 1', 'Tác giả C', 2019, './public/images/anhsach/anhsach_1753723355.png', 2),
(6, 'Văn học 2', 'Tác giả D', 2022, './public/images/anhsach/anhsach_1753723366.png', 2),
(7, 'Công nghệ 1', 'Tác giả G', 2021, NULL, 3),
(8, 'Công nghệ 2', 'Tác giả H', 2022, NULL, 3),
(9, 'Kinh tế 1', 'Tác giả I', 2020, NULL, 4),
(10, 'Kinh tế 2', 'Tác giả J', 2021, NULL, 4),
(11, 'Lịch sử 1', 'Tác giả E', 2018, NULL, 5),
(12, 'Lịch sử 2', 'Tác giả F', 2020, NULL, 5),
(13, 'Văn hóa 1', 'Tác giả K', 2021, NULL, 6),
(14, 'Văn hóa 2', 'Tác giả L', 2020, NULL, 2),
(15, 'Triết học 1', 'Tác giả M', 2022, NULL, 7),
(16, 'Triết học 2', 'Tác giả N', 2020, NULL, 7),
(17, 'Xã hội học 1', 'Tác giả O', 2019, NULL, 8),
(18, 'Xã hội học 2', 'Tác giả P', 2020, NULL, 8),
(19, 'Ngôn ngữ học 1', 'Tác giả Q', 2021, NULL, 9),
(20, 'Ngôn ngữ học 2', 'Tác giả R', 2022, NULL, 9),
(21, 'Giải tich 1', 'Nguyễn Văn A', 2003, NULL, 10),
(22, 'Conan', 'Nguyễn Văn A', 1999, NULL, 11);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `the_loai`
--

CREATE TABLE `the_loai` (
  `id_theloai` int(11) NOT NULL,
  `ten_theloai` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `the_loai`
--

INSERT INTO `the_loai` (`id_theloai`, `ten_theloai`) VALUES
(1, 'Khoa học'),
(2, 'Văn học'),
(3, 'Công nghệ'),
(4, 'Kinh tế'),
(5, 'Lịch sử'),
(6, 'Văn hóa'),
(7, 'Triết học'),
(8, 'Xã hội học'),
(9, 'Ngôn ngữ học'),
(10, 'Toán học'),
(11, 'Truyện');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thu_vien`
--

CREATE TABLE `thu_vien` (
  `id_thuvien` int(11) NOT NULL,
  `ten_thuvien` varchar(255) NOT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `wifi` tinyint(1) DEFAULT 0,
  `phongdoc` tinyint(1) DEFAULT 0,
  `canteen` tinyint(1) DEFAULT 0,
  `dieuhoa` tinyint(1) DEFAULT 0,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `anh_360` text DEFAULT NULL,
  `gio_mo_cua` time DEFAULT NULL,
  `gio_dong_cua` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thu_vien`
--

INSERT INTO `thu_vien` (`id_thuvien`, `ten_thuvien`, `dia_chi`, `wifi`, `phongdoc`, `canteen`, `dieuhoa`, `latitude`, `longitude`, `anh_360`, `gio_mo_cua`, `gio_dong_cua`) VALUES
(1, 'Thư Viện Quận Hoàn Kiếm', 'Hà Nội, Quận Hoàn Kiếm, Phố Ngô Quyền', 1, 1, 1, 0, 21.02850000, 105.85420000, 'public/images/360/thuvien_1_360.jpg', '08:00:00', '17:00:00'),
(2, 'Thư Viện Ba Đình', 'Hà Nội, Quận Ba Đình, Phố Nguyễn Thái Học', 1, 1, 0, 1, 21.02750000, 105.83230000, 'public/images/360/thuvien_2_360.jpg', '07:30:00', '18:00:00'),
(3, 'Thư Viện Quận Cầu Giấy', 'Hà Nội, Quận Cầu Giấy, Phố Trần Duy Hưng', 1, 1, 1, 0, 21.00840000, 105.82410000, 'public/images/anh360/anh360_688902d5cfa03.jpg', '08:30:00', '17:30:00'),
(4, 'Thư Viện Quận Đống Đa', 'Hà Nội, Quận Đống Đa, Phố Tôn Đức Thắng', 0, 1, 1, 1, 21.02990000, 105.83390000, 'public/images/anh360/anh360_688902dfdf8fb.jpg', '08:00:00', '16:30:00'),
(5, 'Thư Viện Quận Long Biên', 'Hà Nội, Quận Long Biên, Phố Ngọc Thụy', 1, 1, 1, 1, 21.04250000, 105.88090000, 'public/images/360/thuvien_5_360.jpg', '07:00:00', '18:30:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thu_vien_the_loai`
--

CREATE TABLE `thu_vien_the_loai` (
  `id_thuvien` int(11) NOT NULL,
  `id_theloai` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thu_vien_the_loai`
--

INSERT INTO `thu_vien_the_loai` (`id_thuvien`, `id_theloai`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nguoi_dung_tai_khoan_key` (`tai_khoan`),
  ADD UNIQUE KEY `nguoi_dung_email_key` (`email`);

--
-- Chỉ mục cho bảng `sach`
--
ALTER TABLE `sach`
  ADD PRIMARY KEY (`id_sach`),
  ADD KEY `id_sach_chung` (`id_sach_chung`),
  ADD KEY `id_thuvien` (`id_thuvien`),
  ADD KEY `id_theloai` (`id_theloai`);

--
-- Chỉ mục cho bảng `sach_chung`
--
ALTER TABLE `sach_chung`
  ADD PRIMARY KEY (`id_sach_chung`),
  ADD KEY `fk_sach_chung_theloai` (`id_theloai`);

--
-- Chỉ mục cho bảng `the_loai`
--
ALTER TABLE `the_loai`
  ADD PRIMARY KEY (`id_theloai`);

--
-- Chỉ mục cho bảng `thu_vien`
--
ALTER TABLE `thu_vien`
  ADD PRIMARY KEY (`id_thuvien`);

--
-- Chỉ mục cho bảng `thu_vien_the_loai`
--
ALTER TABLE `thu_vien_the_loai`
  ADD PRIMARY KEY (`id_thuvien`,`id_theloai`),
  ADD KEY `id_theloai` (`id_theloai`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `sach`
--
ALTER TABLE `sach`
  MODIFY `id_sach` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `sach_chung`
--
ALTER TABLE `sach_chung`
  MODIFY `id_sach_chung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `the_loai`
--
ALTER TABLE `the_loai`
  MODIFY `id_theloai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `thu_vien`
--
ALTER TABLE `thu_vien`
  MODIFY `id_thuvien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `sach`
--
ALTER TABLE `sach`
  ADD CONSTRAINT `sach_ibfk_1` FOREIGN KEY (`id_sach_chung`) REFERENCES `sach_chung` (`id_sach_chung`) ON DELETE CASCADE,
  ADD CONSTRAINT `sach_ibfk_2` FOREIGN KEY (`id_thuvien`) REFERENCES `thu_vien` (`id_thuvien`) ON DELETE CASCADE,
  ADD CONSTRAINT `sach_ibfk_3` FOREIGN KEY (`id_theloai`) REFERENCES `the_loai` (`id_theloai`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sach_chung`
--
ALTER TABLE `sach_chung`
  ADD CONSTRAINT `fk_sach_chung_theloai` FOREIGN KEY (`id_theloai`) REFERENCES `the_loai` (`id_theloai`);

--
-- Các ràng buộc cho bảng `thu_vien_the_loai`
--
ALTER TABLE `thu_vien_the_loai`
  ADD CONSTRAINT `thu_vien_the_loai_ibfk_1` FOREIGN KEY (`id_thuvien`) REFERENCES `thu_vien` (`id_thuvien`) ON DELETE CASCADE,
  ADD CONSTRAINT `thu_vien_the_loai_ibfk_2` FOREIGN KEY (`id_theloai`) REFERENCES `the_loai` (`id_theloai`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

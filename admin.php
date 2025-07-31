<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] !== 'Quan_ly') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị - Quản Lý Thư Viện</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">

</head>

<body>
    <div class="admin-container">
        <h2>Trang Quản Trị</h2>
        <div class="admin-nav">
            <a href="thu_vien.php">Thư viện</a>
            <a href="the_loai.php">Thể loại sách trong thư viện</a>
            <a href="sach_chung.php">Sách</a>
            <a href="sachtv.php">Sách trong thư viện</a>
            <a href="logout.php" class="logout">Đăng xuất</a>
        </div>
    </div>
</body>

</html>
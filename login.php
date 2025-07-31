<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tai_khoan = $_POST['tai_khoan'] ?? '';
    $mat_khau = $_POST['mat_khau'] ?? '';

    if (empty($tai_khoan) || empty($mat_khau)) {
        $error = "Vui lòng nhập đầy đủ tài khoản và mật khẩu.";
    } else {
        $stmt = $conn->prepare("SELECT id_user, tai_khoan, mat_khau, vai_tro FROM nguoi_dung WHERE tai_khoan = ? AND vai_tro = 'Quan_ly'");
        $stmt->bind_param("s", $tai_khoan);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($mat_khau === $user['mat_khau']) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['tai_khoan'] = $user['tai_khoan'];
                $_SESSION['vai_tro'] = $user['vai_tro'];
                header("Location: admin.php");
                exit;
            } else {
                $error = "Mật khẩu không đúng.";
            }
        } else {
            $error = "Tài khoản không tồn tại hoặc không phải admin.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Admin - Quản Lý Thư Viện</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
</head>
<body>
    <div class="login-container">
        <h2>Đăng Nhập Admin</h2>
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="tai_khoan">Tài khoản:</label>
                <input type="text" id="tai_khoan" name="tai_khoan" required>
            </div>
            <div class="form-group">
                <label for="mat_khau">Mật khẩu:</label>
                <input type="password" id="mat_khau" name="mat_khau" required>
            </div>
            <button type="submit">Đăng Nhập</button>
            <a href="index.php"><button type="button" class="home-button">Trang Chính</button></a>
        </form>
    </div>
</body>
</html>
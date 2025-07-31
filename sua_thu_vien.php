<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] !== 'Quan_ly') {
    header('Location: login.php');
    exit;
}


$message = '';
$message_class = '';
$id_thuvien = isset($_GET['id_thuvien']) ? (int)$_GET['id_thuvien'] : 0;
$record = null;

if ($id_thuvien > 0) {
    $sql = "SELECT id_thuvien, ten_thuvien, dia_chi, wifi, phongdoc, canteen, dieuhoa, latitude, longitude, anh_360, gio_mo_cua, gio_dong_cua 
            FROM thu_vien WHERE id_thuvien = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_thuvien);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$record) {
        $message = "Không tìm thấy thư viện với ID: $id_thuvien";
        $message_class = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_thuvien'])) {
    $ten_thuvien = trim($_POST['ten_thuvien'] ?? '');
    $dia_chi = trim($_POST['dia_chi'] ?? '');
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $phongdoc = isset($_POST['phongdoc']) ? 1 : 0;
    $canteen = isset($_POST['canteen']) ? 1 : 0;
    $dieuhoa = isset($_POST['dieuhoa']) ? 1 : 0;
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
    $gio_mo_cua = !empty($_POST['gio_mo_cua']) ? $_POST['gio_mo_cua'] . ':00' : null;
    $gio_dong_cua = !empty($_POST['gio_dong_cua']) ? $_POST['gio_dong_cua'] . ':00' : null;


    if (empty($ten_thuvien) || $id_thuvien <= 0) {
        $message = "Vui lòng nhập tên thư viện và ID hợp lệ.";
        $message_class = 'error';
    } elseif ($gio_mo_cua && $gio_dong_cua && strtotime($gio_mo_cua) >= strtotime($gio_dong_cua)) {
        $message = "Giờ mở cửa phải nhỏ hơn giờ đóng cửa.";
        $message_class = 'error';
    } else {
        $sql = "SELECT anh_360 FROM thu_vien WHERE id_thuvien = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_thuvien);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $anh_360 = $result['anh_360'] ?? null;
        $stmt->close();

        if (isset($_FILES['anh_360']) && $_FILES['anh_360']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $file = $_FILES['anh_360'];

            if (!in_array($file['type'], $allowed_types)) {
                $message = "Chỉ chấp nhận file ảnh JPG, JPEG hoặc PNG.";
                $message_class = 'error';
            } elseif ($file['size'] > $max_size) {
                $message = "Kích thước file ảnh tối đa là 5MB.";
                $message_class = 'error';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('anh360_') . '.' . $ext;
                $upload_path = 'public/images/anh360/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    if ($anh_360 && file_exists($anh_360)) {
                        unlink($anh_360);
                    }
                    $anh_360 = $upload_path;
                } else {
                    $message = "Lỗi khi tải lên ảnh.";
                    $message_class = 'error';
                }
            }
        }

        if (!$message) {
            $sql = "UPDATE thu_vien SET ten_thuvien = ?, dia_chi = ?, wifi = ?, phongdoc = ?, canteen = ?, dieuhoa = ?, latitude = ?, longitude = ?, anh_360 = ?, gio_mo_cua = ?, gio_dong_cua = ? WHERE id_thuvien = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $message = "Lỗi chuẩn bị truy vấn.";
                $message_class = 'error';
            } else {
                $stmt->bind_param("ssiiiiddsssi", $ten_thuvien, $dia_chi, $wifi, $phongdoc, $canteen, $dieuhoa, $latitude, $longitude, $anh_360, $gio_mo_cua, $gio_dong_cua, $id_thuvien);
                if ($stmt->execute()) {
                    $message = "Cập nhật thư viện thành công.";
                    $message_class = 'success';
                    header('Location: thu_vien.php');
                    exit;
                } else {
                    $message = "Lỗi khi cập nhật thư viện.";
                    $message_class = 'error';
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thư viện</title>
</head>

<body>
    <div class="admin-nav">
        <a href="thu_vien.php">Thư viện</a>
        <a href="the_loai.php">Thể loại sách trong thư viện</a>
        <a href="sach_chung.php">Sách</a>
        <a href="sachtv.php">Sách trong thư viện</a>
        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>
    <div class="container">
        <h2>Sửa thư viện</h2>
        <div id="message">
            <?php if ($message): ?>
                <p class="<?php echo htmlspecialchars($message_class); ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($record): ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="update_thuvien" value="1">
                <div class="form-group">
                    <label for="ten_thuvien">Tên thư viện:</label>
                    <input type="text" id="ten_thuvien" name="ten_thuvien" value="<?php echo htmlspecialchars($record['ten_thuvien'] ?? $_POST['ten_thuvien'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="dia_chi">Địa chỉ:</label>
                    <input type="text" id="dia_chi" name="dia_chi" value="<?php echo htmlspecialchars($record['dia_chi'] ?? $_POST['dia_chi'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="wifi" <?php echo ($record['wifi'] || isset($_POST['wifi'])) ? 'checked' : ''; ?>> Có Wi-Fi</label>
                    <label><input type="checkbox" name="phongdoc" <?php echo ($record['phongdoc'] || isset($_POST['phongdoc'])) ? 'checked' : ''; ?>> Có phòng đọc</label>
                    <label><input type="checkbox" name="canteen" <?php echo ($record['canteen'] || isset($_POST['canteen'])) ? 'checked' : ''; ?>> Có căng tin</label>
                    <label><input type="checkbox" name="dieuhoa" <?php echo ($record['dieuhoa'] || isset($_POST['dieuhoa'])) ? 'checked' : ''; ?>> Có điều hòa</label>
                </div>
                <div class="form-group">
                    <label for="latitude">Vĩ độ:</label>
                    <input type="number" id="latitude" name="latitude" step="any" value="<?php echo htmlspecialchars($record['latitude'] ?? $_POST['latitude'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="longitude">Kinh độ:</label>
                    <input type="number" id="longitude" name="longitude" step="any" value="<?php echo htmlspecialchars($record['longitude'] ?? $_POST['longitude'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="anh_360">Ảnh 360:</label>
                    <input type="file" id="anh_360" name="anh_360" accept="image/jpeg,image/jpg,image/png">
                    <?php if ($record['anh_360']): ?>
                        <div class="current-image">
                            <p>Ảnh hiện tại:</p>
                            <img src="<?php echo htmlspecialchars($record['anh_360']); ?>" alt="Ảnh 360">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="gio_mo_cua">Giờ mở cửa:</label>
                    <input type="time" id="gio_mo_cua" name="gio_mo_cua" value="<?php echo htmlspecialchars(substr($record['gio_mo_cua'] ?? $_POST['gio_mo_cua'] ?? '', 0, 5)); ?>">
                </div>
                <div class="form-group">
                    <label for="gio_dong_cua">Giờ đóng cửa:</label>
                    <input type="time" id="gio_dong_cua" name="gio_dong_cua" value="<?php echo htmlspecialchars(substr($record['gio_dong_cua'] ?? $_POST['gio_dong_cua'] ?? '', 0, 5)); ?>">
                </div>
                <button type="submit" class="btn btn-save">Lưu</button>
                <a href="thu_vien.php" class="btn btn-back">Quay lại</a>
            </form>
        <?php else: ?>
            <p>Không tìm thấy thư viện.</p>
            <a href="thu_vien.php" class="btn btn-back">Quay lại</a>
        <?php endif; ?>
    </div>
</body>

</html>
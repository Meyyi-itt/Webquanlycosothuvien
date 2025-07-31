<?php
ob_start(); 
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] !== 'Quan_ly') {
    header('Location: login.php'); // Chuyển hướng nếu không có quyền
    exit;
}

$message = '';
$message_class = '';

$thuvien_data = [];
$sql = "SELECT id_thuvien, ten_thuvien, dia_chi, wifi, phongdoc, canteen, dieuhoa, latitude, longitude, anh_360, gio_mo_cua, gio_dong_cua 
        FROM thu_vien";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $thuvien_data[] = $row;
    }
    $stmt->close();
} else {
    $message = "Lỗi khi tải danh sách thư viện.";
    $message_class = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_thuvien'])) {
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

    if (empty($ten_thuvien)) {
        $message = "Vui lòng nhập tên thư viện.";
        $message_class = 'error';
    } elseif ($gio_mo_cua && $gio_dong_cua && strtotime($gio_mo_cua) >= strtotime($gio_dong_cua)) {
        $message = "Giờ mở cửa phải nhỏ hơn giờ đóng cửa.";
        $message_class = 'error';
    } else {
        $anh_360 = null;
        $upload_dir = 'public/images/anh360/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
            $message = "Thư mục lưu ảnh không tồn tại hoặc không có quyền ghi.";
            $message_class = 'error';
        } elseif (isset($_FILES['anh_360']) && $_FILES['anh_360']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            $max_size = 5 * 1024 * 1024;
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
                $upload_path = $upload_dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $anh_360 = $upload_path;
                } else {
                    $message = "Lỗi khi tải lên ảnh.";
                    $message_class = 'error';
                }
            }
        }

        if (!$message) {
            $sql = "INSERT INTO thu_vien (ten_thuvien, dia_chi, wifi, phongdoc, canteen, dieuhoa, latitude, longitude, anh_360, gio_mo_cua, gio_dong_cua) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $message = "Lỗi chuẩn bị truy vấn.";
                $message_class = 'error';
            } else {
                $stmt->bind_param("ssiiiiddsss", $ten_thuvien, $dia_chi, $wifi, $phongdoc, $canteen, $dieuhoa, $latitude, $longitude, $anh_360, $gio_mo_cua, $gio_dong_cua);
                if ($stmt->execute()) {
                    $message = "Thêm thư viện thành công.";
                    $message_class = 'success';
                    header('Location: thu_vien.php');
                    exit;
                } else {
                    $message = "Lỗi khi thêm thư viện.";
                    $message_class = 'error';
                }
                $stmt->close();
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_thuvien'])) {
    $id_thuvien = (int)($_POST['delete_id'] ?? 0);

    if ($id_thuvien <= 0) {
        $message = "Dữ liệu không hợp lệ.";
        $message_class = 'error';
    } else {
        $sql = "SELECT anh_360 FROM thu_vien WHERE id_thuvien = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $message = "Lỗi chuẩn bị truy vấn.";
            $message_class = 'error';
        } else {
            $stmt->bind_param("i", $id_thuvien);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $anh_360 = $result['anh_360'] ?? null;
            $stmt->close();

            $sql = "DELETE FROM thu_vien WHERE id_thuvien = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $message = "Lỗi chuẩn bị truy vấn.";
                $message_class = 'error';
            } else {
                $stmt->bind_param("i", $id_thuvien);
                if ($stmt->execute()) {
                    if ($anh_360 && file_exists($anh_360)) {
                        unlink($anh_360);
                    }
                    $message = "Xóa thư viện thành công.";
                    $message_class = 'success';
                    header('Location: thu_vien.php');
                    exit;
                } else {
                    $message = "Lỗi khi xóa thư viện.";
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
    <title>Quản lý thư viện</title>
</head>
<body>
    <div class="admin-nav">
        <a href="admin.php">Trang chủ</a>
        <a href="the_loai.php">Thể loại sách trong thư viện</a>
        <a href="sach_chung.php">Sách</a>
        <a href="sachtv.php">Sách trong thư viện</a>
        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>
    <h2>Quản lý thư viện</h2>
    <div id="message">
        <?php if ($message): ?>
            <p class="<?php echo htmlspecialchars($message_class); ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>

    <div class="form-container">
        <h3>Thêm thư viện mới</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="add_thuvien" value="1">
            <div class="form-group">
                <label for="ten_thuvien">Tên thư viện:</label>
                <input type="text" id="ten_thuvien" name="ten_thuvien" required value="<?php echo isset($_POST['ten_thuvien']) ? htmlspecialchars($_POST['ten_thuvien']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="dia_chi">Địa chỉ:</label>
                <input type="text" id="dia_chi" name="dia_chi" value="<?php echo isset($_POST['dia_chi']) ? htmlspecialchars($_POST['dia_chi']) : ''; ?>">
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="wifi" <?php echo isset($_POST['wifi']) ? 'checked' : ''; ?>> Có Wi-Fi</label>
                <label><input type="checkbox" name="phongdoc" <?php echo isset($_POST['phongdoc']) ? 'checked' : ''; ?>> Có phòng đọc</label>
                <label><input type="checkbox" name="canteen" <?php echo isset($_POST['canteen']) ? 'checked' : ''; ?>> Có căng tin</label>
                <label><input type="checkbox" name="dieuhoa" <?php echo isset($_POST['dieuhoa']) ? 'checked' : ''; ?>> Có điều hòa</label>
            </div>
            <div class="form-group">
                <label for="latitude">Vĩ độ:</label>
                <input type="number" id="latitude" name="latitude" step="any" value="<?php echo isset($_POST['latitude']) ? htmlspecialchars($_POST['latitude']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="longitude">Kinh độ:</label>
                <input type="number" id="longitude" name="longitude" step="any" value="<?php echo isset($_POST['longitude']) ? htmlspecialchars($_POST['longitude']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="anh_360">Ảnh 360:</label>
                <input type="file" id="anh_360" name="anh_360" accept="image/jpeg,image/jpg,image/png">
            </div>
            <div class="form-group">
                <label for="gio_mo_cua">Giờ mở cửa:</label>
                <input type="time" id="gio_mo_cua" name="gio_mo_cua" value="<?php echo isset($_POST['gio_mo_cua']) ? htmlspecialchars($_POST['gio_mo_cua']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="gio_dong_cua">Giờ đóng cửa:</label>
                <input type="time" id="gio_dong_cua" name="gio_dong_cua" value="<?php echo isset($_POST['gio_dong_cua']) ? htmlspecialchars($_POST['gio_dong_cua']) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-save">Thêm thư viện</button>
        </form>
    </div>

    <table id="thuvien_table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên thư viện</th>
                <th>Địa chỉ</th>
                <th>Wi-Fi</th>
                <th>Phòng đọc</th>
                <th>Căng tin</th>
                <th>Điều hòa</th>
                <th>Vĩ độ</th>
                <th>Kinh độ</th>
                <th>Ảnh 360</th>
                <th>Giờ mở cửa</th>
                <th>Giờ đóng cửa</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($thuvien_data)): ?>
                <tr><td colspan="13">Không có thư viện nào.</td></tr>
            <?php else: ?>
                <?php foreach ($thuvien_data as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id_thuvien']); ?></td>
                        <td><?php echo htmlspecialchars($item['ten_thuvien']); ?></td>
                        <td><?php echo htmlspecialchars($item['dia_chi'] ?? 'Không xác định'); ?></td>
                        <td><?php echo $item['wifi'] ? 'Có' : 'Không'; ?></td>
                        <td><?php echo $item['phongdoc'] ? 'Có' : 'Không'; ?></td>
                        <td><?php echo $item['canteen'] ? 'Có' : 'Không'; ?></td>
                        <td><?php echo $item['dieuhoa'] ? 'Có' : 'Không'; ?></td>
                        <td><?php echo htmlspecialchars($item['latitude'] ?? 'Không xác định'); ?></td>
                        <td><?php echo htmlspecialchars($item['longitude'] ?? 'Không xác định'); ?></td>
                        <td><?php echo $item['anh_360'] ? '<img src="' . htmlspecialchars($item['anh_360']) . '" alt="Ảnh 360" class="thumbnail">' : 'Không có'; ?></td>
                        <td><?php echo htmlspecialchars($item['gio_mo_cua'] ?? 'Không xác định'); ?></td>
                        <td><?php echo htmlspecialchars($item['gio_dong_cua'] ?? 'Không xác định'); ?></td>
                        <td>
                            <a href="sua_thu_vien.php?id_thuvien=<?php echo htmlspecialchars($item['id_thuvien']); ?>" class="btn btn-edit">Sửa</a>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                                <input type="hidden" name="delete_thuvien" value="1">
                                <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($item['id_thuvien']); ?>">
                                <button type="submit" class="btn btn-delete">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php
require_once 'config.php'; 

$message = '';
$message_class = '';
$id_sach_chung = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$book = null;


$upload_dir = './public/images/anhsach';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (!is_writable($upload_dir)) {
    $message = "Thư mục $upload_dir không có quyền ghi.";
    $message_class = 'error';
}


if ($id_sach_chung > 0 && !$message) {
    $sql = "SELECT sc.*, tl.ten_theloai FROM sach_chung sc LEFT JOIN the_loai tl ON sc.id_theloai = tl.id_theloai WHERE sc.id_sach_chung = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $message = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
        $message_class = 'error';
    } else {
        $stmt->bind_param("i", $id_sach_chung);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$book) {
            $message = "Không tìm thấy sách với ID: $id_sach_chung";
            $message_class = 'error';
        }
    }
}


$sql = "SELECT id_theloai, ten_theloai FROM the_loai";
$the_loai_result = $conn->query($sql);
if ($the_loai_result === false) {
    $message = "Lỗi khi lấy danh sách thể loại: " . $conn->error;
    $message_class = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_sach']) && !$message) {
    $ten_sach = trim($_POST['ten_sach'] ?? '');
    $tac_gia = !empty($_POST['tac_gia']) ? trim($_POST['tac_gia']) : null;
    $nam_xuat_ban = !empty($_POST['nam_xuat_ban']) ? (int)$_POST['nam_xuat_ban'] : null;
    $id_theloai = !empty($_POST['id_theloai']) ? (int)$_POST['id_theloai'] : null;
    $anh_sach = $book['anh_sach'] ?? null;

    if (empty($ten_sach)) {
        $message = "Tên sách không được để trống.";
        $message_class = 'error';
    } else {
        if (isset($_FILES['anh_sach']) && $_FILES['anh_sach']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($_FILES['anh_sach']['name'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed)) {
                $message = "Chỉ chấp nhận tệp ảnh định dạng jpg, jpeg, png, gif.";
                $message_class = 'error';
            } elseif ($_FILES['anh_sach']['size'] > 5 * 1024 * 1024) {
                $message = "Kích thước ảnh không được vượt quá 5MB.";
                $message_class = 'error';
            } else {
                $file_name = 'anhsach_' . time() . '.' . $file_ext;
                $file_path = $upload_dir . '/' . $file_name;
                if (move_uploaded_file($_FILES['anh_sach']['tmp_name'], $file_path)) {
                    if ($anh_sach && file_exists($anh_sach)) {
                        unlink($anh_sach);
                    }
                    $anh_sach = $file_path;
                } else {
                    $message = "Lỗi khi tải lên ảnh.";
                    $message_class = 'error';
                }
            }
        }

        if (!$message) {
            $sql = "UPDATE sach_chung SET ten_sach = ?, tac_gia = ?, nam_xuat_ban = ?, id_theloai = ?, anh_sach = ? WHERE id_sach_chung = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $message = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
                $message_class = 'error';
            } else {
                $stmt->bind_param("ssisis", $ten_sach, $tac_gia, $nam_xuat_ban, $id_theloai, $anh_sach, $id_sach_chung);
                if ($stmt->execute()) {
                    $message = "Cập nhật sách thành công.";
                    $message_class = 'success';
                    header('Location: sach_chung.php');
                    exit;
                } else {
                    $message = "Lỗi khi cập nhật sách: " . $stmt->error;
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
    <title>Sửa sách chung</title>
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
        <h2>Sửa sách chung</h2>
        <div id="message">
            <?php if ($message): ?>
                <p class="<?php echo htmlspecialchars($message_class); ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($book): ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="update_sach" value="1">
                <div class="form-group">
                    <label for="ten_sach">Tên sách:</label>
                    <input type="text" id="ten_sach" name="ten_sach" value="<?php echo htmlspecialchars($book['ten_sach'] ?? (isset($_POST['ten_sach']) ? $_POST['ten_sach'] : '')); ?>" required>
                </div>
                <div class="form-group">
                    <label for="tac_gia">Tác giả:</label>
                    <input type="text" id="tac_gia" name="tac_gia" value="<?php echo htmlspecialchars($book['tac_gia'] ?? (isset($_POST['tac_gia']) ? $_POST['tac_gia'] : '')); ?>">
                </div>
                <div class="form-group">
                    <label for="nam_xuat_ban">Năm xuất bản:</label>
                    <input type="number" id="nam_xuat_ban" name="nam_xuat_ban" value="<?php echo htmlspecialchars($book['nam_xuat_ban'] ?? (isset($_POST['nam_xuat_ban']) ? $_POST['nam_xuat_ban'] : '')); ?>">
                </div>
                <div class="form-group">
                    <label for="id_theloai">Thể loại:</label>
                    <select id="id_theloai" name="id_theloai">
                        <option value="">Chọn thể loại</option>
                        <?php while ($row = $the_loai_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_theloai']; ?>" <?php echo ($book['id_theloai'] ?? (isset($_POST['id_theloai']) ? $_POST['id_theloai'] : '')) == $row['id_theloai'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['ten_theloai']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="anh_sach">Ảnh sách hiện tại:</label>
                    <?php if ($book['anh_sach'] && file_exists($book['anh_sach'])): ?>
                        <img src="<?php echo htmlspecialchars($book['anh_sach']); ?>" alt="Ảnh sách">
                    <?php else: ?>
                        <p>Không có ảnh</p>
                    <?php endif; ?>
                    <label for="anh_sach">Chọn ảnh mới (nếu muốn thay đổi):</label>
                    <input type="file" id="anh_sach" name="anh_sach" accept="image/*">
                </div>
                <button type="submit" class="btn btn-save">Lưu</button>
                <a href="sach_chung.php" class="btn btn-back">Quay lại</a>
            </form>
        <?php else: ?>
            <p>Không tìm thấy sách.</p>
            <a href="sach_chung.php" class="btn btn-back">Quay lại</a>
        <?php endif; ?>
    </div>
</body>
</html>
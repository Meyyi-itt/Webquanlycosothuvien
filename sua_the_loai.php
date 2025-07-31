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
$id_theloai = isset($_GET['id_theloai']) ? (int)$_GET['id_theloai'] : 0;
$record = null;

if ($id_thuvien > 0 && $id_theloai > 0) {
    $sql = "SELECT tvt.id_thuvien, tvt.id_theloai, tv.ten_thuvien, tl.ten_theloai 
            FROM thu_vien_the_loai tvt 
            JOIN thu_vien tv ON tvt.id_thuvien = tv.id_thuvien 
            JOIN the_loai tl ON tvt.id_theloai = tl.id_theloai 
            WHERE tvt.id_thuvien = ? AND tvt.id_theloai = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_thuvien, $id_theloai);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$record) {
        $message = "Không tìm thấy bản ghi.";
        $message_class = 'error';
    }
}

$sql = "SELECT id_thuvien, ten_thuvien FROM thu_vien";
$thu_vien_result = $conn->query($sql);

$sql = "SELECT id_theloai, ten_theloai FROM the_loai";
$the_loai_result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_thuvien_theloai'])) {
    $id_thuvien_new = (int)$_POST['id_thuvien'];
    $id_theloai_new = (int)$_POST['id_theloai'];
    $id_thuvien_old = (int)$_POST['id_thuvien_old'];
    $id_theloai_old = (int)$_POST['id_theloai_old'];

    if ($id_thuvien_new <= 0 || $id_theloai_new <= 0 || $id_thuvien_old <= 0 || $id_theloai_old <= 0) {
        $message = "Vui lòng chọn thư viện và thể loại.";
        $message_class = 'error';
    } else {
        $sql = "SELECT 1 FROM thu_vien_the_loai WHERE id_thuvien = ? AND id_theloai = ? AND (id_thuvien != ? OR id_theloai != ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $id_thuvien_new, $id_theloai_new, $id_thuvien_old, $id_theloai_old);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $message = "Thể loại này đã tồn tại trong thư viện.";
            $message_class = 'error';
        } else {
            $sql = "DELETE FROM thu_vien_the_loai WHERE id_thuvien = ? AND id_theloai = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_thuvien_old, $id_theloai_old);
            $stmt->execute();
            $sql = "INSERT INTO thu_vien_the_loai (id_thuvien, id_theloai) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_thuvien_new, $id_theloai_new);
            if ($stmt->execute()) {
                $message = "Cập nhật thể loại thành công.";
                $message_class = 'success';
                header('Location: the_loai.php' . ($id_thuvien > 0 ? "?id_thuvien=$id_thuvien" : ''));
                exit;
            } else {
                $message = "Lỗi khi cập nhật thể loại.";
                $message_class = 'error';
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thể loại trong thư viện</title>
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
        <h2>Sửa thể loại trong thư viện</h2>
        <div id="message">
            <?php if ($message): ?>
                <p class="<?php echo htmlspecialchars($message_class); ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($record): ?>
            <form method="post">
                <input type="hidden" name="update_thuvien_theloai" value="1">
                <input type="hidden" name="id_thuvien_old" value="<?php echo htmlspecialchars($id_thuvien); ?>">
                <input type="hidden" name="id_theloai_old" value="<?php echo htmlspecialchars($id_theloai); ?>">
                <div class="form-group">
                    <label for="id_thuvien">Thư viện:</label>
                    <select id="id_thuvien" name="id_thuvien" required>
                        <option value="">Chọn thư viện</option>
                        <?php while ($row = $thu_vien_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_thuvien']; ?>" <?php echo $record['id_thuvien'] == $row['id_thuvien'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['ten_thuvien']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_theloai">Thể loại:</label>
                    <select id="id_theloai" name="id_theloai" required>
                        <option value="">Chọn thể loại</option>
                        <?php while ($row = $the_loai_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_theloai']; ?>" <?php echo $record['id_theloai'] == $row['id_theloai'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['ten_theloai']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-save">Lưu</button>
                <a href="the_loai.php<?php echo $id_thuvien > 0 ? '?id_thuvien=' . $id_thuvien : ''; ?>" class="btn btn-back">Quay lại</a>
            </form>
        <?php else: ?>
            <p>Không tìm thấy bản ghi.</p>
            <a href="the_loai.php<?php echo $id_thuvien > 0 ? '?id_thuvien=' . $id_thuvien : ''; ?>" class="btn btn-back">Quay lại</a>
        <?php endif; ?>
    </div>
</body>
</html>
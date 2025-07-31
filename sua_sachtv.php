<?php
require_once 'config.php';


$message = '';
$message_class = '';
$id_sach = isset($_GET['id_sach']) ? (int)$_GET['id_sach'] : 0;
$id_thuvien = isset($_GET['id_thuvien']) ? (int)$_GET['id_thuvien'] : 0;
$book = null;

if ($id_sach > 0) {
    $sql = "SELECT s.id_sach, s.id_sach_chung, s.id_thuvien, s.id_theloai, s.tongsl, sc.ten_sach, sc.tac_gia, tv.ten_thuvien, tl.ten_theloai 
            FROM sach s 
            JOIN sach_chung sc ON s.id_sach_chung = sc.id_sach_chung 
            JOIN thu_vien tv ON s.id_thuvien = tv.id_thuvien 
            JOIN the_loai tl ON s.id_theloai = tl.id_theloai 
            WHERE s.id_sach = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $message = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
        $message_class = 'error';
    } else {
        $stmt->bind_param("i", $id_sach);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$book) {
            $message = "Không tìm thấy sách với ID: $id_sach";
            $message_class = 'error';
        }
    }
}


$sql = "SELECT id_sach_chung, ten_sach, id_theloai FROM sach_chung";
$sach_chung_result = $conn->query($sql);
if ($sach_chung_result === false) {
    $message = "Lỗi khi lấy danh sách sách chung: " . $conn->error;
    $message_class = 'error';
}

$sql = "SELECT id_thuvien, ten_thuvien FROM thu_vien";
$thu_vien_result = $conn->query($sql);
if ($thu_vien_result === false) {
    $message = "Lỗi khi lấy danh sách thư viện: " . $conn->error;
    $message_class = 'error';
}

$sql = "SELECT id_theloai, ten_theloai FROM the_loai";
$the_loai_result = $conn->query($sql);
if ($the_loai_result === false) {
    $message = "Lỗi khi lấy danh sách thể loại: " . $conn->error;
    $message_class = 'error';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_sach'])) {
    $id_sach_chung = (int)$_POST['id_sach_chung'];
    $id_thuvien = (int)$_POST['id_thuvien'];
    $id_theloai = (int)$_POST['id_theloai'];
    $tongsl = (int)$_POST['tongsl'];

    if ($id_sach_chung <= 0 || $id_thuvien <= 0 || $id_theloai <= 0 || $tongsl < 0 || $id_sach <= 0) {
        $message = "Vui lòng nhập đầy đủ thông tin và đảm bảo số lượng không âm.";
        $message_class = 'error';
    } else {
        $sql = "UPDATE sach SET id_sach_chung = ?, id_thuvien = ?, id_theloai = ?, tongsl = ? WHERE id_sach = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $message = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
            $message_class = 'error';
        } else {
            $stmt->bind_param("iiiii", $id_sach_chung, $id_thuvien, $id_theloai, $tongsl, $id_sach);
            if ($stmt->execute()) {
                $message = "Cập nhật sách thành công.";
                $message_class = 'success';
                header('Location: sachtv.php' . ($id_thuvien > 0 ? "?id_thuvien=$id_thuvien" : ''));
                exit;
            } else {
                $message = "Lỗi khi cập nhật sách: " . $stmt->error;
                $message_class = 'error';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sách trong thư viện</title>
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
        <h2>Sửa sách trong thư viện</h2>
        <div id="message">
            <?php if ($message): ?>
                <p class="<?php echo htmlspecialchars($message_class); ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($book): ?>
            <form method="post">
                <input type="hidden" name="update_sach" value="1">
                <div class="form-group">
                    <label for="id_sach_chung">Tên sách:</label>
                    <select id="id_sach_chung" name="id_sach_chung" required>
                        <option value="">Chọn sách</option>
                        <?php while ($row = $sach_chung_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_sach_chung']; ?>" <?php echo $book['id_sach_chung'] == $row['id_sach_chung'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['ten_sach']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_thuvien">Thư viện:</label>
                    <select id="id_thuvien" name="id_thuvien" required>
                        <option value="">Chọn thư viện</option>
                        <?php while ($row = $thu_vien_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_thuvien']; ?>" <?php echo $book['id_thuvien'] == $row['id_thuvien'] ? 'selected' : ''; ?>>
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
                            <option value="<?php echo $row['id_theloai']; ?>" <?php echo $book['id_theloai'] == $row['id_theloai'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['ten_theloai']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tongsl">Số lượng:</label>
                    <input type="number" id="tongsl" name="tongsl" min="0" value="<?php echo htmlspecialchars($book['tongsl'] ?? (isset($_POST['tongsl']) ? $_POST['tongsl'] : '0')); ?>" required>
                </div>
                <button type="submit" class="btn btn-save">Lưu</button>
                <a href="sachtv.php<?php echo $id_thuvien > 0 ? '?id_thuvien=' . $id_thuvien : ''; ?>" class="btn btn-back">Quay lại</a>
            </form>
        <?php else: ?>
            <p>Không tìm thấy sách.</p>
            <a href="sachtv.php<?php echo $id_thuvien > 0 ? '?id_thuvien=' . $id_thuvien : ''; ?>" class="btn btn-back">Quay lại</a>
        <?php endif; ?>
    </div>
</body>
</html>
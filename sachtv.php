<?php
require_once 'config.php';


$message = '';
$message_class = '';
$id_thuvien = isset($_GET['id_thuvien']) ? (int)$_GET['id_thuvien'] : 0;


$sach_data = [];
$sql = "SELECT s.id_sach, s.id_sach_chung, s.id_thuvien, s.id_theloai, s.tongsl, sc.ten_sach, sc.tac_gia, tv.ten_thuvien, tl.ten_theloai 
        FROM sach s 
        JOIN sach_chung sc ON s.id_sach_chung = sc.id_sach_chung 
        JOIN thu_vien tv ON s.id_thuvien = tv.id_thuvien 
        JOIN the_loai tl ON s.id_theloai = tl.id_theloai";
if ($id_thuvien > 0) {
    $sql .= " WHERE s.id_thuvien = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_thuvien);
} else {
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $sach_data[] = $row;
}
$stmt->close();


$ten_thuvien = '';
if ($id_thuvien > 0) {
    $sql = "SELECT ten_thuvien FROM thu_vien WHERE id_thuvien = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_thuvien);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $ten_thuvien = $result['ten_thuvien'] ?? 'Thư viện';
    $stmt->close();
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sach'])) {
    $id_sach_chung = (int)$_POST['id_sach_chung'];
    $id_thuvien = (int)$_POST['id_thuvien'];
    $id_theloai = (int)$_POST['id_theloai'];
    $tongsl = (int)$_POST['tongsl'];

    if ($id_sach_chung <= 0 || $id_thuvien <= 0 || $id_theloai <= 0 || $tongsl < 0) {
        $message = "Vui lòng nhập đầy đủ thông tin và đảm bảo số lượng không âm.";
        $message_class = 'error';
    } else {
        $sql = "INSERT INTO sach (id_sach_chung, id_thuvien, id_theloai, tongsl) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $message = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
            $message_class = 'error';
        } else {
            $stmt->bind_param("iiii", $id_sach_chung, $id_thuvien, $id_theloai, $tongsl);
            if ($stmt->execute()) {
                $message = "Thêm sách thành công.";
                $message_class = 'success';
                header('Location: sachtv.php' . ($id_thuvien > 0 ? "?id_thuvien=$id_thuvien" : ''));
                exit;
            } else {
                $message = "Lỗi khi thêm sách: " . $stmt->error;
                $message_class = 'error';
            }
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_sach'])) {
    $id_sach = (int)$_POST['delete_id'];

    if ($id_sach <= 0) {
        $message = "Dữ liệu không hợp lệ.";
        $message_class = 'error';
    } else {
        $sql = "DELETE FROM sach WHERE id_sach = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $message = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
            $message_class = 'error';
        } else {
            $stmt->bind_param("i", $id_sach);
            if ($stmt->execute()) {
                $message = "Xóa sách thành công.";
                $message_class = 'success';
                header('Location: sachtv.php' . ($id_thuvien > 0 ? "?id_thuvien=$id_thuvien" : ''));
                exit;
            } else {
                $message = "Lỗi khi xóa sách: " . $stmt->error;
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
    <title>Quản lý sách trong thư viện<?php echo $id_thuvien > 0 ? ' - ' . htmlspecialchars($ten_thuvien) : ''; ?></title>
</head>
<body>
    <div class="admin-nav">
        <a href="thu_vien.php">Thư viện</a>
        <a href="the_loai.php">Thể loại sách trong thư viện</a>
        <a href="sach_chung.php">Sách</a>
        <a href="sachtv.php">Sách trong thư viện</a>
        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>
    <h2>Quản lý sách trong thư viện<?php echo $id_thuvien > 0 ? ' - ' . htmlspecialchars($ten_thuvien) : ''; ?></h2>
    <div id="message">
        <?php if ($message): ?>
            <p class="<?php echo htmlspecialchars($message_class); ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>

    <div class="form-container">
        <h3>Thêm sách mới</h3>
        <form method="post">
            <input type="hidden" name="add_sach" value="1">
            <div class="form-group">
                <label for="id_sach_chung">Tên sách:</label>
                <select id="id_sach_chung" name="id_sach_chung" required>
                    <option value="">Chọn sách</option>
                    <?php while ($row = $sach_chung_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_sach_chung']; ?>" <?php echo isset($_POST['id_sach_chung']) && $_POST['id_sach_chung'] == $row['id_sach_chung'] ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $row['id_thuvien']; ?>" <?php echo $id_thuvien == $row['id_thuvien'] || (isset($_POST['id_thuvien']) && $_POST['id_thuvien'] == $row['id_thuvien']) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $row['id_theloai']; ?>" <?php echo isset($_POST['id_theloai']) && $_POST['id_theloai'] == $row['id_theloai'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['ten_theloai']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="tongsl">Số lượng:</label>
                <input type="number" id="tongsl" name="tongsl" min="0" value="<?php echo isset($_POST['tongsl']) ? htmlspecialchars($_POST['tongsl']) : '0'; ?>" required>
            </div>
            <button type="submit" class="btn btn-save">Thêm sách</button>
        </form>
    </div>

    <table id="sach_table">
        <thead>
            <tr>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Thư viện</th>
                <th>Thể loại</th>
                <th>Số lượng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sach_data)): ?>
                <tr><td colspan="6">Không có sách trong thư viện này.</td></tr>
            <?php else: ?>
                <?php foreach ($sach_data as $sach): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sach['ten_sach']); ?></td>
                        <td><?php echo htmlspecialchars($sach['tac_gia'] ?? 'Không xác định'); ?></td>
                        <td><?php echo htmlspecialchars($sach['ten_thuvien']); ?></td>
                        <td><?php echo htmlspecialchars($sach['ten_theloai']); ?></td>
                        <td><?php echo htmlspecialchars($sach['tongsl']); ?></td>
                        <td>
                            <a href="sua_sachtv.php?id_sach=<?php echo htmlspecialchars($sach['id_sach']); ?>&id_thuvien=<?php echo htmlspecialchars($sach['id_thuvien']); ?>" class="btn btn-edit">Sửa</a>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                                <input type="hidden" name="delete_sach" value="1">
                                <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($sach['id_sach']); ?>">
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
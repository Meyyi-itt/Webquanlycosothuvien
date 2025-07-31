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
$thuvien_theloai_data = [];
$sql = "SELECT tvt.id_thuvien, tvt.id_theloai, tv.ten_thuvien, tl.ten_theloai 
        FROM thu_vien_the_loai tvt 
        JOIN thu_vien tv ON tvt.id_thuvien = tv.id_thuvien 
        JOIN the_loai tl ON tvt.id_theloai = tl.id_theloai";
if ($id_thuvien > 0) {
    $sql .= " WHERE tvt.id_thuvien = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_thuvien);
} else {
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $thuvien_theloai_data[] = $row;
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

$sql = "SELECT id_thuvien, ten_thuvien FROM thu_vien";
$thu_vien_result = $conn->query($sql);

$sql = "SELECT id_theloai, ten_theloai FROM the_loai";
$the_loai_result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_thuvien_theloai'])) {
    $id_thuvien = (int)$_POST['id_thuvien'];
    $id_theloai = (int)$_POST['id_theloai'];

    if ($id_thuvien <= 0 || $id_theloai <= 0) {
        $message = "Vui lòng chọn thư viện và thể loại.";
        $message_class = 'error';
    } else {
        $sql = "SELECT 1 FROM thu_vien_the_loai WHERE id_thuvien = ? AND id_theloai = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_thuvien, $id_theloai);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $message = "Thể loại này đã tồn tại trong thư viện.";
            $message_class = 'error';
        } else {
            $sql = "INSERT INTO thu_vien_the_loai (id_thuvien, id_theloai) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_thuvien, $id_theloai);
            if ($stmt->execute()) {
                $message = "Thêm thể loại thành công.";
                $message_class = 'success';
                header('Location: the_loai.php' . ($id_thuvien > 0 ? "?id_thuvien=$id_thuvien" : ''));
                exit;
            } else {
                $message = "Lỗi khi thêm thể loại.";
                $message_class = 'error';
            }
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_thuvien_theloai'])) {
    $id_thuvien = (int)$_POST['id_thuvien'];
    $id_theloai = (int)$_POST['id_theloai'];

    if ($id_thuvien <= 0 || $id_theloai <= 0) {
        $message = "Dữ liệu không hợp lệ.";
        $message_class = 'error';
    } else {
        $sql = "DELETE FROM thu_vien_the_loai WHERE id_thuvien = ? AND id_theloai = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_thuvien, $id_theloai);
        if ($stmt->execute()) {
            $message = "Xóa thể loại thành công.";
            $message_class = 'success';
            header('Location: the_loai.php' . ($id_thuvien > 0 ? "?id_thuvien=$id_thuvien" : ''));
            exit;
        } else {
            $message = "Lỗi khi xóa thể loại.";
            $message_class = 'error';
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
    <title>Quản lý thể loại trong thư viện<?php echo $id_thuvien > 0 ? ' - ' . htmlspecialchars($ten_thuvien) : ''; ?></title>
</head>
<body>
    <div class="admin-nav">
        <a href="thu_vien.php">Thư viện</a>
        <a href="the_loai.php">Thể loại sách trong thư viện</a>
        <a href="sach_chung.php">Sách</a>
        <a href="sachtv.php">Sách trong thư viện</a>
        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>
    <h2>Quản lý thể loại trong thư viện<?php echo $id_thuvien > 0 ? ' - ' . htmlspecialchars($ten_thuvien) : ''; ?></h2>
    <div id="message">
        <?php if ($message): ?>
            <p class="<?php echo htmlspecialchars($message_class); ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>

    <div class="form-container">
        <h3>Thêm thể loại vào thư viện</h3>
        <form method="post">
            <input type="hidden" name="add_thuvien_theloai" value="1">
            <div class="form-group">
                <label for="id_thuvien">Thư viện:</label>
                <select id="id_thuvien" name="id_thuvien" required>
                    <option value="">Chọn thư viện</option>
                    <?php while ($row = $thu_vien_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_thuvien']; ?>" <?php echo $id_thuvien == $row['id_thuvien'] ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $row['id_theloai']; ?>">
                            <?php echo htmlspecialchars($row['ten_theloai']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-save">Thêm thể loại</button>
        </form>
    </div>

    <table id="thuvien_theloai_table">
        <thead>
            <tr>
                <th>Thư viện</th>
                <th>Thể loại</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($thuvien_theloai_data)): ?>
                <tr><td colspan="3">Không có thể loại trong thư viện.</td></tr>
            <?php else: ?>
                <?php foreach ($thuvien_theloai_data as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['ten_thuvien']); ?></td>
                        <td><?php echo htmlspecialchars($item['ten_theloai']); ?></td>
                        <td>
                            <a href="sua_the_loai.php?id_thuvien=<?php echo htmlspecialchars($item['id_thuvien']); ?>&id_theloai=<?php echo htmlspecialchars($item['id_theloai']); ?>" class="btn btn-edit">Sửa</a>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                                <input type="hidden" name="delete_thuvien_theloai" value="1">
                                <input type="hidden" name="id_thuvien" value="<?php echo htmlspecialchars($item['id_thuvien']); ?>">
                                <input type="hidden" name="id_theloai" value="<?php echo htmlspecialchars($item['id_theloai']); ?>">
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
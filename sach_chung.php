<?php
require_once 'config.php';


$message = '';
$message_class = '';
$upload_dir = './public/images/anhsach';


if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (!is_writable($upload_dir)) {
    $message = "Thư mục $upload_dir không có quyền ghi.";
    $message_class = 'error';
}


$sql = "SELECT sc.*, tl.ten_theloai FROM sach_chung sc LEFT JOIN the_loai tl ON sc.id_theloai = tl.id_theloai";
$result = $conn->query($sql);
if ($result === false) {
    $message = "Lỗi khi lấy danh sách sách: " . $conn->error;
    $message_class = 'error';
}

$sql = "SELECT id_theloai, ten_theloai FROM the_loai";
$the_loai_result = $conn->query($sql);
if ($the_loai_result === false) {
    $message = "Lỗi khi lấy danh sách thể loại: " . $conn->error;
    $message_class = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sach']) && !$message) {
    $ten_sach = trim($_POST['ten_sach'] ?? '');
    $tac_gia = !empty($_POST['tac_gia']) ? trim($_POST['tac_gia']) : null;
    $nam_xuat_ban = !empty($_POST['nam_xuat_ban']) ? (int)$_POST['nam_xuat_ban'] : null;
    $id_theloai = !empty($_POST['id_theloai']) ? (int)$_POST['id_theloai'] : null;
    $anh_sach = null;

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
                if (!move_uploaded_file($_FILES['anh_sach']['tmp_name'], $file_path)) {
                    $message = "Lỗi khi tải lên ảnh.";
                    $message_class = 'error';
                } else {
                    $anh_sach = $file_path;
                }
            }
        }


        if (!$message) {
            $sql = "INSERT INTO sach_chung (ten_sach, tac_gia, nam_xuat_ban, id_theloai, anh_sach) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $message = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
                $message_class = 'error';
            } else {
                $stmt->bind_param("sssis", $ten_sach, $tac_gia, $nam_xuat_ban, $id_theloai, $anh_sach);
                if ($stmt->execute()) {
                    $message = "Thêm sách thành công.";
                    $message_class = 'success';
                    header('Location: sach_chung.php');
                    exit;
                } else {
                    $message = "Lỗi khi thêm sách: " . $stmt->error;
                    $message_class = 'error';
                }
                $stmt->close();
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_sach']) && !$message) {
    $id = (int)$_POST['delete_id'];


    $sql = "SELECT anh_sach FROM sach_chung WHERE id_sach_chung = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $message = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
        $message_class = 'error';
    } else {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        $stmt->close();


        if ($book['anh_sach'] && file_exists($book['anh_sach'])) {
            unlink($book['anh_sach']);
        }


        $sql = "DELETE FROM sach_chung WHERE id_sach_chung = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $message = "Lỗi chuẩn bị câu lệnh SQL: " . $conn->error;
            $message_class = 'error';
        } else {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Xóa sách thành công.";
                $message_class = 'success';
                header('Location: sach_chung.php');
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
    <title>Quản lý sách chung</title>
</head>
<body>
    <div class="admin-nav">
        <a href="thu_vien.php">Thư viện</a>
        <a href="the_loai.php">Thể loại sách trong thư viện</a>
        <a href="sach_chung.php">Sách</a>
        <a href="sachtv.php">Sách trong thư viện</a>
        <a href="logout.php" class="logout">Đăng xuất</a>
    </div>
    <h2>Quản lý sách chung</h2>
    <div id="message">
        <?php if ($message): ?>
            <p class="<?php echo htmlspecialchars($message_class); ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>

    <div class="form-container">
        <h3>Thêm sách mới</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="add_sach" value="1">
            <div class="form-group">
                <label for="ten_sach">Tên sách:</label>
                <input type="text" id="ten_sach" name="ten_sach" value="<?php echo isset($_POST['ten_sach']) ? htmlspecialchars($_POST['ten_sach']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="tac_gia">Tác giả:</label>
                <input type="text" id="tac_gia" name="tac_gia" value="<?php echo isset($_POST['tac_gia']) ? htmlspecialchars($_POST['tac_gia']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="nam_xuat_ban">Năm xuất bản:</label>
                <input type="number" id="nam_xuat_ban" name="nam_xuat_ban" value="<?php echo isset($_POST['nam_xuat_ban']) ? htmlspecialchars($_POST['nam_xuat_ban']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="id_theloai">Thể loại:</label>
                <select id="id_theloai" name="id_theloai">
                    <option value="">Chọn thể loại</option>
                    <?php while ($row = $the_loai_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_theloai']; ?>" <?php echo isset($_POST['id_theloai']) && $_POST['id_theloai'] == $row['id_theloai'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['ten_theloai']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="anh_sach">Ảnh sách:</label>
                <input type="file" id="anh_sach" name="anh_sach" accept="image/*">
            </div>
            <button type="submit" class="btn btn-save">Thêm sách</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Năm xuất bản</th>
                <th>Thể loại</th>
                <th>Ảnh sách</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_sach_chung']); ?></td>
                        <td><?php echo htmlspecialchars($row['ten_sach']); ?></td>
                        <td><?php echo htmlspecialchars($row['tac_gia'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['nam_xuat_ban'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['ten_theloai'] ?? 'Không xác định'); ?></td>
                        <td>
                            <?php if ($row['anh_sach'] && file_exists($row['anh_sach'])): ?>
                                <img src="<?php echo htmlspecialchars($row['anh_sach']); ?>" alt="Ảnh sách">
                            <?php else: ?>
                                Không có ảnh
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="sua_sach_chung.php?id=<?php echo htmlspecialchars($row['id_sach_chung']); ?>" class="btn btn-edit">Sửa</a>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                                <input type="hidden" name="delete_sach" value="1">
                                <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($row['id_sach_chung']); ?>">
                                <button type="submit" class="btn btn-delete">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Không có sách nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
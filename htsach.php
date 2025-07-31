<?php
require_once 'config.php';


$idThuVien = isset($_GET['id_thuvien']) ? (int)$_GET['id_thuvien'] : 0;
if ($idThuVien <= 0) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT ten_thuvien FROM thu_vien WHERE id_thuvien = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idThuVien);
$stmt->execute();
$thuVien = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$thuVien) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT s.id_sach, sc.ten_sach, sc.tac_gia, sc.nam_xuat_ban, sc.anh_sach, s.tongsl, tl.ten_theloai 
        FROM sach s 
        JOIN sach_chung sc ON s.id_sach_chung = sc.id_sach_chung 
        JOIN the_loai tl ON s.id_theloai = tl.id_theloai 
        WHERE s.id_thuvien = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idThuVien);
$stmt->execute();
$danhSachSach = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./public/map.css">
    <title>Danh sach sach - <?php echo htmlspecialchars($thuVien['ten_thuvien']); ?></title>
</head>
<body>
    <div class="danh-sach-sach">
        <a href="popup.php?id_thuvien=<?php echo $idThuVien; ?>" class="nut-quay-lai">Quay lai</a>
        <h3>Danh sach sach - <?php echo htmlspecialchars($thuVien['ten_thuvien']); ?></h3>
        <?php if (!empty($danhSachSach)): ?>
            <div class="danh-sach-sach-grid">
                <?php foreach ($danhSachSach as $sach): ?>
                    <div class="sach-moi">
                        <?php if ($sach['anh_sach'] && file_exists($sach['anh_sach'])): ?>
                            <img src="<?php echo htmlspecialchars($sach['anh_sach']); ?>" alt="Anh sach">
                        <?php else: ?>
                            <div class="khong-anh">Khong co anh</div>
                        <?php endif; ?>
                        <div class="thong-tin-sach">
                            <p><strong>Ten sach:</strong> <?php echo htmlspecialchars($sach['ten_sach'] ?? 'Khong xac dinh'); ?></p>
                            <p><strong>The loai:</strong> <?php echo htmlspecialchars($sach['ten_theloai'] ?? 'Khong xac dinh'); ?></p>
                            <p><strong>Tac gia:</strong> <?php echo htmlspecialchars($sach['tac_gia'] ?? 'Khong xac dinh'); ?></p>
                            <p><strong>So luong:</strong> <?php echo htmlspecialchars($sach['tongsl'] ?? 'Khong xac dinh'); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Khong co sach trong thu vien nay.</p>
        <?php endif; ?>
    </div>
</body>
</html>

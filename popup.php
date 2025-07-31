<?php
require_once 'data.php';
require_once 'config.php';


$idThuVien = isset($_GET['id_thuvien']) ? (int)$_GET['id_thuvien'] : 0;
if ($idThuVien <= 0) {
    header("Location: index.php");
    exit;
}


$duLieuThuVien = getLibraryData($conn);
$thuVien = null;
foreach ($duLieuThuVien as $item) {
    if ($item['properties']['ID'] == $idThuVien) {
        $thuVien = $item;
        break;
    }
}

if (!$thuVien) {
    header("Location: index.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <link rel="stylesheet" href="./public/map.css">
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <title>Chi tiet thu vien - <?php echo htmlspecialchars($thuVien['properties']['TenThuVien']); ?></title>
</head>
<body>
    <div class="chi-tiet-thu-vien">
        <a href="index.php" class="nut-quay-lai">Quay lai ban do</a>
        <a href="htsach.php?id_thuvien=<?php echo $idThuVien; ?>" class="nut-xem-sach">Xem sach</a>
        <h3><?php echo htmlspecialchars($thuVien['properties']['TenThuVien']); ?></h3>
        <p><strong>Dia chi:</strong> <?php echo htmlspecialchars($thuVien['properties']['DiaChi']); ?></p>
        <p><strong>Gio mo cua:</strong> <?php echo htmlspecialchars($thuVien['properties']['GioMoCua'] ?? 'Khong xac dinh'); ?></p>
        <p><strong>Gio dong cua:</strong> <?php echo htmlspecialchars($thuVien['properties']['GioDongCua'] ?? 'Khong xac dinh'); ?></p>
        <p><strong>Wifi:</strong> <?php echo $thuVien['properties']['Wifi'] ? 'Co' : 'Khong'; ?></p>
        <p><strong>Phong doc:</strong> <?php echo $thuVien['properties']['PhongDoc'] ? 'Co' : 'Khong'; ?></p>
        <p><strong>Canteen:</strong> <?php echo $thuVien['properties']['Canteen'] ? 'Co' : 'Khong'; ?></p>
        <p><strong>Dieu hoa:</strong> <?php echo $thuVien['properties']['DieuHoa'] ? 'Co' : 'Khong'; ?></p>
        <?php if ($thuVien['properties']['Anh360']): ?>
            <h4>Anh 360:</h4>
            <div id="panorama" class="panorama-container"></div>
        <?php else: ?>
            <p>Khong co anh 360.</p>
        <?php endif; ?>
    </div>

    <?php if ($thuVien['properties']['Anh360']): ?>
        <script>
            pannellum.viewer('panorama', {
                type: "equirectangular",
                panorama: "<?php echo htmlspecialchars($thuVien['properties']['Anh360']); ?>",
                autoLoad: true,
                showControls: true,
                compass: false
            });
        </script>
    <?php endif; ?>
</body>
</html>

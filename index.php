<?php
require_once 'data.php';

$duLieuThuVien = getLibraryData($conn);
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="./public/map.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <title>Quan Ly Thu Vien</title>
</head>
<body>
    <div id="thanh-tim-kiem">
        <input type="text" id="tim-kiem-chinh" placeholder="Tim thu vien...">
        <button id="nut-bo-loc">Bo loc</button>
        <button id="nut-tim-kiem">Tim</button>
        <a href="login.php"><button>Dang nhap</button></a>
        <div id="bo-loc-modal" style="display: none;">
            <h3>Bo loc</h3>
            <label><input type="checkbox" id="loc-wifi"> Wifi</label><br>
            <label><input type="checkbox" id="loc-phong-doc"> Phong doc</label><br>
            <label><input type="checkbox" id="loc-canteen"> Canteen</label><br>
            <label><input type="checkbox" id="loc-dieu-hoa"> Dieu hoa</label><br>
            <input type="text" id="loc-the-loai" placeholder="The loai sach"><br>
            <input type="text" id="loc-ten-sach" placeholder="Ten sach"><br>
            <button id="ap-dung-bo-loc">Ap dung</button>
            <button id="dong-bo-loc">Dong</button>
        </div>
    </div>
    <div id="ban-do"></div>
    <div id="lop-phu-modal" style="display: none;"></div>

    <script>

        const banDo = L.map('ban-do').setView([21.0285, 105.8542], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(banDo);


        const bieuTuongThuVien = L.icon({
            iconUrl: './public/images/igel.png',
            iconSize: [60, 60],
            iconAnchor: [30, 60],
            popupAnchor: [0, -50]
        });


        const duLieuThuVien = <?php echo json_encode($duLieuThuVien); ?>;


        function hienThiDiemDanhDau(duLieu) {
            banDo.eachLayer(lop => {
                if (lop instanceof L.Marker) banDo.removeLayer(lop);
            });
            duLieu.forEach(thuVien => {
                const toaDo = thuVien.geometry.coordinates;
                if (toaDo && toaDo.length === 2 && !isNaN(toaDo[0]) && !isNaN(toaDo[1])) {
                    L.marker([toaDo[1], toaDo[0]], { icon: bieuTuongThuVien })
                        .addTo(banDo)
                        .on('click', () => {
                            window.location.href = `popup.php?id_thuvien=${thuVien.properties.ID}`;
                        });
                }
            });
        }


        document.getElementById('nut-tim-kiem').addEventListener('click', () => {
            const giaTriTimKiem = document.getElementById('tim-kiem-chinh').value.toLowerCase();
            const duLieuLoc = duLieuThuVien.filter(thuVien =>
                thuVien.properties.TenThuVien.toLowerCase().includes(giaTriTimKiem)
            );
            hienThiDiemDanhDau(duLieuLoc);
        });


        document.getElementById('nut-bo-loc').addEventListener('click', () => {
            document.getElementById('bo-loc-modal').style.display = 'block';
            document.getElementById('lop-phu-modal').style.display = 'block';
        });


        document.getElementById('dong-bo-loc').addEventListener('click', () => {
            document.getElementById('bo-loc-modal').style.display = 'none';
            document.getElementById('lop-phu-modal').style.display = 'none';
        });

        document.getElementById('ap-dung-bo-loc').addEventListener('click', () => {
            const boLoc = {
                wifi: document.getElementById('loc-wifi').checked,
                phongDoc: document.getElementById('loc-phong-doc').checked,
                canteen: document.getElementById('loc-canteen').checked,
                dieuHoa: document.getElementById('loc-dieu-hoa').checked,
                theLoai: document.getElementById('loc-the-loai').value.toLowerCase(),
                tenSach: document.getElementById('loc-ten-sach').value.toLowerCase()
            };

            const duLieuLoc = duLieuThuVien.filter(thuVien => {
                const thuocTinh = thuVien.properties;
                return (!boLoc.wifi || thuocTinh.Wifi) &&
                       (!boLoc.phongDoc || thuocTinh.PhongDoc) &&
                       (!boLoc.canteen || thuocTinh.Canteen) &&
                       (!boLoc.dieuHoa || thuocTinh.DieuHoa) &&
                       (!boLoc.theLoai || thuocTinh.Sachs.some(sach =>
                           sach.TheLoai.toLowerCase().includes(boLoc.theLoai))) &&
                       (!boLoc.tenSach || thuocTinh.Sachs.some(sach =>
                           sach.TenSach.toLowerCase().includes(boLoc.tenSach)));
            });

            hienThiDiemDanhDau(duLieuLoc);
            document.getElementById('bo-loc-modal').style.display = 'none';
            document.getElementById('lop-phu-modal').style.display = 'none';
        });

        hienThiDiemDanhDau(duLieuThuVien);
    </script>
</body>
</html>

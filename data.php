<?php
require_once 'config.php';

function getLibraryData($conn)
{
    $sql = "SELECT 
                tv.id_thuvien AS ID, 
                tv.ten_thuvien AS TenThuVien, 
                tv.dia_chi AS DiaChi, 
                tv.wifi AS Wifi, 
                tv.phongdoc AS PhongDoc, 
                tv.canteen AS Canteen, 
                tv.dieuhoa AS DieuHoa, 
                tv.latitude AS Latitude, 
                tv.longitude AS Longitude, 
                tv.anh_360 AS Anh360,
                tv.gio_mo_cua AS GioMoCua,
                tv.gio_dong_cua AS GioDongCua
            FROM thu_vien tv
            LEFT JOIN thu_vien_the_loai tvt ON tv.id_thuvien = tvt.id_thuvien
            LEFT JOIN the_loai tl ON tvt.id_theloai = tl.id_theloai
            GROUP BY tv.id_thuvien";
    $result = $conn->query($sql);

    if ($result->num_rows === 0) {
        return [];
    }

    $libraryData = [];
    while ($row = $result->fetch_assoc()) {
        $id = $row['ID'];
        if (!isset($libraryData[$id])) {
            $libraryData[$id] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$row['Longitude'], (float)$row['Latitude']]
                ],
                'properties' => [
                    'ID' => $id,
                    'TenThuVien' => $row['TenThuVien'],
                    'DiaChi' => $row['DiaChi'],
                    'Wifi' => (bool)$row['Wifi'],
                    'PhongDoc' => (bool)$row['PhongDoc'],
                    'Canteen' => (bool)$row['Canteen'],
                    'DieuHoa' => (bool)$row['DieuHoa'],
                    'Anh360' => $row['Anh360'],
                    'GioMoCua' => $row['GioMoCua'],
                    'GioDongCua' => $row['GioDongCua'],
                    'Sachs' => []
                ]
            ];
        }
        $books_sql = "SELECT sc.ten_sach AS TenSach, sc.tac_gia AS TacGia, sc.nam_xuat_ban AS NamXuatBan, 
                             tl.ten_theloai AS TheLoai, s.tongsl AS SlTon
                      FROM sach s
                      JOIN sach_chung sc ON s.id_sach_chung = sc.id_sach_chung
                      JOIN the_loai tl ON s.id_theloai = tl.id_theloai
                      WHERE s.id_thuvien = ?";
        $stmt = $conn->prepare($books_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $books_result = $stmt->get_result();
        while ($book = $books_result->fetch_assoc()) {
            $libraryData[$id]['properties']['Sachs'][] = $book;
        }
        $stmt->close();
    }
    return array_values($libraryData);
}

<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';

if (!empty($nim)) {
    $stmt = $conn->prepare("
        SELECT 
            k.kode_matkul,
            m.nama_mk AS mata_kuliah,
            COUNT(CASE WHEN k.status = 'Hadir' THEN 1 END) AS hadir,
            COUNT(CASE WHEN k.status = 'Izin' THEN 1 END) AS izin,
            COUNT(CASE WHEN k.status = 'Sakit' THEN 1 END) AS sakit
        FROM kehadiran k
        JOIN mata_kuliah m ON k.kode_matkul = m.kode_mk
        WHERE k.nim = ?
        GROUP BY k.kode_matkul
        ORDER BY m.nama_mk ASC
    ");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $data]);
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan.']);
}

$conn->close();
?>
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';
$kode_mk = $_GET['kode_mk'] ?? '';

if (!empty($nim) && !empty($kode_mk)) {
    $stmt = $conn->prepare("SELECT k.pertemuan, k.tanggal, k.status, jk.dosen FROM kehadiran k JOIN jadwal_kuliah jk ON k.nim = jk.nim AND k.kode_matkul = jk.kode_matkul WHERE k.nim = ? AND k.kode_matkul = ? ORDER BY k.tanggal ASC");
    $stmt->bind_param("ss", $nim, $kode_mk);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $data]);
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'NIM atau kode mata kuliah tidak diberikan.']);
}

$conn->close();
?>
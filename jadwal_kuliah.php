<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';
$semester = $_GET['semester'] ?? '';

if (!empty($nim) && !empty($semester)) {
    $stmt = $conn->prepare("SELECT hari, jam_mulai, jam_selesai, ruang, kode_matkul, matkul, dosen, jenis, kelas, goggle_classroom_id FROM jadwal_kuliah WHERE nim = ? ORDER BY hari, jam_mulai ASC");
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
    echo json_encode(['status' => 'error', 'message' => 'NIM atau semester tidak diberikan.']);
}

$conn->close();
?>
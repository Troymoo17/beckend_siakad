<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';

if (!empty($nim)) {
    $stmt = $conn->prepare("SELECT n.kode_mk, m.nama_mk, n.grade, n.bobot, n.sks, n.bobot_sks FROM nilai n JOIN mata_kuliah m ON n.kode_mk = m.kode_mk WHERE n.nim = ? ORDER BY n.semester, n.kode_mk ASC");
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
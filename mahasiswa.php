<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';

if (!empty($nim)) {
    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    if ($result->num_rows > 0) {
        $data[] = $result->fetch_assoc();
    }
    
    echo json_encode(['status' => 'success', 'data' => $data]);
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan.']);
}

$conn->close();
?>
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $nim = $_GET['nim'] ?? '';
    if (empty($nim)) {
        echo json_encode(['status' => 'error', 'message' => 'NIM tidak boleh kosong.']);
        exit;
    }

    $sql = "SELECT tanggal, bab, pembimbing, uraian, status FROM bimbingan_skripsi WHERE nim = ? ORDER BY tanggal DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    $stmt->close();

} elseif ($method === 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode POST tidak diizinkan untuk endpoint ini.']);
}

$conn->close();
?>
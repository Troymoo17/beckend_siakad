<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $nim = $_GET['nim'] ?? '';

    if (empty($nim)) {
        echo json_encode(['status' => 'error', 'message' => 'NIM tidak ditemukan.']);
        $conn->close();
        exit();
    }

    $sql = "SELECT id, tanggal_pinjam, tanggal_kembali, nama_buku, status_pinjaman FROM pinjaman WHERE nim = ? ORDER BY tanggal_pinjam DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
}

$conn->close();
?>
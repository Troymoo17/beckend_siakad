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

    $sql = "SELECT id, tanggal, nama_kegiatan, poin, keterangan FROM point_book WHERE nim = ? ORDER BY tanggal DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    $total_poin = 0;
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
        $total_poin += $row['poin'];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'total_poin' => $total_poin
    ]);
}

$conn->close();
?>
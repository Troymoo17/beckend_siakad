<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;

    if ($id) {
        $sql = "SELECT * FROM pengumuman WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        $stmt->close();
    } else {
        $tgl_tiga_bulan_lalu = date('Y-m-d', strtotime('-3 months'));
        $sql = "SELECT id, judul, tanggal_upload FROM pengumuman WHERE tanggal_upload >= ? ORDER BY tanggal_upload DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tgl_tiga_bulan_lalu);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        $stmt->close();
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
}

$conn->close();
?>
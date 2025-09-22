<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT id, keterangan, link_download FROM download_materi ORDER BY id ASC";
    $result = $conn->query($sql);

    $data = [];
    $no = 1;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'no' => $no++,
                'keterangan' => $row['keterangan'],
                'link_download' => $row['link_download']
            ];
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'success', 'data' => []]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
}

$conn->close();
?>
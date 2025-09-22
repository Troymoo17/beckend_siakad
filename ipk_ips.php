<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';

if (!empty($nim)) {
    $stmt_khs = $conn->prepare("SELECT * FROM khs WHERE nim = ? ORDER BY semester ASC");
    $stmt_khs->bind_param("s", $nim);
    $stmt_khs->execute();
    $result_khs = $stmt_khs->get_result();
    $ips_data = [];
    $total_sks_kumulatif = 0;
    $total_bobot_sks_kumulatif = 0;

    while ($row = $result_khs->fetch_assoc()) {
        $ips_data[] = [
            'semester' => $row['semester'],
            'tahun_akademik' => $row['tahun_akademik'],
            'total_sks' => (int)$row['total_sks'],
            'ips' => (float)$row['ips']
        ];
        $total_sks_kumulatif += (int)$row['total_sks'];
        $total_bobot_sks_kumulatif += ((float)$row['ips'] * (int)$row['total_sks']);
    }
    $stmt_khs->close();

    $ipk = ($total_sks_kumulatif > 0) ? round($total_bobot_sks_kumulatif / $total_sks_kumulatif, 2) : 0.00;

    echo json_encode([
        'status' => 'success',
        'data' => [
            'ipk' => number_format($ipk, 2),
            'ips_per_semester' => $ips_data
        ]
    ]);

} else {
    echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan.']);
}

$conn->close();
?>
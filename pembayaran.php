<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $nim = $_GET['nim'] ?? '';

    if (empty($nim)) {
        echo json_encode(['status' => 'error', 'message' => 'NIM tidak ditemukan.']);
        $conn->close();
        exit();
    }

    // Mengambil data mahasiswa (untuk virtual account) dan tagihan
    $sql = "SELECT m.virtual_account, t.* FROM mahasiswa m
            LEFT JOIN tagihan t ON m.nim = t.nim
            WHERE m.nim = ?
            ORDER BY t.semester ASC, t.tanggal_pembayaran ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    $virtual_account = null;

    if ($result->num_rows > 0) {
        $tagihan_by_semester = [];
        while ($row = $result->fetch_assoc()) {
            if ($virtual_account === null) {
                $virtual_account = $row['virtual_account'];
            }
            if ($row['semester'] !== null) {
                $semester = $row['semester'];
                if (!isset($tagihan_by_semester[$semester])) {
                    $tagihan_by_semester[$semester] = [
                        'semester' => $semester,
                        'tagihan' => 0,
                        'dibayar' => 0,
                        'sisa_tagihan' => 0,
                        'rincian' => [],
                        'pembayaran' => []
                    ];
                }

                if ($row['jenis_tagihan'] === 'UKP' || $row['jenis_tagihan'] === 'SKS') {
                    $tagihan_by_semester[$semester]['rincian'][] = [
                        'jenis' => $row['jenis_tagihan'],
                        'deskripsi' => $row['deskripsi'],
                        'nominal' => (float) $row['nominal_tagihan']
                    ];
                    $tagihan_by_semester[$semester]['tagihan'] += (float) $row['nominal_tagihan'];
                } elseif ($row['jenis_tagihan'] === 'Pembayaran') {
                    $tagihan_by_semester[$semester]['pembayaran'][] = [
                        'tanggal' => $row['tanggal_pembayaran'],
                        'nominal' => (float) $row['jumlah_dibayar']
                    ];
                    $tagihan_by_semester[$semester]['dibayar'] += (float) $row['jumlah_dibayar'];
                }
            }
        }

        foreach ($tagihan_by_semester as $semester => $data) {
            $tagihan_by_semester[$semester]['sisa_tagihan'] = $data['tagihan'] - $data['dibayar'];
        }
        
        $data = array_values($tagihan_by_semester);
    }

    echo json_encode([
        'status' => 'success',
        'virtual_account' => $virtual_account,
        'data' => $data
    ]);
}

$conn->close();
?>
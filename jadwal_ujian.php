<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';

if (!empty($nim)) {
    // Ambil jadwal UTS
    $stmt_uts = $conn->prepare("SELECT tanggal, hari, mulai, selesai, ruangan, mata_kuliah, kelas, dosen, no_kursi, soal FROM jadwal_ujian WHERE nim = ? AND jenis_ujian = 'UTS' ORDER BY tanggal ASC");
    $stmt_uts->bind_param("s", $nim);
    $stmt_uts->execute();
    $result_uts = $stmt_uts->get_result();
    $uts_data = [];
    while ($row = $result_uts->fetch_assoc()) {
        $uts_data[] = $row;
    }
    $stmt_uts->close();

    // Ambil jadwal UAS
    $stmt_uas = $conn->prepare("SELECT tanggal, hari, mulai, selesai, ruangan, mata_kuliah, kelas, dosen, no_kursi, soal FROM jadwal_ujian WHERE nim = ? AND jenis_ujian = 'UAS' ORDER BY tanggal ASC");
    $stmt_uas->bind_param("s", $nim);
    $stmt_uas->execute();
    $result_uas = $stmt_uas->get_result();
    $uas_data = [];
    while ($row = $result_uas->fetch_assoc()) {
        $uas_data[] = $row;
    }
    $stmt_uas->close();

    echo json_encode([
        'status' => 'success',
        'uts' => $uts_data,
        'uas' => $uas_data
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan.']);
}

$conn->close();
?>
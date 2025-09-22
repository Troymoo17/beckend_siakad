<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';

if (!empty($nim)) {
    $stmt_mhs = $conn->prepare("SELECT semester_sekarang FROM mahasiswa WHERE nim = ?");
    $stmt_mhs->bind_param("s", $nim);
    $stmt_mhs->execute();
    $result_mhs = $stmt_mhs->get_result();
    $mahasiswa = $result_mhs->fetch_assoc();
    $semester_sekarang = $mahasiswa['semester_sekarang'] ?? null;
    $stmt_mhs->close();

    if ($semester_sekarang) {
        $stmt = $conn->prepare("
            SELECT
                t2.kode_mk,
                t2.nama_mk AS mata_kuliah,
                COUNT(CASE WHEN t1.status = 'Hadir' THEN 1 END) AS hadir,
                COUNT(CASE WHEN t1.status = 'Izin' THEN 1 END) AS izin,
                COUNT(CASE WHEN t1.status = 'Sakit' THEN 1 END) AS sakit,
                COUNT(CASE WHEN t1.status = 'Tidak Hadir' THEN 1 END) AS alfa
            FROM kehadiran t1
            JOIN mata_kuliah t2 ON t1.kode_matkul = t2.kode_mk
            JOIN jadwal_kuliah t3 ON t1.kode_matkul = t3.kode_matkul AND t1.nim = t3.nim
            WHERE t1.nim = ? 
            GROUP BY t2.kode_mk
            ORDER BY t2.nama_mk ASC
        ");
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
        echo json_encode(['status' => 'error', 'message' => 'Data semester tidak ditemukan untuk NIM ini.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan.']);
}

$conn->close();
?>
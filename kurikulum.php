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
                k.kode_mk,
                m.nama_mk,
                m.sks,
                k.status,
                k.ipk_min,
                k.sks_min,
                k.grade_min,
                k.mk_persyaratan
            FROM kurikulum k
            JOIN mata_kuliah m ON k.kode_mk = m.kode_mk
            WHERE k.semester = ?
            ORDER BY m.nama_mk ASC
        ");
        $stmt->bind_param("i", $semester_sekarang);
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
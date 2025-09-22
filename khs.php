<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';

if (!empty($nim)) {
    $query = "
        SELECT
            h.semester,
            h.total_sks,
            h.ips,
            d.kode_mk,
            m.nama_mk,
            d.grade,
            d.bobot,
            d.sks,
            d.bobot_sks
        FROM khs h
        JOIN khs_detail d ON h.nim = d.nim AND h.semester = d.semester
        JOIN mata_kuliah m ON d.kode_mk = m.kode_mk
        WHERE h.nim = ?
        ORDER BY h.semester ASC, d.kode_mk ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $khs_data = [];
    while ($row = $result->fetch_assoc()) {
        $semester = $row['semester'];
        if (!isset($khs_data[$semester])) {
            $khs_data[$semester] = [
                'semester' => (int)$semester,
                'total_sks' => (int)$row['total_sks'],
                'ips' => (float)$row['ips'],
                'mata_kuliah' => []
            ];
        }
        $khs_data[$semester]['mata_kuliah'][] = [
            'kode_mk' => $row['kode_mk'],
            'nama_mk' => $row['nama_mk'],
            'grade' => $row['grade'],
            'bobot' => (float)$row['bobot'],
            'sks' => (int)$row['sks'],
            'bobot_sks' => (float)$row['bobot_sks']
        ];
    }
    
    $stmt_profile = $conn->prepare("SELECT prodi, program FROM mahasiswa WHERE nim = ?");
    $stmt_profile->bind_param("s", $nim);
    $stmt_profile->execute();
    $profile_result = $stmt_profile->get_result()->fetch_assoc();
    $stmt_profile->close();
    
    echo json_encode([
        'status' => 'success',
        'program_studi' => $profile_result['prodi'] ?? 'Tidak tersedia',
        'jenjang_studi' => $profile_result['program'] ?? 'Tidak tersedia',
        'data' => array_values($khs_data)
    ]);

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan.']);
}

$conn->close();
?>
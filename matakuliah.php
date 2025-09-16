<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';

if (!empty($nim)) {
    $query = "
        SELECT 
            m.nama,
            m.prodi,
            m.program,
            m.semester_sekarang,
            jk.kode_matkul,
            jk.matkul AS nama_mk,
            jk.kelas,
            mk.sks
        FROM mahasiswa m
        JOIN jadwal_kuliah jk ON m.nim = jk.nim
        JOIN mata_kuliah mk ON jk.kode_matkul = mk.kode_mk
        WHERE m.nim = ?
        ORDER BY jk.kode_matkul ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    $mahasiswa = null;
    $matakuliah = [];
    
    while ($row = $result->fetch_assoc()) {
        if ($mahasiswa === null) {
            $mahasiswa = [
                'nim' => $nim,
                'nama' => $row['nama'],
                'prodi' => $row['prodi'],
                'program' => $row['program'],
                'semester_sekarang' => $row['semester_sekarang']
            ];
        }
        $matakuliah[] = [
            'kode_mk' => $row['kode_matkul'],
            'nama_mk' => $row['nama_mk'],
            'sks' => $row['sks'],
            'kelas' => $row['kelas']
        ];
    }
    
    $stmt->close();
    
    if ($mahasiswa !== null) {
        echo json_encode([
            'status' => 'success',
            'mahasiswa' => $mahasiswa,
            'matakuliah' => $matakuliah
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data mahasiswa atau mata kuliah tidak ditemukan.']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan.']);
}

$conn->close();
?>
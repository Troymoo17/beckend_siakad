<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

$nim = $_GET['nim'] ?? '';
$semester_sekarang = $_GET['semester'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($nim) && !empty($semester_sekarang)) {
        $query = "SELECT kode_mk, nama_mk, sks FROM mata_kuliah WHERE semester = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $semester_sekarang);
        $stmt->execute();
        $result = $stmt->get_result();
        $mata_kuliah_tersedia = [];
        while ($row = $result->fetch_assoc()) {
            $mata_kuliah_tersedia[] = $row;
        }

        $query_krs = "SELECT kode_mk FROM krs WHERE nim = ? AND semester = ?";
        $stmt_krs = $conn->prepare($query_krs);
        $stmt_krs->bind_param("si", $nim, $semester_sekarang);
        $stmt_krs->execute();
        $result_krs = $stmt_krs->get_result();
        $krs_terisi = [];
        while ($row = $result_krs->fetch_assoc()) {
            $krs_terisi[] = $row['kode_mk'];
        }

        echo json_encode([
            'status' => 'success',
            'mata_kuliah_tersedia' => $mata_kuliah_tersedia,
            'krs_terisi' => $krs_terisi
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'NIM atau semester tidak ditemukan.']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = $_POST['nim'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $kode_mk = $_POST['kode_mk'] ?? '';

    if (!empty($nim) && !empty($semester) && !empty($kode_mk)) {
        $stmt = $conn->prepare("INSERT INTO krs (nim, semester, kode_mk) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $nim, $semester, $kode_mk);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Mata kuliah berhasil ditambahkan ke KRS.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan mata kuliah: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    }
}

$conn->close();
?>
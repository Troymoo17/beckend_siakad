<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    $nim = $_GET['nim'] ?? '';
    if (empty($nim)) {
        echo json_encode(['status' => 'error', 'message' => 'NIM tidak boleh kosong']);
        exit;
    }

    $sql = "SELECT id, judul, abstrak, jalur, tgl_pengajuan, status, tgl_proses, komentar_prodi, cetak_form FROM skripsi_pengajuan WHERE nim = ? ORDER BY tgl_pengajuan DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $data]);

} elseif ($method == 'POST') {
    $nim = $_POST['nim'] ?? '';
    $judul = $_POST['judul'] ?? '';
    $abstrak = $_POST['abstrak'] ?? '';
    $jalur = $_POST['jalur'] ?? '';
    $baru_ulang = $_POST['baru_ulang'] ?? '';
    $tgl_pengajuan = date('Y-m-d');
    
    // Asumsi pembimbing diisi dari backend setelah pengajuan disetujui, jadi biarkan null saat POST
    $pembimbing = null;
    $cetak_form = null;

    if (empty($nim) || empty($judul) || empty($abstrak) || empty($jalur) || empty($baru_ulang)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi.']);
        exit;
    }

    $sql = "INSERT INTO skripsi_pengajuan (nim, judul, abstrak, jalur, baru_ulang, tgl_pengajuan, pembimbing, cetak_form) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $nim, $judul, $abstrak, $jalur, $baru_ulang, $tgl_pengajuan, $pembimbing, $cetak_form);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pengajuan judul skripsi berhasil.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pengajuan: ' . $conn->error]);
    }

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
}

$conn->close();
?>
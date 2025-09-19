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

    // Ambil data pengajuan judul yang disetujui terbaru
    $sql_judul = "SELECT judul, pembimbing FROM skripsi_pengajuan WHERE nim = ? AND status = 'Disetujui' ORDER BY tgl_proses DESC LIMIT 1";
    $stmt_judul = $conn->prepare($sql_judul);
    $stmt_judul->bind_param("s", $nim);
    $stmt_judul->execute();
    $result_judul = $stmt_judul->get_result();
    $judul_data = $result_judul->fetch_assoc();

    // Ambil data IPK dan SKS dari tabel nilai (contoh: nilai kumulatif)
    // Ini adalah placeholder, Anda harus mengimplementasikan logika ini sesuai dengan struktur database Anda
    $ipk_sks_data = [
        'ipk_terakhir' => 3.43,
        'jumlah_sks' => 130
    ];

    echo json_encode([
        'status' => 'success',
        'judul_disetujui' => $judul_data,
        'ipk_sks' => $ipk_sks_data
    ]);

} elseif ($method == 'POST') {
    $nim = $_POST['nim'] ?? '';
    $judul_skripsi = $_POST['judul_skripsi'] ?? '';
    $pembimbing1 = $_POST['pembimbing1'] ?? '';
    $pembimbing2 = $_POST['pembimbing2'] ?? '';
    $ipk_terakhir = $_POST['ipk_terakhir'] ?? 0.0;
    $jumlah_sks = $_POST['jumlah_sks'] ?? 0;
    $sertifikasi = isset($_POST['sertifikasi']) ? implode(', ', $_POST['sertifikasi']) : '';
    $tgl_pengajuan = date('Y-m-d');
    
    if (empty($nim) || empty($judul_skripsi) || empty($pembimbing1)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $sql = "INSERT INTO skripsi_ujian (nim, judul_skripsi, pembimbing1, pembimbing2, ipk_terakhir, jumlah_sks, sertifikasi, tgl_pengajuan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdidss", $nim, $judul_skripsi, $pembimbing1, $pembimbing2, $ipk_terakhir, $jumlah_sks, $sertifikasi, $tgl_pengajuan);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pengajuan ujian skripsi berhasil.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pengajuan: ' . $conn->error]);
    }

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
}

$conn->close();
?>
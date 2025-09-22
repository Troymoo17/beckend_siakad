<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = $_POST['nim'] ?? '';
    $jenis_tempat_magang = $_POST['jenis_tempat_magang'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $nama_tempat_magang = $_POST['nama_tempat_magang'] ?? '';
    $kota_kabupaten_magang = $_POST['kota_kabupaten_magang'] ?? '';
    $baru_ulang = $_POST['baru_ulang'] ?? '';
    $rencana_mulai = $_POST['rencana_mulai'] ?? '';
    $rencana_selesai = $_POST['rencana_selesai'] ?? '';
    $tgl_pengajuan = date('d-m-Y');

    if (empty($nim) || empty($jenis_tempat_magang) || empty($nama_tempat_magang) || empty($rencana_mulai) || empty($rencana_selesai)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        $conn->close();
        exit();
    }

    $sql = "INSERT INTO pengajuan_magang (nim, jenis_tempat_magang, alamat, nama_tempat_magang, kota_kabupaten_magang, baru_ulang, rencana_mulai, rencana_selesai, tgl_pengajuan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $nim, $jenis_tempat_magang, $alamat, $nama_tempat_magang, $kota_kabupaten_magang, $baru_ulang, $rencana_mulai, $rencana_selesai, $tgl_pengajuan);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pengajuan magang berhasil dikirim.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim pengajuan: ' . $stmt->error]);
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $nim = $_GET['nim'] ?? '';
    if (empty($nim)) {
        echo json_encode(['status' => 'error', 'message' => 'NIM tidak ditemukan.']);
        $conn->close();
        exit();
    }

    $sql = "SELECT id, jenis_tempat_magang, alamat, nama_tempat_magang, kota_kabupaten_magang, rencana_mulai, rencana_selesai, tgl_pengajuan, status_magang, tgl_proses, komentar_prodi, surat_pengantar FROM pengajuan_magang WHERE nim = ? ORDER BY tgl_pengajuan DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid.']);
}

$conn->close();
?>
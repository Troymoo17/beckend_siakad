<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $nim = $_POST['nim'] ?? '';
    $judul_dokumen = $_POST['judul_dokumen'] ?? '';
    $jenis_file = $_POST['jenis_file'] ?? '';
    $tanggal_upload = date('Y-m-d H:i:s');
    $status_upload = 'Menunggu';

    if (empty($nim) || empty($judul_dokumen) || empty($jenis_file) || !isset($_FILES['file_dokumen'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['file_dokumen']['name']);
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = $nim . '_' . time() . '.' . $fileExtension;
    $uploadFile = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $uploadFile)) {
        $sql = "INSERT INTO laporan_magang (nim, judul_dokumen, jenis_file, lokasi_file, status_upload, tanggal_upload) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nim, $judul_dokumen, $jenis_file, $uploadFile, $status_upload, $tanggal_upload);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Dokumen berhasil diunggah.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data ke database: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah file.']);
    }

} elseif ($method === 'GET') {
    $nim = $_GET['nim'] ?? '';
    if (empty($nim)) {
        echo json_encode(['status' => 'error', 'message' => 'NIM tidak boleh kosong.']);
        exit;
    }

    $sql = "SELECT judul_dokumen, status_upload, lokasi_file, tanggal_upload FROM laporan_magang WHERE nim = ? ORDER BY tanggal_upload DESC";
    $stmt = $conn->prepare($sql);
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
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
}

$conn->close();
?>
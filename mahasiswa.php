<?php
// File: mahasiswa.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST'); // Tambahkan metode POST
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $nim = $_GET['nim'] ?? '';
    if (!empty($nim)) {
        $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        if ($result->num_rows > 0) {
            $data[] = $result->fetch_assoc();
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan.']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logika untuk menangani pembaruan profil
    $nim = $_POST['nim'] ?? '';
    $nik = $_POST['nik'] ?? null;
    $email = $_POST['email'] ?? null;
    $telp = $_POST['telp'] ?? null;
    $handphone = $_POST['handphone'] ?? null;

    if (empty($nim)) {
        echo json_encode(['status' => 'error', 'message' => 'NIM tidak diberikan.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE mahasiswa SET nik = ?, email = ?, telp = ?, handphone = ? WHERE nim = ?");
    $stmt->bind_param("sssss", $nik, $email, $telp, $handphone, $nim);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil: ' . $conn->error]);
    }
    $stmt->close();
}

$conn->close();
?>
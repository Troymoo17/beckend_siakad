<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = $_POST['nim'] ?? '';
    $inputPassword = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT nim, password, nama FROM mahasiswa WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Anda harus menggunakan password_hash() dan password_verify() untuk keamanan
        // Contoh: if (password_verify($inputPassword, $user['password'])) {
        if ($inputPassword === $user['password']) { // Contoh sederhana, GANTI DENGAN VERIFIKASI HASH!
            echo json_encode([
                'status' => 'success',
                'message' => 'Login berhasil!',
                'data' => ['nim' => $user['nim']]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kata sandi salah.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID Pengguna tidak ditemukan.']);
    }

    $stmt->close();
}

$conn->close();
?>
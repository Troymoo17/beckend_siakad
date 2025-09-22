<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $nim = $_POST['nim'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($nim) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'NIM atau password tidak boleh kosong.']);
        exit;
    }

    $sql_check = "SELECT nim FROM hotspot WHERE nim = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $nim);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $sql = "UPDATE hotspot SET password = ? WHERE nim = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $password, $nim);
        $message = 'Password hotspot berhasil diperbarui.';
    } else {
        $sql = "INSERT INTO hotspot (nim, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nim, $password);
        $message = 'Pendaftaran hotspot berhasil.';
    }
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . $conn->error]);
    }
    
    $stmt->close();
    $stmt_check->close();

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
}

$conn->close();
?>
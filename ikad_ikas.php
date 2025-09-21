<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include 'db_config.php';

$nim = $_GET['nim'] ?? null;
$semester = $_GET['semester'] ?? null;

$stmt_mhs = $conn->prepare("SELECT semester_sekarang FROM mahasiswa WHERE nim = ?");
$stmt_mhs->bind_param("s", $nim);
$stmt_mhs->execute();
$result_mhs = $stmt_mhs->get_result();
$mahasiswa = $result_mhs->fetch_assoc();
$current_semester = $mahasiswa['semester_sekarang'] ?? $semester;
$stmt_mhs->close();


if (!$nim || !$current_semester) {
    echo json_encode(['status' => 'error', 'message' => 'NIM dan semester diperlukan.']);
    $conn->close();
    exit();
}

$response = [
    'status' => 'success',
    'ikad' => [],
    'ikas' => []
];

$stmt_ikad = $conn->prepare("SELECT DISTINCT mk.kode_mk, mk.nama_mk, mk.sks, jk.kelas, jk.dosen FROM jadwal_kuliah jk
    JOIN mata_kuliah mk ON jk.kode_matkul = mk.kode_mk
    WHERE jk.nim = ?");

if ($stmt_ikad === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query IKAD: ' . $conn->error]);
    $conn->close();
    exit();
}

$stmt_ikad->bind_param("s", $nim);
$stmt_ikad->execute();
$result_ikad = $stmt_ikad->get_result();

$ikad_data = [];
if ($result_ikad && $result_ikad->num_rows > 0) {
    $ikad_status = [];
    $stmt_ikad_status = $conn->prepare("SELECT kode_mk FROM ikad_kuisioner WHERE nim = ? AND semester = ?");
    if ($stmt_ikad_status) {
        $stmt_ikad_status->bind_param("ss", $nim, $current_semester);
        $stmt_ikad_status->execute();
        $result_ikad_status = $stmt_ikad_status->get_result();
        while ($row_status = $result_ikad_status->fetch_assoc()) {
            $ikad_status[] = $row_status['kode_mk'];
        }
        $stmt_ikad_status->close();
    }
    
    $no_ikad = 1;
    while ($row = $result_ikad->fetch_assoc()) {
        $status = in_array($row['kode_mk'], $ikad_status) ? 'Sudah Diisi' : 'Belum Diisi';
        $ikad_data[] = [
            'no' => $no_ikad++,
            'nama_matkul' => $row['nama_mk'],
            'sks' => $row['sks'],
            'kelas' => $row['kelas'],
            'dosen' => $row['dosen'],
            'status' => $status
        ];
    }
}
$stmt_ikad->close();
$response['ikad'] = $ikad_data;

$stmt_ikas = $conn->prepare("SELECT s.id_staff, s.nama_staf, s.bagian, s.jabatan FROM staff s");

if ($stmt_ikas === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query IKAS: ' . $conn->error]);
    $conn->close();
    exit();
}
$stmt_ikas->execute();
$result_ikas = $stmt_ikas->get_result();

$ikas_data = [];
if ($result_ikas && $result_ikas->num_rows > 0) {
    $ikas_status = [];
    $stmt_ikas_status = $conn->prepare("SELECT id_staff FROM ikas_response WHERE nim = ? AND semester = ?");
    if ($stmt_ikas_status) {
        $stmt_ikas_status->bind_param("ss", $nim, $current_semester);
        $stmt_ikas_status->execute();
        $result_ikas_status = $stmt_ikas_status->get_result();
        while ($row_status = $result_ikas_status->fetch_assoc()) {
            $ikas_status[] = $row_status['id_staff'];
        }
        $stmt_ikas_status->close();
    }

    $no_ikas = 1;
    while ($row = $result_ikas->fetch_assoc()) {
        $status = in_array($row['id_staff'], $ikas_status) ? 'Sudah Diisi' : 'Belum Diisi';
        $ikas_data[] = [
            'no' => $no_ikas++,
            'nama_staf' => $row['nama_staf'],
            'bagian' => $row['bagian'],
            'jabatan' => $row['jabatan'],
            'status' => $status
        ];
    }
}
$stmt_ikas->close();
$response['ikas'] = $ikas_data;

echo json_encode($response);
$conn->close();
?>
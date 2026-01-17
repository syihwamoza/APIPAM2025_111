<?php
/**
 * File: register.php
 * Deskripsi: API endpoint untuk registrasi user baru
 * Method: POST
 * Input: username, password, nama_lengkap
 * Output: status, message
 */

require_once 'koneksi.php';

// Hanya terima method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ]);
    exit;
}

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);

// Validasi input
if (empty($input['username']) || empty($input['password']) || empty($input['nama_lengkap'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Semua field harus diisi'
    ]);
    exit;
}

$username = $conn->real_escape_string($input['username']);
$password = $input['password'];
$nama_lengkap = $conn->real_escape_string($input['nama_lengkap']);

// Cek apakah username sudah ada
$check_sql = "SELECT id_user FROM users WHERE username = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('s', $username);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username sudah digunakan'
    ]);
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user baru
$insert_sql = "INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param('sss', $username, $hashed_password, $nama_lengkap);

if ($insert_stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Registrasi berhasil'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Registrasi gagal: ' . $conn->error
    ]);
}

$insert_stmt->close();
$conn->close();
?>
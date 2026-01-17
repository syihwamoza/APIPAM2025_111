<?php
/**
 * File: login.php
 * Deskripsi: API endpoint untuk login user
 * Method: POST
 * Input: username, password
 * Output: status, id_user, username, nama_lengkap / pesan error
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
if (empty($input['username']) || empty($input['password'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username dan password harus diisi'
    ]);
    exit;
}

$username = $conn->real_escape_string($input['username']);
$password = $input['password'];

// Query untuk mendapatkan data user
$sql = "SELECT id_user, username, password, nama_lengkap FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah user ditemukan
if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username atau password salah'
    ]);
    exit;
}

$user = $result->fetch_assoc();

// Verifikasi password
if (password_verify($password, $user['password'])) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Login berhasil',
        'data' => [
            'id_user' => $user['id_user'],
            'username' => $user['username'],
            'nama_lengkap' => $user['nama_lengkap']
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username atau password salah'
    ]);
}

$stmt->close();
$conn->close();
?>
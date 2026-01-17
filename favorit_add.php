<?php
/**
 * File: favorit_add.php
 * Deskripsi: API endpoint untuk menambah resep ke favorit
 * Method: POST
 * Input: id_user, id_resep
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
if (empty($input['id_user']) || empty($input['id_resep'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID user dan ID resep harus diisi'
    ]);
    exit;
}

$id_user = (int) $input['id_user'];
$id_resep = (int) $input['id_resep'];

// Cek apakah sudah ada di favorit
$check_sql = "SELECT id_favorit FROM favorit WHERE id_user = ? AND id_resep = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('ii', $id_user, $id_resep);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Resep sudah ada di favorit'
    ]);
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

// Insert ke favorit
$insert_sql = "INSERT INTO favorit (id_user, id_resep) VALUES (?, ?)";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param('ii', $id_user, $id_resep);

if ($insert_stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Resep berhasil ditambahkan ke favorit'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menambahkan ke favorit: ' . $conn->error
    ]);
}

$insert_stmt->close();
$conn->close();
?>
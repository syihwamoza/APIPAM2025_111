<?php
/**
 * File: favorit_remove.php
 * Deskripsi: API endpoint untuk menghapus resep dari favorit
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

// Hapus dari favorit
$delete_sql = "DELETE FROM favorit WHERE id_user = ? AND id_resep = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param('ii', $id_user, $id_resep);

if ($delete_stmt->execute()) {
    if ($delete_stmt->affected_rows > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Resep berhasil dihapus dari favorit'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Resep tidak ditemukan di favorit'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menghapus dari favorit: ' . $conn->error
    ]);
}

$delete_stmt->close();
$conn->close();
?>
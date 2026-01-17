<?php
/**
 * File: resep_delete.php
 * Deskripsi: API endpoint untuk menghapus resep
 * Method: POST (simulasi DELETE)
 * Input: id_resep, id_user
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
if (empty($input['id_resep']) || empty($input['id_user'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID resep dan ID user harus diisi'
    ]);
    exit;
}

$id_resep = (int) $input['id_resep'];
$id_user = (int) $input['id_user'];

// Cek apakah resep milik user yang bersangkutan
$check_sql = "SELECT foto FROM resep WHERE id_resep = ? AND id_user = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('ii', $id_resep, $id_user);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Resep tidak ditemukan atau bukan milik Anda'
    ]);
    $check_stmt->close();
    $conn->close();
    exit;
}

$resep = $check_result->fetch_assoc();
$foto_name = $resep['foto'];
$check_stmt->close();

// Hapus resep dari database
$delete_sql = "DELETE FROM resep WHERE id_resep = ? AND id_user = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param('ii', $id_resep, $id_user);

if ($delete_stmt->execute()) {
    // Hapus foto jika ada
    if (!empty($foto_name)) {
        $upload_path = __DIR__ . '/uploads/resep/' . $foto_name;
        if (file_exists($upload_path)) {
            unlink($upload_path);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Resep berhasil dihapus'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menghapus resep: ' . $conn->error
    ]);
}

$delete_stmt->close();
$conn->close();
?>
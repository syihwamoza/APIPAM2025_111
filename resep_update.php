<?php
/**
 * File: resep_update.php
 * Deskripsi: API endpoint untuk mengupdate resep
 * Method: POST (simulasi PUT)
 * Input: id_resep, id_user, judul, bahan, langkah, foto (base64, optional)
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

$id_resep = isset($input['id_resep']) ? (int) $input['id_resep'] : 0;
$id_user = isset($input['id_user']) ? (int) $input['id_user'] : 0;
$judul = isset($input['judul']) ? trim($input['judul']) : '';
$bahan = isset($input['bahan']) ? trim($input['bahan']) : '';
$langkah = isset($input['langkah']) ? trim($input['langkah']) : '';
$foto = isset($input['foto']) ? trim($input['foto']) : '';

// Validasi input (foto adalah opsional)
if (empty($id_resep) || empty($id_user) || empty($judul) || empty($bahan) || empty($langkah)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Judul, bahan, dan langkah wajib diisi'
    ]);
    exit;
}

$judul = $conn->real_escape_string($judul);
$bahan = $conn->real_escape_string($bahan);
$langkah = $conn->real_escape_string($langkah);

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


$existing_resep = $check_result->fetch_assoc();
$foto_name = $existing_resep['foto'];
$check_stmt->close();

// Update foto name jika ada foto baru dari URL (Base64)
if (!empty($foto)) {
    try {
        // Generate nama file unik
        $new_foto_name = 'resep_' . time() . '_' . uniqid() . '.jpg';
        $upload_dir = __DIR__ . '/uploads/resep/';
        $upload_path = $upload_dir . $new_foto_name;

        // Pastikan folder ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Decode base64
        $imageData = base64_decode($foto);

        // Simpan file
        if (file_put_contents($upload_path, $imageData)) {
            // Hapus foto lama jika ada
            if (!empty($foto_name) && file_exists($upload_dir . $foto_name)) {
                unlink($upload_dir . $foto_name);
            }
            $foto_name = $new_foto_name;
        }
    } catch (Exception $e) {
        // Biarkan foto_name lama jika gagal
    }
}

// Update resep
$sql = "UPDATE resep SET judul = ?, bahan = ?, langkah = ?, foto = ? WHERE id_resep = ? AND id_user = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssssii', $judul, $bahan, $langkah, $foto_name, $id_resep, $id_user);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Resep berhasil diupdate'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengupdate resep: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>
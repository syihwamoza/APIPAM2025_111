<?php
/**
 * File: resep_add.php
 * Deskripsi: API endpoint untuk menambah resep baru
 * Method: POST
 * Input: id_user, judul, bahan, langkah, foto (base64)
 * Output: status, message, id_resep
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

$id_user = isset($input['id_user']) ? (int) $input['id_user'] : 0;
$judul = isset($input['judul']) ? trim($input['judul']) : '';
$bahan = isset($input['bahan']) ? trim($input['bahan']) : '';
$langkah = isset($input['langkah']) ? trim($input['langkah']) : '';
$foto = isset($input['foto']) ? trim($input['foto']) : '';

// Validasi input (foto adalah opsional)
if (empty($id_user) || empty($judul) || empty($bahan) || empty($langkah)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Judul, bahan, dan langkah wajib diisi'
    ]);
    exit;
}


$judul = $conn->real_escape_string($judul);
$bahan = $conn->real_escape_string($bahan);
$langkah = $conn->real_escape_string($langkah);

// Proses foto (Base64 to File)
$foto_name = null;
if (!empty($foto)) {
    try {
        // Generate nama file unik
        $foto_name = 'resep_' . time() . '_' . uniqid() . '.jpg';
        $upload_dir = __DIR__ . '/uploads/resep/';
        $upload_path = $upload_dir . $foto_name;

        // Pastikan folder ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Decode base64
        $imageData = base64_decode($foto);

        // Simpan file
        if (!file_put_contents($upload_path, $imageData)) {
            $foto_name = null; // Gagal simpan file
        }
    } catch (Exception $e) {
        $foto_name = null;
    }
}

// Insert resep baru
$sql = "INSERT INTO resep (id_user, judul, bahan, langkah, foto) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issss', $id_user, $judul, $bahan, $langkah, $foto_name);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Resep berhasil ditambahkan',
        'data' => [
            'id_resep' => $conn->insert_id
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menambahkan resep: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>
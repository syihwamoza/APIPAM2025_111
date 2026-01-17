<?php
/**
 * File: resep_detail.php
 * Deskripsi: API endpoint untuk menampilkan detail satu resep
 * Method: GET
 * Input: id (parameter GET)
 * Output: success, message, data (objek resep)
 */

require_once 'koneksi.php';

// Hanya terima method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan. Gunakan GET.'
    ]);
    exit;
}

// Ambil parameter id
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID resep tidak valid'
    ]);
    exit;
}

// Query untuk mendapatkan detail resep dengan informasi user
$sql = "SELECT r.id_resep, r.id_user, r.judul, r.bahan, r.langkah, r.foto, r.created_at, r.updated_at, u.username 
        FROM resep r 
        INNER JOIN users u ON r.id_user = u.id_user 
        WHERE r.id_resep = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Resep tidak ditemukan',
        'data' => null
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$resep = [
    'id_resep' => (int) $row['id_resep'],
    'id_user' => (int) $row['id_user'],
    'judul' => $row['judul'],
    'bahan' => $row['bahan'],
    'langkah' => $row['langkah'],
    'foto' => $row['foto'],
    'username' => $row['username'],
    'created_at' => $row['created_at'],
    'updated_at' => $row['updated_at']
];

echo json_encode([
    'success' => true,
    'message' => 'Data resep berhasil diambil',
    'data' => $resep
]);

$stmt->close();
$conn->close();
?>
<?php
/**
 * File: resep_user.php
 * Deskripsi: API endpoint untuk menampilkan resep milik user tertentu
 * Method: GET
 * Input: id_user (parameter GET)
 * Output: status, data (array resep milik user)
 */

require_once 'koneksi.php';

// Hanya terima method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan. Gunakan GET.'
    ]);
    exit;
}

// Ambil parameter id_user
$id_user = isset($_GET['id_user']) ? (int) $_GET['id_user'] : 0;

if ($id_user === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID user tidak valid'
    ]);
    exit;
}

// Query untuk mendapatkan resep milik user
$sql = "SELECT r.id_resep, r.id_user, r.judul, r.bahan, r.langkah, r.foto, r.created_at, r.updated_at, u.username 
        FROM resep r 
        INNER JOIN users u ON r.id_user = u.id_user 
        WHERE r.id_user = ? 
        ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();

$resep_list = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $resep_list[] = [
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
    }
}

echo json_encode([
    'status' => 'success',
    'message' => 'Data resep user berhasil diambil',
    'data' => $resep_list
]);

$stmt->close();
$conn->close();
?> 
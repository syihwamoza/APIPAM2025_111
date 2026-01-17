<?php
/**
 * File: resep_all.php
 * Deskripsi: API endpoint untuk menampilkan semua resep publik
 * Method: GET
 * Output: status, data (array resep)
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

// Query untuk mendapatkan semua resep dengan informasi user
$sql = "SELECT r.id_resep, r.id_user, r.judul, r.bahan, r.langkah, r.foto, r.created_at, r.updated_at, u.username 
        FROM resep r 
        INNER JOIN users u ON r.id_user = u.id_user 
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);

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
    'message' => 'Data resep berhasil diambil',
    'data' => $resep_list
]);

$conn->close();
?>
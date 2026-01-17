<?php
/**
 * File: favorit_list.php
 * Deskripsi: API endpoint untuk menampilkan daftar resep favorit user
 * Method: GET
 * Input: id_user (parameter GET)
 * Output: status, data (array resep favorit)
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

// Query untuk mendapatkan resep favorit user
$sql = "SELECT r.id_resep, r.id_user, r.judul, r.bahan, r.langkah, r.foto, r.created_at, r.updated_at, u.username, f.id_favorit 
        FROM favorit f 
        INNER JOIN resep r ON f.id_resep = r.id_resep 
        INNER JOIN users u ON r.id_user = u.id_user 
        WHERE f.id_user = ? 
        ORDER BY f.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();

$favorit_list = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $favorit_list[] = [
            'id_favorit' => (int) $row['id_favorit'],
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
    'message' => 'Data favorit berhasil diambil',
    'data' => $favorit_list
]);

$stmt->close();
$conn->close();
?>
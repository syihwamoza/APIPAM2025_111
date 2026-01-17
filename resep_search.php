<?php
/**
 * File: resep_search.php
 * Deskripsi: API endpoint untuk mencari resep berdasarkan judul dan bahan
 * Method: GET
 * Input: query (parameter GET)
 * Output: status, data (array resep yang cocok)
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

// Ambil query parameter
$query = isset($_GET['query']) ? $_GET['query'] : '';

if (empty($query)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query pencarian tidak boleh kosong'
    ]);
    exit;
}

$search_term = '%' . $conn->real_escape_string($query) . '%';

// Query untuk mencari resep berdasarkan judul atau bahan (LIKE, case-insensitive)
$sql = "SELECT r.id_resep, r.id_user, r.judul, r.bahan, r.langkah, r.foto, r.created_at, r.updated_at, u.username 
        FROM resep r 
        INNER JOIN users u ON r.id_user = u.id_user 
        WHERE r.judul LIKE ? OR r.bahan LIKE ? 
        ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $search_term, $search_term);
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
    'message' => 'Pencarian berhasil',
    'data' => $resep_list
]);

$stmt->close();
$conn->close();
?>
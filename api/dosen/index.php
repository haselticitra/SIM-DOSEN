

<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/auth.php';

if (empty($_SESSION['id_pengguna'])) {
    http_response_code(401);

    echo json_encode([
        'status' => false,
        'message' => 'Silakan login terlebih dahulu'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    exit;
}

require_once __DIR__ . '/../../config.php';


try {
    $sql = "SELECT
                d.id_dosen,
                d.nomor_registrasi,
                d.nuptk,
                d.nama,
                d.nip,
                p.nama_prodi
            FROM dosen d
            LEFT JOIN program_studi p
                ON d.id_prodi = p.id_prodi
            ORDER BY d.id_dosen ASC";

    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);

    echo json_encode([
        'status' => true,
        'message' => 'Data dosen berhasil diambil',
        'total' => count($data),
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'status' => false,
        'message' => 'Gagal mengambil data dosen'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

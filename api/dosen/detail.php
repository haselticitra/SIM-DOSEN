

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
    if (!isset($_GET['id']) || !ctype_digit($_GET['id']) || (int) $_GET['id'] < 1) {
        http_response_code(400);

        echo json_encode([
            'status' => false,
            'message' => 'Parameter id wajib diisi dengan ID yang valid'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }

    $id = (int) $_GET['id'];

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
            WHERE d.id_dosen = :id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        http_response_code(404);

        echo json_encode([
            'status' => false,
            'message' => 'Data dosen tidak ditemukan'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }

    echo json_encode([
        'status' => true,
        'message' => 'Detail dosen berhasil diambil',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'status' => false,
        'message' => 'Terjadi kesalahan saat mengambil detail dosen'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

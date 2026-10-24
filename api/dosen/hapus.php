
<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

function kirimJSON($kode, $data)
{
    http_response_code($kode);
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
    exit;
}

// Pastikan pengguna sudah login.
if (empty($_SESSION['id_pengguna'])) {
    kirimJSON(401, [
        'status' => false,
        'message' => 'Silakan login terlebih dahulu.'
    ]);
}

// API ini menggunakan metode DELETE.
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('Allow: DELETE');

    kirimJSON(405, [
        'status' => false,
        'message' => 'Gunakan metode DELETE untuk menghapus data dosen.'
    ]);
}

// Ambil ID dosen dari JSON.
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    kirimJSON(400, [
        'status' => false,
        'message' => 'Data JSON tidak valid.'
    ]);
}

$id = $input['id_dosen'] ?? null;

if (
    filter_var($id, FILTER_VALIDATE_INT) === false ||
    (int) $id < 1
) {
    kirimJSON(422, [
        'status' => false,
        'message' => 'ID dosen tidak valid.'
    ]);
}

$id = (int) $id;

try {
    // Periksa apakah dosen tersedia.
    $cek = $pdo->prepare(
        'SELECT id_dosen FROM dosen WHERE id_dosen = ?'
    );
    $cek->execute([$id]);

    if (!$cek->fetchColumn()) {
        kirimJSON(404, [
            'status' => false,
            'message' => 'Data dosen tidak ditemukan.'
        ]);
    }

    // Hapus hanya data dengan ID yang dipilih.
    $hapus = $pdo->prepare(
        'DELETE FROM dosen WHERE id_dosen = ?'
    );
    $hapus->execute([$id]);

    kirimJSON(200, [
        'status' => true,
        'message' => 'Data dosen berhasil dihapus.',
        'id_dosen' => $id
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    kirimJSON(500, [
        'status' => false,
        'message' => 'Data gagal dihapus. Periksa relasi database.'
    ]);
}

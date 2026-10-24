
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

if (empty($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dosen.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id_dosen', FILTER_VALIDATE_INT);
$token = $_POST['csrf_token'] ?? '';
$tokenSession = $_SESSION['csrf_token'] ?? '';

if (
    !$id ||
    $tokenSession === '' ||
    !is_string($token) ||
    !hash_equals($tokenSession, $token)
) {
    http_response_code(403);
    exit('Permintaan tidak valid. Silakan kembali dan muat ulang halaman.');
}

try {
    $stmt = $pdo->prepare(
        'DELETE FROM dosen WHERE id_dosen = ?'
    );
    $stmt->execute([$id]);

    header('Location: dosen.php');
    exit;
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Data dosen gagal dihapus. Periksa apakah data masih digunakan oleh tabel lain.');
}

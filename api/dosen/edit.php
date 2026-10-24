

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


function kirimJSON($statusCode, $hasil)
{
    http_response_code($statusCode);
    echo json_encode(
        $hasil,
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    header('Allow: PUT');
    kirimJSON(405, [
        'status' => false,
        'message' => 'Gunakan metode PUT untuk mengedit data dosen'
    ]);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    kirimJSON(400, [
        'status' => false,
        'message' => 'Body harus berupa JSON yang valid'
    ]);
}

$id = $input['id_dosen'] ?? null;

if (
    filter_var($id, FILTER_VALIDATE_INT) === false ||
    (int) $id < 1
) {
    kirimJSON(422, [
        'status' => false,
        'message' => 'id_dosen wajib diisi dengan ID yang valid'
    ]);
}

$id = (int) $id;

$nomor = trim((string) ($input['nomor_registrasi'] ?? ''));
$nama = trim((string) ($input['nama'] ?? ''));
$nuptk = trim((string) ($input['nuptk'] ?? ''));
$nip = trim((string) ($input['nip'] ?? ''));
$idProdi = $input['id_prodi'] ?? null;

if ($nomor === '' || $nama === '') {
    kirimJSON(422, [
        'status' => false,
        'message' => 'Nomor registrasi dan nama wajib diisi'
    ]);
}

try {
    $cekDosen = $pdo->prepare(
        'SELECT id_dosen FROM dosen WHERE id_dosen = ?'
    );
    $cekDosen->execute([$id]);

    if (!$cekDosen->fetchColumn()) {
        kirimJSON(404, [
            'status' => false,
            'message' => 'Data dosen tidak ditemukan'
        ]);
    }

    $cekNomor = $pdo->prepare(
        'SELECT id_dosen FROM dosen
         WHERE nomor_registrasi = ? AND id_dosen <> ?
         LIMIT 1'
    );
    $cekNomor->execute([$nomor, $id]);

    if ($cekNomor->fetchColumn()) {
        kirimJSON(409, [
            'status' => false,
            'message' => 'Nomor registrasi sudah digunakan'
        ]);
    }

    if ($idProdi !== null && $idProdi !== '') {
        if (
            filter_var($idProdi, FILTER_VALIDATE_INT) === false ||
            (int) $idProdi < 1
        ) {
            kirimJSON(422, [
                'status' => false,
                'message' => 'id_prodi tidak valid'
            ]);
        }

        $cekProdi = $pdo->prepare(
            'SELECT id_prodi FROM program_studi WHERE id_prodi = ?'
        );
        $cekProdi->execute([(int) $idProdi]);

        if (!$cekProdi->fetchColumn()) {
            kirimJSON(422, [
                'status' => false,
                'message' => 'Program studi tidak ditemukan'
            ]);
        }

        $idProdi = (int) $idProdi;
    } else {
        $idProdi = null;
    }

    $sql = 'UPDATE dosen
            SET nomor_registrasi = ?,
                nama = ?,
                nuptk = ?,
                nip = ?,
                id_prodi = ?
            WHERE id_dosen = ?';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nomor,
        $nama,
        $nuptk !== '' ? $nuptk : null,
        $nip !== '' ? $nip : null,
        $idProdi,
        $id
    ]);

    kirimJSON(200, [
        'status' => true,
        'message' => 'Data dosen berhasil diperbarui',
        'id_dosen' => $id
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    kirimJSON(500, [
        'status' => false,
        'message' => 'Terjadi kesalahan saat memperbarui data dosen'
    ]);
}

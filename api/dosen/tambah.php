
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


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);

    echo json_encode([
        'status' => false,
        'message' => 'Gunakan metode POST untuk menambah data dosen'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);

        echo json_encode([
            'status' => false,
            'message' => 'Body harus berupa JSON yang valid'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }

    $nomor_registrasi = trim((string) ($input['nomor_registrasi'] ?? ''));
    $nama = trim((string) ($input['nama'] ?? ''));
    $nuptk = trim((string) ($input['nuptk'] ?? ''));
    $nip = trim((string) ($input['nip'] ?? ''));
    $id_prodi = $input['id_prodi'] ?? null;

    if ($nomor_registrasi === '' || $nama === '') {
        http_response_code(422);

        echo json_encode([
            'status' => false,
            'message' => 'nomor_registrasi dan nama wajib diisi'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }

    if ($id_prodi !== null && $id_prodi !== '') {
        if (
            filter_var($id_prodi, FILTER_VALIDATE_INT) === false ||
            (int) $id_prodi < 1
        ) {
            http_response_code(422);

            echo json_encode([
                'status' => false,
                'message' => 'id_prodi harus berupa ID yang valid'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            exit;
        }

        $cek = $pdo->prepare(
            'SELECT id_prodi FROM program_studi WHERE id_prodi = ?'
        );
        $cek->execute([(int) $id_prodi]);

        if (!$cek->fetchColumn()) {
            http_response_code(422);

            echo json_encode([
                'status' => false,
                'message' => 'Program studi tidak ditemukan'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            exit;
        }

        $id_prodi = (int) $id_prodi;
    } else {
        $id_prodi = null;
    }

    $sql = "INSERT INTO dosen
                (nomor_registrasi, nama, nuptk, nip, id_prodi)
            VALUES
                (:nomor_registrasi, :nama, :nuptk, :nip, :id_prodi)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nomor_registrasi' => $nomor_registrasi,
        'nama' => $nama,
        'nuptk' => $nuptk !== '' ? $nuptk : null,
        'nip' => $nip !== '' ? $nip : null,
        'id_prodi' => $id_prodi
    ]);

    http_response_code(201);

    echo json_encode([
        'status' => true,
        'message' => 'Data dosen berhasil ditambahkan',
        'id_dosen' => (int) $pdo->lastInsertId()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'status' => false,
        'message' => 'Gagal menambahkan data. Periksa data dan struktur database.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

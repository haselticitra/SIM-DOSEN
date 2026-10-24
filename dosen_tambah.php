
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

wajib_login();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function h($value) {
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$error = '';
$nomorRegistrasi = '';
$nama = '';
$nuptk = '';
$nip = '';
$idProdi = '';
$idStatusAktivitas = '';

$programStudi = $pdo->query("
    SELECT id_prodi, nama_prodi
    FROM program_studi
    ORDER BY nama_prodi
")->fetchAll(PDO::FETCH_ASSOC);

$statusAktivitas = $pdo->query("
    SELECT id_status_aktivitas, nama_status
    FROM status_aktivitas
    ORDER BY nama_status
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $nomorRegistrasi = trim($_POST['nomor_registrasi'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $nuptk = trim($_POST['nuptk'] ?? '');
        $nip = trim($_POST['nip'] ?? '');
        $idProdi = trim($_POST['id_prodi'] ?? '');
        $idStatusAktivitas = trim(
            $_POST['id_status_aktivitas'] ?? ''
        );

        if ($nomorRegistrasi === '' || $nama === '' || $idProdi === '') {
            $error = 'Nomor registrasi, nama, dan program studi wajib diisi.';
        } elseif (
            strlen($nomorRegistrasi) > 50 ||
            strlen($nama) > 150 ||
            strlen($nuptk) > 30 ||
            strlen($nip) > 30
        ) {
            $error = 'Ada isian yang melebihi batas karakter database.';
        } elseif (
            !ctype_digit($idProdi) ||
            (
                $idStatusAktivitas !== '' &&
                !ctype_digit($idStatusAktivitas)
            )
        ) {
            $error = 'Pilihan program studi atau status tidak valid.';
        } else {
            $cekProdi = $pdo->prepare("
                SELECT COUNT(*)
                FROM program_studi
                WHERE id_prodi = ?
            ");
            $cekProdi->execute([(int)$idProdi]);

            $cekStatus = true;

            if ($idStatusAktivitas !== '') {
                $stmtStatus = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM status_aktivitas
                    WHERE id_status_aktivitas = ?
                ");
                $stmtStatus->execute([(int)$idStatusAktivitas]);
                $cekStatus = (int)$stmtStatus->fetchColumn() > 0;
            }

            $cekRegistrasi = $pdo->prepare("
                SELECT COUNT(*)
                FROM dosen
                WHERE nomor_registrasi = ?
            ");
            $cekRegistrasi->execute([$nomorRegistrasi]);

            if ((int)$cekProdi->fetchColumn() === 0) {
                $error = 'Program studi yang dipilih tidak ditemukan.';
            } elseif (!$cekStatus) {
                $error = 'Status aktivitas yang dipilih tidak ditemukan.';
            } elseif ((int)$cekRegistrasi->fetchColumn() > 0) {
                $error = 'Nomor registrasi tersebut sudah digunakan.';
            } else {
                try {
                    $sql = "
                        INSERT INTO dosen (
                            nomor_registrasi,
                            nuptk,
                            nama,
                            nip,
                            id_prodi,
                            id_status_aktivitas
                        ) VALUES (?, ?, ?, ?, ?, ?)
                    ";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $nomorRegistrasi,
                        $nuptk !== '' ? $nuptk : null,
                        $nama,
                        $nip !== '' ? $nip : null,
                        (int)$idProdi,
                        $idStatusAktivitas !== ''
                            ? (int)$idStatusAktivitas
                            : null
                    ]);

                    unset($_SESSION['csrf_token']);

                    header('Location: dosen.php?pesan=tambah_berhasil');
                    exit;
                } catch (PDOException $e) {
                    error_log('Tambah dosen gagal: ' . $e->getMessage());
                    $error = 'Data gagal disimpan. Periksa kembali data dan struktur database.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Dosen | SIM Dosen</title>

<style>
:root {
    --primary: #0f766e;
    --primary-dark: #115e59;
    --bg: #f3f6fb;
    --text: #172b4d;
    --muted: #718096;
    --border: #e2e8f0;
    --danger: #be123c;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Inter, "Segoe UI", Arial, sans-serif;
    color: var(--text);
    background:
        radial-gradient(ellipse at top left, #ddf7f1, transparent 35%),
        var(--bg);
    min-height: 100vh;
}

.navbar {
    background: linear-gradient(115deg, #115e59, #0f766e, #0d9488);
    color: white;
    padding: 19px 5%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-icon {
    width: 44px;
    height: 44px;
    display: grid;
    place-items: center;
    border-radius: 13px;
    background: rgba(255,255,255,.15);
    font-size: 22px;
}

.brand h2 {
    margin: 0;
    font-size: 19px;
}

.brand p {
    margin: 5px 0 0;
    color: #d5f5ef;
    font-size: 12px;
}

.back-link {
    color: white;
    text-decoration: none;
    padding: 10px 14px;
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 10px;
    transition: .2s;
}

.back-link:hover {
    background: rgba(255,255,255,.14);
    transform: translateY(-2px);
}

main {
    width: min(850px, calc(100% - 32px));
    margin: 34px auto 55px;
    animation: rise .5s ease both;
}

.page-heading {
    margin-bottom: 22px;
}

.eyebrow {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 30px;
    background: #dff7f1;
    color: var(--primary);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .5px;
}

.page-heading h1 {
    margin: 13px 0 8px;
    font-size: clamp(28px, 5vw, 36px);
    letter-spacing: -.8px;
}

.page-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
    line-height: 1.7;
}

.form-card {
    overflow: hidden;
    background: white;
    border: 1px solid var(--border);
    border-radius: 19px;
    box-shadow: 0 14px 40px rgba(27,47,75,.07);
}

.card-heading {
    padding: 23px 27px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 13px;
}

.heading-icon {
    width: 46px;
    height: 46px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: #dff7f1;
    color: var(--primary);
    font-size: 23px;
}

.card-heading h2 {
    margin: 0;
    font-size: 18px;
}

.card-heading p {
    margin: 5px 0 0;
    color: var(--muted);
    font-size: 12px;
}

form.main-form {
    padding: 27px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 21px 18px;
}

.field {
    min-width: 0;
}

.field.full {
    grid-column: 1 / -1;
}

label {
    display: block;
    margin-bottom: 9px;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
}

.required {
    color: #e11d48;
}

input, select {
    display: block;
    width: 100%;
    min-height: 47px;
    padding: 12px 13px;
    color: var(--text);
    background: #fbfdff;
    border: 1px solid #dce4ed;
    border-radius: 10px;
    outline: none;
    font: inherit;
    font-size: 13px;
    transition: border-color .2s, box-shadow .2s, background .2s;
}

input::placeholder {
    color: #a0aec0;
}

input:focus, select:focus {
    background: white;
    border-color: #0d9488;
    box-shadow: 0 0 0 4px rgba(13,148,136,.10);
}

.hint {
    margin: 7px 0 0;
    color: #94a3b8;
    font-size: 11px;
    line-height: 1.6;
}

.alert {
    margin: 0 27px;
    padding: 13px 15px;
    border-radius: 10px;
    color: var(--danger);
    background: #fff1f2;
    border: 1px solid #ffe4e6;
    font-size: 13px;
    line-height: 1.6;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 11px;
    margin-top: 29px;
    padding-top: 22px;
    border-top: 1px solid var(--border);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    padding: 12px 18px;
    border-radius: 10px;
    border: 1px solid transparent;
    font: inherit;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: transform .2s, box-shadow .2s, background .2s;
}

.btn:hover {
    transform: translateY(-2px);
}

.btn-primary {
    color: white;
    background: linear-gradient(120deg, #0f766e, #0d9488);
    box-shadow: 0 6px 16px rgba(15,118,110,.18);
}

.btn-primary:hover {
    box-shadow: 0 10px 23px rgba(15,118,110,.25);
}

.btn-secondary {
    color: #475569;
    background: white;
    border-color: var(--border);
}

.btn-secondary:hover {
    background: #f8fafc;
}

.card-footer {
    padding: 16px 27px;
    color: #94a3b8;
    background: #fcfdff;
    border-top: 1px solid var(--border);
    font-size: 11px;
}

@keyframes rise {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 600px) {
    .navbar { padding: 15px; }
    .brand h2 { font-size: 16px; }
    .brand p { font-size: 10px; }
    .brand-icon { width: 38px; height: 38px; }
    .back-link { font-size: 11px; padding: 9px; }
    main { margin-top: 23px; }
    .form-grid { grid-template-columns: 1fr; gap: 19px; }
    .field.full { grid-column: auto; }
    .card-heading { padding: 19px; }
    form.main-form { padding: 20px; }
    .alert { margin: 0 20px; }
    .form-actions { flex-direction: column-reverse; }
    .form-actions .btn { width: 100%; }
    .card-footer { padding: 15px 20px; }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}
</style>
</head>

<body>
<header class="navbar">
    <div class="brand">
        <div class="brand-icon">▦</div>
        <div>
            <h2>SIM DOSEN</h2>
            <p>Universitas Muhammadiyah Bengkulu</p>
        </div>
    </div>

    <a class="back-link" href="dosen.php">← Kembali</a>
</header>

<main>
    <section class="page-heading">
        <span class="eyebrow">MANAJEMEN AKADEMIK</span>
        <h1>Tambah Dosen</h1>
        <p>Lengkapi formulir berikut untuk mendaftarkan data dosen baru.</p>
    </section>

    <section class="form-card">
        <div class="card-heading">
            <div class="heading-icon">＋</div>
            <div>
                <h2>Informasi Dosen</h2>
                <p>Kolom bertanda <span class="required">*</span> wajib diisi.</p>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert" role="alert" style="margin-top:20px">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <form class="main-form" method="post" action="dosen_tambah.php">
            <input type="hidden" name="csrf_token"
                   value="<?= h($_SESSION['csrf_token']) ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="nomor_registrasi">
                        Nomor Registrasi <span class="required">*</span>
                    </label>
                    <input
                        id="nomor_registrasi"
                        name="nomor_registrasi"
                        type="text"
                        maxlength="50"
                        value="<?= h($nomorRegistrasi) ?>"
                        placeholder="Contoh: 12345678910"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="field">
                    <label for="nama">
                        Nama Lengkap <span class="required">*</span>
                    </label>
                    <input
                        id="nama"
                        name="nama"
                        type="text"
                        maxlength="150"
                        value="<?= h($nama) ?>"
                        placeholder="Masukkan nama lengkap"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="field">
                    <label for="nuptk">NUPTK</label>
                    <input
                        id="nuptk"
                        name="nuptk"
                        type="text"
                        maxlength="30"
                        value="<?= h($nuptk) ?>"
                        placeholder="Masukkan NUPTK"
                    >
                    <p class="hint">Kosongkan jika belum tersedia.</p>
                </div>

                <div class="field">
                    <label for="nip">NIP</label>
                    <input
                        id="nip"
                        name="nip"
                        type="text"
                        maxlength="30"
                        value="<?= h($nip) ?>"
                        placeholder="Masukkan NIP"
                    >
                    <p class="hint">Kosongkan jika belum tersedia.</p>
                </div>

                <div class="field">
                    <label for="id_prodi">
                        Program Studi <span class="required">*</span>
                    </label>
                    <select id="id_prodi" name="id_prodi" required>
                        <option value="">Pilih program studi</option>
                        <?php foreach ($programStudi as $prodi): ?>
                            <option
                                value="<?= (int)$prodi['id_prodi'] ?>"
                                <?= (string)$idProdi === (string)$prodi['id_prodi'] ? 'selected' : '' ?>
                            >
                                <?= h($prodi['nama_prodi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="id_status_aktivitas">Status Aktivitas</label>
                    <select id="id_status_aktivitas"
                            name="id_status_aktivitas">
                        <option value="">Belum diatur</option>
                        <?php foreach ($statusAktivitas as $status): ?>
                            <option
                                value="<?= (int)$status['id_status_aktivitas'] ?>"
                                <?= (string)$idStatusAktivitas === (string)$status['id_status_aktivitas'] ? 'selected' : '' ?>
                            >
                                <?= h($status['nama_status']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="hint">Pilih status sesuai data kepegawaian.</p>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-secondary" href="dosen.php">
                    Batal
                </a>
                <button class="btn btn-primary" type="submit">
                    ✓ Simpan Data Dosen
                </button>
            </div>
        </form>

        <div class="card-footer">
            SIM Dosen · Universitas Muhammadiyah Bengkulu
        </div>
    </section>
</main>
</body>
</html>

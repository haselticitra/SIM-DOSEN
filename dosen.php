
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

// Pencarian dosen.
$cari = trim($_GET['cari'] ?? '');

$sql = "
    SELECT
        d.id_dosen,
        d.nomor_registrasi,
        d.nuptk,
        d.nama,
        d.nip,
        d.id_status_aktivitas,
        p.nama_prodi,
        s.nama_status
    FROM dosen d
    LEFT JOIN program_studi p
        ON p.id_prodi = d.id_prodi
    LEFT JOIN status_aktivitas s
        ON s.id_status_aktivitas = d.id_status_aktivitas
";

$params = [];

if ($cari !== '') {
    $sql .= "
        WHERE d.nama LIKE ?
           OR d.nomor_registrasi LIKE ?
           OR d.nuptk LIKE ?
           OR d.nip LIKE ?
           OR p.nama_prodi LIKE ?
           OR s.nama_status LIKE ?
    ";

    $keyword = '%' . $cari . '%';

    $params = [
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword
    ];
}

$sql .= " ORDER BY d.id_dosen DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftarDosen = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalDitemukan = count($daftarDosen);

$baseUrl = '/simdosen';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Data Dosen | SIM Dosen</title>

<style>
:root {
    --primary: #0f766e;
    --primary-dark: #115e59;
    --bg: #f3f6fb;
    --text: #172b4d;
    --muted: #718096;
    --border: #e5eaf1;
    --white: #fff;
    --shadow: 0 10px 35px rgba(27, 47, 75, .06);
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Inter, "Segoe UI", Arial, sans-serif;
    color: var(--text);
    background:
        radial-gradient(ellipse at 5% 0%, #e0f7f3 0, transparent 32%),
        var(--bg);
    min-height: 100vh;
}

a {
    text-decoration: none;
}

button, input {
    font: inherit;
}

/* HEADER */

.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 5%;
    color: white;
    background: linear-gradient(
        115deg, #115e59, #0f766e, #0d9488
    );
    box-shadow: 0 5px 24px rgba(15, 118, 110, .16);
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-icon {
    display: grid;
    place-items: center;
    width: 45px;
    height: 45px;
    border-radius: 14px;
    background: rgba(255,255,255,.15);
    font-size: 22px;
}

.brand h2 {
    margin: 0;
    font-size: 19px;
}

.brand p {
    margin: 5px 0 0;
    font-size: 12px;
    color: #d5f5ef;
}

.back-button {
    color: white;
    padding: 10px 15px;
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 10px;
    transition: .25s ease;
}

.back-button:hover {
    background: rgba(255,255,255,.15);
    transform: translateY(-2px);
}

/* MAIN */

main {
    max-width: 1400px;
    width: calc(100% - 48px);
    margin: 34px auto 55px;
}

.page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    animation: rise .55s ease both;
}

.eyebrow {
    display: inline-block;
    color: var(--primary);
    background: #dff7f1;
    padding: 7px 12px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .6px;
    text-transform: uppercase;
}

.page-heading h1 {
    margin: 12px 0 8px;
    font-size: clamp(27px, 4vw, 36px);
    letter-spacing: -.8px;
}

.page-heading p {
    color: var(--muted);
    margin: 0;
    line-height: 1.7;
}

.add-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-shrink: 0;
    color: white;
    background: linear-gradient(120deg, #0f766e, #0d9488);
    border: none;
    border-radius: 11px;
    padding: 13px 18px;
    font-weight: 700;
    box-shadow: 0 7px 17px rgba(15,118,110,.18);
    transition: transform .25s, box-shadow .25s;
}

.add-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(15,118,110,.25);
}

/* SUMMARY */

.summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 23px;
    animation: rise .6s .08s ease both;
}

.summary-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border: 1px solid var(--border);
    border-radius: 15px;
    background: white;
    box-shadow: var(--shadow);
    transition: transform .25s, box-shadow .25s;
}

.summary-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px rgba(27,47,75,.09);
}

.summary-icon {
    display: grid;
    place-items: center;
    width: 45px;
    height: 45px;
    flex-shrink: 0;
    border-radius: 13px;
    background: #e0f7f3;
    color: var(--primary);
    font-size: 21px;
}

.summary-card:nth-child(2) .summary-icon {
    background: #dcfce7;
    color: #15803d;
}

.summary-card:nth-child(3) .summary-icon {
    background: #f1f5f9;
    color: #64748b;
}

.summary-label {
    margin: 0 0 5px;
    font-size: 12px;
    color: var(--muted);
}

.summary-number {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
}

/* TABLE PANEL */

.panel {
    background: white;
    border: 1px solid var(--border);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: var(--shadow);
    animation: rise .65s .15s ease both;
}

.panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 24px 25px;
    border-bottom: 1px solid var(--border);
}

.panel-heading h2 {
    margin: 0;
    font-size: 18px;
}

.panel-heading p {
    color: var(--muted);
    font-size: 12px;
    margin: 6px 0 0;
}

.count-badge {
    padding: 7px 11px;
    border-radius: 30px;
    background: #e0f7f3;
    color: var(--primary);
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

/* SEARCH */

.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 18px 25px;
    border-bottom: 1px solid var(--border);
    background: #fcfdff;
}

.search-form {
    display: flex;
    gap: 9px;
    width: min(100%, 550px);
}

.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
    padding: 0 13px;
    border: 1px solid #dce4ed;
    border-radius: 10px;
    background: white;
    transition: border-color .2s, box-shadow .2s;
}

.search-box:focus-within {
    border-color: #0d9488;
    box-shadow: 0 0 0 3px rgba(13,148,136,.1);
}

.search-box span {
    color: #94a3b8;
}

.search-box input {
    width: 100%;
    min-width: 0;
    padding: 12px 0;
    border: none;
    outline: none;
    color: var(--text);
    background: transparent;
    font-size: 13px;
}

.search-button,
.reset-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 15px;
    border-radius: 9px;
    border: 1px solid transparent;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    transition: .2s;
}

.search-button {
    background: var(--primary);
    color: white;
}

.search-button:hover {
    background: var(--primary-dark);
}

.reset-button {
    color: #475569;
    background: white;
    border-color: var(--border);
}

.reset-button:hover {
    background: #f1f5f9;
}

.result-info {
    color: var(--muted);
    font-size: 12px;
    white-space: nowrap;
}

/* TABLE */

.table-wrap {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    white-space: nowrap;
}

thead {
    background: #f8fafc;
}

th {
    padding: 15px 17px;
    font-size: 11px;
    color: #64748b;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .45px;
    border-bottom: 1px solid var(--border);
}

td {
    padding: 17px;
    border-bottom: 1px solid #edf1f6;
    font-size: 13px;
    vertical-align: middle;
}

tbody tr {
    animation: rowIn .45s ease both;
    transition: background .2s;
}

tbody tr:nth-child(1) { animation-delay: .04s; }
tbody tr:nth-child(2) { animation-delay: .08s; }
tbody tr:nth-child(3) { animation-delay: .12s; }
tbody tr:nth-child(4) { animation-delay: .16s; }
tbody tr:nth-child(5) { animation-delay: .20s; }
tbody tr:nth-child(6) { animation-delay: .24s; }
tbody tr:nth-child(7) { animation-delay: .28s; }
tbody tr:nth-child(8) { animation-delay: .32s; }

tbody tr:hover {
    background: #f5fbfa;
}

tbody tr:last-child td {
    border-bottom: none;
}

.row-number {
    color: #94a3b8;
    font-size: 12px;
}

.dosen-name {
    display: flex;
    align-items: center;
    gap: 11px;
}

.avatar {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, #ccfbf1, #e0f2fe);
    color: #0f766e;
    font-weight: 800;
    font-size: 14px;
}

.name-text {
    font-weight: 700;
    color: #1e293b;
}

.sub-text {
    margin-top: 4px;
    color: #94a3b8;
    font-size: 11px;
}

.reg-number {
    font-weight: 600;
    color: #475569;
}

.status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 10px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
}

.status::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.status-active {
    background: #dcfce7;
    color: #15803d;
}

.status-inactive {
    background: #ffe4e6;
    color: #be123c;
}

.status-unknown {
    background: #f1f5f9;
    color: #64748b;
}

.actions {
    display: flex;
    align-items: center;
    gap: 7px;
}

.action-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 8px 11px;
    border: 1px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    transition: transform .2s, box-shadow .2s, background .2s;
}

.action-button:hover {
    transform: translateY(-2px);
}

.edit-button {
    background: #eff6ff;
    color: #2563eb;
    border-color: #dbeafe;
}

.edit-button:hover {
    background: #dbeafe;
    box-shadow: 0 5px 12px rgba(37,99,235,.12);
}

.delete-button {
    background: #fff1f2;
    color: #e11d48;
    border-color: #ffe4e6;
}

.delete-button:hover {
    background: #ffe4e6;
    box-shadow: 0 5px 12px rgba(225,29,72,.1);
}

.delete-form {
    display: inline;
    margin: 0;
}

/* EMPTY STATE */

.empty-state {
    padding: 55px 20px;
    text-align: center;
}

.empty-icon {
    display: grid;
    place-items: center;
    width: 65px;
    height: 65px;
    margin: 0 auto 17px;
    border-radius: 20px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 28px;
}

.empty-state h3 {
    margin: 0 0 8px;
    font-size: 16px;
}

.empty-state p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.7;
}

.empty-state a {
    display: inline-block;
    margin-top: 8px;
    padding: 10px 15px;
    background: #e0f7f3;
    color: var(--primary);
    border-radius: 9px;
    font-size: 12px;
    font-weight: 700;
}

/* FOOTER */

.panel-footer {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 25px;
    border-top: 1px solid var(--border);
    color: var(--muted);
    font-size: 11px;
    background: #fcfdff;
}

.footer {
    padding: 24px 0;
    color: #94a3b8;
    font-size: 11px;
    text-align: center;
}

/* ANIMATIONS */

@keyframes rise {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes rowIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 900px) {
    .summary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .summary-card:last-child {
        grid-column: 1 / -1;
    }

    .toolbar {
        align-items: flex-start;
        flex-direction: column;
    }

    .result-info {
        white-space: normal;
    }
}

@media (max-width: 600px) {
    .navbar {
        padding: 15px;
    }

    .brand h2 {
        font-size: 16px;
    }

    .brand p {
        font-size: 10px;
    }

    .brand-icon {
        width: 39px;
        height: 39px;
    }

    .back-button {
        padding: 9px;
        font-size: 11px;
    }

    main {
        width: calc(100% - 28px);
        margin-top: 24px;
    }

    .page-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .add-button {
        width: 100%;
    }

    .summary {
        grid-template-columns: 1fr;
        gap: 11px;
    }

    .summary-card:last-child {
        grid-column: auto;
    }

    .summary-card {
        padding: 16px;
    }

    .panel-heading {
        padding: 19px;
    }

    .toolbar {
        padding: 15px;
    }

    .search-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        width: 100%;
    }

    .search-button {
        padding: 10px;
    }

    .reset-button {
        grid-column: 1 / -1;
    }

    .panel-footer {
        padding: 14px 16px;
        flex-direction: column;
    }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
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

    <a class="back-button" href="<?= $baseUrl ?>/dashboard.php">
        ← Kembali ke Dashboard
    </a>
</header>

<main>
    <section class="page-heading">
        <div>
            <span class="eyebrow">Manajemen Akademik</span>
            <h1>Data Dosen</h1>
            <p>
                Kelola informasi dosen Universitas Muhammadiyah Bengkulu
                dalam satu halaman.
            </p>
        </div>

        <a class="add-button" href="<?= $baseUrl ?>/dosen_tambah.php">
            <span>＋</span> Tambah Dosen
        </a>
    </section>

    <section class="summary">
        <article class="summary-card">
            <div class="summary-icon">▤</div>
            <div>
                <p class="summary-label">Total Dosen</p>
                <div class="summary-number count-up"
                     data-count="<?= $totalDitemukan ?>">0</div>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-icon">⌕</div>
            <div>
                <p class="summary-label">Hasil Pencarian</p>
                <div class="summary-number">
                    <?= $totalDitemukan ?>
                </div>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-icon">◷</div>
            <div>
                <p class="summary-label">Sistem</p>
                <div class="summary-number" style="font-size:19px">
                    Online
                </div>
            </div>
        </article>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Daftar Dosen</h2>
                <p>Informasi dan status dosen yang tersimpan.</p>
            </div>

            <span class="count-badge">
                <?= $totalDitemukan ?> data
            </span>
        </div>

        <div class="toolbar">
            <form class="search-form" method="get" action="dosen.php">
                <label class="search-box">
                    <span>⌕</span>
                    <input
                        type="search"
                        name="cari"
                        value="<?= h($cari) ?>"
                        placeholder="Cari nama, NIP, NUPTK, registrasi..."
                        aria-label="Cari data dosen"
                    >
                </label>

                <button class="search-button" type="submit">
                    Cari
                </button>

                <?php if ($cari !== ''): ?>
                    <a class="reset-button" href="dosen.php">
                        Reset pencarian
                    </a>
                <?php endif; ?>
            </form>

            <div class="result-info">
                <?php if ($cari !== ''): ?>
                    Hasil untuk: <strong><?= h($cari) ?></strong>
                <?php else: ?>
                    Data dosen terbaru
                <?php endif; ?>
            </div>
        </div>

        <?php if ($totalDitemukan > 0): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Dosen</th>
                        <th>Nomor Registrasi</th>
                        <th>NUPTK</th>
                        <th>NIP</th>
                        <th>Program Studi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($daftarDosen as $i => $dosen): ?>
                    <?php
                    $status = trim((string)($dosen['nama_status'] ?? ''));

                    if (strtolower($status) === 'aktif') {
                        $statusClass = 'status-active';
                        $statusLabel = 'Aktif';
                    } elseif (
                        strtolower($status) === 'tidak aktif'
                    ) {
                        $statusClass = 'status-inactive';
                        $statusLabel = 'Tidak Aktif';
                    } else {
                        $statusClass = 'status-unknown';
                        $statusLabel = $status !== ''
                            ? $status
                            : 'Belum diatur';
                    }

                    $namaDosen = trim((string)$dosen['nama']);
                    $inisial = function_exists('mb_substr')
                        ? mb_strtoupper(mb_substr($namaDosen, 0, 1, 'UTF-8'), 'UTF-8')
                        : strtoupper(substr($namaDosen, 0, 1));
                    ?>
                    <tr>
                        <td class="row-number"><?= $i + 1 ?></td>

                        <td>
                            <div class="dosen-name">
                                <div class="avatar"><?= h($inisial) ?></div>
                                <div>
                                    <div class="name-text">
                                        <?= h($namaDosen) ?>
                                    </div>
                                    <div class="sub-text">
                                        ID: <?= (int)$dosen['id_dosen'] ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="reg-number">
                            <?= h($dosen['nomor_registrasi']) ?>
                        </td>

                        <td><?= h($dosen['nuptk'] ?: '—') ?></td>

                        <td><?= h($dosen['nip'] ?: '—') ?></td>

                        <td><?= h($dosen['nama_prodi'] ?: 'Belum diatur') ?></td>

                        <td>
                            <span class="status <?= h($statusClass) ?>">
                                <?= h($statusLabel) ?>
                            </span>
                        </td>

                        <td>
                            <div class="actions">
                                <a class="action-button edit-button"
                                   href="<?= $baseUrl ?>/dosen_edit.php?id=<?= (int)$dosen['id_dosen'] ?>">
                                    ✎ Edit
                                </a>

                                <form
                                    class="delete-form"
                                    method="post"
                                    action="<?= $baseUrl ?>/dosen_hapus.php"
                                    onsubmit="return confirm('Yakin ingin menghapus data dosen <?= h($namaDosen) ?>? Tindakan ini tidak dapat dibatalkan.');"
                                >
                                    <input
                                        type="hidden"
                                        name="id_dosen"
                                        value="<?= (int)$dosen['id_dosen'] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= h($_SESSION['csrf_token']) ?>"
                                    >

                                    <button
                                        class="action-button delete-button"
                                        type="submit"
                                    >
                                        × Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">⌕</div>
            <h3>
                <?= $cari !== ''
                    ? 'Data tidak ditemukan'
                    : 'Belum ada data dosen' ?>
            </h3>
            <p>
                <?= $cari !== ''
                    ? 'Coba kata kunci lain, atau reset pencarian untuk melihat semua data.'
                    : 'Mulai dengan menambahkan data dosen ke sistem.' ?>
            </p>

            <?php if ($cari !== ''): ?>
                <a href="dosen.php">Tampilkan semua data</a>
            <?php else: ?>
                <a href="<?= $baseUrl ?>/dosen_tambah.php">
                    ＋ Tambah Dosen
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="panel-footer">
            <span>SIM Dosen · Universitas Muhammadiyah Bengkulu</span>
            <span>
                Menampilkan <?= $totalDitemukan ?> data
            </span>
        </div>
    </section>

    <footer class="footer">
        Sistem Informasi Manajemen Dosen · SIM DOSEN
    </footer>
</main>

<script>
(function () {
    const reducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    document.querySelectorAll('.count-up').forEach(function (element) {
        const target = Number(element.dataset.count || 0);

        if (reducedMotion) {
            element.textContent = target.toLocaleString('id-ID');
            return;
        }

        const duration = 800;
        const start = performance.now();

        function animate(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 4);

            element.textContent = Math.round(
                target * eased
            ).toLocaleString('id-ID');

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }

        requestAnimationFrame(animate);
    });
})();
</script>

</body>
</html>

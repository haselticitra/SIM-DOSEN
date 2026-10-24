
<?php
require_once __DIR__ . '/includes/rbac.php';
require_once __DIR__ . '/config.php';

wajib_login();

$totalDosen = (int) $pdo->query(
    "SELECT COUNT(*) FROM dosen"
)->fetchColumn();

$dosenAktif = (int) $pdo->query("
    SELECT COUNT(*)
    FROM dosen d
    INNER JOIN status_aktivitas s
        ON s.id_status_aktivitas = d.id_status_aktivitas
    WHERE LOWER(TRIM(s.nama_status)) = 'aktif'
")->fetchColumn();

$dosenTidakAktif = (int) $pdo->query("
    SELECT COUNT(*)
    FROM dosen d
    INNER JOIN status_aktivitas s
        ON s.id_status_aktivitas = d.id_status_aktivitas
    WHERE LOWER(TRIM(s.nama_status)) = 'tidak aktif'
")->fetchColumn();

$nama = $_SESSION['nama'] ?? 'Pengguna';
$role = $_SESSION['nama_role'] ?? 'Pengguna';

$baseUrl = '/simdosen';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard | SIM Dosen</title>

<style>
:root {
    --primary: #0f766e;
    --primary-dark: #115e59;
    --bg: #f3f6fb;
    --text: #172b4d;
    --muted: #718096;
    --white: #ffffff;
    --border: #e6ebf2;
    --shadow: 0 10px 35px rgba(27, 47, 75, .07);
}

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
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

/* NAVBAR */

.navbar {
    position: relative;
    overflow: hidden;
    color: white;
    background: linear-gradient(115deg, #115e59, #0f766e, #0d9488);
    padding: 20px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    box-shadow: 0 5px 24px rgba(15, 118, 110, .18);
}

.navbar::after {
    content: "";
    position: absolute;
    width: 280px;
    height: 280px;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 50%;
    right: 15%;
    top: -190px;
    pointer-events: none;
}

.brand {
    display: flex;
    align-items: center;
    gap: 13px;
    position: relative;
    z-index: 1;
}

.brand-icon {
    width: 46px;
    height: 46px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 14px;
    font-size: 23px;
}

.brand h2 {
    font-size: 20px;
    margin: 0;
    letter-spacing: .4px;
}

.brand p {
    font-size: 12px;
    margin: 5px 0 0;
    color: #d5f5ef;
}

.logout {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: white;
    border: 1px solid rgba(255,255,255,.4);
    background: rgba(255,255,255,.08);
    padding: 11px 17px;
    border-radius: 10px;
    transition: background .25s, transform .25s;
}

.logout:hover {
    background: rgba(255,255,255,.19);
    transform: translateY(-2px);
}

/* LAYOUT */

main {
    max-width: 1250px;
    width: calc(100% - 48px);
    margin: 38px auto 55px;
}

.hero {
    position: relative;
    overflow: hidden;
    padding: 12px 0 28px;
    animation: slideUp .65s ease both;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
    background: #dff7f1;
    border-radius: 30px;
    padding: 8px 13px;
    letter-spacing: .5px;
    text-transform: uppercase;
}

.live-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 4px rgba(16,185,129,.13);
    animation: pulse 2s infinite;
}

.hero h1 {
    margin: 18px 0 10px;
    font-size: clamp(30px, 4vw, 43px);
    letter-spacing: -1.3px;
}

.hero p {
    color: var(--muted);
    line-height: 1.8;
    margin: 0;
}

.user-name {
    color: var(--primary);
    font-weight: 700;
}

.role-label {
    display: inline-block;
    background: white;
    border: 1px solid var(--border);
    padding: 4px 10px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
}

/* STAT CARDS */

.section-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 5px 0 17px;
}

.section-title h2 {
    font-size: 17px;
    margin: 0;
}

.section-title span {
    color: var(--muted);
    font-size: 12px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
}

.stat-card {
    --accent: #0f766e;
    --tint: #e0f7f3;
    position: relative;
    overflow: hidden;
    background: white;
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 24px;
    box-shadow: var(--shadow);
    opacity: 0;
    animation: slideUp .65s ease forwards;
    transition: transform .28s ease, box-shadow .28s ease;
}

.stat-card:nth-child(1) {
    animation-delay: .12s;
}

.stat-card:nth-child(2) {
    --accent: #16a34a;
    --tint: #dcfce7;
    animation-delay: .24s;
}

.stat-card:nth-child(3) {
    --accent: #e11d48;
    --tint: #ffe4e6;
    animation-delay: .36s;
}

.stat-card::after {
    content: "";
    position: absolute;
    width: 125px;
    height: 125px;
    border-radius: 50%;
    background: var(--tint);
    opacity: .7;
    right: -55px;
    top: -55px;
    transition: transform .35s ease;
}

.stat-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 18px 42px rgba(27, 47, 75, .12);
}

.stat-card:hover::after {
    transform: scale(1.35);
}

.stat-top {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.stat-icon {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: var(--tint);
    color: var(--accent);
    font-size: 23px;
}

.stat-tag {
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 6px 9px;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
}

.stat-label {
    color: var(--muted);
    font-size: 13px;
    margin: 22px 0 6px;
    position: relative;
    z-index: 1;
}

.stat-number {
    color: var(--accent);
    font-size: 39px;
    font-weight: 800;
    letter-spacing: -1px;
    position: relative;
    z-index: 1;
}

.stat-foot {
    color: #94a3b8;
    font-size: 11px;
    margin-top: 8px;
    position: relative;
    z-index: 1;
}

/* MENU */

.menu-panel {
    margin-top: 26px;
    background: white;
    padding: 28px;
    border-radius: 18px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    animation: slideUp .7s .35s ease both;
}

.menu-heading {
    display: flex;
    align-items: center;
    gap: 13px;
}

.menu-heading-icon {
    width: 45px;
    height: 45px;
    display: grid;
    place-items: center;
    border-radius: 13px;
    color: var(--primary);
    background: #e0f7f3;
    font-size: 21px;
}

.menu-heading h2 {
    font-size: 19px;
    margin: 0;
}

.menu-heading p {
    color: var(--muted);
    font-size: 12px;
    margin: 5px 0 0;
}

.menu-links {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-top: 24px;
}

.menu-link {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
    padding: 17px;
    color: var(--text);
    background: #fbfdff;
    border: 1px solid var(--border);
    border-radius: 13px;
    transition: transform .25s, border-color .25s,
                background .25s, box-shadow .25s;
}

.menu-link:hover {
    transform: translateY(-4px);
    border-color: #8edbd0;
    background: #f0fdfa;
    box-shadow: 0 10px 22px rgba(15,118,110,.08);
}

.menu-icon {
    flex: 0 0 43px;
    width: 43px;
    height: 43px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    color: var(--primary);
    background: #dff7f1;
    font-size: 20px;
}

.menu-copy {
    min-width: 0;
    flex: 1;
}

.menu-copy strong {
    display: block;
    font-size: 13px;
}

.menu-copy small {
    display: block;
    margin-top: 5px;
    color: var(--muted);
    font-size: 11px;
    line-height: 1.5;
}

.arrow {
    color: #94a3b8;
    transition: transform .25s;
}

.menu-link:hover .arrow {
    transform: translateX(4px);
}

.footer {
    text-align: center;
    color: #94a3b8;
    font-size: 11px;
    padding: 25px 0 0;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 4px rgba(16,185,129,.13); }
    50% { box-shadow: 0 0 0 7px rgba(16,185,129,.04); }
}

@media (max-width: 850px) {
    .stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .stat-card:last-child {
        grid-column: 1 / -1;
    }

    .menu-links {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 520px) {
    .navbar {
        padding: 16px;
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

    .logout {
        padding: 9px 11px;
        font-size: 12px;
    }

    main {
        width: calc(100% - 28px);
        margin-top: 24px;
    }

    .hero h1 {
        font-size: 32px;
    }

    .stats {
        grid-template-columns: 1fr;
        gap: 13px;
    }

    .stat-card:last-child {
        grid-column: auto;
    }

    .stat-card {
        padding: 20px;
    }

    .menu-panel {
        padding: 20px;
    }

    .section-title span {
        display: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
        scroll-behavior: auto !important;
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

    <a class="logout" href="<?= $baseUrl ?>/logout.php">
        ↪ Logout
    </a>
</header>

<main>
    <section class="hero">
        <div class="eyebrow">
            <span class="live-dot"></span>
            Sistem Informasi Manajemen
        </div>

        <h1>Dashboard</h1>

        <p>
            Selamat datang,
            <span class="user-name"><?= htmlspecialchars(
                $nama, ENT_QUOTES, 'UTF-8'
            ) ?></span>.
            Kelola data dosen dengan lebih mudah dan efisien.
        </p>

        <p style="margin-top:10px">
            Peran akun:
            <span class="role-label"><?= htmlspecialchars(
                $role, ENT_QUOTES, 'UTF-8'
            ) ?></span>
        </p>
    </section>

    <div class="section-title">
        <h2>Ringkasan Data</h2>
        <span>Statistik langsung dari database</span>
    </div>

    <section class="stats">
        <article class="stat-card">
            <div class="stat-top">
                <div class="stat-icon">▤</div>
                <span class="stat-tag">TOTAL</span>
            </div>
            <p class="stat-label">Total Data Dosen</p>
            <div class="stat-number count-up"
                 data-count="<?= $totalDosen ?>">0</div>
            <div class="stat-foot">Seluruh data dosen terdaftar</div>
        </article>

        <article class="stat-card">
            <div class="stat-top">
                <div class="stat-icon">✓</div>
                <span class="stat-tag">AKTIF</span>
            </div>
            <p class="stat-label">Dosen Berstatus Aktif</p>
            <div class="stat-number count-up"
                 data-count="<?= $dosenAktif ?>">0</div>
            <div class="stat-foot">Dosen dengan status aktif</div>
        </article>

        <article class="stat-card">
            <div class="stat-top">
                <div class="stat-icon">⊘</div>
                <span class="stat-tag">NONAKTIF</span>
            </div>
            <p class="stat-label">Dosen Tidak Aktif</p>
            <div class="stat-number count-up"
                 data-count="<?= $dosenTidakAktif ?>">0</div>
            <div class="stat-foot">Dosen dengan status tidak aktif</div>
        </article>
    </section>

    <section class="menu-panel">
        <div class="menu-heading">
            <div class="menu-heading-icon">⌘</div>
            <div>
                <h2>Menu Sistem</h2>
                <p>Pilih menu untuk melanjutkan pengelolaan data.</p>
            </div>
        </div>

        <div class="menu-links">
            <a class="menu-link" href="<?= $baseUrl ?>/dosen.php">
                <div class="menu-icon">♙</div>
                <div class="menu-copy">
                    <strong>Data Dosen</strong>
                    <small>Tambah, lihat, edit, dan hapus data</small>
                </div>
                <span class="arrow">→</span>
            </a>

            <a class="menu-link" href="<?= $baseUrl ?>/laporan/">
                <div class="menu-icon">▧</div>
                <div class="menu-copy">
                    <strong>Laporan</strong>
                    <small>Lihat laporan data dosen</small>
                </div>
                <span class="arrow">→</span>
            </a>

            <a class="menu-link"
               href="<?= $baseUrl ?>/api/dosen/index.php">
                <div class="menu-icon">{ }</div>
                <div class="menu-copy">
                    <strong>API Data Dosen</strong>
                    <small>Akses respons data berformat JSON</small>
                </div>
                <span class="arrow">→</span>
            </a>
        </div>
    </section>

    <footer class="footer">
        SIM Dosen · Universitas Muhammadiyah Bengkulu
    </footer>
</main>

<script>
(function () {
    const reduceMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const numbers = document.querySelectorAll('.count-up');

    numbers.forEach(function (element) {
        const target = Number(element.dataset.count || 0);

        if (reduceMotion) {
            element.textContent = target.toLocaleString('id-ID');
            return;
        }

        const duration = 900;
        const startTime = performance.now();

        function update(now) {
            const progress = Math.min(
                (now - startTime) / duration, 1
            );

            const eased = 1 - Math.pow(1 - progress, 4);
            const value = Math.round(target * eased);

            element.textContent = value.toLocaleString('id-ID');

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }

        requestAnimationFrame(update);
    });
})();
</script>

</body>
</html>

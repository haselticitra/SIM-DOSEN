
<?php
require_once __DIR__ . '/../includes/rbac.php';
require_once __DIR__ . '/../config.php';

wajib_login();

// Mengambil ringkasan jumlah dosen.
$stmt = $pdo->query("SELECT COUNT(*) FROM dosen");
$totalDosen = (int) $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM dosen d
    JOIN status_aktivitas s
        ON s.id_status_aktivitas = d.id_status_aktivitas
    WHERE s.nama_status = 'Aktif'
");
$dosenAktif = (int) $stmt->fetchColumn();

// Mengambil daftar dosen beserta program studi dan status.
$sql = "
    SELECT
        d.id_dosen,
        d.nomor_registrasi,
        d.nama,
        d.nuptk,
        d.nip,
        p.nama_prodi,
        s.nama_status
    FROM dosen d
    LEFT JOIN program_studi p
        ON d.id_prodi = p.id_prodi
    LEFT JOIN status_aktivitas s
        ON d.id_status_aktivitas = s.id_status_aktivitas
    ORDER BY d.nama ASC
";

$stmt = $pdo->query($sql);
$dataDosen = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($nilai): string
{
    return htmlspecialchars(
        (string) ($nilai ?? '-'),
        ENT_QUOTES,
        'UTF-8'
    );
}

$nama = $_SESSION['nama'] ?? 'Pengguna';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan SIM Dosen</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        header {
            background: #0f766e;
            color: white;
            padding: 20px 5%;
        }

        header h2 { margin: 0; }

        main {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            padding: 22px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }

        .ringkasan {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .angka {
            font-size: 32px;
            font-weight: bold;
            color: #0f766e;
        }

        .tombol {
            display: inline-block;
            padding: 10px 14px;
            margin: 4px 6px 8px 0;
            border-radius: 6px;
            background: #0f766e;
            color: white;
            text-decoration: none;
            border: 0;
            cursor: pointer;
            font-size: 14px;
        }

        .sekunder {
            background: #475569;
        }

        .tabel {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th, td {
            padding: 11px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
        }

        @media print {
            header, .navigasi, .aksi {
                display: none !important;
            }

            body {
                background: white;
            }

            main {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .panel {
                border: 0;
                padding: 8px 0;
            }
        }

        @media (max-width: 600px) {
            .ringkasan {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header>
    <h2>SIM DOSEN — Laporan</h2>
    <p>Universitas Muhammadiyah Bengkulu</p>
</header>

<main>
    <section class="panel navigasi">
        <h1>Laporan Data Dosen</h1>
        <p>
            Selamat datang,
            <strong><?= e($nama) ?></strong>.
        </p>

        <a class="tombol" href="../dashboard.php">
            Kembali ke Dashboard
        </a>

        <a class="tombol sekunder" href="../dosen.php">
            Data Dosen
        </a>

        <button class="tombol aksi" onclick="window.print()">
            Cetak / Simpan PDF
        </button>
    </section>

    <section class="ringkasan">
        <div class="panel">
            <p>Total Data Dosen</p>
            <div class="angka"><?= $totalDosen ?></div>
        </div>

        <div class="panel">
            <p>Dosen Berstatus Aktif</p>
            <div class="angka"><?= $dosenAktif ?></div>
        </div>
    </section>

    <section class="panel">
        <h2>Daftar Dosen</h2>
        <p>Jumlah data: <?= count($dataDosen) ?> dosen.</p>

        <div class="tabel">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Registrasi</th>
                        <th>Nama Dosen</th>
                        <th>NUPTK</th>
                        <th>NIP</th>
                        <th>Program Studi</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (empty($dataDosen)): ?>
                    <tr>
                        <td colspan="7">Belum ada data dosen.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dataDosen as $i => $dosen): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($dosen['nomor_registrasi']) ?></td>
                            <td><?= e($dosen['nama']) ?></td>
                            <td><?= e($dosen['nuptk']) ?></td>
                            <td><?= e($dosen['nip']) ?></td>
                            <td><?= e($dosen['nama_prodi']) ?></td>
                            <td><?= e($dosen['nama_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>

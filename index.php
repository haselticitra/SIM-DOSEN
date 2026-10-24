
<?php
require_once __DIR__ . '/config.php';

// Menghitung jumlah dosen yang tersimpan
$totalDosen = 0;

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM dosen");
    $totalDosen = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM Dosen | Universitas Muhammadiyah Bengkulu</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
            background: #f8fafc;
            line-height: 1.6;
        }

        header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }

        .navbar {
            max-width: 1150px;
            margin: auto;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #0f766e;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 19px;
            font-weight: bold;
        }

        .brand h2 {
            font-size: 17px;
            color: #0f172a;
        }

        .brand p {
            font-size: 12px;
            color: #64748b;
        }

        .nav-login {
            background: #0f766e;
            color: white;
            padding: 11px 22px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        .nav-login:hover,
        .btn-primary:hover {
            background: #115e59;
        }

        .hero {
            background: linear-gradient(135deg, #0f766e, #134e4a);
            color: white;
            padding: 85px 24px;
        }

        .hero-content {
            max-width: 1100px;
            margin: auto;
        }

        .badge {
            display: inline-block;
            padding: 7px 13px;
            border: 1px solid rgba(255,255,255,.4);
            background: rgba(255,255,255,.1);
            border-radius: 30px;
            font-size: 13px;
            margin-bottom: 22px;
        }

        .hero h1 {
            max-width: 750px;
            font-size: clamp(30px, 5vw, 48px);
            line-height: 1.2;
            margin-bottom: 22px;
        }

        .hero h1 span {
            color: #99f6e4;
        }

        .hero p {
            max-width: 650px;
            font-size: 16px;
            color: #e2f5f2;
            margin-bottom: 30px;
        }

        .btn-primary {
            display: inline-block;
            background: #ffffff;
            color: #115e59;
            padding: 13px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: .2s;
        }

        .btn-primary:hover {
            background: #ccfbf1;
        }

        .stats {
            max-width: 1100px;
            margin: -30px auto 0;
            padding: 0 24px;
            position: relative;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(15,23,42,.06);
            max-width: 320px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #0f766e;
        }

        .stat-card p {
            color: #64748b;
            font-size: 14px;
        }

        .features {
            max-width: 1100px;
            margin: auto;
            padding: 75px 24px;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 35px;
        }

        .section-heading h2 {
            font-size: 29px;
            margin-bottom: 10px;
        }

        .section-heading p {
            color: #64748b;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .feature-card {
            background: white;
            padding: 27px;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: #ccfbf1;
            color: #0f766e;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 23px;
            margin-bottom: 17px;
        }

        .feature-card h3 {
            margin-bottom: 9px;
            font-size: 18px;
        }

        .feature-card p {
            color: #64748b;
            font-size: 14px;
        }

        footer {
            text-align: center;
            background: #0f172a;
            color: #cbd5e1;
            padding: 25px 20px;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            .navbar {
                padding: 15px;
            }

            .brand h2 {
                font-size: 14px;
            }

            .brand p {
                font-size: 10px;
            }

            .logo {
                width: 40px;
                height: 40px;
            }

            .nav-login {
                padding: 10px 13px;
                font-size: 12px;
            }

            .hero {
                padding: 65px 22px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .features {
                padding-top: 55px;
            }
        }
    </style>
</head>

<body>

<header>
    <nav class="navbar">
        <div class="brand">
            <div class="logo">SD</div>
            <div>
                <h2>SIM DOSEN</h2>
                <p>Universitas Muhammadiyah Bengkulu</p>
            </div>
        </div>

        <a href="login.php" class="nav-login">Login Pengguna</a>
    </nav>
</header>

<main>
    <section class="hero">
        <div class="hero-content">
            <div class="badge">Sistem Informasi Akademik</div>

            <h1>
                Sistem Informasi
                <span>Manajemen Dosen</span>
            </h1>

            <p>
                Platform pengelolaan data dosen Universitas Muhammadiyah
                Bengkulu yang terintegrasi untuk mendukung administrasi,
                pengelolaan informasi kepegawaian, dan penyajian laporan
                dosen secara lebih terstruktur.
            </p>

            <a href="login.php" class="btn-primary">
                Masuk ke Sistem &rarr;
            </a>
        </div>
    </section>

    <section class="stats">
        <div class="stat-card">
            <div class="number">
                <?= number_format($totalDosen, 0, ',', '.') ?>
            </div>
            <p>Total data dosen dalam sistem</p>
        </div>
    </section>

    <section class="features">
        <div class="section-heading">
            <h2>Fitur Sistem</h2>
            <p>Mendukung pengelolaan informasi dosen secara terstruktur.</p>
        </div>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">&#128101;</div>
                <h3>Data Dosen</h3>
                <p>
                    Mengelola informasi dosen, nomor registrasi, NUPTK,
                    NIP, pendidikan, jabatan fungsional, dan status
                    kepegawaian.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">&#128274;</div>
                <h3>Hak Akses Pengguna</h3>
                <p>
                    Mengatur akses berdasarkan peran pengguna agar
                    setiap pengguna dapat mengakses fitur sesuai
                    kewenangannya.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">&#128196;</div>
                <h3>Laporan dan API</h3>
                <p>
                    Menyediakan laporan daftar dosen serta layanan API
                    untuk pertukaran data dalam format JSON.
                </p>
            </div>
        </div>
    </section>
</main>

<footer>
    <p>
        &copy; <?= date('Y') ?> SIM Dosen -
        Universitas Muhammadiyah Bengkulu
    </p>
    <p>Sistem Informasi Manajemen Dosen</p>
</footer>

</body>
</html>

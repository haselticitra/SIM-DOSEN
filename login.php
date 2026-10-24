
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['id_pengguna'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT
                    p.id_pengguna,
                    p.nama,
                    p.username,
                    p.password,
                    p.id_role,
                    r.nama_role
                FROM pengguna p
                JOIN role r ON r.id_role = p.id_role
                WHERE p.username = ? AND p.aktif = 1
                LIMIT 1
            ");

            $stmt->execute([$username]);
            $pengguna = $stmt->fetch();

            if (
                $pengguna &&
                password_verify($password, $pengguna['password'])
            ) {
                session_regenerate_id(true);

                $_SESSION['id_pengguna'] = $pengguna['id_pengguna'];
                $_SESSION['nama'] = $pengguna['nama'];
                $_SESSION['username'] = $pengguna['username'];
                $_SESSION['id_role'] = $pengguna['id_role'];
                $_SESSION['nama_role'] = $pengguna['nama_role'];

                header('Location: dashboard.php');
                exit;
            }

            $error = 'Username atau password salah.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login SIM Dosen</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(15, 23, 42, .08);
        }

        .logo {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            background: #0f766e;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 22px;
            margin-bottom: 22px;
        }

        h1 {
            font-size: 26px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            font-size: 14px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: 2px solid #99f6e4;
            border-color: #0f766e;
        }

        button {
            width: 100%;
            margin-top: 23px;
            padding: 13px;
            background: #0f766e;
            color: white;
            border: 0;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #115e59;
        }

        .error {
            background: #fef2f2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #0f766e;
            text-decoration: none;
            font-size: 14px;
        }

        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo">SD</div>

    <h1>Selamat Datang</h1>
    <p class="subtitle">
        Login ke Sistem Informasi Manajemen Dosen
        Universitas Muhammadiyah Bengkulu.
    </p>

    <?php if ($error !== ''): ?>
        <div class="error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            maxlength="50"
            autocomplete="username"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            autocomplete="current-password"
            required
        >

        <button type="submit">Login</button>
    </form>

    <a class="back" href="index.php">
        &larr; Kembali ke halaman utama
    </a>
</div>

</body>
</html>


<?php
$host = 'sql210.infinityfree.com';
$db   = 'if0_42995845_simdosen';
$user = 'if0_42995845';
$pass = 'mo6rVACUIqrKR';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Koneksi database gagal. Periksa konfigurasi database.');
}
?>

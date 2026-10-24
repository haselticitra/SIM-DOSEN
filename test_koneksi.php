
<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT DATABASE() AS nama_database");
    $hasil = $stmt->fetch();

    echo "<h2>Koneksi database berhasil!</h2>";
    echo "<p>Database aktif: "
        . htmlspecialchars($hasil['nama_database'])
        . "</p>";

    $stmt = $pdo->query("SHOW TABLES");
    $tabel = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Tabel yang ditemukan:</h3><ul>";

    foreach ($tabel as $nama_tabel) {
        echo "<li>" . htmlspecialchars($nama_tabel) . "</li>";
    }

    echo "</ul>";
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "Gagal membaca database. Periksa konfigurasi dan tabel.";
}
?>

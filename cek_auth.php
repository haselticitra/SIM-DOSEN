
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Tahap 1: PHP berjalan.<br>";

require_once __DIR__ . '/config.php';
echo "Tahap 2: config.php berhasil dimuat.<br>";

require_once __DIR__ . '/includes/auth.php';
echo "Tahap 3: auth.php berhasil dimuat.<br>";

echo "Pemeriksaan selesai.";
?>

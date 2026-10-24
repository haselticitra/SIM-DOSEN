
<?php
$path = __DIR__ . '/includes/auth.php';

echo "Folder simdosen: " . (is_dir(__DIR__) ? "ADA" : "TIDAK ADA") . "<br>";
echo "Folder includes: " . (is_dir(__DIR__ . '/includes') ? "ADA" : "TIDAK ADA") . "<br>";
echo "File auth.php: " . (file_exists($path) ? "ADA" : "TIDAK ADA") . "<br>";
echo "Lokasi yang diperiksa: " . $path;
?>

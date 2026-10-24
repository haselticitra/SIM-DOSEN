
<?php
require_once __DIR__ . '/auth.php';

function wajib_role(array $daftar_role): void
{
    wajib_login();

    $role = $_SESSION['nama_role'] ?? '';

    if (!in_array($role, $daftar_role, true)) {
        http_response_code(403);
        exit('Akses ditolak. Anda tidak memiliki hak akses.');
    }
}

function punya_role(string $role): bool
{
    return ($_SESSION['nama_role'] ?? '') === $role;
}

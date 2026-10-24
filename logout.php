
<?php
require_once __DIR__ . '/includes/auth.php';

logout_pengguna();

header('Location: login.php');
exit;

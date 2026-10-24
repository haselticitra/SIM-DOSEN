
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function wajib_login(): void
{
    if (empty($_SESSION['id_pengguna'])) {
        header('Location: /simdosen/login.php');
        exit;
    }
}

function logout_pengguna(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

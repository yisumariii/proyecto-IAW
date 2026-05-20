<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true, 
        'cookie_samesite' => 'Strict', 
        'cookie_secure' => false,   
    ]);
}

function login($username) {
    $_SESSION['user'] = $username;
    session_regenerate_id(true);
}


function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}


function is_logged_in() {
    return isset($_SESSION['user']);
}


function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

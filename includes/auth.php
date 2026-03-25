<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_admin(): bool {
    return !empty($_SESSION['admin_logged_in']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: /admin/');
        exit;
    }
}

function admin_login(string $password): bool {
    require_once __DIR__ . '/functions.php';
    $config = load_json_assoc(data_path('config.json'));
    $hash = $config['password_hash'] ?? null;

    // First-time setup: no password set yet
    if (empty($hash)) return false;

    if (password_verify($password, $hash)) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

function admin_logout(): void {
    $_SESSION = [];
    session_destroy();
}

function has_admin_password(): bool {
    require_once __DIR__ . '/functions.php';
    $config = load_json_assoc(data_path('config.json'));
    return !empty($config['password_hash']);
}

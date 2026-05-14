<?php
require_once __DIR__ . '/config.php';
session_name(SESSION_NAME);
session_start();

function is_logged_in() {
    return !empty($_SESSION['admin_logged_in']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function login($password) {
    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = [];
    session_destroy();
}

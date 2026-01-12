<?php
// helpers.php
session_start();

function old($name) {
    return isset($_POST[$name]) ? htmlspecialchars($_POST[$name]) : '';
}

function flash_set($key, $msg) {
    $_SESSION['flash'][$key] = $msg;
}

function flash_get($key) {
    if (!isset($_SESSION['flash'][$key])) return null;
    $msg = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

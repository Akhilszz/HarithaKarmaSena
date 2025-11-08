<?php
// ---------------------------------------------------
// config.php - database connection and helpers (fixed)
// ---------------------------------------------------

// Prevent re-running this file more than once
if (defined('HKS_CONFIG_LOADED')) {
    return;
}
define('HKS_CONFIG_LOADED', true);

// Start session once
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Database credentials
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'hks';

// Database connection
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('DB Connect Error: ' . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

// Helpers (guarded to prevent redeclaration)

if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user']);
    }
}

if (!function_exists('current_user')) {
    function current_user() {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('require_role')) {
    function require_role($role){
        if(!is_logged_in() || ($_SESSION['user']['role'] ?? '') !== $role){
            header('Location: login.php'); 
            exit;
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(){
        if(empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf($token){
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('e')) {
    function e($s){
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }
}

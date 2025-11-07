<?php
// config.php - database connection and helpers
session_start();

// Update these settings
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'hks';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('DB Connect Error: ' . $mysqli->connect_error);
}
// Basic helpers
function is_logged_in() {
    return isset($_SESSION['user']);
}
function current_user() {
    return $_SESSION['user'] ?? null;
}
function require_role($role){
    if(!is_logged_in() || $_SESSION['user']['role'] !== $role){
        header('Location: login.php'); exit;
    }
}
// CSRF helpers (simple)
function csrf_token(){
    if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf_token'];
}
function verify_csrf($token){
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
// Escape helper
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

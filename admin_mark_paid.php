<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $mysqli->query("UPDATE collection_requests SET payment_status = 'paid' WHERE id = $id");
    $_SESSION['success'] = "Payment marked as paid successfully";
}

header('Location: admin_payments.php');
exit;
<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='worker') header('Location: login.php');
$action = $_GET['action'] ?? null; $id = intval($_GET['id'] ?? 0);
if($action=='accept'){
  $mysqli->query("UPDATE collection_requests SET status='accepted' WHERE id={$id}");
} elseif($action=='collected'){
  $mysqli->query("UPDATE collection_requests SET status='collected' WHERE id={$id}");
} elseif($action=='cancel'){
  $mysqli->query("UPDATE collection_requests SET status='cancelled' WHERE id={$id}");
}
header('Location: worker_dashboard.php'); exit;

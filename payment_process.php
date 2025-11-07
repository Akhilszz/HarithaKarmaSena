<?php
require 'config.php';
if(!is_logged_in()) header('Location: login.php');
if($_SERVER['REQUEST_METHOD']==='POST'){
  // In production, verify payment signature/webhook before marking paid.
  $uid = $_SESSION['user']['id'];
  $mysqli->query("UPDATE users SET dues=0 WHERE id={$uid}");
}
header('Location: user_dashboard.php'); exit;

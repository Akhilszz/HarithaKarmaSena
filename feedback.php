<?php
require 'config.php';
if(!is_logged_in()) header('Location: login.php');
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verify_csrf($_POST['csrf'] ?? '')){ header('Location: user_dashboard.php'); exit; }
  $msg = trim($_POST['message'] ?? ''); $uid = $_SESSION['user']['id'];
  if($msg){
    $stmt = $mysqli->prepare('INSERT INTO feedbacks (user_id,message) VALUES (?,?)'); $stmt->bind_param('is',$uid,$msg); $stmt->execute();
  }
}
header('Location: user_dashboard.php'); exit;

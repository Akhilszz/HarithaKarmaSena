<?php
require 'config.php';
if(!is_logged_in()) header('Location: login.php');
$uid = $_SESSION['user']['id'];
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verify_csrf($_POST['csrf'] ?? '')){ $err='Invalid CSRF'; }
  else {
    $name = trim($_POST['name'] ?? ''); $phone = trim($_POST['phone'] ?? ''); $addr = trim($_POST['address'] ?? '');
    $stmt = $mysqli->prepare('UPDATE users SET name=?, phone=?, address=? WHERE id=?'); $stmt->bind_param('sssi',$name,$phone,$addr,$uid); $stmt->execute();
    $_SESSION['user']['name'] = $name;
  }
}
$res = $mysqli->query("SELECT * FROM users WHERE id={$uid}"); $user = $res->fetch_assoc();
?>
<!doctype html><html><head><meta charset='utf-8'><title>Profile</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-green-50'>
<div class='max-w-md mx-auto bg-white p-6 mt-10 rounded shadow'>
<h2 class='text-xl font-semibold mb-4'>Edit Profile</h2>
<?php if(!empty($err)) echo '<div class="bg-red-100 text-red-700 p-3 rounded mb-3">'.e($err).'</div>'; ?>
<form method='post' class='space-y-3'>
  <input name='name' value='<?php echo e($user['name']); ?>' required class='w-full p-2 border rounded'>
  <input name='phone' value='<?php echo e($user['phone']); ?>' class='w-full p-2 border rounded'>
  <textarea name='address' class='w-full p-2 border rounded'><?php echo e($user['address']); ?></textarea>
  <input type='hidden' name='csrf' value='<?php echo csrf_token(); ?>'>
  <div><input type='submit' value='Save' class='bg-green-600 text-white px-4 py-2 rounded'></div>
</form>
</div></body></html>

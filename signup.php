<?php
require 'config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $phone = trim($_POST['phone'] ?? '');
  $token = $_POST['csrf'] ?? '';
  if(!verify_csrf($token)){ $err = 'Invalid CSRF token'; }
  elseif(!$name || !$email || !$pass){ $err = 'Please fill required fields.'; }
  else {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (name,email,password,phone) VALUES (?,?,?,?)');
    $stmt->bind_param('ssss',$name,$email,$hash,$phone);
    if($stmt->execute()){ header('Location: login.php'); exit; } else { $err = 'Error: '.$stmt->error; }
  }
}
?>
<!doctype html><html><head><meta charset='utf-8'><title>Sign Up</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-green-50'>
<div class='max-w-md mx-auto bg-white p-6 mt-10 rounded shadow'>
<h2 class='text-xl font-semibold mb-4'>Sign Up</h2>
<?php if(!empty($err)) echo '<div class="bg-red-100 text-red-700 p-3 rounded mb-3">'.e($err).'</div>'; ?>
<form method='post' class='space-y-3'>
  <input name='name' placeholder='Full name' required class='w-full p-2 border rounded' value='<?php echo e($_POST['name'] ?? ''); ?>'>
  <input name='email' type='email' placeholder='Email' required class='w-full p-2 border rounded' value='<?php echo e($_POST['email'] ?? ''); ?>'>
  <input name='password' type='password' placeholder='Password' required class='w-full p-2 border rounded'>
  <input name='phone' placeholder='Phone' class='w-full p-2 border rounded' value='<?php echo e($_POST['phone'] ?? ''); ?>'>
  <input type='hidden' name='csrf' value='<?php echo csrf_token(); ?>'>
  <div><input type='submit' value='Create account' class='bg-green-600 text-white px-4 py-2 rounded'></div>
</form>
<p class='mt-3 text-sm'>Already have an account? <a href='login.php' class='text-green-600'>Login</a></p>
</div></body></html>

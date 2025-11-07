<?php
require 'config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $token = $_POST['csrf'] ?? '';
  if(!verify_csrf($token)){ $err = 'Invalid CSRF token'; }
  else {
    $stmt = $mysqli->prepare('SELECT id,name,email,password,role,dues FROM users WHERE email=? LIMIT 1');
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows===1){
      $u = $res->fetch_assoc();
      if(password_verify($pass, $u['password'])){
        unset($u['password']);
        $_SESSION['user'] = $u;
        if($u['role']==='admin') header('Location: admin_dashboard.php');
        elseif($u['role']==='worker') header('Location: worker_dashboard.php');
        else header('Location: user_dashboard.php');
        exit;
      } else $err = 'Invalid credentials';
    } else $err = 'Invalid credentials';
  }
}
?>
<!doctype html><html><head><meta charset='utf-8'><title>Login</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-green-50'>
<div class='max-w-md mx-auto bg-white p-6 mt-10 rounded shadow'>
<h2 class='text-xl font-semibold mb-4'>Login</h2>
<?php if(!empty($err)) echo '<div class="bg-red-100 text-red-700 p-3 rounded mb-3">'.e($err).'</div>'; ?>
<form method='post' class='space-y-3'>
  <input name='email' type='email' placeholder='Email' required class='w-full p-2 border rounded' value='<?php echo e($_POST['email'] ?? ''); ?>'>
  <input name='password' type='password' placeholder='Password' required class='w-full p-2 border rounded'>
  <input type='hidden' name='csrf' value='<?php echo csrf_token(); ?>'>
  <div><input type='submit' value='Login' class='bg-green-600 text-white px-4 py-2 rounded'></div>
</form>
</div></body></html>

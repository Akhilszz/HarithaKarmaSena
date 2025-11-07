<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');
$id = intval($_GET['id'] ?? 0);
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verify_csrf($_POST['csrf'] ?? '')){ $err = 'Invalid CSRF'; }
  else {
    $role = $_POST['role']; $dues = floatval($_POST['dues']);
    $stmt = $mysqli->prepare('UPDATE users SET role=?, dues=? WHERE id=?');
    $stmt->bind_param('sdi',$role,$dues,$id); $stmt->execute();
    header('Location: admin_dashboard.php'); exit;
  }
}
$res = $mysqli->query("SELECT * FROM users WHERE id={$id}"); $u = $res->fetch_assoc();
?>
<!doctype html><html><head><meta charset='utf-8'><title>Edit User</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-green-50'>
<div class='max-w-md mx-auto bg-white p-6 mt-10 rounded shadow'>
<h2 class='text-xl font-semibold mb-4'>Edit User</h2>
<?php if(!empty($err)) echo '<div class="bg-red-100 text-red-700 p-3 rounded mb-3">'.e($err).'</div>'; ?>
<form method='post' class='space-y-3'>
  <label class='block'>Role
    <select name='role' class='w-full p-2 border rounded'>
      <option value='user' <?php if($u['role']=='user') echo 'selected'; ?>>User</option>
      <option value='worker' <?php if($u['role']=='worker') echo 'selected'; ?>>Worker</option>
      <option value='admin' <?php if($u['role']=='admin') echo 'selected'; ?>>Admin</option>
    </select>
  </label>
  <label class='block'>Dues
    <input name='dues' value='<?php echo e($u['dues']); ?>' class='w-full p-2 border rounded'>
  </label>
  <input type='hidden' name='csrf' value='<?php echo csrf_token(); ?>'>
  <div><input type='submit' value='Save' class='bg-green-600 text-white px-4 py-2 rounded'></div>
</form>
</div></body></html>

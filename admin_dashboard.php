<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');
$users = $mysqli->query('SELECT * FROM users ORDER BY created_at DESC');
?>
<!doctype html><html><head><meta charset='utf-8'><title>Admin</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-green-50'>
<div class='max-w-6xl mx-auto bg-white p-6 mt-10 rounded shadow'>
  <h2 class='text-xl font-semibold mb-4'>Admin Dashboard</h2>
  <div class='mb-6 p-4 border rounded'>
    <h3 class='font-medium mb-2'>Users & Workers</h3>
    <table class='w-full text-left border'>
      <tr class='bg-gray-100'><th class='p-2'>ID</th><th class='p-2'>Name</th><th class='p-2'>Email</th><th class='p-2'>Role</th><th class='p-2'>Dues</th><th class='p-2'>Action</th></tr>
      <?php while($u=$users->fetch_assoc()): ?>
        <tr>
          <td class='p-2'><?php echo $u['id']; ?></td>
          <td class='p-2'><?php echo e($u['name']); ?></td>
          <td class='p-2'><?php echo e($u['email']); ?></td>
          <td class='p-2'><?php echo $u['role']; ?></td>
          <td class='p-2'><?php echo number_format($u['dues'],2); ?></td>
          <td class='p-2'><a href='admin_edit_user.php?id=<?php echo $u['id']; ?>'><button class='bg-yellow-600 text-white px-2 py-1 rounded'>Edit</button></a></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
  <div class='mb-6 p-4 border rounded'>
    <h3 class='font-medium mb-2'>Collections</h3>
    <?php $reqs = $mysqli->query("SELECT cr.*, u.name FROM collection_requests cr JOIN users u ON cr.user_id=u.id ORDER BY cr.created_at DESC"); ?>
    <table class='w-full text-left border'>
      <tr class='bg-gray-100'><th class='p-2'>ID</th><th class='p-2'>User</th><th class='p-2'>Addr</th><th class='p-2'>Status</th><th class='p-2'>Payment</th></tr>
      <?php while($r=$reqs->fetch_assoc()): ?>
        <tr>
          <td class='p-2'><?php echo $r['id']; ?></td>
          <td class='p-2'><?php echo e($r['name']); ?></td>
          <td class='p-2'><?php echo e($r['address']); ?></td>
          <td class='p-2'><?php echo $r['status']; ?></td>
          <td class='p-2'><?php echo $r['payment_status']; ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div></body></html>

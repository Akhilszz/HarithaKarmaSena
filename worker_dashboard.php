<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='worker') header('Location: login.php');
$reqs = $mysqli->query("SELECT cr.*, u.name AS user_name, u.phone FROM collection_requests cr JOIN users u ON cr.user_id=u.id ORDER BY cr.created_at DESC");
?>
<!doctype html><html><head><meta charset='utf-8'><title>Worker Dashboard</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-green-50'>
<div class='max-w-4xl mx-auto bg-white p-6 mt-10 rounded shadow'>
  <h2 class='text-xl font-semibold mb-4'>Worker Dashboard</h2>
  <div class='mb-6 p-4 border rounded'>
    <h3 class='font-medium mb-2'>Collection requests</h3>
    <table class='w-full text-left border'>
      <tr class='bg-gray-100'><th class='p-2'>ID</th><th class='p-2'>User</th><th class='p-2'>Addr</th><th class='p-2'>Date</th><th class='p-2'>Status</th><th class='p-2'>Action</th></tr>
      <?php while($r=$reqs->fetch_assoc()): ?>
        <tr>
          <td class='p-2'><?php echo $r['id']; ?></td>
          <td class='p-2'><?php echo e($r['user_name']); ?> (<?php echo e($r['phone']); ?>)</td>
          <td class='p-2'><?php echo e($r['address']); ?></td>
          <td class='p-2'><?php echo $r['schedule_date']; ?></td>
          <td class='p-2'><?php echo $r['status']; ?></td>
          <td class='p-2'><?php if($r['status']=='pending'): ?><a href='worker_action.php?action=accept&id=<?php echo $r['id']; ?>'><button class='bg-indigo-600 text-white px-2 py-1 rounded'>Accept</button></a><?php else: ?>--<?php endif; ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
  <div class='mb-6 p-4 border rounded'>
    <h3 class='font-medium mb-2'>Complaints & Feedback</h3>
    <?php
    $compl = $mysqli->query("SELECT c.*, u.name FROM complaints c JOIN users u ON c.user_id=u.id ORDER BY c.created_at DESC");
    while($c=$compl->fetch_assoc()): ?>
      <div class='p-3 bg-gray-50 rounded mb-2'><strong><?php echo e($c['name']); ?></strong>: <?php echo e($c['message']); ?> <div class='text-sm text-gray-500'>Status: <?php echo $c['status']; ?></div></div>
    <?php endwhile; ?>
    <?php $fb = $mysqli->query("SELECT f.*, u.name FROM feedbacks f JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC"); while($f=$fb->fetch_assoc()): ?>
      <div class='p-3 bg-gray-50 rounded mb-2'><strong><?php echo e($f['name']); ?></strong>: <?php echo e($f['message']); ?></div>
    <?php endwhile; ?>
  </div>
</div></body></html>

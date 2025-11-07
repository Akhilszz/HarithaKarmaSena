<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='user'){ header('Location: login.php'); exit; }
$uid = $_SESSION['user']['id'];
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['request_collection'])){
  if(!verify_csrf($_POST['csrf'] ?? '')){ $err = 'Invalid CSRF'; }
  else {
    $addr = $_POST['address'] ?? ''; $date = $_POST['schedule_date'] ?? null;
    $stmt = $mysqli->prepare('INSERT INTO collection_requests (user_id,address,schedule_date) VALUES (?,?,?)');
    $stmt->bind_param('iss',$uid,$addr,$date); $stmt->execute();
  }
}
$reqs = $mysqli->prepare('SELECT * FROM collection_requests WHERE user_id=? ORDER BY created_at DESC');
$reqs->bind_param('i',$uid); $reqs->execute(); $res_reqs = $reqs->get_result();
$dues = 0.00; $r = $mysqli->query("SELECT dues FROM users WHERE id={$uid}")->fetch_assoc(); if($r) $dues = $r['dues'];
?>
<!doctype html><html><head><meta charset='utf-8'><title>User Dashboard</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-green-50'>
<div class='max-w-4xl mx-auto bg-white p-6 mt-10 rounded shadow'>
  <h2 class='text-xl font-semibold mb-4'>Your Dashboard</h2>
  <?php if(!empty($err)) echo '<div class="bg-red-100 text-red-700 p-3 rounded mb-3">'.e($err).'</div>'; ?>
  <div class='mb-6 p-4 border rounded'>
    <h3 class='font-medium mb-2'>Request garbage collection</h3>
    <form method='post' class='space-y-2'>
      <input name='address' placeholder='Pickup address' required class='w-full p-2 border rounded'>
      <input name='schedule_date' type='date' required class='w-full p-2 border rounded'>
      <input type='hidden' name='csrf' value='<?php echo csrf_token(); ?>'>
      <button name='request_collection' class='bg-green-600 text-white px-4 py-2 rounded'>Request Pickup</button>
    </form>
  </div>

  <div class='mb-6 p-4 border rounded'>
    <h3 class='font-medium mb-2'>Requests</h3>
    <table class='w-full text-left border'>
      <tr class='bg-gray-100'><th class='p-2'>ID</th><th class='p-2'>Address</th><th class='p-2'>Date</th><th class='p-2'>Status</th><th class='p-2'>Payment</th></tr>
      <?php while($r = $res_reqs->fetch_assoc()): ?>
        <tr><td class='p-2'><?php echo $r['id']; ?></td><td class='p-2'><?php echo e($r['address']); ?></td><td class='p-2'><?php echo $r['schedule_date']; ?></td><td class='p-2'><?php echo $r['status']; ?></td><td class='p-2'><?php echo $r['payment_status']; ?> <?php if($r['payment_status']=='pending'): ?><a href='payment.php?req=<?php echo $r['id']; ?>'><button class='bg-indigo-600 text-white px-2 py-1 rounded'>Pay</button></a><?php endif; ?></td></tr>
      <?php endwhile; ?>
    </table>
  </div>

  <div class='mb-6 p-4 border rounded'>
    <h3 class='font-medium'>Dues</h3>
    <p>Your unpaid dues: <strong>₹ <?php echo number_format($dues,2); ?></strong></p>
    <a href='payment.php'><button class='bg-green-600 text-white px-4 py-2 rounded'>Pay Dues</button></a>
  </div>

  <div class='mb-6 p-4 border rounded'>
    <h3 class='font-medium mb-2'>Complaints</h3>
    <form method='post' action='complaint.php' class='space-y-2'>
      <textarea name='message' placeholder='Describe complaint' required class='w-full p-2 border rounded'></textarea>
      <input type='hidden' name='csrf' value='<?php echo csrf_token(); ?>'>
      <button type='submit' class='bg-red-600 text-white px-4 py-2 rounded'>Submit Complaint</button>
    </form>
  </div>

  <div class='mb-6 p-4 border rounded'>
    <h3 class='font-medium mb-2'>Feedback</h3>
    <form method='post' action='feedback.php' class='space-y-2'>
      <textarea name='message' placeholder='Your feedback' required class='w-full p-2 border rounded'></textarea>
      <input type='hidden' name='csrf' value='<?php echo csrf_token(); ?>'>
      <button type='submit' class='bg-blue-600 text-white px-4 py-2 rounded'>Send Feedback</button>
    </form>
  </div>
</div></body></html>

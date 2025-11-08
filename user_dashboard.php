<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='user'){ 
  header('Location: login.php'); 
  exit; 
}

$uid = $_SESSION['user']['id'];
$page_title = "User Dashboard";

// Fixed collection fee
$COLLECTION_FEE = 50.00;

// Handle collection request submission
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['request_collection'])){
  if(!verify_csrf($_POST['csrf'] ?? '')){ 
    $err = 'Invalid CSRF token'; 
  } else {
    $addr = $_POST['address'] ?? ''; 
    $date = $_POST['schedule_date'] ?? null;
    
    if(empty($addr) || empty($date)){
      $err = 'Please fill all required fields';
    } else {
      // Start transaction
      $mysqli->begin_transaction();
      
      try {
        // 1. Insert collection request
        $stmt = $mysqli->prepare('INSERT INTO collection_requests (user_id, address, schedule_date) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $uid, $addr, $date); 
        
        if(!$stmt->execute()){
          throw new Exception('Error submitting request: ' . $stmt->error);
        }
        
        $request_id = $mysqli->insert_id;
        
        // 2. Add ₹50 to user dues
        $update_dues = $mysqli->prepare('UPDATE users SET dues = dues + ? WHERE id = ?');
        $update_dues->bind_param('di', $COLLECTION_FEE, $uid);
        
        if(!$update_dues->execute()){
          throw new Exception('Error updating dues: ' . $update_dues->error);
        }
        
        // Commit transaction
        $mysqli->commit();
        $success = 'Collection request submitted successfully! ₹' . $COLLECTION_FEE . ' has been added to your dues.';
        
      } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();
        $err = $e->getMessage();
      }
    }
  }
}

// Get user stats
$stats_stmt = $mysqli->prepare('
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_requests,
        SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_requests,
        SUM(CASE WHEN status = "collected" THEN 1 ELSE 0 END) as collected_requests
    FROM collection_requests 
    WHERE user_id=?
');
$stats_stmt->bind_param('i', $uid);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get user dues
$dues = 0.00; 
$r = $mysqli->query("SELECT dues FROM users WHERE id={$uid}")->fetch_assoc(); 
if($r) $dues = $r['dues'];

// Get recent requests
$reqs = $mysqli->prepare('SELECT * FROM collection_requests WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
$reqs->bind_param('i', $uid); 
$reqs->execute(); 
$res_reqs = $reqs->get_result();

// Get pending payments count
$pending_payments = $mysqli->prepare('SELECT COUNT(*) as count FROM collection_requests WHERE user_id=? AND payment_status="pending" AND status != "cancelled"');
$pending_payments->bind_param('i', $uid);
$pending_payments->execute();
$pending_count = $pending_payments->get_result()->fetch_assoc()['count'];

require 'header.php';
?>

<!-- Main Content -->
<div class="flex-1">
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Welcome Section -->
    <div class="glass rounded-2xl shadow-xl p-8 gradient-border mb-8">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">
            Welcome back, <?php echo e($_SESSION['user']['name']); ?>!
          </h1>
          <p class="text-gray-600">Manage your waste collection requests and payments</p>
        </div>
        <div class="flex flex-col space-y-2 mt-4 md:mt-0">
          <?php if($dues > 0): ?>
          <div class="bg-red-100 text-red-800 px-4 py-2 rounded-full font-semibold text-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            Dues: ₹<?php echo number_format($dues, 2); ?>
          </div>
          <?php endif; ?>
          <?php if($pending_count > 0): ?>
          <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-semibold text-center">
            <i class="fas fa-clock mr-2"></i>
            <?php echo $pending_count; ?> Pending Payment<?php echo $pending_count > 1 ? 's' : ''; ?>
          </div>
          <?php endif; ?>
          <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full font-semibold text-center">
            <i class="fas fa-info-circle mr-2"></i>
            Collection Fee: ₹<?php echo number_format($COLLECTION_FEE, 2); ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="glass rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-shadow cursor-pointer" onclick="window.location.href='collection_requests.php'">
        <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="fas fa-trash text-blue-600 text-xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_requests'] ?? 0; ?></h3>
        <p class="text-gray-600">Total Requests</p>
      </div>

      <div class="glass rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-shadow cursor-pointer" onclick="window.location.href='collection_requests.php?filter=pending'">
        <div class="bg-yellow-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['pending_requests'] ?? 0; ?></h3>
        <p class="text-gray-600">Pending</p>
      </div>

      <div class="glass rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-shadow cursor-pointer" onclick="window.location.href='collection_requests.php?filter=accepted'">
        <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['accepted_requests'] ?? 0; ?></h3>
        <p class="text-gray-600">Accepted</p>
      </div>

      <div class="glass rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-shadow cursor-pointer" onclick="window.location.href='collection_requests.php?filter=collected'">
        <div class="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="fas fa-truck-loading text-purple-600 text-xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['collected_requests'] ?? 0; ?></h3>
        <p class="text-gray-600">Collected</p>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Request Collection -->
      <div class="glass rounded-2xl p-6 shadow-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-calendar-plus text-green-600 mr-3"></i>
          Request Collection
        </h2>
        
        <?php if(!empty($err)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4 flex items-start space-x-3">
          <i class="fas fa-exclamation-circle mt-0.5"></i>
          <span><?php echo e($err); ?></span>
        </div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-4 flex items-start space-x-3">
          <i class="fas fa-check-circle mt-0.5"></i>
          <span><?php echo e($success); ?></span>
        </div>
        <?php endif; ?>

        <!-- Collection Fee Notice -->
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg mb-4">
          <div class="flex items-start space-x-3">
            <i class="fas fa-info-circle mt-0.5"></i>
            <div>
              <p class="font-semibold">Collection Fee: ₹<?php echo number_format($COLLECTION_FEE, 2); ?></p>
              <p class="text-sm">This amount will be added to your dues upon request submission.</p>
            </div>
          </div>
        </div>

        <form method="post" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pickup Address *</label>
            <input 
              name="address" 
              placeholder="Enter your complete address for waste collection" 
              required 
              class="input-field w-full px-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
              value="<?php echo e($_POST['address'] ?? ''); ?>"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date *</label>
            <input 
              name="schedule_date" 
              type="date" 
              required 
              min="<?php echo date('Y-m-d'); ?>"
              class="input-field w-full px-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
              value="<?php echo e($_POST['schedule_date'] ?? ''); ?>"
            >
          </div>
          <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
          <button 
            name="request_collection" 
            class="btn-hover w-full bg-gradient-green text-white py-3 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl"
          >
            <i class="fas fa-paper-plane mr-2"></i>Request Pickup (₹<?php echo $COLLECTION_FEE; ?>)
          </button>
        </form>
      </div>

      <!-- Recent Requests -->
      <div class="glass rounded-2xl p-6 shadow-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-history text-blue-600 mr-3"></i>
          Recent Requests
        </h2>
        <div class="space-y-4">
          <?php while($r = $res_reqs->fetch_assoc()): ?>
          <div class="border-l-4 
            <?php 
            switch($r['status']) {
                case 'pending': echo 'border-yellow-500 bg-yellow-50'; break;
                case 'accepted': echo 'border-blue-500 bg-blue-50'; break;
                case 'collected': echo 'border-green-500 bg-green-50'; break;
                case 'cancelled': echo 'border-red-500 bg-red-50'; break;
                default: echo 'border-gray-500 bg-gray-50';
            }
            ?> p-4 rounded">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <p class="font-semibold text-gray-800">Request #<?php echo $r['id']; ?></p>
                <p class="text-sm text-gray-600 truncate"><?php echo e($r['address']); ?></p>
                <div class="flex justify-between items-center mt-2">
                  <p class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($r['schedule_date'])); ?></p>
                  <div class="flex space-x-2">
                    <span class="text-xs px-2 py-1 rounded-full 
                      <?php echo $r['payment_status'] == 'paid' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                      <?php echo ucfirst($r['payment_status']); ?>
                    </span>
                    <span class="text-xs px-2 py-1 rounded-full 
                      <?php 
                      switch($r['status']) {
                          case 'pending': echo 'bg-yellow-200 text-yellow-800'; break;
                          case 'accepted': echo 'bg-blue-200 text-blue-800'; break;
                          case 'collected': echo 'bg-green-200 text-green-800'; break;
                          case 'cancelled': echo 'bg-red-200 text-red-800'; break;
                      }
                      ?>">
                      <?php echo ucfirst($r['status']); ?>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <?php if($r['payment_status'] == 'pending' && $r['status'] != 'cancelled'): ?>
            <div class="mt-3 text-center">
              <a href='payment.php?type=collection&req=<?php echo $r['id']; ?>' class="inline-block">
                <button class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700 transition-colors">
                  <i class="fas fa-credit-card mr-1"></i>Pay ₹<?php echo $COLLECTION_FEE; ?>
                </button>
              </a>
            </div>
            <?php endif; ?>
          </div>
          <?php endwhile; ?>
          
          <?php if($res_reqs->num_rows === 0): ?>
            <div class="text-center py-8 text-gray-500">
              <i class="fas fa-inbox text-4xl mb-3"></i>
              <p>No collection requests yet</p>
              <p class="text-sm mt-1">Create your first waste collection request</p>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="mt-4 text-center">
          <a href="collection_requests.php" class="text-green-600 hover:text-green-800 font-medium inline-flex items-center">
            View All Requests <i class="fas fa-arrow-right ml-1"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
      <a href="payment.php" class="glass rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-shadow group">
        <div class="bg-indigo-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-indigo-200 transition-colors">
          <i class="fas fa-credit-card text-indigo-600 text-xl"></i>
        </div>
        <h3 class="font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors">Payments</h3>
        <p class="text-sm text-gray-600 mt-1">Manage your dues and payments</p>
        <?php if($pending_count > 0): ?>
        <span class="inline-block mt-2 bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
          <?php echo $pending_count; ?> pending
        </span>
        <?php endif; ?>
        <?php if($dues > 0): ?>
        <span class="inline-block mt-1 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
          ₹<?php echo number_format($dues, 2); ?> due
        </span>
        <?php endif; ?>
      </a>

      <a href="complaints.php" class="glass rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-shadow group">
        <div class="bg-red-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-red-200 transition-colors">
          <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
        </div>
        <h3 class="font-semibold text-gray-800 group-hover:text-red-600 transition-colors">Complaints</h3>
        <p class="text-sm text-gray-600 mt-1">Report issues and concerns</p>
      </a>

      <a href="feedback.php" class="glass rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-shadow group">
        <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 transition-colors">
          <i class="fas fa-comment-dots text-blue-600 text-xl"></i>
        </div>
        <h3 class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Feedback</h3>
        <p class="text-sm text-gray-600 mt-1">Share your suggestions</p>
      </a>
    </div>

    <!-- Upcoming Collections -->
    <?php
    // Get upcoming collections (next 7 days)
    $upcoming = $mysqli->prepare('
        SELECT * FROM collection_requests 
        WHERE user_id=? AND schedule_date >= CURDATE() AND schedule_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND status IN ("pending", "accepted")
        ORDER BY schedule_date ASC 
        LIMIT 3
    ');
    $upcoming->bind_param('i', $uid);
    $upcoming->execute();
    $res_upcoming = $upcoming->get_result();
    
    if($res_upcoming->num_rows > 0):
    ?>
    <div class="glass rounded-2xl shadow-xl p-6 mt-8">
      <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-calendar-day text-purple-600 mr-3"></i>
        Upcoming Collections (Next 7 Days)
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php while($upcoming_req = $res_upcoming->fetch_assoc()): ?>
        <div class="bg-white border-l-4 border-purple-500 p-4 rounded-lg shadow-sm">
          <div class="flex justify-between items-start mb-2">
            <p class="font-semibold text-gray-800">Request #<?php echo $upcoming_req['id']; ?></p>
            <span class="text-xs px-2 py-1 rounded-full 
              <?php echo $upcoming_req['status'] == 'accepted' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'; ?>">
              <?php echo ucfirst($upcoming_req['status']); ?>
            </span>
          </div>
          <p class="text-sm text-gray-600 mb-2 truncate"><?php echo e($upcoming_req['address']); ?></p>
          <p class="text-xs text-gray-500">
            <i class="fas fa-calendar mr-1"></i>
            <?php echo date('D, M j', strtotime($upcoming_req['schedule_date'])); ?>
          </p>
          <?php if($upcoming_req['payment_status'] == 'pending'): ?>
          <div class="mt-2 text-center">
            <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Payment Due</span>
          </div>
          <?php endif; ?>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Set minimum date for schedule date to today
document.addEventListener('DOMContentLoaded', function() {
  const dateInput = document.querySelector('input[name="schedule_date"]');
  const today = new Date().toISOString().split('T')[0];
  if (!dateInput.value) {
    dateInput.value = today;
  }
});

// Make stat cards clickable
document.querySelectorAll('.glass.rounded-xl').forEach(card => {
  card.style.cursor = 'pointer';
});
</script>

<?php require 'footer.php'; ?>
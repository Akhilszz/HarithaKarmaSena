<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='user'){ 
  header('Location: login.php'); 
  exit; 
}

$uid = $_SESSION['user']['id'];
$page_title = "Collection Requests";

// Handle cancellation if requested
if(isset($_POST['cancel_request']) && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    
    // Verify the request belongs to the current user and is pending
    $verify_stmt = $mysqli->prepare('SELECT id, status, payment_status FROM collection_requests WHERE id=? AND user_id=? AND status="pending"');
    $verify_stmt->bind_param('ii', $request_id, $uid);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if($verify_result->num_rows === 1) {
        $request_data = $verify_result->fetch_assoc();
        $current_payment_status = $request_data['payment_status'];
        
        // Start transaction
        $mysqli->begin_transaction();
        
        try {
            // Determine the new payment status based on current payment status
            $new_payment_status = ($current_payment_status == 'paid') ? 'refunded' : 'cancelled';
            
            // Update the status to cancelled and payment_status accordingly
            $update_stmt = $mysqli->prepare('UPDATE collection_requests SET status="cancelled", payment_status=?, updated_at=NOW() WHERE id=?');
            $update_stmt->bind_param('si', $new_payment_status, $request_id);
            $update_stmt->execute();
            
            // Update user dues to 0 in users table
            $update_user_stmt = $mysqli->prepare('UPDATE users SET dues=0 WHERE id=?');
            $update_user_stmt->bind_param('i', $uid);
            $update_user_stmt->execute();
            
            // Commit transaction
            $mysqli->commit();
            
            if($new_payment_status == 'refunded') {
                $_SESSION['success_msg'] = "Request #{$request_id} has been cancelled successfully. Payment will be refunded and dues cleared.";
            } else {
                $_SESSION['success_msg'] = "Request #{$request_id} has been cancelled successfully and dues cleared.";
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $mysqli->rollback();
            $_SESSION['error_msg'] = "Failed to cancel the request. Please try again.";
        }
        
        $update_stmt->close();
        $update_user_stmt->close();
        
        // Redirect to avoid form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['error_msg'] = "Request not found, cannot be cancelled, or is no longer pending.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get all requests
$reqs = $mysqli->prepare('SELECT * FROM collection_requests WHERE user_id=? ORDER BY created_at DESC');
$reqs->bind_param('i', $uid); 
$reqs->execute(); 
$res_reqs = $reqs->get_result();

// Get statistics
$stats_stmt = $mysqli->prepare('
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status = "collected" THEN 1 ELSE 0 END) as collected,
        SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled
    FROM collection_requests 
    WHERE user_id=?
');
$stats_stmt->bind_param('i', $uid);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

require 'header.php';
?>

<div class="flex-1">
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Success/Error Messages -->
    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="glass rounded-2xl shadow-xl p-8 gradient-border mb-8">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Collection Requests</h1>
          <p class="text-gray-600">Manage and track your waste collection requests</p>
        </div>
        <a href="user_dashboard.php" class="btn-hover bg-gradient-green text-white px-6 py-3 rounded-lg font-semibold mt-4 md:mt-0">
          <i class="fas fa-plus mr-2"></i>New Request
        </a>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
      <div class="glass rounded-xl p-4 text-center shadow-lg">
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['total'] ?? 0; ?></div>
        <div class="text-sm text-gray-600">Total</div>
      </div>
      <div class="glass rounded-xl p-4 text-center shadow-lg border-l-4 border-yellow-500">
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['pending'] ?? 0; ?></div>
        <div class="text-sm text-gray-600">Pending</div>
      </div>
      <div class="glass rounded-xl p-4 text-center shadow-lg border-l-4 border-blue-500">
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['accepted'] ?? 0; ?></div>
        <div class="text-sm text-gray-600">Accepted</div>
      </div>
      <div class="glass rounded-xl p-4 text-center shadow-lg border-l-4 border-green-500">
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['collected'] ?? 0; ?></div>
        <div class="text-sm text-gray-600">Collected</div>
      </div>
      <div class="glass rounded-xl p-4 text-center shadow-lg border-l-4 border-red-500">
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['cancelled'] ?? 0; ?></div>
        <div class="text-sm text-gray-600">Cancelled</div>
      </div>
    </div>

    <!-- Requests Table -->
    <div class="glass rounded-2xl shadow-xl p-6">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Request ID</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Address</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Schedule Date</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Payment</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Created</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php while($r = $res_reqs->fetch_assoc()): ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?php echo $r['id']; ?></td>
              <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate"><?php echo e($r['address']); ?></td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <?php echo date('M j, Y', strtotime($r['schedule_date'])); ?>
              </td>
              <td class="px-6 py-4">
                <?php
                $status_config = [
                    'pending' => ['color' => 'yellow', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                    'accepted' => ['color' => 'blue', 'bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                    'collected' => ['color' => 'green', 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                    'cancelled' => ['color' => 'red', 'bg' => 'bg-red-100', 'text' => 'text-red-800']
                ];
                $status = $r['status'];
                $config = $status_config[$status] ?? $status_config['pending'];
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $config['bg'] . ' ' . $config['text']; ?>">
                  <span class="w-2 h-2 bg-<?php echo $config['color']; ?>-500 rounded-full mr-2"></span>
                  <?php echo ucfirst($status); ?>
                </span>
              </td>
              <td class="px-6 py-4">
                <?php
                $payment_status = $r['payment_status'];
                
                // Define payment status styles
                if($payment_status == 'paid') {
                    $payment_bg = 'bg-green-100';
                    $payment_text = 'text-green-800';
                    $payment_icon = 'fa-check-circle';
                } elseif($payment_status == 'refunded') {
                    $payment_bg = 'bg-purple-100';
                    $payment_text = 'text-purple-800';
                    $payment_icon = 'fa-undo';
                } elseif($payment_status == 'cancelled') {
                    $payment_bg = 'bg-gray-100';
                    $payment_text = 'text-gray-800';
                    $payment_icon = 'fa-ban';
                } else {
                    $payment_bg = 'bg-red-100';
                    $payment_text = 'text-red-800';
                    $payment_icon = 'fa-clock';
                }
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $payment_bg . ' ' . $payment_text; ?>">
                  <i class="fas <?php echo $payment_icon; ?> mr-1 text-xs"></i>
                  <?php echo ucfirst($payment_status); ?>
                </span>
                <?php if($r['payment_status'] == 'pending' && $r['status'] != 'cancelled'): ?>
                  <a href='payment.php?req=<?php echo $r['id']; ?>' class="ml-2">
                    <button class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700 transition-colors">
                      Pay Now
                    </button>
                  </a>
                <?php endif; ?>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                <?php echo date('M j, Y', strtotime($r['created_at'])); ?>
              </td>
              <td class="px-6 py-4">
                <div class="flex space-x-2">
                  <!-- View Details -->
                  <!-- <button onclick="showRequestDetails(<?php echo $r['id']; ?>)" 
                          class="text-blue-600 hover:text-blue-800 text-sm transition-colors"
                          title="View Details">
                    <i class="fas fa-eye"></i>
                  </button> -->
                  
                  <!-- Cancel Request (only if pending) -->
                  <?php if($r['status'] == 'pending'): ?>
                    <form method="POST" class="inline" onsubmit="return confirmCancel(<?php echo $r['payment_status'] == 'paid' ? 'true' : 'false'; ?>)">
                      <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                      <button type="submit" name="cancel_request" 
                              class="text-red-600 hover:text-red-800 text-sm transition-colors"
                              title="Cancel Request">
                        <i class="fas fa-times"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                  
                  <!-- Repeat Request (if collected or cancelled) -->
                  <?php if($r['status'] == 'collected' || $r['status'] == 'cancelled'): ?>
                    <button onclick="repeatRequest(<?php echo $r['id']; ?>)" 
                            class="text-green-600 hover:text-green-800 text-sm transition-colors"
                            title="Repeat Request">
                      <i class="fas fa-redo"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
            
            <?php if($res_reqs->num_rows === 0): ?>
            <tr>
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                <div class="flex flex-col items-center">
                  <i class="fas fa-inbox text-4xl mb-3 text-gray-400"></i>
                  <p class="text-lg mb-2">No collection requests found</p>
                  <p class="text-sm mb-4">Start by creating your first collection request</p>
                  <a href="user_dashboard.php" class="btn-hover bg-gradient-green text-white px-6 py-2 rounded-lg font-semibold">
                    <i class="fas fa-plus mr-2"></i>Create First Request
                  </a>
                </div>
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Status Legend -->
    <div class="glass rounded-2xl shadow-xl p-6 mt-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Request Status Guide</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
        <div class="flex items-center space-x-2">
          <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
          <span class="text-gray-700">Pending - Waiting for approval</span>
        </div>
        <div class="flex items-center space-x-2">
          <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
          <span class="text-gray-700">Accepted - Scheduled for collection</span>
        </div>
        <div class="flex items-center space-x-2">
          <span class="w-3 h-3 bg-green-500 rounded-full"></span>
          <span class="text-gray-700">Collected - Waste successfully collected</span>
        </div>
        <div class="flex items-center space-x-2">
          <span class="w-3 h-3 bg-red-500 rounded-full"></span>
          <span class="text-gray-700">Cancelled - Request was cancelled</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Request Details Modal -->
<div id="requestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="glass rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold text-gray-800">Request Details</h3>
      <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div id="modalContent" class="space-y-3">
      <!-- Content will be loaded via JavaScript -->
    </div>
  </div>
</div>

<script>
function showRequestDetails(requestId) {
  // In a real application, you would fetch this data via AJAX
  // For now, we'll show a simple message
  const modalContent = document.getElementById('modalContent');
  modalContent.innerHTML = `
    <div class="text-center py-8">
      <i class="fas fa-info-circle text-4xl text-blue-500 mb-3"></i>
      <p class="text-gray-700">Detailed view for request #${requestId}</p>
      <p class="text-sm text-gray-500 mt-2">This would show complete address, worker details, collection notes, etc.</p>
    </div>
  `;
  document.getElementById('requestModal').classList.remove('hidden');
}

function confirmCancel(isPaid) {
  if (isPaid) {
    return confirm('Are you sure you want to cancel this collection request? Your payment will be refunded and this action cannot be undone.');
  } else {
    return confirm('Are you sure you want to cancel this collection request? This action cannot be undone.');
  }
}

function repeatRequest(requestId) {
  // In a real application, you would pre-fill the form with previous request data
  window.location.href = `user_dashboard.php?repeat=${requestId}`;
}

function closeModal() {
  document.getElementById('requestModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('requestModal').addEventListener('click', function(e) {
  if(e.target === this) {
    closeModal();
  }
});
</script>

<?php require 'footer.php'; ?>
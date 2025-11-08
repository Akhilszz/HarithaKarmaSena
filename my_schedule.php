<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='worker'){ 
  header('Location: login.php'); 
  exit; 
}

$worker_id = $_SESSION['user']['id'];
$page_title = "My Schedule";

// Get assigned collection requests
$today = date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : $today;

// Build query based on filters
$query = "
    SELECT cr.*, u.name as user_name, u.phone as user_phone, u.address as user_address,
           admin.name as assigned_by_name, cr.assigned_at
    FROM collection_requests cr
    JOIN users u ON cr.user_id = u.id
    LEFT JOIN users admin ON cr.assigned_by_admin = admin.id
    WHERE cr.assigned_worker_id = ?
";

$params = [$worker_id];
$types = "i";

if ($status_filter !== 'all') {
    $query .= " AND cr.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_filter !== 'all') {
    $query .= " AND DATE(cr.schedule_date) = ?";
    $params[] = $date_filter;
    $types .= "s";
}

$query .= " ORDER BY cr.schedule_date ASC, cr.collection_time ASC";

$stmt = $mysqli->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$requests_result = $stmt->get_result();

// Handle status updates
if(isset($_POST['update_status'])) {
    $request_id = intval($_POST['request_id']);
    $new_status = $_POST['status'];
    $collection_time = isset($_POST['collection_time']) ? $_POST['collection_time'] : null;
    
    // Verify the request is assigned to this worker
    $verify_stmt = $mysqli->prepare("SELECT id FROM collection_requests WHERE id = ? AND assigned_worker_id = ?");
    $verify_stmt->bind_param('ii', $request_id, $worker_id);
    $verify_stmt->execute();
    
    if($verify_stmt->get_result()->num_rows === 1) {
        if($collection_time) {
            $update_stmt = $mysqli->prepare("UPDATE collection_requests SET status = ?, collection_time = ?, updated_at = NOW() WHERE id = ?");
            $update_stmt->bind_param('ssi', $new_status, $collection_time, $request_id);
        } else {
            $update_stmt = $mysqli->prepare("UPDATE collection_requests SET status = ?, updated_at = NOW() WHERE id = ?");
            $update_stmt->bind_param('si', $new_status, $request_id);
        }
        
        if($update_stmt->execute()) {
            $_SESSION['success_msg'] = "Request status updated successfully!";
            
            // Log the activity
            $log_stmt = $mysqli->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
            $action = "status_update";
            $description = "Updated request #{$request_id} to {$new_status}";
            $log_stmt->bind_param('iss', $worker_id, $action, $description);
            $log_stmt->execute();
            
            // If marked as collected, update user dues
            if($new_status === 'collected') {
                $amount_stmt = $mysqli->prepare("SELECT amount FROM collection_requests WHERE id = ?");
                $amount_stmt->bind_param('i', $request_id);
                $amount_stmt->execute();
                $amount_stmt->bind_result($amount);
                $amount_stmt->fetch();
                $amount_stmt->close();
                
                if($amount > 0) {
                    $user_stmt = $mysqli->prepare("SELECT user_id FROM collection_requests WHERE id = ?");
                    $user_stmt->bind_param('i', $request_id);
                    $user_stmt->execute();
                    $user_stmt->bind_result($customer_id);
                    $user_stmt->fetch();
                    $user_stmt->close();
                    
                    // Update user dues
                    $dues_stmt = $mysqli->prepare("UPDATE users SET dues = dues + ? WHERE id = ?");
                    $dues_stmt->bind_param('di', $amount, $customer_id);
                    $dues_stmt->execute();
                }
            }
        } else {
            $_SESSION['error_msg'] = "Error updating status: " . $mysqli->error;
        }
    } else {
        $_SESSION['error_msg'] = "Request not found or not assigned to you!";
    }
    
    header("Location: my_schedule.php");
    exit;
}

// Get statistics for the worker
$stats_stmt = $mysqli->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as collected,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM collection_requests 
    WHERE assigned_worker_id = ? AND DATE(schedule_date) >= CURDATE()
");
$stats_stmt->bind_param('i', $worker_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get worker performance stats
$performance_stmt = $mysqli->prepare("
    SELECT 
        COUNT(*) as total_assignments,
        SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        MIN(assigned_at) as first_assignment
    FROM collection_requests 
    WHERE assigned_worker_id = ?
");
$performance_stmt->bind_param('i', $worker_id);
$performance_stmt->execute();
$performance = $performance_stmt->get_result()->fetch_assoc();

require 'worker_header.php';
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
          <h1 class="text-3xl font-bold text-gray-800 mb-2">My Work Schedule</h1>
          <p class="text-gray-600">View and manage your assigned collection requests</p>
        </div>
        <div class="mt-4 md:mt-0">
          <div class="text-sm text-gray-600">Assigned by Admin</div>
          <div class="text-lg font-semibold text-gray-800">
            <?php echo $performance['total_assignments'] ?? 0; ?> Total Assignments
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div class="glass rounded-xl p-4 text-center shadow-lg">
        <div class="text-2xl font-bold text-gray-800"><?php echo $performance['completed'] ?? 0; ?></div>
        <div class="text-sm text-gray-600">Completed</div>
      </div>
      <div class="glass rounded-xl p-4 text-center shadow-lg border-l-4 border-yellow-500">
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['pending'] ?? 0; ?></div>
        <div class="text-sm text-gray-600">Pending</div>
      </div>
      <div class="glass rounded-xl p-4 text-center shadow-lg border-l-4 border-blue-500">
        <div class="text-2xl font-bold text-gray-800"><?php echo $stats['accepted'] ?? 0; ?></div>
        <div class="text-sm text-gray-600">In Progress</div>
      </div>
      <div class="glass rounded-xl p-4 text-center shadow-lg border-l-4 border-red-500">
        <div class="text-2xl font-bold text-gray-800"><?php echo $performance['cancelled'] ?? 0; ?></div>
        <div class="text-sm text-gray-600">Cancelled</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="glass rounded-2xl shadow-xl p-6 mb-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Assignments</h3>
      <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
            <option value="collected" <?php echo $status_filter === 'collected' ? 'selected' : ''; ?>>Collected</option>
            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
          <input type="date" name="date" value="<?php echo e($date_filter); ?>" 
                 class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
          <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors w-full">
            Apply Filters
          </button>
        </div>
      </form>
    </div>

    <!-- Assignments Table -->
    <div class="glass rounded-2xl shadow-xl p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Assigned Collection Requests</h3>
      
      <?php if($requests_result->num_rows > 0): ?>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50">
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Request ID</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Customer Details</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Schedule</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Address</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Assigned By</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Payment</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php while($request = $requests_result->fetch_assoc()): ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?php echo $request['id']; ?></td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="font-medium"><?php echo e($request['user_name']); ?></div>
                  <div class="text-gray-500 text-xs"><?php echo e($request['user_phone']); ?></div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="font-medium"><?php echo date('M j, Y', strtotime($request['schedule_date'])); ?></div>
                  <div class="text-gray-500 text-xs">
                    <?php echo $request['collection_time'] ? date('g:i A', strtotime($request['collection_time'])) : 'Time not set'; ?>
                  </div>
                  <?php if($request['assigned_at']): ?>
                    <div class="text-xs text-gray-400 mt-1">
                      Assigned: <?php echo date('M j, g:i A', strtotime($request['assigned_at'])); ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                  <div class="truncate" title="<?php echo e($request['address']); ?>">
                    <?php echo e($request['address']); ?>
                  </div>
                  <?php if($request['special_instructions']): ?>
                    <button onclick="showInstructions(<?php echo $request['id']; ?>, `<?php echo e($request['special_instructions']); ?>`)" 
                            class="text-blue-600 hover:text-blue-800 text-xs mt-1">
                      <i class="fas fa-info-circle mr-1"></i>View Instructions
                    </button>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <?php echo e($request['assigned_by_name'] ?? 'System'); ?>
                </td>
                <td class="px-6 py-4">
                  <?php
                  $status_config = [
                      'pending' => ['color' => 'yellow', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                      'accepted' => ['color' => 'blue', 'bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                      'collected' => ['color' => 'green', 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                      'cancelled' => ['color' => 'red', 'bg' => 'bg-red-100', 'text' => 'text-red-800']
                  ];
                  $status = $request['status'];
                  $config = $status_config[$status] ?? $status_config['pending'];
                  ?>
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $config['bg'] . ' ' . $config['text']; ?>">
                    <span class="w-2 h-2 bg-<?php echo $config['color']; ?>-500 rounded-full mr-2"></span>
                    <?php echo ucfirst($status); ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <?php
                  $payment_config = [
                      'pending' => ['color' => 'red', 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
                      'paid' => ['color' => 'green', 'bg' => 'bg-green-100', 'text' => 'text-green-800']
                  ];
                  $payment_status = $request['payment_status'];
                  $payment_cfg = $payment_config[$payment_status] ?? $payment_config['pending'];
                  ?>
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $payment_cfg['bg'] . ' ' . $payment_cfg['text']; ?>">
                    <i class="fas <?php echo $payment_status == 'paid' ? 'fa-check-circle' : 'fa-clock'; ?> mr-1 text-xs"></i>
                    <?php echo ucfirst($payment_status); ?>
                  </span>
                  <?php if($request['amount']): ?>
                    <div class="text-xs text-gray-500 mt-1">₹<?php echo $request['amount']; ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col space-y-2">
                    <!-- Status Update -->
                    <?php if($request['status'] != 'collected' && $request['status'] != 'cancelled'): ?>
                      <form method="POST" class="inline">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        <div class="flex space-x-1 mb-1">
                          <select name="status" class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="accepted" <?php echo $request['status'] == 'accepted' ? 'selected' : ''; ?>>Accept</option>
                            <option value="collected" <?php echo $request['status'] == 'collected' ? 'selected' : ''; ?>>Mark Collected</option>
                            <option value="cancelled" <?php echo $request['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancel</option>
                          </select>
                        </div>
                        <div class="mb-1">
                          <input type="time" name="collection_time" 
                                 class="text-xs border border-gray-300 rounded px-2 py-1 w-full"
                                 placeholder="Collection Time">
                        </div>
                        <button type="submit" name="update_status" 
                                onclick="return confirm('Update this request?')"
                                class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 transition-colors w-full">
                          Update
                        </button>
                      </form>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <!-- <div class="flex space-x-2 justify-center">
                      <a href="tel:<?php echo e($request['user_phone']); ?>" 
                         class="text-green-600 hover:text-green-800 text-sm transition-colors"
                         title="Call Customer">
                        <i class="fas fa-phone"></i>
                      </a>
                      <button onclick="showRequestDetails(<?php echo $request['id']; ?>)" 
                              class="text-blue-600 hover:text-blue-800 text-sm transition-colors"
                              title="View Details">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div> -->
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-12 text-gray-500">
          <i class="fas fa-inbox text-4xl mb-3"></i>
          <p class="text-lg mb-2">No assigned requests found</p>
          <p class="text-sm">You don't have any collection requests assigned by admin matching your filters.</p>
          <p class="text-xs text-gray-400 mt-2">Contact administrator for new assignments</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
      <!-- Today's Priority -->
      <div class="glass rounded-2xl shadow-xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Today's Priority</h3>
        <div class="space-y-3">
          <?php
          $today_priority = $mysqli->query("
            SELECT cr.id, u.name, cr.schedule_date, cr.status
            FROM collection_requests cr
            JOIN users u ON cr.user_id = u.id
            WHERE cr.assigned_worker_id = $worker_id 
            AND DATE(cr.schedule_date) = '$today'
            AND cr.status IN ('pending', 'accepted')
            ORDER BY cr.collection_time ASC
            LIMIT 5
          ");
          
          while($priority = $today_priority->fetch_assoc()): 
          ?>
            <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
              <div>
                <span class="font-medium"><?php echo e($priority['name']); ?></span>
                <span class="text-xs text-gray-500 ml-2">#<?php echo $priority['id']; ?></span>
              </div>
              <span class="text-xs <?php echo $priority['status'] == 'pending' ? 'text-yellow-600' : 'text-blue-600'; ?>">
                <?php echo ucfirst($priority['status']); ?>
              </span>
            </div>
          <?php endwhile; ?>
          
          <?php if($today_priority->num_rows === 0): ?>
            <p class="text-gray-500 text-sm text-center py-2">No priority tasks for today</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recent Completions -->
      <div class="glass rounded-2xl shadow-xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Completions</h3>
        <div class="space-y-3">
          <?php
          $recent_completed = $mysqli->query("
            SELECT cr.id, u.name, cr.updated_at
            FROM collection_requests cr
            JOIN users u ON cr.user_id = u.id
            WHERE cr.assigned_worker_id = $worker_id 
            AND cr.status = 'collected'
            ORDER BY cr.updated_at DESC
            LIMIT 5
          ");
          
          while($completed = $recent_completed->fetch_assoc()): 
          ?>
            <div class="flex justify-between items-center text-sm">
              <span class="text-gray-700"><?php echo e($completed['name']); ?></span>
              <span class="text-gray-500 text-xs"><?php echo date('M j', strtotime($completed['updated_at'])); ?></span>
            </div>
          <?php endwhile; ?>
          
          <?php if($recent_completed->num_rows === 0): ?>
            <p class="text-gray-500 text-sm text-center py-2">No recent completions</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Instructions Modal -->
<div id="instructionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="glass rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold text-gray-800">Special Instructions</h3>
      <button onclick="closeInstructionsModal()" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div id="instructionsContent" class="text-gray-700">
    </div>
  </div>
</div>

<script>
function showInstructions(requestId, instructions) {
  document.getElementById('instructionsContent').textContent = instructions;
  document.getElementById('instructionsModal').classList.remove('hidden');
}

function closeInstructionsModal() {
  document.getElementById('instructionsModal').classList.add('hidden');
}

function showRequestDetails(requestId) {
  alert('Detailed view for request #' + requestId + '\nThis would show complete customer details, location map, payment information, etc.');
}

// Close modals when clicking outside
document.getElementById('instructionsModal').addEventListener('click', function(e) {
  if(e.target === this) {
    closeInstructionsModal();
  }
});

// Auto-refresh every 3 minutes to check for new assignments
setTimeout(() => {
  window.location.reload();
}, 180000);
</script>

<?php require 'worker_footer.php'; ?>
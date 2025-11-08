<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='worker') header('Location: login.php');

$worker_id = $_SESSION['user']['id'];
$page_title = "Worker Dashboard";

// Get collection requests assigned to this worker
$reqs = $mysqli->prepare("
    SELECT cr.*, u.name AS user_name, u.phone, u.address as user_address,
           admin.name as assigned_by_name, cr.assigned_at
    FROM collection_requests cr 
    JOIN users u ON cr.user_id = u.id
    LEFT JOIN users admin ON cr.assigned_by_admin = admin.id
    WHERE cr.assigned_worker_id = ?
    ORDER BY 
        CASE 
            WHEN cr.status = 'pending' THEN 1
            WHEN cr.status = 'accepted' THEN 2
            WHEN cr.status = 'collected' THEN 3
            ELSE 4
        END,
        cr.schedule_date ASC,
        cr.created_at DESC
");
$reqs->bind_param('i', $worker_id);
$reqs->execute();
$reqs_result = $reqs->get_result();

// Get statistics for this worker only
$stats_stmt = $mysqli->prepare("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_requests,
        SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as collected_requests,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_requests
    FROM collection_requests
    WHERE assigned_worker_id = ?
");
$stats_stmt->bind_param('i', $worker_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get today's assignments
$today = date('Y-m-d');
$today_stmt = $mysqli->prepare("
    SELECT COUNT(*) as today_count
    FROM collection_requests 
    WHERE assigned_worker_id = ? AND DATE(schedule_date) = ? AND status IN ('pending', 'accepted')
");
$today_stmt->bind_param('is', $worker_id, $today);
$today_stmt->execute();
$today_count = $today_stmt->get_result()->fetch_assoc()['today_count'];

// Get feedback (recent feedback for collections done by this worker)
$fb = $mysqli->prepare("
    SELECT f.*, u.name 
    FROM feedbacks f 
    JOIN users u ON f.user_id = u.id
    WHERE f.user_id IN (
        SELECT user_id FROM collection_requests WHERE assigned_worker_id = ? AND status = 'collected'
    )
    ORDER BY f.created_at DESC 
    LIMIT 5
");
$fb->bind_param('i', $worker_id);
$fb->execute();
$fb_result = $fb->get_result();

// Get worker performance
$performance_stmt = $mysqli->prepare("
    SELECT 
        COUNT(*) as total_assigned,
        SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM collection_requests 
    WHERE assigned_worker_id = ?
");
$performance_stmt->bind_param('i', $worker_id);
$performance_stmt->execute();
$performance = $performance_stmt->get_result()->fetch_assoc();

$completion_rate = $performance['total_assigned'] > 0 ? 
    round(($performance['completed'] / $performance['total_assigned']) * 100, 1) : 0;

require 'worker_header.php';
?>

<div class="flex-1">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="glass rounded-2xl shadow-xl p-8 gradient-border mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        Welcome, <?php echo e($_SESSION['user']['name']); ?>!
                    </h1>
                    <p class="text-gray-600">Manage your assigned collection requests</p>
                    <div class="flex items-center mt-3 space-x-4 text-sm">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                            <i class="fas fa-tasks mr-1"></i>
                            <?php echo $performance['total_assigned'] ?? 0; ?> Total Assignments
                        </span>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">
                            <i class="fas fa-check-circle mr-1"></i>
                            <?php echo $completion_rate; ?>% Completion Rate
                        </span>
                        <?php if($today_count > 0): ?>
                        <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full">
                            <i class="fas fa-calendar-day mr-1"></i>
                            <?php echo $today_count; ?> Today
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="bg-green-100 text-green-800 px-6 py-3 rounded-lg font-semibold">
                        <i class="fas fa-user-hard-hat mr-2"></i>
                        Worker Dashboard
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="glass rounded-xl p-6 text-center shadow-lg">
                <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-list-check text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_requests'] ?? 0; ?></h3>
                <p class="text-gray-600">My Assignments</p>
            </div>

            <div class="glass rounded-xl p-6 text-center shadow-lg">
                <div class="bg-yellow-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['pending_requests'] ?? 0; ?></h3>
                <p class="text-gray-600">Pending</p>
            </div>

            <div class="glass rounded-xl p-6 text-center shadow-lg">
                <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['collected_requests'] ?? 0; ?></h3>
                <p class="text-gray-600">Completed</p>
            </div>

            <div class="glass rounded-xl p-6 text-center shadow-lg">
                <div class="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo $completion_rate; ?>%</h3>
                <p class="text-gray-600">Success Rate</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- My Assigned Requests -->
            <div class="glass rounded-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-list-alt text-green-600 mr-3"></i>
                        My Assigned Requests
                    </h2>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                        <?php echo $reqs_result->num_rows; ?> requests
                    </span>
                </div>
                
                <?php if($reqs_result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Request</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Customer</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Schedule</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while($r = $reqs_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">#<?php echo $r['id']; ?></p>
                                        <p class="text-xs text-gray-500 max-w-xs truncate"><?php echo e($r['address']); ?></p>
                                        <?php if($r['assigned_by_name']): ?>
                                            <p class="text-xs text-blue-600 mt-1">
                                                <i class="fas fa-user-shield mr-1"></i>By <?php echo e($r['assigned_by_name']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo e($r['user_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo e($r['phone']); ?></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <div class="font-medium"><?php echo date('M j, Y', strtotime($r['schedule_date'])); ?></div>
                                    <?php if($r['collection_time']): ?>
                                        <div class="text-xs text-gray-500"><?php echo date('g:i A', strtotime($r['collection_time'])); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
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
                                <td class="px-4 py-3">
                                    <div class="flex space-x-2">
                                        <?php if($r['status'] == 'pending'): ?>
                                            <form action="worker_action.php" method="POST" class="inline-block">
                                                <input type="hidden" name="action" value="accept">
                                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition-colors flex items-center">
                                                    <i class="fas fa-check mr-1"></i> Accept
                                                </button>
                                            </form>
                                        <?php elseif($r['status'] == 'accepted'): ?>
                                            <form action="worker_action.php" method="POST" class="inline-block">
                                                <input type="hidden" name="action" value="collect">
                                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition-colors flex items-center">
                                                    <i class="fas fa-truck-loading mr-1"></i> Collect
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-500">Completed</span>
                                        <?php endif; ?>
                                        
                                        <!-- Contact Customer -->
                                        
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
                    <p class="text-lg mb-2">No assignments yet</p>
                    <p class="text-sm">You don't have any collection requests assigned by admin.</p>
                    <p class="text-xs text-gray-400 mt-2">Contact administrator for new assignments</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Feedback & Quick Stats -->
            <div class="space-y-8">
                <!-- Quick Stats -->
                <div class="glass rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-pie text-purple-600 mr-3"></i>
                        My Performance
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700">Completion Rate</span>
                                <span class="font-semibold"><?php echo $completion_rate; ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $completion_rate; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="bg-green-50 p-3 rounded-lg">
                                <div class="text-lg font-bold text-green-800"><?php echo $performance['completed'] ?? 0; ?></div>
                                <div class="text-xs text-green-600">Completed</div>
                            </div>
                            <div class="bg-red-50 p-3 rounded-lg">
                                <div class="text-lg font-bold text-red-800"><?php echo $performance['cancelled'] ?? 0; ?></div>
                                <div class="text-xs text-red-600">Cancelled</div>
                            </div>
                        </div>
                        
                        <?php if($today_count > 0): ?>
                        <div class="bg-orange-50 p-3 rounded-lg border-l-4 border-orange-500">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-orange-500 mr-2"></i>
                                <span class="text-sm font-semibold text-orange-800">
                                    <?php echo $today_count; ?> pending task(s) for today
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Customer Feedback -->
                <div class="glass rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-comment-dots text-blue-600 mr-3"></i>
                        Recent Feedback
                    </h2>
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        <?php while($f = $fb_result->fetch_assoc()): ?>
                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4 rounded">
                            <div class="flex justify-between items-start mb-2">
                                <p class="font-semibold text-gray-800"><?php echo e($f['name']); ?></p>
                                <span class="text-xs text-gray-500">
                                    <?php echo date('M j, Y', strtotime($f['created_at'])); ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-700"><?php echo e($f['message']); ?></p>
                            <?php if($f['rating']): ?>
                                <div class="flex items-center mt-2">
                                    <span class="text-yellow-500 text-sm">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $f['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php if(!empty($f['admin_response'])): ?>
                            <div class="mt-3 p-3 bg-white rounded border">
                                <p class="text-sm font-semibold text-gray-800 mb-1">Admin Response:</p>
                                <p class="text-sm text-gray-700"><?php echo e($f['admin_response']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                        
                        <?php if($fb_result->num_rows === 0): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-comment-slash text-4xl mb-3"></i>
                            <p>No feedback yet</p>
                            <p class="text-xs text-gray-400 mt-1">Feedback from your completed collections will appear here</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass rounded-2xl shadow-xl p-6 mt-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-bolt text-yellow-600 mr-3"></i>
                Quick Actions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="my_schedule.php" class="bg-green-100 border-2 border-green-200 rounded-xl p-4 text-center hover:border-green-500 transition-colors group">
                    <div class="bg-green-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-green-700 transition-colors">
                        <i class="fas fa-calendar-alt text-white text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">My Schedule</h3>
                    <p class="text-sm text-gray-600 mt-1">View all my assignments</p>
                </a>

                <a href="view_feedback.php" class="bg-blue-100 border-2 border-blue-200 rounded-xl p-4 text-center hover:border-blue-500 transition-colors group">
                    <div class="bg-blue-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-700 transition-colors">
                        <i class="fas fa-comments text-white text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">All Feedback</h3>
                    <p class="text-sm text-gray-600 mt-1">View customer feedback</p>
                </a>
                
                <a href="profile.php" class="bg-purple-100 border-2 border-purple-200 rounded-xl p-4 text-center hover:border-purple-500 transition-colors group">
                    <div class="bg-purple-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-purple-700 transition-colors">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">My Profile</h3>
                    <p class="text-sm text-gray-600 mt-1">Update your information</p>
                </a>
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
    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-info-circle text-4xl text-blue-500 mb-3"></i>
            <p class="text-gray-700">Detailed view for request #${requestId}</p>
            <p class="text-sm text-gray-500 mt-2">This would show complete customer details, special instructions, etc.</p>
        </div>
    `;
    document.getElementById('requestModal').classList.remove('hidden');
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

// Auto-refresh every 5 minutes to check for new assignments
setTimeout(() => {
    window.location.reload();
}, 300000);
</script>

<?php require 'worker_footer.php'; ?>
<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='worker') header('Location: login.php');

$page_title = "Worker Dashboard";

// Get collection requests
$reqs = $mysqli->query("SELECT cr.*, u.name AS user_name, u.phone FROM collection_requests cr JOIN users u ON cr.user_id=u.id ORDER BY cr.created_at DESC");

// Get statistics
$stats = $mysqli->query("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_requests,
        SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as collected_requests
    FROM collection_requests
")->fetch_assoc();

// Get complaints
$compl = $mysqli->query("SELECT c.*, u.name FROM complaints c JOIN users u ON c.user_id=u.id ORDER BY c.created_at DESC LIMIT 5");

// Get feedback
$fb = $mysqli->query("SELECT f.*, u.name FROM feedbacks f JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC LIMIT 5");

require 'header.php';
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
                    <p class="text-gray-600">Manage collection requests and customer communications</p>
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
                    <i class="fas fa-trash text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_requests'] ?? 0; ?></h3>
                <p class="text-gray-600">Total Requests</p>
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
                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['accepted_requests'] ?? 0; ?></h3>
                <p class="text-gray-600">Accepted</p>
            </div>

            <div class="glass rounded-xl p-6 text-center shadow-lg">
                <div class="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-truck-loading text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['collected_requests'] ?? 0; ?></h3>
                <p class="text-gray-600">Collected</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Collection Requests -->
            <div class="glass rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-list-alt text-green-600 mr-3"></i>
                    Collection Requests
                </h2>
                
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
                            <?php while($r = $reqs->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">#<?php echo $r['id']; ?></p>
                                        <p class="text-xs text-gray-500 max-w-xs truncate"><?php echo e($r['address']); ?></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo e($r['user_name']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo e($r['phone']); ?></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <?php echo date('M j, Y', strtotime($r['schedule_date'])); ?>
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
                                            <a href='worker_action.php?action=accept&id=<?php echo $r['id']; ?>' class="inline-block">
                                                <button class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition-colors flex items-center">
                                                    <i class="fas fa-check mr-1"></i> Accept
                                                </button>
                                            </a>
                                        <?php elseif($r['status'] == 'accepted'): ?>
                                            <a href='worker_action.php?action=collect&id=<?php echo $r['id']; ?>' class="inline-block">
                                                <button class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700 transition-colors flex items-center">
                                                    <i class="fas fa-truck-loading mr-1"></i> Collect
                                                </button>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-500">Completed</span>
                                        <?php endif; ?>
                                        
                                        <!-- View Details -->
                                        <button onclick="showRequestDetails(<?php echo $r['id']; ?>)" class="text-gray-600 hover:text-gray-800 text-xs flex items-center">
                                            <i class="fas fa-eye mr-1"></i> Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Customer Communications -->
            <div class="space-y-6">
                <!-- Complaints -->
                <div class="glass rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                        Recent Complaints
                    </h2>
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        <?php while($c = $compl->fetch_assoc()): ?>
                        <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo e($c['name']); ?></p>
                                    <p class="text-sm text-gray-700 mt-1"><?php echo e($c['message']); ?></p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $c['status'] == 'resolved' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'; ?>">
                                    <?php echo ucfirst($c['status']); ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">
                                <i class="far fa-clock mr-1"></i>
                                <?php echo date('M j, Y g:i A', strtotime($c['created_at'])); ?>
                            </p>
                            <?php if($c['status'] == 'open'): ?>
                            <div class="mt-3 text-right">
                                <a href='resolve_complaint.php?id=<?php echo $c['id']; ?>' class="inline-block">
                                    <button class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition-colors">
                                        <i class="fas fa-check mr-1"></i> Resolve
                                    </button>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                        
                        <?php if($compl->num_rows === 0): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3"></i>
                            <p>No complaints</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Feedback -->
                <div class="glass rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-comment-dots text-blue-600 mr-3"></i>
                        Recent Feedback
                    </h2>
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        <?php while($f = $fb->fetch_assoc()): ?>
                        <div class="border-l-4 border-blue-500 bg-blue-50 p-4 rounded">
                            <div class="flex justify-between items-start mb-2">
                                <p class="font-semibold text-gray-800"><?php echo e($f['name']); ?></p>
                                <span class="text-xs text-gray-500">
                                    <?php echo date('M j, Y', strtotime($f['created_at'])); ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-700"><?php echo e($f['message']); ?></p>
                            <?php if(!empty($f['admin_response'])): ?>
                            <div class="mt-3 p-3 bg-white rounded border">
                                <p class="text-sm font-semibold text-gray-800 mb-1">Admin Response:</p>
                                <p class="text-sm text-gray-700"><?php echo e($f['admin_response']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                        
                        <?php if($fb->num_rows === 0): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-comment-slash text-4xl mb-3"></i>
                            <p>No feedback</p>
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
                <a href="collection_requests.php" class="bg-green-100 border-2 border-green-200 rounded-xl p-4 text-center hover:border-green-500 transition-colors group">
                    <div class="bg-green-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-green-700 transition-colors">
                        <i class="fas fa-list text-white text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">View All Requests</h3>
                    <p class="text-sm text-gray-600 mt-1">See complete request list</p>
                </a>

                <a href="complaints.php" class="bg-red-100 border-2 border-red-200 rounded-xl p-4 text-center hover:border-red-500 transition-colors group">
                    <div class="bg-red-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-red-700 transition-colors">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Manage Complaints</h3>
                    <p class="text-sm text-gray-600 mt-1">Handle customer issues</p>
                </a>

                <a href="feedback.php" class="bg-blue-100 border-2 border-blue-200 rounded-xl p-4 text-center hover:border-blue-500 transition-colors group">
                    <div class="bg-blue-600 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-700 transition-colors">
                        <i class="fas fa-comments text-white text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">View Feedback</h3>
                    <p class="text-sm text-gray-600 mt-1">Read customer feedback</p>
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
</script>

<?php require 'worker_footer.php'; ?>
<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

$page_title = "Complaint Management";

// Handle complaint actions
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if($action === 'delete') {
        $mysqli->query("DELETE FROM complaints WHERE id = $id");
        $_SESSION['success'] = "Complaint deleted successfully";
    } elseif(in_array($action, ['open', 'in_progress', 'resolved'])) {
        $mysqli->query("UPDATE complaints SET status = '$action', updated_at = NOW() WHERE id = $id");
        $_SESSION['success'] = "Complaint status updated to " . str_replace('_', ' ', $action);
    }
    header('Location: admin_complaints.php');
    exit;
}

// Handle admin response
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $response = trim($_POST['response']);
    $status = $_POST['status'] ?? 'resolved';
    
    if($response) {
        $stmt = $mysqli->prepare("UPDATE complaints SET admin_response = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $response, $status, $complaint_id);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Response submitted successfully";
        } else {
            $_SESSION['error'] = "Error submitting response";
        }
    } else {
        $_SESSION['error'] = "Please enter a response";
    }
    header('Location: admin_complaints.php');
    exit;
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$priority_filter = $_GET['priority'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query based on filters
$where_conditions = [];
$query_params = [];

if ($filter !== 'all') {
    $where_conditions[] = "c.status = ?";
    $query_params[] = $filter;
}

if ($priority_filter !== 'all') {
    $where_conditions[] = "c.priority = ?";
    $query_params[] = $priority_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE ? OR c.subject LIKE ? OR c.message LIKE ?)";
    $search_term = "%$search%";
    $query_params = array_merge($query_params, [$search_term, $search_term, $search_term]);
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

// Get complaints with filters
$complaints_query = "
    SELECT c.*, u.name as user_name, u.email, u.phone 
    FROM complaints c 
    JOIN users u ON c.user_id = u.id 
    $where_clause
    ORDER BY 
        CASE c.priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
        END,
        c.created_at DESC
";

$stmt = $mysqli->prepare($complaints_query);
if (!empty($query_params)) {
    $types = str_repeat('s', count($query_params));
    $stmt->bind_param($types, ...$query_params);
}
$stmt->execute();
$complaints_result = $stmt->get_result();

// Get complaint statistics - FIXED: removed reserved keyword aliases
$stats = $mysqli->query("
    SELECT 
        COUNT(*) as total_complaints,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_complaints,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_complaints,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_complaints,
        SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_count,
        SUM(CASE WHEN priority = 'medium' THEN 1 ELSE 0 END) as medium_count,
        SUM(CASE WHEN priority = 'low' THEN 1 ELSE 0 END) as low_count
    FROM complaints
")->fetch_assoc();

require 'admin_header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Complaint Management</h1>
                    <p class="text-green-100">Manage and respond to customer complaints</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="bg-green-800 bg-opacity-50 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $complaints_result->num_rows; ?> Complaints
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_complaints']; ?></h3>
                    <p class="text-gray-600">Total Complaints</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-list text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['open_complaints']; ?></h3>
                    <p class="text-gray-600">Open</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['in_progress_complaints']; ?></h3>
                    <p class="text-gray-600">In Progress</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-tasks text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['resolved_complaints']; ?></h3>
                    <p class="text-gray-600">Resolved</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-red-600"><?php echo $stats['high_count']; ?></h3>
                    <p class="text-gray-600">High Priority</p>
                </div>
                <div class="bg-red-100 p-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-yellow-600"><?php echo $stats['medium_count']; ?></h3>
                    <p class="text-gray-600">Medium Priority</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-info-circle text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-green-600"><?php echo $stats['low_count']; ?></h3>
                    <p class="text-gray-600">Low Priority</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-flag text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="?filter=all&priority=<?php echo $priority_filter; ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'all' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    All Complaints
                </a>
                <a href="?filter=open&priority=<?php echo $priority_filter; ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'open' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Open
                </a>
                <a href="?filter=in_progress&priority=<?php echo $priority_filter; ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'in_progress' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    In Progress
                </a>
                <a href="?filter=resolved&priority=<?php echo $priority_filter; ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'resolved' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Resolved
                </a>
            </div>

            <!-- Priority Filter -->
            <div class="flex flex-wrap gap-2">
                <span class="text-sm text-gray-700 font-medium mr-2">Priority:</span>
                <a href="?filter=<?php echo $filter; ?>&priority=all" class="px-3 py-1 rounded text-xs font-medium transition-colors <?php echo $priority_filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    All
                </a>
                <a href="?filter=<?php echo $filter; ?>&priority=high" class="px-3 py-1 rounded text-xs font-medium transition-colors <?php echo $priority_filter === 'high' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    High
                </a>
                <a href="?filter=<?php echo $filter; ?>&priority=medium" class="px-3 py-1 rounded text-xs font-medium transition-colors <?php echo $priority_filter === 'medium' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Medium
                </a>
                <a href="?filter=<?php echo $filter; ?>&priority=low" class="px-3 py-1 rounded text-xs font-medium transition-colors <?php echo $priority_filter === 'low' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Low
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                <input type="hidden" name="priority" value="<?php echo $priority_filter; ?>">
                <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search complaints..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 w-48">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-search"></i>
                </button>
                <?php if(!empty($search)): ?>
                <a href="?filter=<?php echo $filter; ?>&priority=<?php echo $priority_filter; ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Complaints List -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-exclamation-triangle text-orange-600 mr-3"></i>
                Customer Complaints
            </h2>
        </div>

        <?php if($complaints_result->num_rows > 0): ?>
            <div class="divide-y divide-gray-200">
                <?php while($complaint = $complaints_result->fetch_assoc()): 
                    $is_urgent = $complaint['priority'] === 'high';
                    $is_recent = strtotime($complaint['created_at']) >= strtotime('-24 hours');
                ?>
                <div class="p-6 hover:bg-gray-50 transition-colors <?php echo $is_urgent ? 'bg-red-50' : ''; ?>">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <!-- Complaint Content -->
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-4">
                                <div class="flex items-start space-x-3 mb-3 sm:mb-0">
                                    <div class="bg-<?php echo $complaint['priority'] === 'high' ? 'red' : ($complaint['priority'] === 'medium' ? 'yellow' : 'green'); ?>-100 p-3 rounded-lg">
                                        <i class="fas fa-<?php echo $complaint['priority'] === 'high' ? 'exclamation-triangle' : ($complaint['priority'] === 'medium' ? 'info-circle' : 'flag'); ?> text-<?php echo $complaint['priority'] === 'high' ? 'red' : ($complaint['priority'] === 'medium' ? 'yellow' : 'green'); ?>-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?php echo e($complaint['subject']); ?></h3>
                                        <div class="flex items-center space-x-4 mt-1">
                                            <span class="text-sm text-gray-600">
                                                <i class="fas fa-user mr-1"></i>
                                                <?php echo e($complaint['user_name']); ?>
                                            </span>
                                            <span class="text-sm text-gray-600">
                                                <i class="fas fa-envelope mr-1"></i>
                                                <?php echo e($complaint['email']); ?>
                                            </span>
                                            <?php if($complaint['phone']): ?>
                                            <span class="text-sm text-gray-600">
                                                <i class="fas fa-phone mr-1"></i>
                                                <?php echo e($complaint['phone']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php if($is_recent && $complaint['status'] !== 'resolved'): ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                        <i class="fas fa-star mr-1"></i>New
                                    </span>
                                    <?php endif; ?>
                                    
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                        <?php 
                                        $status_colors = [
                                            'open' => 'bg-orange-100 text-orange-800',
                                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                                            'resolved' => 'bg-green-100 text-green-800'
                                        ];
                                        echo $status_colors[$complaint['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>">
                                        <?php echo str_replace('_', ' ', ucfirst($complaint['status'])); ?>
                                    </span>
                                    
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                        <?php 
                                        $priority_colors = [
                                            'high' => 'bg-red-100 text-red-800',
                                            'medium' => 'bg-yellow-100 text-yellow-800',
                                            'low' => 'bg-green-100 text-green-800'
                                        ];
                                        echo $priority_colors[$complaint['priority']] ?? 'bg-gray-100 text-gray-800';
                                        ?>">
                                        <?php echo ucfirst($complaint['priority']); ?> Priority
                                    </span>
                                </div>
                            </div>

                            <!-- Complaint Message -->
                            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                                <p class="text-gray-700 leading-relaxed"><?php echo nl2br(e($complaint['message'])); ?></p>
                            </div>

                            <!-- Timestamps -->
                            <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-4">
                                <span>
                                    <i class="far fa-clock mr-1"></i>
                                    Submitted: <?php echo date('M j, Y g:i A', strtotime($complaint['created_at'])); ?>
                                </span>
                                <?php if($complaint['updated_at'] !== $complaint['created_at']): ?>
                                <span>
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    Updated: <?php echo date('M j, Y g:i A', strtotime($complaint['updated_at'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <!-- Admin Response -->
                            <?php if(!empty($complaint['admin_response'])): ?>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center mb-2">
                                    <div class="bg-green-600 text-white p-2 rounded-full mr-3">
                                        <i class="fas fa-user-shield text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-green-800">Admin Response</h4>
                                        <p class="text-sm text-green-600">
                                            <?php echo date('M j, Y g:i A', strtotime($complaint['updated_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <p class="text-green-700 leading-relaxed ml-11"><?php echo nl2br(e($complaint['admin_response'])); ?></p>
                            </div>
                            <?php else: ?>
                            <!-- Response Form -->
                            <form method="POST" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Response</label>
                                    <textarea name="response" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Type your response to the customer..." required></textarea>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <label class="text-sm text-gray-700 mr-2">Update Status:</label>
                                        <select name="status" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                            <option value="in_progress">In Progress</option>
                                            <option value="resolved">Resolved</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-paper-plane mr-2"></i>Send Response
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Actions -->
                        <div class="lg:w-48 flex lg:flex-col gap-2">
                            <?php if(empty($complaint['admin_response'])): ?>
                            <button onclick="openResponseModal(<?php echo $complaint['id']; ?>)" class="flex-1 lg:flex-none bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-reply mr-2"></i>Respond
                            </button>
                            <?php endif; ?>
                            
                            <div class="flex-1 lg:flex-none flex flex-col space-y-2">
                                <a href="?action=open&id=<?php echo $complaint['id']; ?>" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors text-center">
                                    Mark Open
                                </a>
                                <a href="?action=in_progress&id=<?php echo $complaint['id']; ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors text-center">
                                    In Progress
                                </a>
                                <a href="?action=resolved&id=<?php echo $complaint['id']; ?>" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-medium transition-colors text-center">
                                    Resolve
                                </a>
                            </div>
                            
                            <a href="?action=delete&id=<?php echo $complaint['id']; ?>" onclick="return confirm('Are you sure you want to delete this complaint?')" class="flex-1 lg:flex-none bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="bg-green-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No Complaints Found</h3>
                <p class="text-gray-600 mb-6">
                    <?php if(!empty($search)): ?>
                    No complaints match your search criteria. Try different keywords.
                    <?php elseif($filter !== 'all'): ?>
                    No <?php echo $filter; ?> complaints found.
                    <?php else: ?>
                    All customer complaints have been addressed. Great work!
                    <?php endif; ?>
                </p>
                <?php if(!empty($search) || $filter !== 'all' || $priority_filter !== 'all'): ?>
                <a href="admin_complaints.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="fas fa-refresh mr-2"></i>View All Complaints
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Response Modal -->
<div id="responseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Respond to Complaint</h3>
                <button onclick="closeResponseModal()" class="text-gray-500 hover:text-gray-700 p-2">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <form id="responseForm" method="POST">
                <input type="hidden" name="complaint_id" id="modalComplaintId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Response</label>
                    <textarea name="response" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none" placeholder="Type your detailed response to the customer..." required></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeResponseModal()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>Send Response
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentComplaintId = null;

function openResponseModal(complaintId) {
    currentComplaintId = complaintId;
    document.getElementById('modalComplaintId').value = complaintId;
    document.getElementById('responseModal').classList.remove('hidden');
}

function closeResponseModal() {
    document.getElementById('responseModal').classList.add('hidden');
    currentComplaintId = null;
}

// Close modal when clicking outside
document.getElementById('responseModal').addEventListener('click', function(e) {
    if(e.target === this) closeResponseModal();
});

// Close error messages
document.querySelectorAll('[role="alert"] button').forEach(button => {
    button.addEventListener('click', function() {
        this.closest('[role="alert"]').style.display = 'none';
    });
});

// Auto-hide new indicators after 24 hours
setTimeout(() => {
    document.querySelectorAll('.bg-blue-100').forEach(element => {
        element.style.display = 'none';
    });
}, 3000);
</script>

<?php require 'admin_footer.php'; ?>
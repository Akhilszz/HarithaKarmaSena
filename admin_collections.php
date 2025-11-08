<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

$page_title = "Collection Management";

// Handle collection actions
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if($action === 'delete') {
        $mysqli->query("DELETE FROM collection_requests WHERE id = $id");
        $_SESSION['success'] = "Collection request deleted successfully";
    } elseif(in_array($action, ['accept', 'collect', 'cancel'])) {
        $status_map = ['accept' => 'accepted', 'collect' => 'collected', 'cancel' => 'cancelled'];
        $mysqli->query("UPDATE collection_requests SET status = '{$status_map[$action]}' WHERE id = $id");
        $_SESSION['success'] = "Collection request {$action}ed successfully";
    }
    header('Location: admin_collections.php');
    exit;
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$where = '';
if($filter === 'pending') $where = "WHERE cr.status = 'pending'";
elseif($filter === 'accepted') $where = "WHERE cr.status = 'accepted'";
elseif($filter === 'collected') $where = "WHERE cr.status = 'collected'";
elseif($filter === 'cancelled') $where = "WHERE cr.status = 'cancelled'";

// Get collection requests
$collections = $mysqli->query("
    SELECT cr.*, u.name as user_name, u.phone, u.email, w.name as worker_name
    FROM collection_requests cr 
    JOIN users u ON cr.user_id = u.id 
    LEFT JOIN users w ON cr.assigned_worker_id = w.id 
    $where
    ORDER BY cr.created_at DESC
");

require 'admin_header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Collection Management</h1>
                    <p class="text-green-100">Manage and monitor all waste collection requests</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="bg-green-800 bg-opacity-50 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-trash mr-2"></i>
                        <?php echo $collections->num_rows; ?> Collections
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <?php
        $stats = $mysqli->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as collected
            FROM collection_requests
        ")->fetch_assoc();
        ?>
        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total']; ?></h3>
                    <p class="text-gray-600">Total Requests</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-list text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['pending']; ?></h3>
                    <p class="text-gray-600">Pending</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['accepted']; ?></h3>
                    <p class="text-gray-600">Accepted</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['collected']; ?></h3>
                    <p class="text-gray-600">Collected</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-truck-loading text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
        <div class="flex flex-wrap gap-2">
            <a href="?filter=all" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'all' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                All Collections
            </a>
            <a href="?filter=pending" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'pending' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Pending
            </a>
            <a href="?filter=accepted" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'accepted' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Accepted
            </a>
            <a href="?filter=collected" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'collected' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Collected
            </a>
            <a href="?filter=cancelled" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Cancelled
            </a>
        </div>
    </div>

    <!-- Collections Table -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-list-alt text-green-600 mr-3"></i>
                Collection Requests
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Request Details</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Customer</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Schedule</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Worker</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while($collection = $collections->fetch_assoc()): 
                        $status_config = [
                            'pending' => ['color' => 'orange', 'bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                            'accepted' => ['color' => 'blue', 'bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                            'collected' => ['color' => 'green', 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                            'cancelled' => ['color' => 'red', 'bg' => 'bg-red-100', 'text' => 'text-red-800']
                        ];
                        $status = $collection['status'];
                        $config = $status_config[$status] ?? $status_config['pending'];
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">#<?php echo $collection['id']; ?></p>
                                <p class="text-sm text-gray-600 mt-1 max-w-md">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                    <?php echo e($collection['address']); ?>
                                </p>
                                <?php if(!empty($collection['special_instructions'])): ?>
                                <p class="text-xs text-blue-600 mt-1">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <?php echo e($collection['special_instructions']); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?php echo e($collection['user_name']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo e($collection['phone']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($collection['email']); ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">
                                <?php echo date('M j, Y', strtotime($collection['schedule_date'])); ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?php echo date('l', strtotime($collection['schedule_date'])); ?>
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $config['bg'] . ' ' . $config['text']; ?>">
                                <span class="w-2 h-2 bg-<?php echo $config['color']; ?>-500 rounded-full mr-2"></span>
                                <?php echo ucfirst($status); ?>
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo date('M j, g:i A', strtotime($collection['created_at'])); ?>
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <?php if($collection['worker_name']): ?>
                            <div class="flex items-center space-x-2">
                                <div class="bg-green-100 w-8 h-8 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-hard-hat text-green-600 text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-900"><?php echo e($collection['worker_name']); ?></span>
                            </div>
                            <?php else: ?>
                            <span class="text-sm text-gray-500">Not assigned</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <?php if($status === 'pending'): ?>
                                    <a href="?action=accept&id=<?php echo $collection['id']; ?>" class="text-green-600 hover:text-green-900 text-sm font-medium">
                                        <i class="fas fa-check mr-1"></i>Accept
                                    </a>
                                <?php elseif($status === 'accepted'): ?>
                                    <a href="?action=collect&id=<?php echo $collection['id']; ?>" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        <i class="fas fa-truck-loading mr-1"></i>Collect
                                    </a>
                                <?php endif; ?>
                                
                                <a href="admin_edit_collection.php?id=<?php echo $collection['id']; ?>" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <a href="?action=delete&id=<?php echo $collection['id']; ?>" onclick="return confirm('Are you sure you want to delete this collection request?')" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'admin_footer.php'; ?>
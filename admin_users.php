<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

$page_title = "User Management";

// Handle user actions
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if($action === 'delete' && $id !== $_SESSION['user']['id']) {
        $mysqli->query("DELETE FROM users WHERE id = $id");
        $_SESSION['success'] = "User deleted successfully";
    } elseif($action === 'toggle_status') {
        $mysqli->query("UPDATE users SET status = IF(status='active','inactive','active') WHERE id = $id");
        $_SESSION['success'] = "User status updated";
    }
    header('Location: admin_users.php');
    exit;
}

// Get all users with statistics
$users = $mysqli->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM collection_requests WHERE user_id = u.id) as total_requests,
           (SELECT COUNT(*) FROM collection_requests WHERE user_id = u.id AND status = 'collected') as completed_requests
    FROM users u 
    ORDER BY u.created_at DESC
");

require 'admin_header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">User Management</h1>
                    <p class="text-green-100">Manage all customers, workers, and system users</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <a href="admin_add_user.php" class="bg-white text-green-600 hover:bg-green-50 px-4 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add User
                    </a>
                    <button class="bg-green-800 bg-opacity-50 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <?php
        $stats = $mysqli->query("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as customers,
                SUM(CASE WHEN role = 'worker' THEN 1 ELSE 0 END) as workers,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins
            FROM users
        ")->fetch_assoc();
        ?>
        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_users']; ?></h3>
                    <p class="text-gray-600">Total Users</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['customers']; ?></h3>
                    <p class="text-gray-600">Customers</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-user text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['workers']; ?></h3>
                    <p class="text-gray-600">Workers</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg">
                    <i class="fas fa-user-hard-hat text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['admins']; ?></h3>
                    <p class="text-gray-600">Admins</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-user-shield text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-list text-green-600 mr-3"></i>
                All Users
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">User</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Role</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Requests</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Joined</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while($user = $users->fetch_assoc()): 
                        $role_colors = [
                            'admin' => 'purple',
                            'worker' => 'orange', 
                            'customer' => 'green'
                        ];
                        $color = $role_colors[$user['role']] ?? 'gray';
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="bg-<?php echo $color; ?>-100 w-10 h-10 rounded-full flex items-center justify-center">
                                    <i class="fas fa-<?php echo $user['role'] === 'worker' ? 'user-hard-hat' : ($user['role'] === 'admin' ? 'user-shield' : 'user'); ?> text-<?php echo $color; ?>-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo e($user['name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo e($user['email']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo e($user['phone'] ?? 'No phone'); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <span class="font-semibold"><?php echo $user['total_requests']; ?></span> total
                            </div>
                            <div class="text-xs text-gray-500">
                                <?php echo $user['completed_requests']; ?> completed
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <?php if($user['id'] !== $_SESSION['user']['id']): ?>
                                <a href="?action=toggle_status&id=<?php echo $user['id']; ?>" class="text-orange-600 hover:text-orange-900 text-sm font-medium">
                                    <i class="fas fa-ban mr-1"></i>Disable
                                </a>
                                <a href="?action=delete&id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?')" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </a>
                                <?php endif; ?>
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
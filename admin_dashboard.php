<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

$page_title = "Admin Dashboard";

// Get statistics
$stats = $mysqli->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'user') as total_customers,
        (SELECT COUNT(*) FROM users WHERE role = 'worker') as total_workers,
        (SELECT COUNT(*) FROM collection_requests WHERE status = 'pending') as pending_requests,
        (SELECT COUNT(*) FROM collection_requests WHERE status = 'accepted') as accepted_requests,
        (SELECT COUNT(*) FROM collection_requests WHERE status = 'collected') as collected_requests,
        (SELECT COUNT(*) FROM feedbacks WHERE admin_response IS NULL OR admin_response = '') as pending_feedback,
        (SELECT SUM(dues) FROM users) as total_dues,
        (SELECT COUNT(*) FROM collection_requests WHERE DATE(created_at) = CURDATE()) as today_requests
")->fetch_assoc();

// Get recent activities
$recent_activities = $mysqli->query("
    (SELECT 'collection' as type, cr.id, u.name, cr.created_at, cr.status 
     FROM collection_requests cr 
     JOIN users u ON cr.user_id = u.id 
     ORDER BY cr.created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'feedback' as type, f.id, u.name, f.created_at, f.status 
     FROM feedbacks f 
     JOIN users u ON f.user_id = u.id 
     ORDER BY f.created_at DESC LIMIT 5)
    ORDER BY created_at DESC LIMIT 8
");

// Get workers performance
$workers_performance = $mysqli->query("
    SELECT u.name, 
           COUNT(cr.id) as total_collections,
           AVG(CASE WHEN f.rating IS NOT NULL THEN f.rating ELSE 0 END) as avg_rating
    FROM users u
    LEFT JOIN collection_requests cr ON u.id = cr.assigned_worker_id AND cr.status = 'collected'
    LEFT JOIN feedbacks f ON u.id = f.user_id
    WHERE u.role = 'worker'
    GROUP BY u.id, u.name
    ORDER BY total_collections DESC
    LIMIT 5
");

require 'admin_header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Admin Dashboard</h1>
                    <p class="text-green-100">Welcome back, <?php echo e($_SESSION['user']['name']); ?>! Here's what's happening today.</p>
                </div>
                <div class="mt-4 md:mt-0 bg-green-800 bg-opacity-50 px-4 py-2 rounded-lg">
                    <p class="text-sm">
                        <i class="fas fa-calendar-day mr-2"></i>
                        <?php echo date('l, F j, Y'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Customers -->
        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_customers']; ?></h3>
                    <p class="text-gray-600">Total Customers</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-green-600">
                <i class="fas fa-arrow-up mr-1"></i>
                <span>12% increase</span>
            </div>
        </div>

        <!-- Total Workers -->
        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_workers']; ?></h3>
                    <p class="text-gray-600">Active Workers</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-user-hard-hat text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-green-600">
                <i class="fas fa-check-circle mr-1"></i>
                <span>All active</span>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['pending_requests']; ?></h3>
                    <p class="text-gray-600">Pending Requests</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-orange-600">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <span>Needs attention</span>
            </div>
        </div>

        <!-- Total Dues -->
        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">₹<?php echo number_format($stats['total_dues'] ?? 0, 2); ?></h3>
                    <p class="text-gray-600">Total Dues</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-credit-card text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-red-600">
                <i class="fas fa-money-bill-wave mr-1"></i>
                <span>Pending collection</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Activities -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-history text-blue-600 mr-3"></i>
                        Recent Activities
                    </h2>
                    <a href="admin_activities.php" class="text-sm text-green-600 hover:text-green-700 font-medium">
                        View All
                    </a>
                </div>
                <div class="space-y-4">
                    <?php while($activity = $recent_activities->fetch_assoc()): ?>
                    <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                        <div class="bg-<?php echo $activity['type'] == 'collection' ? 'green' : 'blue'; ?>-100 p-2 rounded-lg">
                            <i class="fas fa-<?php echo $activity['type'] == 'collection' ? 'trash' : 'comment'; ?> text-<?php echo $activity['type'] == 'collection' ? 'green' : 'blue'; ?>-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">
                                <?php echo e($activity['name']); ?> - 
                                <?php echo $activity['type'] == 'collection' ? 'Collection Request' : 'Feedback'; ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?>
                            </p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold 
                            <?php echo $activity['status'] == 'pending' ? 'bg-orange-200 text-orange-800' : 'bg-green-200 text-green-800'; ?>">
                            <?php echo ucfirst($activity['status']); ?>
                        </span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Worker Performance -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-chart-line text-green-600 mr-3"></i>
                Top Workers
            </h2>
            <div class="space-y-4">
                <?php while($worker = $workers_performance->fetch_assoc()): ?>
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="bg-green-100 w-10 h-10 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-hard-hat text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800"><?php echo e($worker['name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo $worker['total_collections']; ?> collections</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center space-x-1">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span class="text-sm font-semibold"><?php echo number_format($worker['avg_rating'] ?? 0, 1); ?></span>
                        </div>
                        <p class="text-xs text-gray-500">Rating</p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
        <a href="admin_users.php" class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-green-500 group">
            <div class="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center mb-4 group-hover:bg-green-200 transition-colors">
                <i class="fas fa-users text-green-600 text-xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-2">Manage Users</h3>
            <p class="text-sm text-gray-600">View and manage all users and workers</p>
        </a>

        <a href="admin_collections.php" class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-blue-500 group">
            <div class="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-200 transition-colors">
                <i class="fas fa-trash text-blue-600 text-xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-2">Collection Requests</h3>
            <p class="text-sm text-gray-600">Manage all collection requests</p>
        </a>

        <a href="admin_payments.php" class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-purple-500 group">
            <div class="bg-purple-100 w-12 h-12 rounded-lg flex items-center justify-center mb-4 group-hover:bg-purple-200 transition-colors">
                <i class="fas fa-credit-card text-purple-600 text-xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-2">Payment Management</h3>
            <p class="text-sm text-gray-600">View and manage payments</p>
        </a>

        <a href="admin_feedback.php" class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-orange-500 group">
            <div class="bg-orange-100 w-12 h-12 rounded-lg flex items-center justify-center mb-4 group-hover:bg-orange-200 transition-colors">
                <i class="fas fa-comments text-orange-600 text-xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-2">Customer Feedback</h3>
            <p class="text-sm text-gray-600">Respond to customer feedback</p>
        </a>
    </div>
</div>

<?php require 'admin_footer.php'; ?>
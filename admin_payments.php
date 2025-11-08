<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

$page_title = "Payment Management";

// Get payment statistics
$stats = $mysqli->query("
    SELECT 
        COUNT(*) as total_payments,
        COALESCE(SUM(amount), 0) as total_revenue,
        COALESCE(AVG(amount), 0) as avg_payment,
        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
        SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_payments
    FROM collection_requests 
    WHERE payment_status IS NOT NULL
")->fetch_assoc();

// Get payments with user information
$payments = $mysqli->query("
    SELECT cr.*, u.name as user_name, u.email, u.phone
    FROM collection_requests cr 
    JOIN users u ON cr.user_id = u.id 
    WHERE cr.payment_status IS NOT NULL
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
                    <h1 class="text-3xl font-bold mb-2">Payment Management</h1>
                    <p class="text-green-100">Monitor and manage all payment transactions</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="bg-green-800 bg-opacity-50 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-credit-card mr-2"></i>
                        Total Revenue: ₹<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?>
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
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_payments']; ?></h3>
                    <p class="text-gray-600">Total Payments</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-credit-card text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">₹<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h3>
                    <p class="text-gray-600">Total Revenue</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">₹<?php echo number_format($stats['avg_payment'] ?? 0, 2); ?></h3>
                    <p class="text-gray-600">Average Payment</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['pending_payments']; ?></h3>
                    <p class="text-gray-600">Pending Payments</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Status Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Status Distribution</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm font-medium">Paid</span>
                    </div>
                    <span class="text-sm font-semibold"><?php echo $stats['paid_payments']; ?> payments</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                        <span class="text-sm font-medium">Pending</span>
                    </div>
                    <span class="text-sm font-semibold"><?php echo $stats['pending_payments']; ?> payments</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-receipt text-green-600 mr-3"></i>
                Payment Transactions
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Payment ID</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Customer</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Collection</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Amount</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while($payment = $payments->fetch_assoc()): 
                        $amount = $payment['amount'] ?? 150.00; // Default amount if not set
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">#<?php echo $payment['id']; ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?php echo e($payment['user_name']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo e($payment['email']); ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">Collection #<?php echo $payment['id']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo ucfirst($payment['status']); ?></p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-900">
                                ₹<?php echo number_format($amount, 2); ?>
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $status_color = $payment['payment_status'] === 'paid' ? 'green' : 'orange';
                            $status_text = $payment['payment_status'] === 'paid' ? 'Paid' : 'Pending';
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-<?php echo $status_color; ?>-100 text-<?php echo $status_color; ?>-800">
                                <span class="w-2 h-2 bg-<?php echo $status_color; ?>-500 rounded-full mr-2"></span>
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo date('M j, Y', strtotime($payment['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <?php if($payment['payment_status'] === 'pending'): ?>
                                <a href="admin_mark_paid.php?id=<?php echo $payment['id']; ?>" class="text-green-600 hover:text-green-900 text-sm font-medium">
                                    <i class="fas fa-check mr-1"></i>Mark Paid
                                </a>
                                <?php endif; ?>
                                <button class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    <i class="fas fa-receipt mr-1"></i>Invoice
                                </button>
                                <a href="admin_collections.php" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if($payments->num_rows === 0): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-credit-card text-4xl mb-3 text-gray-400"></i>
                            <p class="text-lg font-medium">No payment records found</p>
                            <p class="text-sm mt-1">Payment records will appear here once collections are processed</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'admin_footer.php'; ?>
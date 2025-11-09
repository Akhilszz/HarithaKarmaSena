<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

$page_title = "Edit Collection Request";

// Get collection ID from URL
$collection_id = intval($_GET['id'] ?? 0);
if(!$collection_id) {
    header('Location: admin_collections.php');
    exit;
}

// Get collection details
$collection = $mysqli->query("
    SELECT cr.*, u.name as user_name, u.email, u.phone 
    FROM collection_requests cr 
    JOIN users u ON cr.user_id = u.id 
    WHERE cr.id = $collection_id
")->fetch_assoc();

if(!$collection) {
    $_SESSION['error'] = "Collection request not found";
    header('Location: admin_collections.php');
    exit;
}

// Get all workers for assignment
$workers = $mysqli->query("SELECT id, name FROM users WHERE role = 'worker' ORDER BY name");

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $schedule_date = $_POST['schedule_date'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    $payment_status = $_POST['payment_status'] ?? 'pending';
    $assigned_worker_id = $_POST['assigned_worker_id'] ? intval($_POST['assigned_worker_id']) : NULL;
    $special_instructions = trim($_POST['special_instructions'] ?? '');
    $amount = floatval($_POST['amount'] ?? 150.00);

    // Validate required fields
    if(empty($address) || empty($schedule_date)) {
        $error = "Address and schedule date are required";
    } else {
        // Update collection request
        $stmt = $mysqli->prepare("
            UPDATE collection_requests 
            SET address = ?, schedule_date = ?, status = ?, payment_status = ?, 
                assigned_worker_id = ?, special_instructions = ?, amount = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        if($assigned_worker_id) {
            $stmt->bind_param("ssssisdi", $address, $schedule_date, $status, $payment_status, 
                             $assigned_worker_id, $special_instructions, $amount, $collection_id);
        } else {
            $stmt->bind_param("ssssssdi", $address, $schedule_date, $status, $payment_status, 
                             $assigned_worker_id, $special_instructions, $amount, $collection_id);
        }
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Collection request updated successfully";
            header('Location: admin_collections.php');
            exit;
        } else {
            $error = "Error updating collection request: " . $stmt->error;
        }
    }
}

require 'admin_header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Edit Collection Request</h1>
                    <p class="text-green-100">Update collection details and assign workers</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="admin_collections.php" class="bg-white text-green-600 hover:bg-green-50 px-4 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Collections
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Information Card -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
            Collection Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700">Request ID:</span>
                <span class="text-gray-900">#<?php echo $collection['id']; ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Customer:</span>
                <span class="text-gray-900"><?php echo e($collection['user_name']); ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Email:</span>
                <span class="text-gray-900"><?php echo e($collection['email']); ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Phone:</span>
                <span class="text-gray-900"><?php echo e($collection['phone']); ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Created:</span>
                <span class="text-gray-900"><?php echo date('M j, Y g:i A', strtotime($collection['created_at'])); ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Last Updated:</span>
                <span class="text-gray-900"><?php echo date('M j, Y g:i A', strtotime($collection['updated_at'] ?? $collection['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <?php if(!empty($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo e($error); ?></span>
            </div>
            <button type="button" class="text-red-700 hover:text-red-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <form method="POST" class="bg-white rounded-2xl shadow-xl p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                <!-- Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Collection Address *</label>
                    <textarea 
                        name="address" 
                        rows="3"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                        placeholder="Enter complete collection address"
                    ><?php echo e($collection['address']); ?></textarea>
                </div>

                <!-- Schedule Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date *</label>
                    <input 
                        type="date" 
                        name="schedule_date" 
                        value="<?php echo $collection['schedule_date']; ?>"
                        required
                        min="<?php echo date('Y-m-d'); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                </div>

                <!-- Special Instructions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
                    <textarea 
                        name="special_instructions" 
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                        placeholder="Any special instructions for the worker..."
                    ><?php echo e($collection['special_instructions'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select 
                        name="status"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                        <option value="pending" <?php echo $collection['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="accepted" <?php echo $collection['status'] === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                        <option value="collected" <?php echo $collection['status'] === 'collected' ? 'selected' : ''; ?>>Collected</option>
                        <option value="cancelled" <?php echo $collection['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <!-- Payment Status (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <div class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                        <?php 
                        $payment_status_text = $collection['payment_status'] === 'paid' ? 'Paid' : 'Pending';
                        $payment_status_color = $collection['payment_status'] === 'paid' ? 'text-green-600' : 'text-yellow-600';
                        ?>
                        <span class="<?php echo $payment_status_color; ?> font-medium">
                            <?php echo $payment_status_text; ?>
                        </span>
                    </div>
                    <!-- Hidden input to maintain the payment_status value in form submission -->
                    <input type="hidden" name="payment_status" value="<?php echo $collection['payment_status']; ?>">
                </div>

                <!-- Assigned Worker -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign Worker</label>
                    <select 
                        name="assigned_worker_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                        <option value="">-- Not Assigned --</option>
                        <?php while($worker = $workers->fetch_assoc()): ?>
                            <option value="<?php echo $worker['id']; ?>" 
                                <?php echo $collection['assigned_worker_id'] == $worker['id'] ? 'selected' : ''; ?>>
                                <?php echo e($worker['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Collection Amount (₹)</label>
                    <input 
                        type="number" 
                        name="amount" 
                        value="<?php echo $collection['amount'] ?? 150.00; ?>"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Enter collection amount"
                    >
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 mt-8 pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-500">
                Last updated: <?php echo date('M j, Y g:i A', strtotime($collection['updated_at'] ?? $collection['created_at'])); ?>
            </div>
            <div class="flex space-x-3">
                <a href="admin_collections.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>Update Collection
                </button>
            </div>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="bg-red-50 border border-red-200 rounded-2xl p-6 mt-8">
        <h3 class="text-lg font-semibold text-red-800 mb-4 flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Danger Zone
        </h3>
        <p class="text-red-700 mb-4">Once you delete a collection request, there is no going back. Please be certain.</p>
        <div class="flex space-x-3">
            <button 
                onclick="confirmDelete()"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors flex items-center"
            >
                <i class="fas fa-trash mr-2"></i>Delete Collection Request
            </button>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    if(confirm('Are you sure you want to delete this collection request? This action cannot be undone.')) {
        window.location.href = 'admin_collections.php?action=delete&id=<?php echo $collection_id; ?>';
    }
}

// Close error message
document.querySelectorAll('[role="alert"] button').forEach(button => {
    button.addEventListener('click', function() {
        this.closest('[role="alert"]').style.display = 'none';
    });
});
</script>

<?php require 'admin_footer.php'; ?>
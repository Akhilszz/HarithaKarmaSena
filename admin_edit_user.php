<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

$page_title = "Edit User";
$id = intval($_GET['id'] ?? 0);

// Get user details
$res = $mysqli->query("SELECT * FROM users WHERE id={$id}"); 
$user = $res->fetch_assoc();

if(!$user) {
    $_SESSION['error'] = "User not found";
    header('Location: admin_users.php');
    exit;
}

// Handle form submission
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!verify_csrf($_POST['csrf'] ?? '')){ 
        $err = 'Invalid CSRF token'; 
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role']; 
        $dues = floatval($_POST['dues']);
        $address = trim($_POST['address'] ?? '');

        // Validate required fields
        if(empty($name) || empty($email)) {
            $err = 'Name and email are required';
        } else {
            $stmt = $mysqli->prepare('UPDATE users SET name=?, email=?, phone=?, role=?, dues=?, address=? WHERE id=?');
            $stmt->bind_param('ssssdsi', $name, $email, $phone, $role, $dues, $address, $id);
            
            if($stmt->execute()){
                $_SESSION['success'] = "User updated successfully";
                header('Location: admin_users.php');
                exit;
            } else {
                $err = 'Error updating user: ' . $stmt->error;
            }
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
                    <h1 class="text-3xl font-bold mb-2">Edit User</h1>
                    <p class="text-green-100">Update user information and permissions</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="admin_users.php" class="bg-white text-green-600 hover:bg-green-50 px-4 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- User Information Card -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-user-circle text-blue-600 mr-2"></i>
            User Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700">User ID:</span>
                <span class="text-gray-900">#<?php echo $user['id']; ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Current Role:</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold 
                    <?php 
                    $role_colors = [
                        'admin' => 'bg-purple-100 text-purple-800',
                        'worker' => 'bg-orange-100 text-orange-800', 
                        'user' => 'bg-green-100 text-green-800'
                    ];
                    echo $role_colors[$user['role']] ?? 'bg-gray-100 text-gray-800';
                    ?>">
                    <?php echo ucfirst($user['role']); ?>
                </span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Joined:</span>
                <span class="text-gray-900"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Last Updated:</span>
                <span class="text-gray-900"><?php echo date('M j, Y', strtotime($user['updated_at'] ?? $user['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <?php if(!empty($err)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo e($err); ?></span>
            </div>
            <button type="button" class="text-red-700 hover:text-red-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <form method="POST" class="bg-white rounded-2xl shadow-xl p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Personal Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Personal Information</h3>
                
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input 
                        type="text" 
                        name="name" 
                        value="<?php echo e($user['name']); ?>"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Enter full name"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="<?php echo e($user['email']); ?>"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Enter email address"
                    >
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input 
                        type="tel" 
                        name="phone" 
                        value="<?php echo e($user['phone'] ?? ''); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Enter phone number"
                    >
                </div>
            </div>

            <!-- Right Column - Account Settings -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Account Settings</h3>
                
                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User Role *</label>
                    <select 
                        name="role"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Customer</option>
                        <option value="worker" <?php echo $user['role'] == 'worker' ? 'selected' : ''; ?>>Worker</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php
                        $role_descriptions = [
                            'user' => 'Can request collections and submit feedback',
                            'worker' => 'Can accept and complete collection requests',
                            'admin' => 'Full system access and management'
                        ];
                        echo $role_descriptions[$user['role']] ?? '';
                        ?>
                    </p>
                </div>

                <!-- Dues -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Outstanding Dues (₹)</label>
                    <input 
                        type="number" 
                        name="dues" 
                        value="<?php echo e($user['dues']); ?>"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Enter outstanding dues"
                    >
                    <p class="text-xs text-gray-500 mt-1">Amount the user owes for services</p>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea 
                        name="address" 
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                        placeholder="Enter user's address"
                    ><?php echo e($user['address'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 mt-8 pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-500">
                User since: <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
            </div>
            <div class="flex space-x-3">
                <a href="admin_users.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>Update User
                </button>
            </div>
        </div>

        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
    </form>

    <!-- User Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <?php
        // Get user statistics
        $user_stats = $mysqli->query("
            SELECT 
                (SELECT COUNT(*) FROM collection_requests WHERE user_id = {$user['id']}) as total_requests,
                (SELECT COUNT(*) FROM collection_requests WHERE user_id = {$user['id']} AND status = 'collected') as completed_requests,
                (SELECT COUNT(*) FROM feedbacks WHERE user_id = {$user['id']}) as feedback_count
        ")->fetch_assoc();
        ?>
        
        <div class="bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $user_stats['total_requests']; ?></h3>
                    <p class="text-gray-600">Total Requests</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-list text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-xl p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $user_stats['completed_requests']; ?></h3>
                    <p class="text-gray-600">Completed</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-xl p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $user_stats['feedback_count']; ?></h3>
                    <p class="text-gray-600">Feedback</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-comment text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <?php if($user['id'] !== $_SESSION['user']['id']): ?>
    <div class="bg-red-50 border border-red-200 rounded-2xl p-6 mt-8">
        <h3 class="text-lg font-semibold text-red-800 mb-4 flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Danger Zone
        </h3>
        <p class="text-red-700 mb-4">These actions are irreversible. Please be certain before proceeding.</p>
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
           
            <button 
                onclick="confirmAction('delete')"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center"
            >
                <i class="fas fa-trash mr-2"></i>Delete User
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function confirmAction(action) {
    const messages = {
        'suspend': 'Are you sure you want to suspend this user? They will not be able to access the system.',
        'delete': 'Are you sure you want to delete this user? All their data will be permanently removed.'
    };
    
    if(confirm(messages[action])) {
        if(action === 'delete') {
            window.location.href = 'admin_users.php?action=delete&id=<?php echo $id; ?>';
        } else if(action === 'suspend') {
            // Implement suspend functionality
            alert('User suspension functionality would be implemented here');
        }
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
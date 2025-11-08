<?php
require 'config.php';
if(!is_logged_in()) header('Location: login.php');

$uid = $_SESSION['user']['id'];
$page_title = "My Profile";

// Handle form submission
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verify_csrf($_POST['csrf'] ?? '')){ 
    $err='Invalid CSRF token'; 
  } else {
    $name = trim($_POST['name'] ?? ''); 
    $phone = trim($_POST['phone'] ?? ''); 
    $addr = trim($_POST['address'] ?? '');
    
    if(empty($name)) {
        $err = 'Name is required';
    } else {
        $stmt = $mysqli->prepare('UPDATE users SET name=?, phone=?, address=? WHERE id=?'); 
        $stmt->bind_param('sssi',$name,$phone,$addr,$uid); 
        
        if($stmt->execute()){
            $_SESSION['user']['name'] = $name;
            $_SESSION['success'] = "Profile updated successfully!";
            header('Location: profile.php');
            exit;
        } else {
            $err = 'Error updating profile: ' . $stmt->error;
        }
    }
  }
}

// Get user data
$res = $mysqli->query("SELECT * FROM users WHERE id={$uid}"); 
$user = $res->fetch_assoc();

// Get user statistics based on role
if($_SESSION['user']['role'] === 'customer') {
    $stats = $mysqli->query("
        SELECT 
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as completed_requests,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests
        FROM collection_requests 
        WHERE user_id = $uid
    ")->fetch_assoc();
} elseif($_SESSION['user']['role'] === 'worker') {
    $stats = $mysqli->query("
        SELECT 
            COUNT(*) as assigned_requests,
            SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as completed_requests,
            SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_requests
        FROM collection_requests 
        WHERE assigned_worker_id = $uid
    ")->fetch_assoc();
}

require 'header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">My Profile</h1>
                    <p class="text-green-100">Manage your personal information and account settings</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="bg-green-800 bg-opacity-50 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-user mr-2"></i>
                        <?php echo ucfirst($_SESSION['user']['role']); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <?php if($_SESSION['user']['role'] === 'customer'): ?>
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_requests'] ?? 0; ?></h3>
                        <p class="text-gray-600">Total Requests</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-list text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['completed_requests'] ?? 0; ?></h3>
                        <p class="text-gray-600">Completed</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['pending_requests'] ?? 0; ?></h3>
                        <p class="text-gray-600">Pending</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

        <?php elseif($_SESSION['user']['role'] === 'worker'): ?>
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['assigned_requests'] ?? 0; ?></h3>
                        <p class="text-gray-600">Assigned</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-tasks text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['completed_requests'] ?? 0; ?></h3>
                        <p class="text-gray-600">Completed</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['accepted_requests'] ?? 0; ?></h3>
                        <p class="text-gray-600">Accepted</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <i class="fas fa-user-check text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-user-edit text-green-600 mr-3"></i>
                    Edit Profile Information
                </h2>

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

                <form method='post' class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name *
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            value="<?php echo e($user['name']); ?>" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                            placeholder="Enter your full name"
                        >
                    </div>

                    <!-- Email (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input 
                            type="email" 
                            value="<?php echo e($user['email']); ?>" 
                            disabled
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500"
                        >
                        <p class="text-xs text-gray-500 mt-1">Email address cannot be changed</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <input 
                            type="tel" 
                            name="phone" 
                            value="<?php echo e($user['phone']); ?>" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                            placeholder="Enter your phone number"
                        >
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <textarea 
                            name="address" 
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                            placeholder="Enter your complete address"
                        ><?php echo e($user['address']); ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            This address will be used for waste collection services
                        </p>
                    </div>

                    <input type='hidden' name='csrf' value='<?php echo csrf_token(); ?>'>
                    
                    <!-- Submit Button -->
                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center"
                        >
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Information Sidebar -->
        <div class="space-y-6">
            <!-- Account Summary -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-id-card text-blue-600 mr-2"></i>
                    Account Summary
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">User ID:</span>
                        <span class="font-medium">#<?php echo $user['id']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Account Type:</span>
                        <span class="font-medium text-green-600"><?php echo ucfirst($user['role']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Member Since:</span>
                        <span class="font-medium"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Updated:</span>
                        <span class="font-medium"><?php echo date('M j, Y', strtotime($user['updated_at'] ?? $user['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                    Quick Actions
                </h3>
                <div class="space-y-3">
                    <?php if($_SESSION['user']['role'] === 'customer'): ?>
                        <a href="collection_requests.php" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <div class="bg-green-100 p-2 rounded-lg mr-3">
                                <i class="fas fa-plus text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">New Collection</p>
                                <p class="text-xs text-gray-600">Request waste collection</p>
                            </div>
                        </a>
                    <?php elseif($_SESSION['user']['role'] === 'worker'): ?>
                        <a href="pending_collections.php" class="flex items-center p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                            <div class="bg-orange-100 p-2 rounded-lg mr-3">
                                <i class="fas fa-list text-orange-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Pending Requests</p>
                                <p class="text-xs text-gray-600">View assigned work</p>
                            </div>
                        </a>
                    <?php endif; ?>
                    
                    <a href="feedback.php" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-comment text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Give Feedback</p>
                            <p class="text-xs text-gray-600">Share your experience</p>
                        </div>
                    </a>

                    <a href="change_password.php" class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <div class="bg-purple-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-lock text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Change Password</p>
                            <p class="text-xs text-gray-600">Update security</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Account Status -->
            <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
                <div class="flex items-center mb-3">
                    <div class="bg-green-100 p-2 rounded-lg mr-3">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-green-800">Account Active</h4>
                        <p class="text-sm text-green-600">Your account is in good standing</p>
                    </div>
                </div>
                <?php if($user['dues'] > 0): ?>
                    <div class="mt-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <p class="text-sm font-medium text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Outstanding Dues: ₹<?php echo number_format($user['dues'], 2); ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200">
                        <p class="text-sm font-medium text-green-800">
                            <i class="fas fa-check mr-1"></i>
                            No outstanding dues
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Close error message
document.querySelectorAll('[role="alert"] button').forEach(button => {
    button.addEventListener('click', function() {
        this.closest('[role="alert"]').style.display = 'none';
    });
});

// Auto-hide success message after 5 seconds
setTimeout(() => {
    document.querySelectorAll('[role="alert"]').forEach(alert => {
        alert.style.display = 'none';
    });
}, 5000);
</script>

<?php require 'footer.php'; ?>
<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

$page_title = "Add New User";

// Handle form submission
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!verify_csrf($_POST['csrf'] ?? '')){ 
        $err = 'Invalid CSRF token'; 
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role']; 
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate required fields
        if(empty($name) || empty($email) || empty($password)) {
            $err = 'Name, email, and password are required';
        } elseif($password !== $confirm_password) {
            $err = 'Passwords do not match';
        } elseif(strlen($password) < 6) {
            $err = 'Password must be at least 6 characters long';
        } else {
            // Check if email already exists
            $check_stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ?');
            $check_stmt->bind_param('s', $email);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if($check_stmt->num_rows > 0) {
                $err = 'Email address already exists';
            } else {
                // Hash password and create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $dues = 0.00; // New users start with zero dues
                
                $stmt = $mysqli->prepare('INSERT INTO users (name, email, phone, role, address, password, dues) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssssd', $name, $email, $phone, $role, $address, $hashed_password, $dues);
                
                if($stmt->execute()){
                    $new_user_id = $stmt->insert_id;
                    $_SESSION['success'] = "User created successfully! User ID: #{$new_user_id}";
                    header('Location: admin_users.php');
                    exit;
                } else {
                    $err = 'Error creating user: ' . $stmt->error;
                }
            }
            $check_stmt->close();
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
                    <h1 class="text-3xl font-bold mb-2">Add New User</h1>
                    <p class="text-green-100">Create a new user account in the system</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="admin_users.php" class="bg-white text-green-600 hover:bg-green-50 px-4 py-2 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Users
                    </a>
                </div>
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

    <!-- Add User Form -->
    <form method="POST" class="bg-white rounded-2xl shadow-xl p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Personal Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 flex items-center">
                    <i class="fas fa-user-circle text-blue-600 mr-2"></i>
                    Personal Information
                </h3>
                
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name *
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="<?php echo e($_POST['name'] ?? ''); ?>"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="Enter full name"
                        autofocus
                    >
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address *
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        value="<?php echo e($_POST['email'] ?? ''); ?>"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="Enter email address"
                    >
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number
                    </label>
                    <input 
                        type="tel" 
                        name="phone" 
                        value="<?php echo e($_POST['phone'] ?? ''); ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="Enter phone number"
                    >
                </div>
            </div>

            <!-- Right Column - Account Settings -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 flex items-center">
                    <i class="fas fa-cog text-green-600 mr-2"></i>
                    Account Settings
                </h3>
                
                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        User Role *
                    </label>
                    <select 
                        name="role"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                    >
                        <option value="">Select a role</option>
                        <option value="user" <?php echo ($_POST['role'] ?? '') == 'user' ? 'selected' : ''; ?>>Customer</option>
                        <option value="worker" <?php echo ($_POST['role'] ?? '') == 'worker' ? 'selected' : ''; ?>>Worker</option>
                        <option value="admin" <?php echo ($_POST['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                    </select>
                    <div class="mt-2 space-y-1 text-xs text-gray-500">
                        <div class="flex items-center">
                            <i class="fas fa-user text-green-600 mr-2 w-4"></i>
                            <span><strong>Customer:</strong> Can request collections and submit feedback</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-user-hard-hat text-orange-600 mr-2 w-4"></i>
                            <span><strong>Worker:</strong> Can accept and complete collection requests</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-user-shield text-purple-600 mr-2 w-4"></i>
                            <span><strong>Admin:</strong> Full system access and management</span>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password *
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        required
                        minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="Enter password (min. 6 characters)"
                        id="password"
                    >
                    <div class="mt-1 flex items-center space-x-2">
                        <div id="password-strength" class="flex-1 bg-gray-200 rounded-full h-2">
                            <div id="password-strength-bar" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <span id="password-strength-text" class="text-xs text-gray-500">Weak</span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password *
                    </label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="Confirm password"
                        id="confirm_password"
                    >
                    <div id="password-match" class="mt-1 text-xs hidden">
                        <i class="fas fa-check text-green-500 mr-1"></i>
                        <span class="text-green-600">Passwords match</span>
                    </div>
                    <div id="password-mismatch" class="mt-1 text-xs hidden">
                        <i class="fas fa-times text-red-500 mr-1"></i>
                        <span class="text-red-600">Passwords do not match</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Field (Full Width) -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 flex items-center">
                <i class="fas fa-map-marker-alt text-red-600 mr-2"></i>
                Address Information
            </h3>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Address
                </label>
                <textarea 
                    name="address" 
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                    placeholder="Enter user's complete address"
                ><?php echo e($_POST['address'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-end space-y-4 sm:space-y-0 sm:space-x-3 mt-8 pt-6 border-t border-gray-200">
            <a href="admin_users.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors text-center">
                Cancel
            </a>
            <button 
                type="submit" 
                id="submit-btn"
                class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i class="fas fa-user-plus mr-2"></i>Create User
            </button>
        </div>

        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
    </form>

    <!-- Quick Tips -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mt-8">
        <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
            Quick Tips
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-700">
            <div class="flex items-start space-x-2">
                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                <span>Ensure email is unique and valid for communication</span>
            </div>
            <div class="flex items-start space-x-2">
                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                <span>Choose appropriate role based on user responsibilities</span>
            </div>
            <div class="flex items-start space-x-2">
                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                <span>Use strong passwords with minimum 6 characters</span>
            </div>
            <div class="flex items-start space-x-2">
                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                <span>Provide complete address for collection services</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordStrengthBar = document.getElementById('password-strength-bar');
    const passwordStrengthText = document.getElementById('password-strength-text');
    const passwordMatch = document.getElementById('password-match');
    const passwordMismatch = document.getElementById('password-mismatch');
    const submitBtn = document.getElementById('submit-btn');

    function checkPasswordStrength(pass) {
        let strength = 0;
        if (pass.length >= 6) strength += 25;
        if (pass.match(/[a-z]+/)) strength += 25;
        if (pass.match(/[A-Z]+/)) strength += 25;
        if (pass.match(/[0-9]+/)) strength += 25;
        
        passwordStrengthBar.style.width = strength + '%';
        
        if (strength < 50) {
            passwordStrengthBar.className = 'h-2 rounded-full bg-red-500 transition-all duration-300';
            passwordStrengthText.textContent = 'Weak';
            passwordStrengthText.className = 'text-xs text-red-500';
        } else if (strength < 75) {
            passwordStrengthBar.className = 'h-2 rounded-full bg-yellow-500 transition-all duration-300';
            passwordStrengthText.textContent = 'Medium';
            passwordStrengthText.className = 'text-xs text-yellow-500';
        } else {
            passwordStrengthBar.className = 'h-2 rounded-full bg-green-500 transition-all duration-300';
            passwordStrengthText.textContent = 'Strong';
            passwordStrengthText.className = 'text-xs text-green-500';
        }
    }

    function checkPasswordMatch() {
        if (confirmPassword.value === '') {
            passwordMatch.classList.add('hidden');
            passwordMismatch.classList.add('hidden');
            return;
        }
        
        if (password.value === confirmPassword.value) {
            passwordMatch.classList.remove('hidden');
            passwordMismatch.classList.add('hidden');
        } else {
            passwordMatch.classList.add('hidden');
            passwordMismatch.classList.remove('hidden');
        }
    }

    function validateForm() {
        const isPasswordStrong = parseInt(passwordStrengthBar.style.width) >= 50;
        const isPasswordMatch = password.value === confirmPassword.value && password.value !== '';
        const isFormValid = isPasswordStrong && isPasswordMatch;
        
        submitBtn.disabled = !isFormValid;
    }

    password.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        checkPasswordMatch();
        validateForm();
    });

    confirmPassword.addEventListener('input', function() {
        checkPasswordMatch();
        validateForm();
    });

    // Close error message
    document.querySelectorAll('[role="alert"] button').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('[role="alert"]').style.display = 'none';
        });
    });

    // Initial validation
    validateForm();
});
</script>

<?php require 'admin_footer.php'; ?>
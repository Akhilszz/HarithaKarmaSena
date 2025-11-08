<?php
// setup_admin.php
// Run this once after importing db.sql to create an admin user.
// DELETE THIS FILE AFTER SETUP FOR SECURITY

require 'config.php';

// Check if admin already exists
$admin_check = $mysqli->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$admin_exists = $admin_check->num_rows > 0;

$msg = '';
$err = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Security check - prevent multiple admin creation
    if($admin_exists) {
        $err = 'An admin user already exists. Please delete this file for security.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = trim($_POST['name'] ?? 'Admin');
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if(!$email || !$password || !$confirm_password) {
            $err = 'Please fill all required fields';
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Please enter a valid email address';
        } elseif($password !== $confirm_password) {
            $err = 'Passwords do not match';
        } elseif(strlen($password) < 8) {
            $err = 'Password must be at least 8 characters long';
        } else {
            // Check if email already exists
            $email_check = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
            $email_check->bind_param('s', $email);
            $email_check->execute();
            if($email_check->get_result()->num_rows > 0) {
                $err = 'Email already exists in the system';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'admin', '0000000000')");
                $stmt->bind_param('sss', $name, $email, $hash);
                if($stmt->execute()){
                    $msg = '✅ Admin user created successfully! <strong>Please delete this file immediately for security.</strong>';
                    $admin_exists = true;
                    
                    // Log the setup
                    $admin_id = $stmt->insert_id;
                    $log_stmt = $mysqli->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
                    $action = "admin_setup";
                    $description = "Initial admin user created via setup script";
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $log_stmt->bind_param('isss', $admin_id, $action, $description, $ip);
                    $log_stmt->execute();
                } else {
                    $err = 'Database Error: ' . $stmt->error;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin | Haritha Karma Sena</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .bg-pattern {
            background-color: #f0fdf4;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(16,185,129,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(6,95,70,0.1) 0%, transparent 50%);
        }
        
        .security-warning {
            border-left: 4px solid #ef4444;
            background: #fef2f2;
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="bg-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-user-shield text-2xl text-emerald-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Admin Setup</h1>
            <p class="text-gray-600">Create initial administrator account</p>
        </div>

        <!-- Security Warning -->
        <?php if(!$admin_exists): ?>
        <div class="security-warning p-4 rounded-lg mb-6">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-red-500 text-lg mr-3 mt-0.5"></i>
                <div>
                    <h3 class="font-semibold text-red-800 text-sm">SECURITY NOTICE</h3>
                    <p class="text-red-700 text-sm mt-1">
                        This file should be deleted immediately after creating the admin account.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Messages -->
        <?php if(!empty($err)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-red-600"></i>
                <span><?php echo htmlspecialchars($err); ?></span>
            </div>
        <?php endif; ?>

        <?php if(!empty($msg)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3 text-green-600"></i>
                    <span><?php echo $msg; ?></span>
                </div>
                <?php if($admin_exists): ?>
                <div class="mt-3 p-3 bg-white rounded border border-green-200">
                    <p class="text-sm text-green-800 font-semibold">Next Steps:</p>
                    <ol class="text-sm text-green-700 mt-2 list-decimal list-inside space-y-1">
                        <li>Delete this setup_admin.php file from the server</li>
                        <li>Login to the system using your new admin credentials</li>
                        <li>Configure your system settings and add workers/users</li>
                    </ol>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Setup Form -->
        <?php if(!$admin_exists): ?>
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-emerald-200">
            <form method="post" class="space-y-4">
                <!-- Name Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-emerald-600"></i>
                        Full Name
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="<?php echo htmlspecialchars($_POST['name'] ?? 'Admin'); ?>" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                        placeholder="Enter admin name"
                    >
                </div>

                <!-- Email Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-emerald-600"></i>
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                        placeholder="admin@example.com"
                    >
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-emerald-600"></i>
                        Password
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                        placeholder="Minimum 8 characters"
                    >
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long</p>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-emerald-600"></i>
                        Confirm Password
                    </label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                        placeholder="Confirm your password"
                    >
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 px-4 rounded-lg font-semibold transition-colors flex items-center justify-center"
                >
                    <i class="fas fa-user-shield mr-2"></i>
                    Create Admin Account
                </button>
            </form>
        </div>
        <?php else: ?>
            <!-- Admin already exists message -->
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center border border-emerald-200">
                <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-2xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Setup Complete</h3>
                <p class="text-gray-600 mb-6">Admin user already exists in the system.</p>
                
                <div class="security-warning p-4 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-500 text-lg mr-3 mt-0.5"></i>
                        <div class="text-left">
                            <h4 class="font-semibold text-red-800 text-sm mb-1">IMPORTANT SECURITY ACTION REQUIRED</h4>
                            <p class="text-red-700 text-sm">
                                Please delete <code class="bg-red-100 px-1 rounded">setup_admin.php</code> from your server immediately.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 space-y-3">
                    <a href="login.php" class="block bg-emerald-600 hover:bg-emerald-700 text-white py-3 px-4 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Go to Login Page
                    </a>
                    <a href="index.php" class="block bg-gray-600 hover:bg-gray-700 text-white py-3 px-4 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Go to Homepage
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Footer Info -->
        <div class="text-center mt-8 text-sm text-gray-500">
            <p>Haritha Karma Sena &copy; <?php echo date('Y'); ?></p>
            <p class="mt-1">Waste Management System</p>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const password = document.querySelector('input[name="password"]');
            const confirmPassword = document.querySelector('input[name="confirm_password"]');
            
            function validatePasswords() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            if (password && confirmPassword) {
                password.addEventListener('input', validatePasswords);
                confirmPassword.addEventListener('input', validatePasswords);
            }
            
            // Form submission enhancement
            if (form) {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating Admin...';
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php
// header.php
if(!isset($page_title)) $page_title = "Haritha Karma Sena";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo e($page_title); ?> | Haritha Karma Sena</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .bg-gradient-green {
      background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%);
    }
    .bg-pattern {
      background-color: #f0fdf4;
      background-image:
        radial-gradient(circle at 20% 50%, rgba(16,185,129,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(6,95,70,0.1) 0%, transparent 50%);
    }
    .brand-text {
      font-size: 48px;
      font-weight: 800;
      background: linear-gradient(90deg, #d4e157, #7cb342, #558b2f);
      background-clip: text;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
  </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col">

<!-- Top Navigation -->
<nav class="bg-gradient-green shadow-lg sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center py-4">

      <!-- Logo -->
      <div class="flex items-center space-x-3">
        <div class="bg-white p-2 rounded-lg shadow-lg">
          <img src="assets/img/leaf.jpeg" alt="Haritha Karma Sena" class="h-8 w-8">
        </div>
        <h1 class="text-white text-2xl font-bold">Haritha Karma Sena</h1>
      </div>

      <!-- Navigation Links -->
      <div class="hidden md:flex items-center space-x-6 text-white font-medium">
        <a href="user_dashboard.php" class="hover:text-green-200 transition">Dashboard</a>
        <a href="collection_requests.php" class="hover:text-green-200 transition">Collection Requests</a>
        <a href="payment.php" class="hover:text-green-200 transition">Payments</a>
        <a href="feedback.php" class="hover:text-green-200 transition">Feedback</a>
        <a href="complaints.php" class="hover:text-green-200 transition">Complaints</a>
      </div>

      <!-- User Menu -->
      <div class="flex items-center space-x-4">
        <div class="relative">
            <button id="userMenuButton" class="flex items-center space-x-2 text-white hover:text-green-200 transition-colors p-2 rounded-lg hover:bg-green-800 hover:bg-opacity-50">
                <div class="bg-green-800 w-8 h-8 rounded-full flex items-center justify-center border-2 border-green-300">
                    <i class="fas fa-user-circle text-green-100 text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-medium"><?php echo e($_SESSION['user']['name'] ?? 'User'); ?></p>
                </div>
                <i class="fas fa-chevron-down text-green-200 text-xs"></i>
            </button>

            <!-- User Dropdown Menu -->
            <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-50">
                <div class="p-4 border-b border-gray-200 bg-green-600 text-white rounded-t-lg">
                    <p class="text-sm font-medium"><?php echo e($_SESSION['user']['name'] ?? 'User'); ?></p>
                    <p class="text-xs text-green-200">Customer</p>
                </div>
                <div class="p-2">
                    <a href="profile.php" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-green-50 rounded-lg transition-colors">
                        <i class="fas fa-user-cog w-4 text-green-600"></i>
                        <span>Profile Settings</span>
                    </a>
                    <a href="user_dashboard.php" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-green-50 rounded-lg transition-colors">
                        <i class="fas fa-tachometer-alt w-4 text-green-600"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="p-2 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                    <a href="logout.php" class="flex items-center space-x-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt w-4"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
      </div>
       <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm transition flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>

    </div>
  </div>

  <!-- Mobile Navigation -->
  <div class="md:hidden bg-white text-gray-700 px-4 py-2 space-y-1 border-t">
    <a href="user_dashboard.php" class="block py-1">Dashboard</a>
    <a href="collection_requests.php" class="block py-1">Collection Requests</a>
    <a href="payment.php" class="block py-1">Payments</a>
    <a href="feedback.php" class="block py-1">Feedback</a>
    <a href="complaints.php" class="block py-1">Complaints</a>
    <a href="logout.php" class="block py-1 text-red-600">Logout</a>
  </div>
</nav>

<!-- JavaScript for User Dropdown -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenuButton = document.getElementById('userMenuButton');
    const userDropdown = document.getElementById('userDropdown');

    if(userMenuButton && userDropdown) {
        // Toggle dropdown on button click
        userMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            userDropdown.classList.add('hidden');
        });

        // Prevent dropdown from closing when clicking inside it
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Close dropdown when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape') {
                userDropdown.classList.add('hidden');
            }
        });
    }
});
</script>
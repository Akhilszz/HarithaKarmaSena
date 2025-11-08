<?php
// header.php
// function e($string) {
//     return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
// }

// function is_logged_in() {
//     return isset($_SESSION['user']);
// }

if(!isset($page_title)) $page_title = "Haritha Karma Sena";

// Get worker-specific data for header
$worker_id = $_SESSION['user']['id'] ?? null;
$pending_count = 0;
$today_stats = ['total' => 0, 'completed' => 0];

if($worker_id) {
    // Get pending count for this worker
    $pending_stmt = $mysqli->prepare("
        SELECT COUNT(*) as count 
        FROM collection_requests 
        WHERE status = 'pending' AND assigned_worker_id = ?
    ");
    $pending_stmt->bind_param('i', $worker_id);
    $pending_stmt->execute();
    $pending_result = $pending_stmt->get_result()->fetch_assoc();
    $pending_count = $pending_result['count'] ?? 0;
    
    // Get today's stats for this worker
    $today = date('Y-m-d');
    $today_stmt = $mysqli->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as completed
        FROM collection_requests 
        WHERE assigned_worker_id = ? AND DATE(created_at) = ?
    ");
    $today_stmt->bind_param('is', $worker_id, $today);
    $today_stmt->execute();
    $today_result = $today_stmt->get_result()->fetch_assoc();
    $today_stats = $today_result ?: ['total' => 0, 'completed' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> | Haritha Karma Sena</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
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
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .sidebar {
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
        }
        
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .nav-active {
            background: linear-gradient(90deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(6, 78, 59, 0.2);
        }
        
        .nav-active:hover {
            background: linear-gradient(90deg, #059669, #047857);
            color: white;
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col">

<!-- Top Navigation -->
<nav class="bg-gradient-green shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center py-4">

            <!-- Logo and Mobile Menu -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Button -->
                <button id="mobileMenuButton" class="lg:hidden text-white p-2 rounded-lg bg-emerald-800 bg-opacity-50">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="bg-white p-2 rounded-lg shadow-lg">
                        <i class="fas fa-recycle text-emerald-600 text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-white text-xl font-bold">Haritha Karma Sena</h1>
                        <p class="text-emerald-200 text-xs">Worker Portal</p>
                    </div>
                </div>
            </div>

            <!-- Desktop Navigation Links -->
            <div class="hidden lg:flex items-center space-x-6 text-white font-medium">
                <a href="worker_dashboard.php" class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'worker_dashboard.php' ? 'bg-emerald-800 bg-opacity-30' : ''; ?>">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="collection_pending.php" class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'collection_pending.php' ? 'bg-emerald-800 bg-opacity-30' : ''; ?>">
                    <i class="fas fa-list-alt mr-2"></i>My Pending
                    <?php if($pending_count > 0): ?>
                    <span class="ml-1 bg-orange-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="my_schedule.php" class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'my_schedule.php' ? 'bg-emerald-800 bg-opacity-30' : ''; ?>">
                    <i class="fas fa-calendar-alt mr-2"></i>My Schedule
                </a>
                <a href="view_feedback.php" class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'view_feedback.php' ? 'bg-emerald-800 bg-opacity-30' : ''; ?>">
                    <i class="fas fa-comments mr-2"></i>Feedback
                </a>
            </div>

            <!-- User Menu & Notifications -->
            <div class="flex items-center space-x-4">
                <!-- User Menu -->
                <div class="relative">
                    <button id="userMenuButton" class="flex items-center space-x-3 text-white p-2 rounded-lg hover:bg-emerald-800 hover:bg-opacity-50 transition">
                        <div class="bg-emerald-800 w-8 h-8 rounded-full flex items-center justify-center border-2 border-emerald-300">
                            <i class="fas fa-user-hard-hat text-emerald-100 text-sm"></i>
                        </div>
                        <div class="text-left hidden md:block">
                            <p class="text-sm font-medium"><?php echo e($_SESSION['user']['name'] ?? 'Worker'); ?></p>
                            <p class="text-xs text-emerald-200">Worker</p>
                        </div>
                        <i class="fas fa-chevron-down text-emerald-200 text-xs"></i>
                    </button>

                    <!-- User Dropdown -->
                    <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-50">
                        <div class="p-4 border-b border-gray-200 bg-emerald-600 text-white rounded-t-lg">
                            <p class="text-sm font-medium"><?php echo e($_SESSION['user']['name'] ?? 'Worker'); ?></p>
                            <p class="text-xs text-emerald-200 truncate"><?php echo e($_SESSION['user']['email'] ?? 'worker@email.com'); ?></p>
                        </div>
                        <div class="p-2">
                            <a href="profile.php" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors">
                                <i class="fas fa-user-cog w-4 text-emerald-600"></i>
                                <span>Profile Settings</span>
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

                <!-- Logout Button -->
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm transition flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Sidebar -->
<div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

<div id="sidebar" class="sidebar bg-white shadow-xl w-64 min-h-screen fixed lg:hidden z-50">
    <!-- Logo Section -->
    <div class="p-6 border-b border-gray-200 bg-emerald-600 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-white p-2 rounded-lg">
                    <i class="fas fa-recycle text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold">Haritha Karma Sena</h1>
                    <p class="text-emerald-200 text-xs">Worker Portal</p>
                </div>
            </div>
            <button id="closeMobileMenu" class="text-white">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
    </div>

    <!-- User Profile Section -->
    <div class="p-4 border-b border-gray-200 bg-emerald-50">
        <div class="flex items-center space-x-3">
            <div class="bg-emerald-600 w-10 h-10 rounded-full flex items-center justify-center border-2 border-emerald-300">
                <i class="fas fa-user-hard-hat text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($_SESSION['user']['name'] ?? 'Worker'); ?></p>
                <p class="text-xs text-emerald-600 truncate">Worker</p>
                <p class="text-xs text-gray-500 truncate"><?php echo e($_SESSION['user']['email'] ?? 'worker@email.com'); ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="p-4 space-y-1">
        <a href="worker_dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'worker_dashboard.php' ? 'nav-active' : ''; ?>">
            <i class="fas fa-tachometer-alt w-5"></i>
            <span>Dashboard</span>
        </a>

        <a href="collection_pending.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'collection_pending.php' ? 'nav-active' : ''; ?>">
            <i class="fas fa-list-alt w-5"></i>
            <span>My Pending</span>
            <?php if($pending_count > 0): ?>
            <span class="ml-auto bg-orange-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $pending_count; ?></span>
            <?php endif; ?>
        </a>

        <a href="my_schedule.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'my_schedule.php' ? 'nav-active' : ''; ?>">
            <i class="fas fa-calendar-alt w-5"></i>
            <span>My Schedule</span>
        </a>

        <a href="view_feedback.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'view_feedback.php' ? 'nav-active' : ''; ?>">
            <i class="fas fa-comments w-5"></i>
            <span>Customer Feedback</span>
        </a>

        <div class="pt-4 border-t border-gray-200">
            <a href="profile.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'nav-active' : ''; ?>">
                <i class="fas fa-user-cog w-5"></i>
                <span>Profile Settings</span>
            </a>

            <a href="logout.php" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Quick Stats -->
    <div class="p-4 border-t border-gray-200 mt-4">
        <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-200">
            <h3 class="text-xs font-semibold text-emerald-800 uppercase mb-2">Today's Summary</h3>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-emerald-700">Assigned:</span>
                    <span class="font-semibold text-emerald-900"><?php echo $today_stats['total']; ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-emerald-700">Completed:</span>
                    <span class="font-semibold text-green-600"><?php echo $today_stats['completed']; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="flex-1">
    <!-- Flash Messages -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="mx-4 mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-600"></i>
                <span class="block sm:inline"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
            <button type="button" class="text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="mx-4 mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-3 text-red-600"></i>
                <span class="block sm:inline"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
            <button type="button" class="text-red-700 hover:text-red-900" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Page Content -->
    <main class="flex-1">

<script>
// Mobile Menu Toggle
document.getElementById('mobileMenuButton').addEventListener('click', function() {
    document.getElementById('sidebar').classList.add('mobile-open');
    document.getElementById('mobileOverlay').classList.remove('hidden');
});

document.getElementById('closeMobileMenu').addEventListener('click', function() {
    document.getElementById('sidebar').classList.remove('mobile-open');
    document.getElementById('mobileOverlay').classList.add('hidden');
});

document.getElementById('mobileOverlay').addEventListener('click', function() {
    document.getElementById('sidebar').classList.remove('mobile-open');
    this.classList.add('hidden');
});

// User Dropdown Toggle
document.getElementById('userMenuButton').addEventListener('click', function() {
    document.getElementById('userDropdown').classList.toggle('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const userDropdown = document.getElementById('userDropdown');
    const userMenuButton = document.getElementById('userMenuButton');
    
    if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
        userDropdown.classList.add('hidden');
    }
});

// Auto-hide flash messages after 5 seconds
setTimeout(() => {
    const flashMessages = document.querySelectorAll('[role="alert"]');
    flashMessages.forEach(message => {
        message.style.transition = 'opacity 0.5s ease';
        message.style.opacity = '0';
        setTimeout(() => message.remove(), 500);
    });
}, 5000);
</script>
<?php
// admin_header.php
if (!isset($page_title)) $page_title = "Admin Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo e($page_title); ?> | Haritha Karma Sena</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

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
    .sidebar { transition: all .3s ease; }
    @media (max-width: 1024px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.mobile-open { transform: translateX(0); }
    }
    .nav-active {
      background: linear-gradient(90deg, #10b981, #059669);
      color: white;
      box-shadow: 0 4px 6px -1px rgba(6, 78, 59, 0.2);
    }
  </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col">

<!-- Top Navigation -->
<nav class="bg-gradient-green shadow-lg sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center py-4">

      <!-- Logo + Mobile Menu -->
      <div class="flex items-center space-x-4">
        <button id="mobileMenuButton" class="lg:hidden text-white p-2 rounded-lg bg-emerald-800 bg-opacity-50">
          <i class="fas fa-bars text-lg"></i>
        </button>

        <div class="flex items-center space-x-3">
          <div class="bg-white p-2 rounded-lg shadow-lg">
            <i class="fas fa-recycle text-emerald-600 text-xl"></i>
          </div>
          <div>
            <h1 class="text-white text-xl font-bold">Haritha Karma Sena</h1>
            <p class="text-emerald-200 text-xs">Admin Portal</p>
          </div>
        </div>
      </div>

      <!-- Desktop Navigation -->
      <div class="hidden lg:flex items-center space-x-6 text-white font-medium">
        <a href="admin_dashboard.php"
           class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF'])==='admin_dashboard.php'?'bg-emerald-800 bg-opacity-30':''; ?>">
          <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
        </a>
        <a href="admin_users.php"
           class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF'])==='admin_users.php'?'bg-emerald-800 bg-opacity-30':''; ?>">
          <i class="fas fa-users mr-2"></i>Users
        </a>
        <a href="admin_complaints.php"
           class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF'])==='admin_complaints.php'?'bg-emerald-800 bg-opacity-30':''; ?>">
          <i class="fas fa-triangle-exclamation mr-2"></i>Complaints
        </a>
        <a href="admin_collections.php"
           class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF'])==='admin_collections.php'?'bg-emerald-800 bg-opacity-30':''; ?>">
          <i class="fas fa-trash mr-2"></i>Collections
        </a>
        <a href="admin_payments.php"
           class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF'])==='admin_payments.php'?'bg-emerald-800 bg-opacity-30':''; ?>">
          <i class="fas fa-credit-card mr-2"></i>Payments
        </a>
        <a href="admin_feedback.php"
           class="hover:text-emerald-200 transition py-2 px-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF'])==='admin_feedback.php'?'bg-emerald-800 bg-opacity-30':''; ?>">
          <i class="fas fa-comments mr-2"></i>Feedback
        </a>
       
        
      </div>

      <!-- Admin Dropdown (no notifications) -->
      <div class="relative">
        <button id="userMenuButton"
                class="flex items-center space-x-3 text-white p-2 rounded-lg hover:bg-emerald-800 hover:bg-opacity-50 transition">
          <div class="bg-emerald-800 w-8 h-8 rounded-full flex items-center justify-center border-2 border-emerald-300">
            <i class="fas fa-user-shield text-emerald-100 text-sm"></i>
          </div>
          <div class="text-left hidden md:block">
            <p class="text-sm font-medium"><?php echo e($_SESSION['user']['name'] ?? 'Admin'); ?></p>
            <p class="text-xs text-emerald-200">Administrator</p>
          </div>
          <i class="fas fa-chevron-down text-emerald-200 text-xs"></i>
        </button>

        <!-- Dropdown -->
        <div id="userDropdown"
             class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-50">
          <div class="p-4 border-b border-gray-200 bg-emerald-600 text-white rounded-t-lg">
            <p class="text-sm font-medium"><?php echo e($_SESSION['user']['name'] ?? 'Admin'); ?></p>
            <p class="text-xs text-emerald-200 truncate"><?php echo e($_SESSION['user']['email'] ?? 'admin@harithakarmasena.com'); ?></p>
          </div>
          <div class="p-2">
            <a href="admin_profile.php"
               class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors">
              <i class="fas fa-user-cog w-4 text-emerald-600"></i>
              <span>Profile Settings</span>
            </a>
            
          </div>
          <div class="p-2 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <a href="logout.php"
               class="flex items-center space-x-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
              <i class="fas fa-sign-out-alt w-4"></i>
              <span>Logout</span>
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</nav>

<!-- Mobile Overlay -->
<div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

<!-- Mobile Sidebar -->
<div id="sidebar" class="sidebar bg-white shadow-xl w-64 min-h-screen fixed lg:hidden z-50">
  <!-- Logo -->
  <div class="p-6 border-b border-gray-200 bg-emerald-600 text-white">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="bg-white p-2 rounded-lg">
          <i class="fas fa-recycle text-emerald-600 text-lg"></i>
        </div>
        <div>
          <h1 class="text-xl font-bold">Haritha Karma Sena</h1>
          <p class="text-emerald-200 text-xs">Admin Portal</p>
        </div>
      </div>
      <button id="closeMobileMenu" class="text-white">
        <i class="fas fa-times text-lg"></i>
      </button>
    </div>
  </div>

  <!-- User mini-card -->
  <div class="p-6 border-b border-gray-200">
    <div class="flex items-center space-x-3">
      <div class="bg-emerald-100 w-12 h-12 rounded-full flex items-center justify-center">
        <i class="fas fa-user-shield text-emerald-600 text-lg"></i>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-gray-800 truncate"><?php echo e($_SESSION['user']['name'] ?? 'Admin'); ?></p>
        <p class="text-xs text-gray-600 truncate"><?php echo e($_SESSION['user']['email'] ?? 'admin@harithakarmasena.com'); ?></p>
        <span class="inline-block mt-1 px-2 py-1 bg-emerald-100 text-emerald-800 text-xs rounded-full font-semibold">
          <i class="fas fa-badge-check mr-1"></i>Administrator
        </span>
      </div>
    </div>
  </div>

  <!-- Nav (mobile) -->
  <nav class="p-4 space-y-1">
    <a href="admin_dashboard.php"
       class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF'])==='admin_dashboard.php'?'nav-active':''; ?>">
      <i class="fas fa-tachometer-alt w-5"></i><span>Dashboard</span>
    </a>
    <a href="admin_users.php"
       class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF'])==='admin_users.php'?'nav-active':''; ?>">
      <i class="fas fa-users w-5"></i><span>Users & Workers</span>
    </a>
    <a href="admin_complaints.php"
       class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF'])==='admin_complaints.php'?'nav-active':''; ?>">
      <i class="fas fa-triangle-exclamation w-5"></i><span>Complaints</span>
    </a>
    <a href="admin_collections.php"
       class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF'])==='admin_collections.php'?'nav-active':''; ?>">
      <i class="fas fa-trash w-5"></i><span>Collection Requests</span>
    </a>
    <a href="admin_payments.php"
       class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF'])==='admin_payments.php'?'nav-active':''; ?>">
      <i class="fas fa-credit-card w-5"></i><span>Payments</span>
    </a>
    <a href="admin_feedback.php"
       class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF'])==='admin_feedback.php'?'nav-active':''; ?>">
      <i class="fas fa-comments w-5"></i><span>Customer Feedback</span>
    </a>
    

    <div class="pt-4 border-t border-gray-200">
      <a href="admin_profile.php"
         class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors <?php echo basename($_SERVER['PHP_SELF'])==='admin_profile.php'?'nav-active':''; ?>">
        <i class="fas fa-user-cog w-5"></i><span>Profile Settings</span>
      </a>
      
      <a href="logout.php"
         class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
        <i class="fas fa-sign-out-alt w-5"></i><span>Logout</span>
      </a>
    </div>
  </nav>
</div>

<!-- Main Content Wrapper starts in the page that includes this header -->
<div class="flex-1">
  <!-- Flash messages (optional, keep if you use them) -->
  <?php if(isset($_SESSION['success'])): ?>
    <div class="mx-4 mt-4 bg-green-50 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
      <div class="flex items-center">
        <i class="fas fa-check-circle mr-3 text-green-600"></i>
        <span class="block sm:inline"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
      </div>
      <button type="button" class="text-green-700 hover:text-green-900" onclick="this.closest('[role=alert]').remove()">
        <i class="fas fa-times"></i>
      </button>
    </div>
  <?php endif; ?>

  <?php if(isset($_SESSION['error'])): ?>
    <div class="mx-4 mt-4 bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
      <div class="flex items-center">
        <i class="fas fa-exclamation-triangle mr-3 text-red-600"></i>
        <span class="block sm:inline"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
      </div>
      <button type="button" class="text-red-700 hover:text-red-900" onclick="this.closest('[role=alert]').remove()">
        <i class="fas fa-times"></i>
      </button>
    </div>
  <?php endif; ?>

  <!-- Page content will continue from here in including file -->
  <main class="flex-1">
<script>
  const mobileBtn = document.getElementById('mobileMenuButton');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('mobileOverlay');
  const closeMobile = document.getElementById('closeMobileMenu');
  const userBtn = document.getElementById('userMenuButton');
  const userDropdown = document.getElementById('userDropdown');

  function openSidebar() {
    sidebar.classList.add('mobile-open');
    overlay.classList.remove('hidden');
  }
  function closeSidebar() {
    sidebar.classList.remove('mobile-open');
    overlay.classList.add('hidden');
  }
  if (mobileBtn) mobileBtn.addEventListener('click', openSidebar);
  if (closeMobile) closeMobile.addEventListener('click', closeSidebar);
  if (overlay) overlay.addEventListener('click', closeSidebar);

  // Dropdown toggle
  if (userBtn && userDropdown) {
    userBtn.addEventListener('click', () => {
      userDropdown.classList.toggle('hidden');
    });
    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.add('hidden');
      }
    });
  }
</script>

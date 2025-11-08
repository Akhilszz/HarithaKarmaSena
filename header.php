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
        radial-gradient(circle at 20% 50%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(6, 95, 70, 0.1) 0%, transparent 50%);
    }

    .glass {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .gradient-border {
      position: relative;
      border: 2px solid transparent;
      background: linear-gradient(white, white) padding-box,
                  linear-gradient(135deg, #10b981, #047857) border-box;
    }

    .btn-hover:hover {
      transform: scale(1.02);
      box-shadow: 0 10px 25px rgba(6, 95, 70, 0.4);
    }

    .input-field:focus {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
    }

    .brand-text {
      font-size: 48px;
      font-weight: 800;
      background: linear-gradient(90deg, #d4e157, #7cb342, #558b2f);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .sidebar-item.active {
      background: linear-gradient(135deg, #10b981, #047857);
      color: white;
    }

    .status-pending { background-color: #fef3c7; color: #92400e; }
    .status-confirmed { background-color: #d1fae5; color: #065f46; }
    .status-completed { background-color: #dbeafe; color: #1e40af; }
    .status-cancelled { background-color: #fee2e2; color: #991b1b; }
  </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col">
<!-- Navigation Header -->
<nav class="bg-gradient-green shadow-lg">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center py-4">
      <!-- Logo and Brand -->
      <div class="flex items-center space-x-3">
        <div class="bg-white p-2 rounded-lg shadow-lg">
          <img src="assets/img/leaf.svg" alt="Haritha Karma Sena" class="h-8 w-8">
        </div>
        <h1 class="text-white text-2xl font-bold">Haritha Karma Sena</h1>
      </div>

      <!-- User Menu -->
      <div class="flex items-center space-x-4">
        <div class="text-white text-sm">
          <i class="fas fa-user-circle mr-1"></i>
          <?php echo e($_SESSION['user']['name'] ?? 'User'); ?>
        </div>
        <div class="relative group">
          <button class="text-white hover:text-green-200 transition-colors">
            <i class="fas fa-cog text-xl"></i>
          </button>
          <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 hidden group-hover:block z-50">
            <a href="user_dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-green-50">
              <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </a>
            <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-green-50">
              <i class="fas fa-user mr-2"></i>Profile
            </a>
            <div class="border-t my-1"></div>
            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">
              <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>
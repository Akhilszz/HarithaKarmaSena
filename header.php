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
          <img src="assets/img/leaf.svg" alt="Haritha Karma Sena" class="h-8 w-8">
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
        <span class="text-white text-sm">
          <i class="fas fa-user-circle mr-1"></i>
          <?php echo e($_SESSION['user']['name'] ?? 'User'); ?>
        </span>

        <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm transition">
          <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>
      </div>

    </div>
  </div>

  <!-- Mobile Navigation -->
  <div class="md:hidden bg-white text-gray-700 px-4 py-2 space-y-1 border-t">
    <a href="user_dashboard.php" class="block py-1">Dashboard</a>
    <a href="collection_requests.php" class="block py-1">Collection Requests</a>
    <a href="payment.php" class="block py-1">Payments</a>
    <a href="complaints.php" class="block py-1">Complaints</a>
    <a href="feedback.php" class="hover:text-green-200 transition">Feedback</a>

    <a href="logout.php" class="block py-1 text-red-600">Logout</a>
  </div>
</nav>

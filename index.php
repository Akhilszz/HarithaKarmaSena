<?php require 'config.php'; ?>
<!doctype html>
<html><head><meta charset='utf-8'><title>Haritha Karma Sena</title>
<link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-green-50'>
<div class='max-w-5xl mx-auto bg-white p-6 shadow rounded-lg mt-10'>
  <header class='flex justify-between items-center border-b pb-4 mb-6'>
    <div>
      <h1 class='text-2xl font-bold text-green-700'>Haritha Karma Sena</h1>
      <p class='text-sm text-gray-500'>Community-led garbage collection & eco services</p>
    </div>
    <nav class='space-x-3'>
      <a href='index.php' class='text-green-600 hover:underline'>Home</a>
      <?php if(is_logged_in()): ?>
        <a href='profile.php' class='text-green-700'>Profile</a>
        <?php if($_SESSION['user']['role']==='user'): ?><a href='user_dashboard.php' class='text-green-600'>Dashboard</a><?php endif; ?>
        <?php if($_SESSION['user']['role']==='worker'): ?><a href='worker_dashboard.php' class='text-green-600'>Worker</a><?php endif; ?>
        <?php if($_SESSION['user']['role']==='admin'): ?><a href='admin_dashboard.php' class='text-green-600'>Admin</a><?php endif; ?>
        <a href='logout.php' class='text-red-500'>Logout</a>
      <?php else: ?>
        <a href='login.php' class='bg-green-600 text-white px-3 py-1 rounded'>Login</a>
        <a href='signup.php' class='bg-green-700 text-white px-3 py-1 rounded'>Sign Up</a>
      <?php endif; ?>
    </nav>
  </header>

  <section class='mb-6'>
    <h2 class='text-xl font-semibold mb-2'>About HKS</h2>
    <p class='text-gray-600'>Haritha Karma Sena (HKS) connects residents with local workers for scheduled garbage collection, recycling and sanitation services. Request pickups, view payments, and send feedback.</p>
    <img src='assets/img/hero.jpg' class='rounded-lg mt-4 w-full' alt='community cleanup' />
  </section>

  <section class='mt-6'>
    <?php if(!is_logged_in()): ?>
      <a href='signup.php' class='inline-block bg-green-700 text-white px-4 py-2 rounded'>Sign up</a>
      <a href='login.php' class='inline-block bg-green-600 text-white px-4 py-2 rounded ml-2'>Login</a>
    <?php else: ?>
      <p>Welcome, <?php echo e($_SESSION['user']['name']); ?> — go to your dashboard to request collection or view dues.</p>
    <?php endif; ?>
  </section>

  <footer class='text-center text-sm text-gray-400 mt-8'>&copy; <?php echo date('Y'); ?> Haritha Karma Sena</footer>
</div></body></html>

<?php
require 'config.php';
// If you don't already, ensure sessions are started inside config.php
// and helper functions like is_logged_in() and e() exist.
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Haritha Karma Sena | Door-to-Door Waste Collection & Eco Services</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Haritha Karma Sena (HKS) connects households and institutions in Kerala with trained Kudumbashree workers for door-to-door collection of segregated waste, recycling and green services.">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <meta name="theme-color" content="#065f46">
  <link rel="preload" as="image" href="assets/img/hero.jpg">
  <style>
    /* Custom gradient backgrounds */
    .bg-gradient-green {
      background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%);
    }
    
    .bg-gradient-light {
      background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    }
    
    .bg-pattern {
      background-color: #f0fdf4;
      background-image: 
        radial-gradient(circle at 20% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(6, 95, 70, 0.05) 0%, transparent 50%);
    }

    /* Glassmorphism effect */
    .glass {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Smooth transitions */
    * {
      transition: all 0.3s ease;
    }

    /* Hover effects */
    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .btn-hover:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 20px rgba(6, 95, 70, 0.3);
    }

    /* Animated gradient border */
    .gradient-border {
      position: relative;
      border: 2px solid transparent;
      background: linear-gradient(white, white) padding-box,
                  linear-gradient(135deg, #10b981, #047857) border-box;
    }

    /* Section divider */
    .section-divider {
      height: 2px;
      background: linear-gradient(90deg, transparent, #10b981, transparent);
    }

    /* Pulse animation for badges */
    @keyframes pulse-green {
      0%, 100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
      }
      50% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
      }
    }

    .pulse-badge {
      animation: pulse-green 2s infinite;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 10px;
    }

    ::-webkit-scrollbar-track {
      background: #f0fdf4;
    }

    ::-webkit-scrollbar-thumb {
      background: #10b981;
      border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #059669;
    }

    /* Leaf decoration */
    .leaf-decoration::before {
      content: '🍃';
      position: absolute;
      opacity: 0.1;
      font-size: 200px;
      top: -50px;
      right: -50px;
      transform: rotate(-15deg);
    }

    /* FAQ details styling */
    details > summary {
      list-style: none;
    }
    
    details > summary::-webkit-details-marker {
      display: none;
    }

    details > summary::before {
      content: '▶';
      margin-right: 10px;
      color: #10b981;
      transition: transform 0.3s;
      display: inline-block;
    }

    details[open] > summary::before {
      transform: rotate(90deg);
    }

    details[open] {
      background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
    }

    /* Mobile menu animation */
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .menu-open {
      animation: slideDown 0.3s ease-out;
    }

    /* Stats counter effect */
    .stat-card {
      position: relative;
      overflow: hidden;
    }

    .stat-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: left 0.5s;
    }

    .stat-card:hover::after {
      left: 100%;
    }
  </style>
  <script>
    // Enhanced mobile menu toggle
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('menuBtn');
      const nav = document.getElementById('menuNav');
      if (btn) {
        btn.addEventListener('click', () => {
          nav.classList.toggle('hidden');
          nav.classList.toggle('menu-open');
          const expanded = nav.classList.contains('hidden') ? 'false' : 'true';
          btn.setAttribute('aria-expanded', expanded);
        });
      }

      // Smooth scroll for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        });
      });
    });
  </script>
  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"NGO",
    "name":"Haritha Karma Sena",
    "areaServed":"Kerala, India",
    "description":"Community-led door-to-door collection of segregated waste, recycling and green services.",
    "url":"<?php echo htmlspecialchars((isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8'); ?>",
    "image":"assets/img/hero.jpg",
    "parentOrganization":{
      "@type":"Organization",
      "name":"Kudumbashree / Suchitwa Mission"
    }
  }
  </script>
</head>
<body class="bg-pattern">
  <div class="max-w-6xl mx-auto glass p-6 md:p-8 shadow-2xl rounded-2xl mt-6 md:mt-10 mb-10">
    <!-- Header -->
    <header class="flex items-center justify-between pb-6 mb-8 border-b-2 border-green-100">
      <div class="flex items-start space-x-3">
        <div class="bg-gradient-green p-2 rounded-lg hidden md:block pulse-badge">
          <img src="assets/img/leaf.jpeg" alt="" class="h-8 w-8" loading="lazy">
        </div>
        <div>
         <h1 style="
  font-size: 48px;
  font-weight: 800;
  background: linear-gradient(90deg, #d4e157, #7cb342, #558b2f);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
">
  Haritha Karma Sena
</h1>


          <p class="text-sm md:text-base text-gray-600 mt-1">Community-led garbage collection, recycling & eco services in Kerala</p>
        </div>
      </div>
      <nav class="relative">
        <button id="menuBtn" class="md:hidden inline-flex items-center px-4 py-2 border-2 border-green-500 rounded-lg text-green-700 font-semibold hover:bg-green-50" aria-expanded="false" aria-controls="menuNav">
          <span>Menu</span>
        </button>
        <div id="menuNav" class="hidden md:flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-5 items-start md:items-center absolute md:relative right-0 top-12 md:top-0 bg-white md:bg-transparent p-4 md:p-0 rounded-lg md:rounded-none shadow-lg md:shadow-none min-w-[200px] md:min-w-0 z-50">
          <a href="index.php" class="text-green-700 font-semibold hover:text-green-900 hover:underline">Home</a>
          <?php if (is_logged_in()): ?>
            <a href="profile.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Profile</a>
            <?php if ($_SESSION['user']['role'] === 'user'): ?>
              <a href="user_dashboard.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Dashboard</a>
            <?php endif; ?>
            <?php if ($_SESSION['user']['role'] === 'worker'): ?>
              <a href="worker_dashboard.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Worker</a>
            <?php endif; ?>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
              <a href="admin_dashboard.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Admin</a>
            <?php endif; ?>
            <a href="logout.php" class="text-red-600 font-medium hover:text-red-800 hover:underline">Logout</a>
          <?php else: ?>
            <a href="login.php" class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium btn-hover">Login</a>
            <a href="signup.php" class="bg-gradient-green text-white px-4 py-2 rounded-lg font-medium btn-hover">Sign Up</a>
          <?php endif; ?>
        </div>
      </nav>
    </header>

    <!-- Hero -->
    <section class="grid md:grid-cols-2 gap-8 md:gap-12 items-center mb-12 relative">
      <div class="leaf-decoration">
       <h2 class="text-2xl md:text-3xl font-bold mb-4 text-gray-800">
  Kerala's neighbourhood 
  <span class="text-transparent bg-clip-text inline-block" style="background-image: linear-gradient(90deg, #d4e157, #7cb342, #558b2f);">
    waste collection network
  </span>
</h2>


        <p class="text-gray-700 leading-relaxed text-base md:text-lg mb-6">
          Haritha Karma Sena (HKS) is a Kudumbashree-led workforce supported by Suchitwa Mission and Local Self-Government Institutions (LSGIs).
          Trained workers visit households and institutions for scheduled, door-to-door collection of <strong class="text-green-700">segregated</strong> waste, deliver it to
          Material Collection Facilities (MCFs)/Resource Recovery Facilities (RRFs), and channel it for reuse or recycling.
        </p>
        <div class="flex flex-wrap gap-3 mt-6">
          <?php if (!is_logged_in()): ?>
            <a href="signup.php" class="inline-block bg-gradient-green text-white px-6 py-3 rounded-lg font-semibold btn-hover shadow-lg">
              Create an account
            </a>
            <a href="login.php" class="inline-block bg-white text-green-700 px-6 py-3 rounded-lg font-semibold border-2 border-green-600 btn-hover">
              Login
            </a>
          <?php else: ?>
            <a href="<?php
              $dash = ($_SESSION['user']['role']==='worker' ? 'worker_dashboard.php' :
                      ($_SESSION['user']['role']==='admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
              echo $dash;
            ?>" class="inline-block bg-gradient-green text-white px-6 py-3 rounded-lg font-semibold btn-hover shadow-lg">
              Go to Dashboard
            </a>
          <?php endif; ?>
        </div>
        <?php if (is_logged_in()): ?>
          <div class="mt-5 p-4 bg-gradient-light rounded-lg border-l-4 border-green-500">
            <p class="text-sm text-gray-700">
              Welcome back, <span class="font-bold text-green-700"><?php echo e($_SESSION['user']['name']); ?></span>! 
              From your dashboard you can request pickups, track dues and share feedback.
            </p>
          </div>
        <?php endif; ?>
      </div>
      <div class="relative">
        <div class="absolute inset-0 bg-gradient-green rounded-2xl transform rotate-3 opacity-20"></div>
        <img src="assets/img/hero.jpg" class="rounded-2xl w-full shadow-2xl relative z-10 card-hover" alt="Community cleanup by Haritha Karma Sena" loading="eager">
      </div>
    </section>

    <div class="section-divider mb-10"></div>

    <!-- Quick stats / badges -->
    <section class="grid md:grid-cols-3 gap-6 mb-12">
      <div class="stat-card p-6 rounded-xl gradient-border bg-white card-hover">
        <div class="flex items-start space-x-3">
          <div class="bg-green-100 p-3 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500 font-medium">Coverage</p>
            <p class="text-lg font-bold text-gray-800 mt-1">Door-to-door across Kerala via LSGIs</p>
          </div>
        </div>
      </div>
      <div class="stat-card p-6 rounded-xl gradient-border bg-white card-hover">
        <div class="flex items-start space-x-3">
          <div class="bg-green-100 p-3 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500 font-medium">Operator</p>
            <p class="text-lg font-bold text-gray-800 mt-1">Kudumbashree units & HKS workers</p>
          </div>
        </div>
      </div>
      <div class="stat-card p-6 rounded-xl gradient-border bg-white card-hover">
        <div class="flex items-start space-x-3">
          <div class="bg-green-100 p-3 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500 font-medium">Support</p>
            <p class="text-lg font-bold text-gray-800 mt-1">Suchitwa Mission & Clean Kerala Co.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section class="mb-12 bg-gradient-light p-8 rounded-2xl">
      <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <span class="bg-gradient-green p-2 rounded-lg mr-3">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </span>
        How it works
      </h3>
      <ol class="space-y-4">
        <li class="flex items-start space-x-4 p-4 bg-white rounded-lg shadow-sm card-hover">
          <span class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">1</span>
          <div>
            <strong class="text-gray-800 block mb-1">Segregate at source:</strong>
            <span class="text-gray-700">Keep wet/biodegradable and dry/non-biodegradable waste separate. Rinse and flatten recyclables.</span>
          </div>
        </li>
        <li class="flex items-start space-x-4 p-4 bg-white rounded-lg shadow-sm card-hover">
          <span class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">2</span>
          <div>
            <strong class="text-gray-800 block mb-1">Scheduled pickup:</strong>
            <span class="text-gray-700">Your ward's HKS worker visits on a published calendar (e.g., paper/plastic monthly; medicine strips/laminated covers bi-monthly; glass as notified).</span>
          </div>
        </li>
        <li class="flex items-start space-x-4 p-4 bg-white rounded-lg shadow-sm card-hover">
          <span class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">3</span>
          <div>
            <strong class="text-gray-800 block mb-1">Hand-over & pay user fee:</strong>
            <span class="text-gray-700">User fee is fixed by your LSGI per Government Orders. Keep the user-fee card/receipt for records.</span>
          </div>
        </li>
        <li class="flex items-start space-x-4 p-4 bg-white rounded-lg shadow-sm card-hover">
          <span class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">4</span>
          <div>
            <strong class="text-gray-800 block mb-1">Processing:</strong>
            <span class="text-gray-700">Collected dry waste goes to MCF/RRF; plastics are baled/shredded and sent for recycling or to authorised projects (e.g., road-tarring via Clean Kerala Company).</span>
          </div>
        </li>
      </ol>
      <div class="mt-6 p-4 bg-white border-l-4 border-green-500 rounded-lg">
        <p class="text-sm text-gray-600">
          <strong class="text-green-700">Note:</strong> Exact schedules and accepted items vary by LSGI. Check your dashboard or ward notice for the latest calendar.
        </p>
      </div>
    </section>

    <div class="section-divider mb-10"></div>

    <!-- Services -->
    <section class="mb-12">
      <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <span class="bg-gradient-green p-2 rounded-lg mr-3">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
        </span>
        Services at a glance
      </h3>
      <div class="grid md:grid-cols-2 gap-6">
        <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover relative overflow-hidden">
          <div class="absolute top-0 right-0 w-20 h-20 bg-green-100 rounded-bl-full opacity-50"></div>
          <h4 class="font-bold text-xl mb-4 text-green-700">Door-to-Door Collection</h4>
          <ul class="space-y-3">
            <li class="flex items-start space-x-2">
              <span class="text-green-600 mt-1">✓</span>
              <span class="text-gray-700">Dry/non-biodegradable: paper, plastics, metal cans, e-waste items (as notified)</span>
            </li>
            <li class="flex items-start space-x-2">
              <span class="text-green-600 mt-1">✓</span>
              <span class="text-gray-700">Biodegradable: where enabled by LSGI/ward arrangements</span>
            </li>
            <li class="flex items-start space-x-2">
              <span class="text-green-600 mt-1">✓</span>
              <span class="text-gray-700">Special items on schedule: medicine strips, toiletry tubes/covers, broken glass</span>
            </li>
          </ul>
        </div>
        <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover relative overflow-hidden">
          <div class="absolute top-0 right-0 w-20 h-20 bg-green-100 rounded-bl-full opacity-50"></div>
          <h4 class="font-bold text-xl mb-4 text-green-700">Extended Eco Services</h4>
          <ul class="space-y-3">
            <li class="flex items-start space-x-2">
              <span class="text-green-600 mt-1">✓</span>
              <span class="text-gray-700">Support for home composting & bio-bins</span>
            </li>
            <li class="flex items-start space-x-2">
              <span class="text-green-600 mt-1">✓</span>
              <span class="text-gray-700">Awareness on source segregation & green protocol</span>
            </li>
            <li class="flex items-start space-x-2">
              <span class="text-green-600 mt-1">✓</span>
              <span class="text-gray-700">Event clean-ups adhering to green guidelines</span>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- User fee & compliance -->
    <section class="mb-12">
      <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <span class="bg-gradient-green p-2 rounded-lg mr-3">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </span>
        User Fee & Compliance
      </h3>
      <div class="p-6 border-2 border-green-200 rounded-2xl bg-gradient-light">
        <p class="text-gray-700 leading-relaxed mb-4">
          User fee is <strong class="text-green-700">set by your Local Self-Government (Panchayat/Municipality/Corporation)</strong> as per Government Orders and local bylaws.
          Minimum slabs may differ for households and commercial establishments; additional charges can apply for excess volume (e.g., per sack).
        </p>
        <div class="p-4 bg-white rounded-lg border-l-4 border-green-500">
          <p class="text-sm text-gray-600">
            <strong class="text-green-700">Keep your User Fee Card/receipt:</strong> It may be required for other services and inspections. For grievances, contact your LSGI Health Section or Suchitwa Mission helpdesk.
          </p>
        </div>
      </div>
    </section>

    <div class="section-divider mb-10"></div>

    <!-- Callouts -->
    <section class="grid md:grid-cols-3 gap-6 mb-12">
      <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover text-center">
        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
        </div>
        <h4 class="font-bold text-lg mb-2 text-gray-800">Residents</h4>
        <p class="text-sm text-gray-600 mb-4">View pickup calendar, pay dues, and request a re-visit.</p>
        <a href="<?php echo is_logged_in() ? 'user_dashboard.php' : 'signup.php'; ?>" class="inline-block px-5 py-2 bg-gradient-green text-white rounded-lg font-medium btn-hover">Get started</a>
      </div>
      <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover text-center">
        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
        </div>
        <h4 class="font-bold text-lg mb-2 text-gray-800">Institutions</h4>
        <p class="text-sm text-gray-600 mb-4">Schedule pickups, declare volumes, download compliance receipts.</p>
        <a href="<?php echo is_logged_in() ? 'user_dashboard.php' : 'signup.php'; ?>" class="inline-block px-5 py-2 bg-gradient-green text-white rounded-lg font-medium btn-hover">Register</a>
      </div>
      <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover text-center">
        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
        </div>
        <h4 class="font-bold text-lg mb-2 text-gray-800">Workers</h4>
        <p class="text-sm text-gray-600 mb-4">Update route completion, collect digital payments, log issues.</p>
        <a href="<?php echo is_logged_in() ? 'worker_dashboard.php' : 'login.php'; ?>" class="inline-block px-5 py-2 bg-gradient-green text-white rounded-lg font-medium btn-hover">Open portal</a>
      </div>
    </section>

    <!-- FAQs -->
    <section class="mb-12">
      <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <span class="bg-gradient-green p-2 rounded-lg mr-3">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </span>
        Frequently Asked Questions
      </h3>
      <div class="space-y-4">
        <details class="p-5 border-2 border-green-200 rounded-xl bg-white card-hover">
          <summary class="font-semibold cursor-pointer text-gray-800 text-lg">What should I give during pickup?</summary>
          <p class="mt-3 text-gray-700 pl-7 leading-relaxed">Clean, dry, and segregated items as per your ward schedule. Rinse bottles/pouches; keep glass in a sturdy bag/box.</p>
        </details>
        <details class="p-5 border-2 border-green-200 rounded-xl bg-white card-hover">
          <summary class="font-semibold cursor-pointer text-gray-800 text-lg">Who decides the user fee?</summary>
          <p class="mt-3 text-gray-700 pl-7 leading-relaxed">Your LSGI fixes user fees via bylaw/GO. Keep fee receipts or the user-fee card for records and inspections.</p>
        </details>
        <details class="p-5 border-2 border-green-200 rounded-xl bg-white card-hover">
          <summary class="font-semibold cursor-pointer text-gray-800 text-lg">Where does the waste go?</summary>
          <p class="mt-3 text-gray-700 pl-7 leading-relaxed">Dry waste goes to MCF/RRF for sorting, baling and recycling; certain plastics may be channelled to authorised projects via Clean Kerala Company.</p>
        </details>
        <details class="p-5 border-2 border-green-200 rounded-xl bg-white card-hover">
          <summary class="font-semibold cursor-pointer text-gray-800 text-lg">How do I register for the service?</summary>
          <p class="mt-3 text-gray-700 pl-7 leading-relaxed">Create an account on our platform, verify your address, and you'll be assigned to your local HKS worker. You can then view your pickup schedule and manage payments online.</p>
        </details>
        <details class="p-5 border-2 border-green-200 rounded-xl bg-white card-hover">
          <summary class="font-semibold cursor-pointer text-gray-800 text-lg">What if I miss my scheduled pickup?</summary>
          <p class="mt-3 text-gray-700 pl-7 leading-relaxed">You can request a re-visit through your dashboard, or store the segregated waste safely until the next scheduled pickup. Contact your local HKS worker for urgent collections.</p>
        </details>
      </div>
    </section>

    <div class="section-divider mb-10"></div>

    <!-- Call to Action -->
    <section class="mb-12 bg-gradient-green rounded-2xl p-8 md:p-12 text-white text-center shadow-2xl relative overflow-hidden">
      <div class="absolute top-0 left-0 w-full h-full opacity-10">
        <div class="absolute top-10 left-10 text-8xl">🍃</div>
        <div class="absolute bottom-10 right-10 text-8xl">♻️</div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-9xl">🌱</div>
      </div>
      <div class="relative z-10">
        <h3 class="text-3xl md:text-4xl font-bold mb-4">Join the Green Revolution</h3>
        <p class="text-lg md:text-xl mb-8 opacity-90">Be part of Kerala's sustainable waste management initiative</p>
        <?php if (!is_logged_in()): ?>
          <a href="signup.php" class="inline-block bg-white text-green-700 px-8 py-4 rounded-lg font-bold text-lg btn-hover shadow-xl">
            Get Started Today
          </a>
        <?php else: ?>
          <a href="<?php
            $dash = ($_SESSION['user']['role']==='worker' ? 'worker_dashboard.php' :
                    ($_SESSION['user']['role']==='admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
            echo $dash;
          ?>" class="inline-block bg-white text-green-700 px-8 py-4 rounded-lg font-bold text-lg btn-hover shadow-xl">
            Visit Your Dashboard
          </a>
        <?php endif; ?>
      </div>
    </section>

    <!-- Footer -->
    <footer class="text-center pt-8 border-t-2 border-green-100">
      <div class="mb-6">
        <div class="flex items-center justify-center space-x-2 mb-3">
          <div class="bg-gradient-green p-2 rounded-lg">
            <img src="assets/img/leaf.jpeg" alt="" class="h-6 w-6" loading="lazy">
          </div>
          <span  <h1 style="
  font-size: 19px;
  font-weight: 800;
  background: linear-gradient(90deg, #d4e157, #7cb342, #558b2f);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
">
  Haritha Karma Sena
</h1></span>
        </div>
        <p class="text-sm text-gray-600 max-w-2xl mx-auto leading-relaxed">
          Building a cleaner, greener Kerala through community-led waste management and sustainable practices.
        </p>
      </div>
      
      <div class="flex justify-center space-x-6 mb-6 text-sm">
        <a href="contact.php" class="text-green-700 hover:text-green-900 font-medium hover:underline">Contact Us</a>
        <span class="text-gray-300">|</span>
        <a href="#" class="text-green-700 hover:text-green-900 font-medium hover:underline">Privacy Policy</a>
        <span class="text-gray-300">|</span>
        <a href="#" class="text-green-700 hover:text-green-900 font-medium hover:underline">Terms of Service</a>
      </div>
      
      <div class="py-6 bg-gradient-light rounded-lg mb-6">
        <p class="text-sm text-gray-700 font-medium">Supported by</p>
        <div class="flex justify-center items-center space-x-4 mt-2 text-sm text-gray-600">
          <span class="font-semibold">Kudumbashree</span>
          <span>•</span>
          <span class="font-semibold">Suchitwa Mission</span>
          <span>•</span>
          <span class="font-semibold">Clean Kerala Company</span>
        </div>
      </div>
      
      <p class="text-sm text-gray-500 pb-6">&copy; <?php echo date('Y'); ?> Haritha Karma Sena. All rights reserved.</p>
    </footer>
  </div>
</body>
</html>
<?php
require 'config.php';

$toast_type = null; // info | success | warning | error
$toast_msg  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $token = $_POST['csrf'] ?? '';

  if (!verify_csrf($token)) {
    $toast_type = 'error';
    $toast_msg  = 'Invalid CSRF token. Please reload the page and try again.';
  } else {
    // Basic server-side validation
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $toast_type = 'warning';
      $toast_msg  = 'Please enter a valid email address.';
    } elseif (!$pass) {
      $toast_type = 'warning';
      $toast_msg  = 'Please enter your password.';
    } else {
      // Auth
      $stmt = $mysqli->prepare('SELECT id, name, email, password, role, dues FROM users WHERE email = ? LIMIT 1');
      $stmt->bind_param('s', $email);
      $stmt->execute();
      $res = $stmt->get_result();

      if ($res->num_rows === 1) {
        $u = $res->fetch_assoc();
        if (password_verify($pass, $u['password'])) {
          unset($u['password']);
          $_SESSION['user'] = $u;
          // Redirect by role
          if ($u['role'] === 'admin')      { header('Location: admin_dashboard.php'); exit; }
          elseif ($u['role'] === 'worker') { header('Location: worker_dashboard.php'); exit; }
          else                              { header('Location: user_dashboard.php'); exit; }
        } else {
          $toast_type = 'error';
          $toast_msg  = 'Invalid credentials. Check your email/password.';
        }
      } else {
        $toast_type = 'error';
        $toast_msg  = 'Invalid credentials. Check your email/password.';
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login | Haritha Karma Sena</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    .bg-gradient-green { background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%); }
    .bg-pattern {
      background-color: #f0fdf4;
      background-image:
        radial-gradient(circle at 20% 50%, rgba(16,185,129,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(6,95,70,0.1) 0%, transparent 50%);
    }
    .glass { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3); }
    .input-field:focus { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16,185,129,0.2); }
    .btn-hover:hover { transform: scale(1.02); box-shadow: 0 10px 25px rgba(6,95,70,0.4); }
    @keyframes slideIn { from {opacity:0; transform: translateY(-8px);} to {opacity:1; transform: translateY(0);} }
    .toast { animation: slideIn .25s ease-out; }
  </style>
  <script>
    // Toast system
    function showToast(message, type='info') {
      const holder = document.getElementById('toast-holder');
      const toast  = document.createElement('div');

      const palette = {
        info:    {wrap:'bg-blue-50 border-blue-200 text-blue-800',  icon:'ℹ️'},
        success: {wrap:'bg-green-50 border-green-200 text-green-800',icon:'✅'},
        warning: {wrap:'bg-yellow-50 border-yellow-200 text-yellow-800',icon:'⚠️'},
        error:   {wrap:'bg-red-50 border-red-200 text-red-800',     icon:'⛔'},
      };
      const p = palette[type] || palette.info;

      toast.className = `toast border ${p.wrap} rounded-lg px-4 py-3 shadow mb-3 flex items-start`;
      toast.innerHTML = `
        <div class="mr-2 text-lg leading-none">${p.icon}</div>
        <div class="text-sm flex-1">${message}</div>
        <button class="ml-3 text-xs underline opacity-70 hover:opacity-100">Close</button>
      `;
      toast.querySelector('button').onclick = () => toast.remove();

      holder.appendChild(toast);
      setTimeout(() => { toast.remove(); }, 6000);
    }

    // Client-side form validation
    function validateLoginForm(e) {
      const email = document.getElementById('email');
      const pass  = document.getElementById('password');

      const emailVal = (email.value || '').trim();
      const passVal  = pass.value || '';

      const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
      if (!emailVal || !emailOk) {
        e.preventDefault();
        showToast('Please enter a valid email address.', 'warning');
        email.focus();
        return false;
      }
      if (!passVal) {
        e.preventDefault();
        showToast('Please enter your password.', 'warning');
        pass.focus();
        return false;
      }
      return true;
    }

    function togglePassword() {
      const input = document.getElementById('password');
      const icon  = document.getElementById('toggle-icon');
      if (input.type === 'password') {
        input.type = 'text'; icon.textContent = '👁️';
      } else {
        input.type = 'password'; icon.textContent = '👁️‍🗨️';
      }
    }

    function forgotPassword(e) {
      e.preventDefault();
      // Adjust the contact details as needed:
      showToast('Forgot password? Please contact the Administrator to change your password.', 'info');
    }
  </script>
</head>
<body class="bg-pattern">
  <!-- Toast holder (top-right) -->
  <div id="toast-holder" class="fixed top-4 right-4 max-w-sm z-50"></div>

  <div class="min-h-screen flex items-center justify-center px-4 py-8 relative overflow-hidden">
    <!-- Decorative circles -->
    <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-green-100"></div>
    <div class="absolute -bottom-16 -left-16 w-48 h-48 rounded-full bg-emerald-50"></div>

    <div class="max-w-md w-full relative z-10">
      <!-- Brand -->
      <div class="text-center mb-8">
        <div class="inline-block bg-gradient-green p-4 rounded-2xl mb-4 shadow-lg">
          <img src="assets/img/leaf.jpeg" alt="Haritha Karma Sena" class="h-12 w-12">
        </div>
        <h1 style="
          font-size: 48px;
          font-weight: 800;
          background: linear-gradient(90deg, #d4e157, #7cb342, #558b2f);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;">
          Haritha Karma Sena
        </h1>
        <p class="text-gray-600 text-sm">Welcome back! Please login to continue</p>
      </div>

      <!-- Card -->
      <div class="glass rounded-2xl shadow-2xl p-8 relative border border-green-100">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-green rounded-t-2xl"></div>

        <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Login to Your Account</h2>

        <form method="post" class="space-y-5" onsubmit="return validateLoginForm(event)">
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                </svg>
              </div>
              <input
                id="email"
                name="email"
                type="email"
                placeholder="you@example.com"
                required
                class="input-field w-full pl-12 pr-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                value="<?php echo e($_POST['email'] ?? ''); ?>"
                autocomplete="email">
            </div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <input
                id="password"
                name="password"
                type="password"
                placeholder="••••••••"
                required
                class="input-field w-full pl-12 pr-12 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                autocomplete="current-password">
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                <span id="toggle-icon" class="cursor-pointer text-gray-500 hover:text-green-600 text-xl" onclick="togglePassword()">👁️‍🗨️</span>
              </div>
            </div>
          </div>

          <!-- Remember + Forgot -->
          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center cursor-pointer">
              <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
              <span class="ml-2 text-gray-600">Remember me</span>
            </label>
            <a href="#" onclick="forgotPassword(event)" class="text-green-600 hover:text-green-800 font-medium hover:underline">Forgot password?</a>
          </div>

          <!-- CSRF -->
          <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

          <!-- Submit -->
          <button type="submit" class="btn-hover w-full bg-gradient-green text-white py-3 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl">
            Login to Dashboard
          </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white text-gray-500">Don't have an account?</span>
          </div>
        </div>

        <!-- Signup -->
        <div class="text-center">
          <a href="signup.php" class="inline-block w-full py-3 border-2 border-green-600 text-green-600 rounded-lg font-semibold hover:bg-green-50 transition-all">
            Create New Account
          </a>
        </div>

        <!-- Home -->
        <div class="mt-6 text-center">
          <a href="index.php" class="text-sm text-gray-600 hover:text-green-600 inline-flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Back to Home</span>
          </a>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 text-center text-xs text-gray-500">
        <p>&copy; <?php echo date('Y'); ?> Haritha Karma Sena. All rights reserved.</p>
        <p class="mt-1">Powered by Kudumbashree &amp; Suchitwa Mission</p>
      </div>
    </div>
  </div>

  <script>
    // Show PHP-side toast if set
    <?php if ($toast_type && $toast_msg): ?>
      window.addEventListener('DOMContentLoaded', function () {
        showToast(<?php echo json_encode($toast_msg); ?>, <?php echo json_encode($toast_type); ?>);
      });
    <?php endif; ?>
  </script>
</body>
</html>

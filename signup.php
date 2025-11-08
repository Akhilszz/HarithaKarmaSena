<?php
require 'config.php'; // provides $mysqli, csrf_token(), verify_csrf(), e()

// ---------- SERVER-SIDE: Handle POST ----------
$toast = null; // ['type'=>'success|warning|error','title'=>'','message'=>'','actions'=>[['label'=>'','href'=>'#']]]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = trim($_POST['name']  ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password']   ?? '';
  $phone = trim($_POST['phone'] ?? '');
  $token = $_POST['csrf']       ?? '';

  // CSRF
  if (!verify_csrf($token)) {
    $toast = [
      'type' => 'error',
      'title'=> 'Security check failed',
      'message' => 'Your session token is invalid or expired. Please refresh the page and try again.',
      'actions' => [
        ['label'=>'Refresh', 'href'=>'signup.php'],
      ],
    ];
  } else {
    // Basic validations
    $errors = [];

    if ($name === '' || mb_strlen($name) < 3) {
      $errors[] = 'Enter your full name (at least 3 characters).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'Enter a valid email address.';
    }

    // Password: ≥8 chars, at least one letter & one digit
    $hasLen   = strlen($pass) >= 8;
    $hasAlpha = preg_match('/[A-Za-z]/', $pass);
    $hasNum   = preg_match('/\d/', $pass);
    if (!$hasLen || !$hasAlpha || !$hasNum) {
      $errors[] = 'Password must be at least 8 characters and include letters and numbers.';
    }

    // Phone optional, but if present, make it digits (+ allowed)
    if ($phone !== '' && !preg_match('/^\+?\d{7,15}$/', $phone)) {
      $errors[] = 'Enter a valid phone number (7–15 digits, optional +).';
    }

    if (!empty($errors)) {
      $toast = [
        'type' => 'warning',
        'title'=> 'Fix the highlighted issues',
        'message' => implode(' ', $errors),
        'actions' => [
          ['label'=>'Need help?', 'href'=>'#password-help'],
        ],
      ];
    } else {
      // Duplicate email check
      $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
      $stmt->bind_param('s', $email);
      $stmt->execute();
      $stmt->store_result();

      if ($stmt->num_rows > 0) {
        // Duplicate — show actionable toast
        $toast = [
          'type' => 'warning',
          'title'=> 'Email already registered',
          'message' => 'Try logging in with this email, or reset your password if you forgot it.',
          'actions' => [
            ['label'=>'Login', 'href'=>'login.php'],
            ['label'=>'Forgot password', 'href'=>'forgot.php'],
          ],
        ];
      } else {
        // Insert user
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare('INSERT INTO users (name,email,password,phone,role) VALUES (?,?,?,?, "user")');
        $stmt->bind_param('ssss', $name, $email, $hash, $phone);

        if ($stmt->execute()) {
          // Success — show toast then redirect
          // You can also use a flash+redirect pattern if you prefer.
          $toast = [
            'type' => 'success',
            'title'=> 'Account created',
            'message' => 'Your account is ready. You can login now.',
            'actions' => [
              ['label'=>'Go to Login', 'href'=>'login.php'],
            ],
          ];
          // Optional immediate redirect:
          header('Refresh: 1; url=login.php'); // brief moment to show toast
        } else {
          $toast = [
            'type' => 'error',
            'title'=> 'Could not create account',
            'message' => 'A server error occurred. Please try again later.',
            'actions' => [
              ['label'=>'Try again', 'href'=>'signup.php'],
            ],
          ];
        }
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign Up | Haritha Karma Sena</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    .bg-gradient-green { background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%); }
    .bg-pattern {
      background-color: #f0fdf4;
      background-image:
        radial-gradient(circle at 20% 50%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(6, 95, 70, 0.1) 0%, transparent 50%);
    }
    .glass { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border:1px solid rgba(255,255,255,0.3); }
    * { transition: all .2s ease; }
    .input-field:focus { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16,185,129,.2); }
    .btn-hover:hover { transform: scale(1.02); box-shadow: 0 10px 25px rgba(6,95,70,.4); }
    @keyframes pulse-green { 0%,100%{ box-shadow:0 0 0 0 rgba(16,185,129,.7);} 50%{ box-shadow:0 0 0 15px rgba(16,185,129,0);} }
    .pulse-badge{ animation:pulse-green 2s infinite; }
    @keyframes float { 0%,100%{ transform:translateY(0) rotate(0);} 50%{ transform:translateY(-20px) rotate(5deg);} }
    .float-leaf{ animation:float 6s ease-in-out infinite; }
    .gradient-border{ position:relative; border:2px solid transparent; background:linear-gradient(#fff,#fff) padding-box, linear-gradient(135deg,#10b981,#047857) border-box; }
    .min-h-screen-custom{ min-height:100vh; display:flex; align-items:center; justify-content:center; }
    .deco-circle{ position:absolute; border-radius:50%; background:linear-gradient(135deg, rgba(16,185,129,.1), rgba(6,95,70,.05)); z-index:0; }
    .deco-circle-1{ width:300px;height:300px; top:-100px; right:-100px; }
    .deco-circle-2{ width:200px;height:200px; bottom:-50px; left:-50px; }
    .password-toggle{ cursor: pointer; user-select:none; }

    /* TOAST */
    .toast-container{ position:fixed; top:1rem; right:1rem; z-index:9999; display:flex; flex-direction:column; gap:.75rem; }
    .toast{ min-width: 280px; max-width: 380px; border-left-width: 6px; border-radius:.75rem; padding: .875rem 1rem; box-shadow: 0 10px 25px rgba(0,0,0,.15); }
    .toast-success{ background:#ecfdf5; border-color:#10b981; color:#065f46; }
    .toast-warning{ background:#fffbeb; border-color:#f59e0b; color:#8a4b00; }
    .toast-error{   background:#fef2f2; border-color:#ef4444; color:#7f1d1d; }
    .toast-title{ font-weight:700; margin-bottom:.25rem; }
    .toast-actions a{ display:inline-flex; align-items:center; gap:.25rem; padding:.25rem .5rem; border-radius:.5rem; font-weight:600; }
    .toast-actions a:hover{ background: rgba(0,0,0,.06); }
  </style>
  <script>
    // CLIENT-SIDE VALIDATION + TOAST API
    function showToast(type, title, message, actions){
      const cont = document.querySelector('.toast-container') || (function(){
        const d=document.createElement('div'); d.className='toast-container'; document.body.appendChild(d); return d;
      })();
      const t = document.createElement('div');
      t.className = 'toast toast-' + type;
      t.innerHTML = `
        <div class="toast-title">${title}</div>
        <div class="toast-message text-sm leading-relaxed">${message}</div>
        <div class="toast-actions mt-2 flex gap-2">${(actions||[]).map(a=>`<a href="${a.href}" class="text-sm">${a.label} →</a>`).join('')}</div>
      `;
      cont.appendChild(t);
      setTimeout(()=>{ t.style.opacity='0'; t.style.transform='translateY(-6px)'; setTimeout(()=>t.remove(), 300); }, 5000);
    }

    function clientValidate(form){
      const name  = form.name.value.trim();
      const email = form.email.value.trim();
      const pass  = form.password.value;
      const phone = form.phone.value.trim();

      const issues = [];
      if (name.length < 3) issues.push('Full name must be at least 3 characters.');
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) issues.push('Enter a valid email address.');
      if (!(pass.length >= 8 && /[A-Za-z]/.test(pass) && /\d/.test(pass))) {
        issues.push('Password must be at least 8 characters and include letters and numbers.');
      }
      if (phone && !/^\+?\d{7,15}$/.test(phone)) issues.push('Enter a valid phone number (7–15 digits, optional +).');

      if (issues.length) {
        showToast('warning', 'Please fix these issues', issues.join(' '), [{label:'See tips', href:'#password-help'}]);
        return false;
      }
      return true;
    }

    function togglePassword(){
      const input = document.getElementById('password');
      const icon = document.getElementById('toggle-icon');
      const isPw = input.type === 'password';
      input.type = isPw ? 'text' : 'password';
      icon.textContent = isPw ? '👁️' : '👁️‍🗨️';
    }
  </script>
</head>
<body class="bg-pattern">
  <!-- TOAST AREA (server-driven) -->
  <div class="toast-container" aria-live="polite" aria-atomic="true"></div>
  <?php if ($toast): ?>
  <script>
    showToast(
      <?php echo json_encode($toast['type'] ?? 'warning'); ?>,
      <?php echo json_encode($toast['title'] ?? 'Notice'); ?>,
      <?php echo json_encode($toast['message'] ?? ''); ?>,
      <?php echo json_encode($toast['actions'] ?? []); ?>
    );
  </script>
  <?php endif; ?>

  <div class="min-h-screen-custom px-4 py-8 relative overflow-hidden">
    <!-- Decorative circles -->
    <div class="deco-circle deco-circle-1"></div>
    <div class="deco-circle deco-circle-2"></div>

    <div class="max-w-md w-full mx-auto relative z-10">
      <!-- Logo and Brand -->
      <div class="text-center mb-8">
        <div class="inline-block bg-gradient-green p-4 rounded-2xl pulse-badge mb-4 shadow-lg">
          <img src="assets/img/leaf.jpeg" alt="Haritha Karma Sena" class="h-12 w-12 float-leaf" loading="eager">
        </div>
        <h1 style="font-size:48px;font-weight:800;background:linear-gradient(90deg,#d4e157,#7cb342,#558b2f);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
          Haritha Karma Sena
        </h1>
        <p class="text-gray-600 text-sm">Join our community! Create your account</p>
      </div>

      <!-- Sign Up Card -->
      <div class="glass rounded-2xl shadow-2xl p-8 gradient-border relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-green"></div>
        <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Create Your Account</h2>

        <form method="post" class="space-y-5" onsubmit="return clientValidate(this)">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <input id="name" name="name" type="text" placeholder="Enter your full name" required
                     class="input-field w-full pl-12 pr-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                     value="<?php echo e($_POST['name'] ?? ''); ?>" autocomplete="name">
            </div>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                </svg>
              </div>
              <input id="email" name="email" type="email" placeholder="Enter your email" required
                     class="input-field w-full pl-12 pr-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                     value="<?php echo e($_POST['email'] ?? ''); ?>" autocomplete="email">
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
              <input id="password" name="password" type="password" placeholder="Create a password" required
                     class="input-field w-full pl-12 pr-12 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                     autocomplete="new-password" aria-describedby="password-help">
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                <span id="toggle-icon" class="password-toggle text-gray-500 hover:text-green-600 text-xl" onclick="togglePassword()">👁️‍🗨️</span>
              </div>
            </div>
            <p id="password-help" class="text-xs text-gray-500 mt-1">
              Use at least 8 characters with letters & numbers. Example: <code class="bg-gray-100 px-1 rounded">Green12Leaf</code>
            </p>
          </div>

          <!-- Phone (optional) -->
          <div>
            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number (Optional)</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
              <input id="phone" name="phone" type="tel" placeholder="Enter your phone number"
                     class="input-field w-full pl-12 pr-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                     value="<?php echo e($_POST['phone'] ?? ''); ?>" autocomplete="tel">
            </div>
          </div>

          <!-- Terms -->
          <div class="flex items-start text-sm">
            <input type="checkbox" id="terms" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 mt-1" required>
            <label for="terms" class="ml-2 text-gray-600">
              I agree to the <a href="#" class="text-green-600 hover:text-green-800 font-medium hover:underline">Terms of Service</a> and <a href="#" class="text-green-600 hover:text-green-800 font-medium hover:underline">Privacy Policy</a>
            </label>
          </div>

          <!-- CSRF -->
          <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

          <!-- Submit -->
          <button type="submit" class="btn-hover w-full bg-gradient-green text-white py-3 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl">
            Create Account
          </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-300"></div></div>
          <div class="relative flex justify-center text-sm"><span class="px-4 bg-white text-gray-500">Already have an account?</span></div>
        </div>

        <!-- Login Link -->
        <div class="text-center">
          <a href="login.php" class="inline-block w-full py-3 border-2 border-green-600 text-green-600 rounded-lg font-semibold hover:bg-green-50 transition-all">
            Login to Your Account
          </a>
        </div>

        <!-- Back to Home -->
        <div class="mt-6 text-center">
          <a href="index.php" class="text-sm text-gray-600 hover:text-green-600 inline-flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Back to Home</span>
          </a>
        </div>
      </div>

      <!-- Security Notice -->
      <div class="mt-6 text-center">
        <div class="inline-flex items-center space-x-2 text-sm text-gray-600 bg-white px-4 py-2 rounded-full shadow-sm">
          <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span>Your connection is secure and encrypted</span>
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-8 text-center text-xs text-gray-500">
        <p>&copy; <?php echo date('Y'); ?> Haritha Karma Sena. All rights reserved.</p>
        <p class="mt-1">Powered by Kudumbashree & Suchitwa Mission</p>
      </div>
    </div>
  </div>
</body>
</html>

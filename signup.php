<?php
require 'config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $phone = trim($_POST['phone'] ?? '');
  $token = $_POST['csrf'] ?? '';
  if(!verify_csrf($token)){ $err = 'Invalid CSRF token'; }
  elseif(!$name || !$email || !$pass){ $err = 'Please fill required fields.'; }
  else {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (name,email,password,phone) VALUES (?,?,?,?)');
    $stmt->bind_param('ssss',$name,$email,$hash,$phone);
    if($stmt->execute()){ header('Location: login.php'); exit; } else { $err = 'Error: '.$stmt->error; }
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
    /* Gradient backgrounds */
    .bg-gradient-green {
      background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%);
    }
    
    .bg-pattern {
      background-color: #f0fdf4;
      background-image: 
        radial-gradient(circle at 20% 50%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(6, 95, 70, 0.1) 0%, transparent 50%);
    }

    /* Glassmorphism effect */
    .glass {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Smooth transitions */
    * {
      transition: all 0.3s ease;
    }

    /* Input focus effects */
    .input-field:focus {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
    }

    /* Button hover effect */
    .btn-hover:hover {
      transform: scale(1.02);
      box-shadow: 0 10px 25px rgba(6, 95, 70, 0.4);
    }

    /* Pulse animation */
    @keyframes pulse-green {
      0%, 100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
      }
      50% {
        box-shadow: 0 0 0 15px rgba(16, 185, 129, 0);
      }
    }

    .pulse-badge {
      animation: pulse-green 2s infinite;
    }

    /* Floating animation for decorative elements */
    @keyframes float {
      0%, 100% {
        transform: translateY(0) rotate(0deg);
      }
      50% {
        transform: translateY(-20px) rotate(5deg);
      }
    }

    .float-leaf {
      animation: float 6s ease-in-out infinite;
    }

    /* Error message slide in */
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .error-message {
      animation: slideIn 0.3s ease-out;
    }

    /* Gradient border */
    .gradient-border {
      position: relative;
      border: 2px solid transparent;
      background: linear-gradient(white, white) padding-box,
                  linear-gradient(135deg, #10b981, #047857) border-box;
    }

    /* Full height centering */
    .min-h-screen-custom {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Decorative circles */
    .deco-circle {
      position: absolute;
      border-radius: 50%;
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(6, 95, 70, 0.05));
      z-index: 0;
    }

    .deco-circle-1 {
      width: 300px;
      height: 300px;
      top: -100px;
      right: -100px;
    }

    .deco-circle-2 {
      width: 200px;
      height: 200px;
      bottom: -50px;
      left: -50px;
    }

    /* Password show/hide icon */
    .password-toggle {
      cursor: pointer;
      user-select: none;
    }
  </style>
  <script>
    function togglePassword() {
      const input = document.getElementById('password');
      const icon = document.getElementById('toggle-icon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '👁️';
      } else {
        input.type = 'password';
        icon.textContent = '👁️‍🗨️';
      }
    }
  </script>
</head>
<body class="bg-pattern">
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
        <h1 style="
  font-size: 48px;
  font-weight: 800;
  background: linear-gradient(90deg, #d4e157, #7cb342, #558b2f);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
">
  Haritha Karma Sena
</h1>
        <p class="text-gray-600 text-sm">Join our community! Create your account</p>
      </div>

      <!-- Sign Up Card -->
      <div class="glass rounded-2xl shadow-2xl p-8 gradient-border relative overflow-hidden">
        <!-- Decorative top accent -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-green"></div>
        
        <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Create Your Account</h2>
        
        <?php if(!empty($err)): ?>
        <div class="error-message bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-start space-x-3">
          <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <span><?php echo e($err); ?></span>
        </div>
        <?php endif; ?>
        
        <form method="post" class="space-y-5">
          <!-- Name Field -->
          <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <input 
                id="name"
                name="name" 
                type="text" 
                placeholder="Enter Your Full Name" 
                required 
                class="input-field w-full pl-12 pr-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                value="<?php echo e($_POST['name'] ?? ''); ?>"
                autocomplete="name"
              >
            </div>
          </div>

          <!-- Email Field -->
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
                placeholder="Enter Your Email" 
                required 
                class="input-field w-full pl-12 pr-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                value="<?php echo e($_POST['email'] ?? ''); ?>"
                autocomplete="email"
              >
            </div>
          </div>

          <!-- Password Field -->
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
                placeholder="Create a password" 
                required 
                class="input-field w-full pl-12 pr-12 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                autocomplete="new-password"
              >
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                <span id="toggle-icon" class="password-toggle text-gray-500 hover:text-green-600 text-xl" onclick="togglePassword()">👁️‍🗨️</span>
              </div>
            </div>
          </div>

          <!-- Phone Field -->
          <div>
            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number (Optional)</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
              <input 
                id="phone"
                name="phone" 
                type="tel" 
                placeholder="Enter your phone number" 
                class="input-field w-full pl-12 pr-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
                value="<?php echo e($_POST['phone'] ?? ''); ?>"
                autocomplete="tel"
              >
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div class="flex items-start text-sm">
            <input type="checkbox" id="terms" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 mt-1" required>
            <label for="terms" class="ml-2 text-gray-600">
              I agree to the <a href="#" class="text-green-600 hover:text-green-800 font-medium hover:underline">Terms of Service</a> and <a href="#" class="text-green-600 hover:text-green-800 font-medium hover:underline">Privacy Policy</a>
            </label>
          </div>

          <!-- CSRF Token -->
          <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

          <!-- Submit Button -->
          <button 
            type="submit" 
            class="btn-hover w-full bg-gradient-green text-white py-3 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl"
          >
            Create Account
          </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white text-gray-500">Already have an account?</span>
          </div>
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
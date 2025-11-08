<?php
require 'config.php';
require_role('admin');

$uid = $_SESSION['user']['id'] ?? 0;
$page_title = "Admin Profile";

// Load current admin
$stmt = $mysqli->prepare('SELECT id, name, email, phone, address, role, created_at, updated_at FROM users WHERE id=? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
  $_SESSION['error'] = 'Admin not found.';
  header('Location: admin_dashboard.php'); exit;
}

// Handle POST (update)
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf'] ?? '';
  if (!verify_csrf($token)) {
    $errors[] = 'Invalid session. Please refresh and try again.';
  } else {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    if ($name === '' || mb_strlen($name) < 2) {
      $errors[] = 'Name is required (min 2 characters).';
    }
    if ($phone !== '') {
      // allow digits, space, +, -, ()
      if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        $errors[] = 'Enter a valid phone number (7–20 characters).';
      }
    }
    if (mb_strlen($address) > 300) {
      $errors[] = 'Address is too long (max 300 characters).';
    }

    if (empty($errors)) {
      $stmt = $mysqli->prepare('UPDATE users SET name=?, phone=?, address=? WHERE id=?');
      $stmt->bind_param('sssi', $name, $phone, $address, $uid);
      if ($stmt->execute()) {
        // Update header/session name immediately
        $_SESSION['user']['name'] = $name;
        $_SESSION['success'] = 'Profile updated successfully.';
        header('Location: admin_profile.php'); exit;
      } else {
        $errors[] = 'Database error: ' . $stmt->error;
      }
    }
  }
}

require 'admin_header.php';
?>

<!-- Page: Admin Profile -->
<div class="max-w-5xl mx-auto px-4 py-8">
  <!-- Header Card -->
  <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8 mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
      <div class="flex items-center space-x-4">
        <div class="bg-white text-emerald-700 w-14 h-14 rounded-xl flex items-center justify-center text-2xl font-bold">
          <?php echo e(mb_strtoupper(mb_substr($user['name'], 0, 1))); ?>
        </div>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold"><?php echo e($user['name']); ?></h1>
          <p class="text-emerald-100">Administrator • Member since <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
        </div>
      </div>
      <div class="mt-4 md:mt-0">
        <span class="bg-emerald-900/40 px-4 py-2 rounded-lg font-semibold inline-flex items-center">
          <i class="fas fa-user-shield mr-2"></i> Admin Profile
        </span>
      </div>
    </div>
  </div>

  <!-- Alerts -->
  <?php if (!empty($errors)): ?>
    <div class="mx-1 mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-start justify-between" role="alert">
      <div class="flex items-start space-x-3">
        <i class="fas fa-exclamation-triangle mt-0.5 text-red-600"></i>
        <div>
          <p class="font-semibold">Please fix the following:</p>
          <ul class="list-disc list-inside text-sm mt-1">
            <?php foreach ($errors as $msg): ?>
              <li><?php echo e($msg); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <button type="button" class="text-red-700 hover:text-red-900" onclick="this.closest('[role=alert]').remove()">
        <i class="fas fa-times"></i>
      </button>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="mx-1 mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
      <div class="flex items-center">
        <i class="fas fa-check-circle mr-3 text-green-600"></i>
        <span><?php echo e($_SESSION['success']); unset($_SESSION['success']); ?></span>
      </div>
      <button type="button" class="text-green-700 hover:text-green-900" onclick="this.closest('[role=alert]').remove()">
        <i class="fas fa-times"></i>
      </button>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Edit Form -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-user-cog text-emerald-600 mr-3"></i> Profile Settings
        </h2>

        <form method="post" class="space-y-6" novalidate>
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
            <input
              type="text"
              name="name"
              required
              minlength="2"
              value="<?php echo e($user['name']); ?>"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600"
              placeholder="Enter full name"
            >
            <p class="text-xs text-gray-500 mt-1">This name appears in the admin header and activity logs.</p>
          </div>

          <!-- Email (read-only) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <input
              type="email"
              value="<?php echo e($user['email']); ?>"
              disabled
              class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500"
            >
            <p class="text-xs text-gray-500 mt-1">
              To change the email, contact a super-admin/developer to avoid login issues.
            </p>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
            <input
              type="tel"
              name="phone"
              value="<?php echo e($user['phone']); ?>"
              pattern="[0-9+\-\s()]{7,20}"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600"
              placeholder="+91 98765 43210"
            >
            <p class="text-xs text-gray-500 mt-1">Allowed: digits, spaces, +, -, ( )</p>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
            <textarea
              name="address"
              rows="4"
              maxlength="300"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 resize-none"
              placeholder="Office address / correspondence address (max 300 chars)"
            ><?php echo e($user['address']); ?></textarea>
          </div>

          <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

          <div class="flex justify-end pt-4 border-t border-gray-200">
            <button
              type="submit"
              class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center"
            >
              <i class="fas fa-save mr-2"></i> Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Sidebar: Account Info -->
    <div class="space-y-6">
      <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-id-card text-blue-600 mr-2"></i> Account Summary
        </h3>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">User ID:</span>
            <span class="font-medium">#<?php echo (int)$user['id']; ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Role:</span>
            <span class="font-medium text-emerald-700"><?php echo e(ucfirst($user['role'])); ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Member Since:</span>
            <span class="font-medium"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Last Updated:</span>
            <span class="font-medium"><?php echo date('M j, Y', strtotime($user['updated_at'] ?? $user['created_at'])); ?></span>
          </div>
        </div>
      </div>

      <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6">
        <div class="flex items-center">
          <div class="bg-emerald-100 p-2 rounded-lg mr-3">
            <i class="fas fa-shield-alt text-emerald-700"></i>
          </div>
          <div>
            <h4 class="font-semibold text-emerald-900">Security Tips</h4>
            <ul class="list-disc list-inside text-sm text-emerald-800 mt-2 space-y-1">
              <li>Use a strong password and change it periodically.</li>
              <li>Don’t share admin access with others.</li>
              <li>Log out from shared or public computers.</li>
            </ul>
          </div>
        </div>
        <a href="change_password.php" class="mt-4 inline-flex items-center text-emerald-700 hover:underline text-sm">
          <i class="fas fa-lock mr-2"></i> Change Password
        </a>
      </div>
    </div>
  </div>
</div>

<script>
// Auto-hide success alerts after 5s
setTimeout(() => {
  document.querySelectorAll('[role="alert"]').forEach(el => el.remove());
}, 5000);
</script>

<?php require 'footer.php'; ?>

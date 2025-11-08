<?php
require 'config.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }

$uid        = (int)($_SESSION['user']['id'] ?? 0);
$page_title = "My Profile";

$errors = [];
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);

/* -------------------- Handle form submission -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf'] ?? '';
  if (!verify_csrf($token)) {
    $errors[] = 'Invalid CSRF token. Please refresh and try again.';
  } else {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $addr  = trim($_POST['address'] ?? '');

    // Server-side validation
    if ($name === '') {
      $errors[] = 'Name is required.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
      $errors[] = 'Name must be between 2 and 80 characters.';
    } elseif (!preg_match('/^[\p{L}\p{M}\s\.\'-]+$/u', $name)) {
      $errors[] = 'Name can include only letters, spaces and . \' - characters.';
    }

    if ($phone !== '') {
      // Accepts 10–15 digits, with optional +, spaces, dashes and parentheses
      $phoneDigits = preg_replace('/\D+/', '', $phone);
      if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
        $errors[] = 'Phone number should have 10 to 15 digits.';
      }
    }

    if ($addr !== '') {
      if (mb_strlen($addr) > 300) {
        $errors[] = 'Address is too long (max 300 characters).';
      }
    }

    if (!$errors) {
      // Update with prepared statement
      $stmt = $mysqli->prepare('UPDATE users SET name=?, phone=?, address=?, updated_at=NOW() WHERE id=?');
      $stmt->bind_param('sssi', $name, $phone, $addr, $uid);

      if ($stmt->execute()) {
        // Update session name (for header menus etc.)
        $_SESSION['user']['name'] = $name;
        $_SESSION['success'] = 'Profile updated successfully!';
        header('Location: profile.php');
        exit;
      } else {
        $errors[] = 'Error updating profile. Please try again.';
      }
    }
  }
}

/* -------------------- Fetch user data safely -------------------- */
$user = [];
$stmt = $mysqli->prepare('SELECT id, name, email, phone, address, role, dues, created_at, updated_at FROM users WHERE id=? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows === 1) {
  $user = $res->fetch_assoc();
} else {
  // If somehow the user is missing, force logout
  $_SESSION = [];
  header('Location: login.php');
  exit;
}

/* -------------------- Role-based stats (safe queries) -------------------- */
$role = $_SESSION['user']['role'] ?? 'user';
$stats = [
  'total_requests'     => 0,
  'completed_requests' => 0,
  'pending_requests'   => 0,
  'assigned_requests'  => 0,
  'accepted_requests'  => 0,
];

// Treat 'customer' and 'user' as the same bucket for stats
if ($role === 'customer' || $role === 'user') {
  $q = "
    SELECT 
      COUNT(*) AS total_requests,
      SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) AS completed_requests,
      SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending_requests
    FROM collection_requests
    WHERE user_id = ?
  ";
  $st = $mysqli->prepare($q);
  $st->bind_param('i', $uid);
  $st->execute();
  $stats = array_merge($stats, $st->get_result()->fetch_assoc() ?? []);
} elseif ($role === 'worker') {
  $q = "
    SELECT 
      COUNT(*) AS assigned_requests,
      SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) AS completed_requests,
      SUM(CASE WHEN status = 'accepted'  THEN 1 ELSE 0 END) AS accepted_requests
    FROM collection_requests
    WHERE assigned_worker_id = ?
  ";
  $st = $mysqli->prepare($q);
  $st->bind_param('i', $uid);
  $st->execute();
  $stats = array_merge($stats, $st->get_result()->fetch_assoc() ?? []);
}

/* -------------------- Include appropriate header based on role -------------------- */
if ($role === 'worker') {
    require 'worker_header.php';
} else {
    require 'header.php';
}
?>

<!-- Toasts -->
<?php if ($success): ?>
  <div id="toast-success" class="fixed top-4 right-4 z-50 bg-white border border-green-300 text-green-800 rounded-lg shadow-lg px-4 py-3 flex items-start space-x-3">
    <div class="mt-0.5">✅</div>
    <div>
      <p class="font-semibold">Success</p>
      <p class="text-sm"><?php echo e($success); ?></p>
      <p class="text-xs text-green-700 mt-1">What to do next: review your details or continue using the dashboard.</p>
    </div>
    <button class="ml-4 text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">✕</button>
  </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div id="toast-error" class="fixed top-4 right-4 z-50 bg-white border border-red-300 text-red-800 rounded-lg shadow-lg px-4 py-3 flex items-start space-x-3">
    <div class="mt-0.5">⚠️</div>
    <div>
      <p class="font-semibold">Please fix the following</p>
      <ul class="list-disc list-inside text-sm">
        <?php foreach ($errors as $e): ?>
          <li><?php echo e($e); ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="text-xs text-red-700 mt-1">What to do: correct the highlighted fields and submit again.</p>
    </div>
    <button class="ml-4 text-red-700 hover:text-red-900" onclick="this.parentElement.remove()">✕</button>
  </div>
<?php endif; ?>

<div class="max-w-4xl mx-auto px-4 py-8">
  <!-- Header Section -->
  <div class="mb-8">
    <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
          <h1 class="text-3xl font-bold mb-2">My Profile</h1>
          <p class="text-green-100">Manage your personal information and account settings</p>
        </div>
        <div class="mt-4 md:mt-0">
          <span class="bg-green-800 bg-opacity-50 text-white px-4 py-2 rounded-lg font-semibold">
            <i class="fas fa-user mr-2"></i>
            <?php echo e(ucfirst($role)); ?>
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- User Statistics -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <?php if ($role === 'customer' || $role === 'user'): ?>
      <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo (int)($stats['total_requests'] ?? 0); ?></h3>
            <p class="text-gray-600">Total Requests</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-lg">
            <i class="fas fa-list text-blue-600 text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo (int)($stats['completed_requests'] ?? 0); ?></h3>
            <p class="text-gray-600">Completed</p>
          </div>
          <div class="bg-green-100 p-3 rounded-lg">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo (int)($stats['pending_requests'] ?? 0); ?></h3>
            <p class="text-gray-600">Pending</p>
          </div>
          <div class="bg-orange-100 p-3 rounded-lg">
            <i class="fas fa-clock text-orange-600 text-xl"></i>
          </div>
        </div>
      </div>
    <?php elseif ($role === 'worker'): ?>
      <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo (int)($stats['assigned_requests'] ?? 0); ?></h3>
            <p class="text-gray-600">Assigned</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-lg">
            <i class="fas fa-tasks text-blue-600 text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo (int)($stats['completed_requests'] ?? 0); ?></h3>
            <p class="text-gray-600">Completed</p>
          </div>
          <div class="bg-green-100 p-3 rounded-lg">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo (int)($stats['accepted_requests'] ?? 0); ?></h3>
            <p class="text-gray-600">Accepted</p>
          </div>
          <div class="bg-orange-100 p-3 rounded-lg">
            <i class="fas fa-user-check text-orange-600 text-xl"></i>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Profile Information -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-user-edit text-green-600 mr-3"></i>
          Edit Profile Information
        </h2>

        <form method="post" class="space-y-6" novalidate>
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Full Name <span class="text-red-600">*</span>
            </label>
            <input
              type="text"
              name="name"
              value="<?php echo e($user['name'] ?? ''); ?>"
              required
              minlength="2"
              maxlength="80"
              pattern="[\p{L}\p{M}\s\.'\-]+"
              class="w-full px-4 py-3 border <?php echo !empty($errors) ? 'border-red-400' : 'border-gray-300'; ?> rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
              placeholder="Enter your full name">
            <p class="text-xs text-gray-500 mt-1">2–80 chars. Letters, spaces, . ' - allowed.</p>
          </div>

          <!-- Email (Read-only) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Email Address
            </label>
            <input
              type="email"
              value="<?php echo e($user['email'] ?? ''); ?>"
              disabled
              class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500">
            <p class="text-xs text-gray-500 mt-1">Email address cannot be changed.</p>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Phone Number
            </label>
            <input
              type="tel"
              name="phone"
              value="<?php echo e($user['phone'] ?? ''); ?>"
              inputmode="tel"
              pattern="[\+\d\-\s\(\)]{10}"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
              placeholder="+91 98XX XXX XXX">
            <p class="text-xs text-gray-500 mt-1">10 digits. You may include +, spaces, dashes, parentheses.</p>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Address
            </label>
            <textarea
              name="address"
              rows="4"
              maxlength="300"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
              placeholder="Enter your complete address"><?php echo e($user['address'] ?? ''); ?></textarea>
            <p class="text-xs text-gray-500 mt-1">Max 300 characters. Used for waste collection services.</p>
          </div>

          <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

          <!-- Submit Button -->
          <div class="flex justify-end pt-4 border-t border-gray-200">
            <button
              type="submit"
              class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center">
              <i class="fas fa-save mr-2"></i>Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Account Information Sidebar -->
    <div class="space-y-6">
      <!-- Account Summary -->
      <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-id-card text-blue-600 mr-2"></i>
          Account Summary
        </h3>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">User ID:</span>
            <span class="font-medium">#<?php echo (int)$user['id']; ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Account Type:</span>
            <span class="font-medium text-green-600"><?php echo e(ucfirst($user['role'])); ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Member Since:</span>
            <span class="font-medium"><?php echo e($user['created_at'] ? date('M j, Y', strtotime($user['created_at'])) : '—'); ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Last Updated:</span>
            <span class="font-medium"><?php echo e($user['updated_at'] ? date('M j, Y', strtotime($user['updated_at'])) : ($user['created_at'] ? date('M j, Y', strtotime($user['created_at'])) : '—')); ?></span>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-bolt text-yellow-500 mr-2"></i>
          Quick Actions
        </h3>
        <div class="space-y-3">
          <?php if ($role === 'customer' || $role === 'user'): ?>
            <a href="collection_requests.php" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
              <div class="bg-green-100 p-2 rounded-lg mr-3">
                <i class="fas fa-plus text-green-600"></i>
              </div>
              <div>
                <p class="font-medium text-gray-800">New Collection</p>
                <p class="text-xs text-gray-600">Request waste collection</p>
              </div>
            </a>
            <a href="feedback.php" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
              <div class="bg-blue-100 p-2 rounded-lg mr-3">
                <i class="fas fa-comment text-blue-600"></i>
              </div>
              <div>
                <p class="font-medium text-gray-800">Give Feedback</p>
                <p class="text-xs text-gray-600">Share your experience</p>
              </div>
            </a>
          <?php elseif ($role === 'worker'): ?>
            <a href="collection_pending.php" class="flex items-center p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
              <div class="bg-orange-100 p-2 rounded-lg mr-3">
                <i class="fas fa-list text-orange-600"></i>
              </div>
              <div>
                <p class="font-medium text-gray-800">Pending Requests</p>
                <p class="text-xs text-gray-600">View assigned work</p>
              </div>
            </a>
            <a href="view_feedback.php" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
              <div class="bg-blue-100 p-2 rounded-lg mr-3">
                <i class="fas fa-comment text-blue-600"></i>
              </div>
              <div>
                <p class="font-medium text-gray-800">View Feedback</p>
                <p class="text-xs text-gray-600">See The Reviews</p>
              </div>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Account Status -->
      <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
        <div class="flex items-center mb-3">
          <div class="bg-green-100 p-2 rounded-lg mr-3">
            <i class="fas fa-check-circle text-green-600"></i>
          </div>
          <div>
            <h4 class="font-semibold text-green-800">Account Active</h4>
            <p class="text-sm text-green-600">Your account is in good standing</p>
          </div>
        </div>
        <?php if ((float)($user['dues'] ?? 0) > 0): ?>
          <div class="mt-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
            <p class="text-sm font-medium text-yellow-800">
              <i class="fas fa-exclamation-triangle mr-1"></i>
              Outstanding Dues: ₹<?php echo number_format((float)$user['dues'], 2); ?>
            </p>
          </div>
        <?php else: ?>
          <div class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200">
            <p class="text-sm font-medium text-green-800">
              <i class="fas fa-check mr-1"></i>
              No outstanding dues
            </p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
// Auto-hide toasts after 5s
setTimeout(() => {
  const s = document.getElementById('toast-success');
  if (s) s.remove();
  const e = document.getElementById('toast-error');
  if (e) e.remove();
}, 5000);
</script>

<?php
/* -------------------- Include appropriate footer based on role -------------------- */
if ($role === 'worker') {
    require 'worker_footer.php';
} else {
    require 'footer.php';
}
?>
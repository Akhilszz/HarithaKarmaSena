<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='user'){ 
  header('Location: login.php'); 
  exit; 
}

$uid = $_SESSION['user']['id'];
$page_title = "Complaints";

// Handle complaint submission
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_complaint'])){
  if(!verify_csrf($_POST['csrf'] ?? '')){ 
    $err = 'Invalid CSRF token'; 
  } else {
    $message = trim($_POST['message'] ?? '');
    if(empty($message)){
      $err = 'Please describe your complaint';
    } else {
      $stmt = $mysqli->prepare('INSERT INTO complaints (user_id, message) VALUES (?, ?)');
      $stmt->bind_param('is', $uid, $message); 
      if($stmt->execute()){
        $success = 'Complaint submitted successfully';
      } else {
        $err = 'Error submitting complaint: ' . $stmt->error;
      }
    }
  }
}

// Get complaints history
$complaints = $mysqli->prepare('SELECT * FROM complaints WHERE user_id=? ORDER BY created_at DESC');
$complaints->bind_param('i', $uid); 
$complaints->execute(); 
$res_complaints = $complaints->get_result();

require 'header.php';
?>

<div class="flex-1">
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="glass rounded-2xl shadow-xl p-8 gradient-border mb-8">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Complaints</h1>
          <p class="text-gray-600">Report issues and concerns about our services</p>
        </div>
        <div class="mt-4 md:mt-0">
          <?php 
          $open_count = 0;
          $resolved_count = 0;
          $temp_complaints = $mysqli->prepare('SELECT status, COUNT(*) as count FROM complaints WHERE user_id=? GROUP BY status');
          $temp_complaints->bind_param('i', $uid);
          $temp_complaints->execute();
          $status_result = $temp_complaints->get_result();
          while($row = $status_result->fetch_assoc()) {
            if($row['status'] == 'open') $open_count = $row['count'];
            if($row['status'] == 'resolved') $resolved_count = $row['count'];
          }
          ?>
          <div class="flex space-x-4">
            <div class="bg-orange-100 text-orange-800 px-4 py-2 rounded-lg">
              <div class="text-sm">Open</div>
              <div class="text-xl font-bold"><?php echo $open_count; ?></div>
            </div>
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg">
              <div class="text-sm">Resolved</div>
              <div class="text-xl font-bold"><?php echo $resolved_count; ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php if(!empty($err)): ?>
    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-start space-x-3">
      <i class="fas fa-exclamation-circle mt-0.5"></i>
      <span><?php echo e($err); ?></span>
    </div>
    <?php endif; ?>

    <?php if(!empty($success)): ?>
    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 flex items-start space-x-3">
      <i class="fas fa-check-circle mt-0.5"></i>
      <span><?php echo e($success); ?></span>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Submit Complaint -->
      <div class="glass rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
          Submit New Complaint
        </h2>
        <form method="post" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Describe your complaint</label>
            <textarea 
              name="message" 
              placeholder="Please describe the issue in detail. Be specific about what happened, when it occurred, and how it affected you..." 
              required 
              rows="8"
              class="input-field w-full px-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
            ><?php echo e($_POST['message'] ?? ''); ?></textarea>
          </div>
          
          <!-- Complaint Type Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Complaint Type (Optional)</label>
            <div class="grid grid-cols-2 gap-3">
              <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="type" value="service" class="text-green-600 focus:ring-green-500">
                <span class="text-sm text-gray-700">Service Quality</span>
              </label>
              <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="type" value="payment" class="text-green-600 focus:ring-green-500">
                <span class="text-sm text-gray-700">Payment Issue</span>
              </label>
              <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="type" value="schedule" class="text-green-600 focus:ring-green-500">
                <span class="text-sm text-gray-700">Scheduling</span>
              </label>
              <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="type" value="other" class="text-green-600 focus:ring-green-500">
                <span class="text-sm text-gray-700">Other</span>
              </label>
            </div>
          </div>

          <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
          <button 
            name="submit_complaint" 
            class="btn-hover w-full bg-red-600 text-white py-3 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl"
          >
            <i class="fas fa-paper-plane mr-2"></i>Submit Complaint
          </button>
        </form>
      </div>

      <!-- Complaint History -->
      <div class="glass rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-history text-blue-600 mr-3"></i>
          Complaint History
        </h2>
        <div class="space-y-4 max-h-[600px] overflow-y-auto">
          <?php while($complaint = $res_complaints->fetch_assoc()): ?>
          <div class="border-l-4 <?php echo $complaint['status'] == 'resolved' ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'; ?> p-4 rounded">
            <div class="flex justify-between items-start mb-2">
              <div>
                <p class="font-semibold text-gray-800">Complaint #<?php echo $complaint['id']; ?></p>
                <p class="text-xs text-gray-500">Submitted on <?php echo date('M j, Y g:i A', strtotime($complaint['created_at'])); ?></p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $complaint['status'] == 'resolved' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                <?php echo ucfirst($complaint['status']); ?>
              </span>
            </div>
            <p class="text-sm text-gray-700 mb-3"><?php echo e($complaint['message']); ?></p>
            
            <?php if($complaint['status'] == 'resolved'): ?>
              <div class="mt-3 p-3 bg-white rounded border">
                <p class="text-sm font-semibold text-gray-800 mb-1 flex items-center">
                  <i class="fas fa-check-circle text-green-600 mr-2"></i>
                  This complaint has been resolved
                </p>
                <p class="text-xs text-gray-600">Thank you for bringing this to our attention.</p>
              </div>
            <?php else: ?>
              <div class="mt-2 p-2 bg-yellow-50 rounded border border-yellow-200">
                <p class="text-xs text-yellow-800 flex items-center">
                  <i class="fas fa-clock mr-1"></i>
                  Your complaint is being reviewed by our team
                </p>
              </div>
            <?php endif; ?>
          </div>
          <?php endwhile; ?>
          
          <?php if($res_complaints->num_rows === 0): ?>
            <div class="text-center py-8 text-gray-500">
              <i class="fas fa-inbox text-4xl mb-3"></i>
              <p>No complaints submitted yet</p>
              <p class="text-sm mt-1">Your complaints help us improve our services</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Complaint Guidelines -->
    <div class="glass rounded-2xl shadow-xl p-6 mt-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
        Complaint Guidelines
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
        <div class="flex items-start space-x-2">
          <i class="fas fa-check text-green-500 mt-1"></i>
          <span>Be specific about the issue and include relevant dates/times</span>
        </div>
        <div class="flex items-start space-x-2">
          <i class="fas fa-check text-green-500 mt-1"></i>
          <span>Provide your contact information if needed for follow-up</span>
        </div>
        <div class="flex items-start space-x-2">
          <i class="fas fa-check text-green-500 mt-1"></i>
          <span>Remain constructive and professional in your description</span>
        </div>
        <div class="flex items-start space-x-2">
          <i class="fas fa-check text-green-500 mt-1"></i>
          <span>Allow 24-48 hours for initial response and resolution</span>
        </div>
      </div>
      
      <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
          <i class="fas fa-phone-alt mr-2"></i>
          Need Immediate Assistance?
        </h4>
        <p class="text-sm text-blue-700">
          For urgent matters, please contact our support team directly at 
          <strong>+91 98765 43210</strong> or email 
          <strong>support@harithakarmasena.org</strong>
        </p>
      </div>
    </div>
  </div>
</div>

<script>
// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
  const textarea = document.querySelector('textarea[name="message"]');
  
  textarea.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
  });
  
  // Trigger initial resize
  textarea.dispatchEvent(new Event('input'));
});
</script>

<?php require 'footer.php'; ?>
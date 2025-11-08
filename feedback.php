<?php
require 'config.php';
if(!is_logged_in()) header('Location: login.php');

$uid = $_SESSION['user']['id'];
$page_title = "Feedback";

// Handle feedback submission
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!verify_csrf($_POST['csrf'] ?? '')){ 
    header('Location: user_dashboard.php'); 
    exit; 
  }
  $msg = trim($_POST['message'] ?? ''); 
  $rating = intval($_POST['rating'] ?? 0);
  $uid = $_SESSION['user']['id'];
  
  if($msg && $rating > 0){
    $stmt = $mysqli->prepare('INSERT INTO feedbacks (user_id, message, rating) VALUES (?, ?, ?)'); 
    $stmt->bind_param('isi', $uid, $msg, $rating); 
    if($stmt->execute()){
      $_SESSION['success'] = 'Feedback submitted successfully. Thank you!';
      header('Location: feedback.php');
      exit;
    } else {
      $err = 'Error submitting feedback: ' . $stmt->error;
    }
  } else {
    $err = 'Please provide both your feedback message and rating';
  }
}

// Get user's previous feedbacks with status
$feedbacks = $mysqli->prepare('
    SELECT f.*, 
           CASE 
             WHEN f.admin_response IS NOT NULL AND f.admin_response != "" THEN "responded"
             ELSE "pending"
           END as response_status
    FROM feedbacks f 
    WHERE f.user_id=? 
    ORDER BY f.created_at DESC
');
$feedbacks->bind_param('i', $uid); 
$feedbacks->execute(); 
$res_feedbacks = $feedbacks->get_result();

// Get feedback statistics for the user
$stats = $mysqli->prepare('
    SELECT 
        COUNT(*) as total_feedback,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN admin_response IS NOT NULL AND admin_response != "" THEN 1 ELSE 0 END) as responded_count
    FROM feedbacks 
    WHERE user_id = ?
');
$stats->bind_param('i', $uid);
$stats->execute();
$user_stats = $stats->get_result()->fetch_assoc();

require 'header.php';
?>

<div class="flex-1">
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8 mb-8">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
          <h1 class="text-3xl font-bold mb-2">Share Your Feedback</h1>
          <p class="text-green-100">We value your opinion and are constantly working to improve our services</p>
        </div>
        <div class="mt-4 md:mt-0 bg-green-800 bg-opacity-50 px-4 py-2 rounded-lg">
          <p class="text-sm">Your feedback helps us serve you better</p>
        </div>
      </div>
    </div>

    <!-- Flash Messages -->
    <?php if(isset($_SESSION['success'])): ?>
      <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <i class="fas fa-check-circle"></i>
          <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        </div>
        <button type="button" class="text-green-700 hover:text-green-900">
          <i class="fas fa-times"></i>
        </button>
      </div>
    <?php endif; ?>

    <?php if(!empty($err)): ?>
      <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <i class="fas fa-exclamation-circle"></i>
          <span><?php echo e($err); ?></span>
        </div>
        <button type="button" class="text-red-700 hover:text-red-900">
          <i class="fas fa-times"></i>
        </button>
      </div>
    <?php endif; ?>

    <!-- User Feedback Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
        <div class="flex items-center">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <i class="fas fa-comment-dots text-blue-600 text-xl"></i>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo $user_stats['total_feedback'] ?? 0; ?></h3>
            <p class="text-gray-600">Total Feedback</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
        <div class="flex items-center">
          <div class="bg-green-100 p-3 rounded-lg mr-4">
            <i class="fas fa-star text-green-600 text-xl"></i>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($user_stats['avg_rating'] ?? 0, 1); ?>/5</h3>
            <p class="text-gray-600">Average Rating</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
        <div class="flex items-center">
          <div class="bg-purple-100 p-3 rounded-lg mr-4">
            <i class="fas fa-reply text-purple-600 text-xl"></i>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo $user_stats['responded_count'] ?? 0; ?></h3>
            <p class="text-gray-600">Responses Received</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Submit Feedback -->
      <div class="bg-white rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-comment-dots text-blue-600 mr-3"></i>
          Submit New Feedback
        </h2>
        <form method="post" class="space-y-6">
          <!-- Rating -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Your Rating</label>
            <div class="flex space-x-2" id="rating-stars">
              <?php for($i = 1; $i <= 5; $i++): ?>
                <button type="button" class="rating-star text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="<?php echo $i; ?>">
                  <i class="far fa-star"></i>
                </button>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="selected-rating" value="0" required>
            <p class="text-sm text-gray-500 mt-2" id="rating-text">Select your rating (1-5 stars)</p>
          </div>

          <!-- Feedback Message -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Your Feedback Message</label>
            <textarea 
              name="message" 
              placeholder="What do you think about our services? How can we improve? Share your suggestions, complaints, or appreciation..." 
              required 
              rows="6"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white resize-none"
            ><?php echo e($_POST['message'] ?? ''); ?></textarea>
          </div>
          
          <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
          <button 
            type="submit" 
            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center"
            id="submit-btn"
          >
            <i class="fas fa-paper-plane mr-2"></i>Submit Feedback
          </button>
        </form>
      </div>

      <!-- Feedback History -->
      <div class="bg-white rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-history text-green-600 mr-3"></i>
          Your Feedback History
        </h2>
        <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
          <?php while($feedback = $res_feedbacks->fetch_assoc()): 
            $status_class = $feedback['response_status'] === 'responded' ? 'border-green-500 bg-green-50' : 'border-blue-500 bg-blue-50';
            $status_icon = $feedback['response_status'] === 'responded' ? 'fa-check-circle text-green-600' : 'fa-clock text-blue-600';
          ?>
          <div class="border-l-4 <?php echo $status_class; ?> p-4 rounded">
            <div class="flex justify-between items-start mb-3">
              <div class="flex items-center space-x-3">
                <span class="font-semibold text-gray-800">Feedback #<?php echo $feedback['id']; ?></span>
                <!-- Star Rating Display -->
                <div class="flex space-x-1">
                  <?php for($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star text-<?php echo $i <= $feedback['rating'] ? 'yellow-400' : 'gray-300'; ?> text-sm"></i>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500">
                  <?php echo date('M j, Y g:i A', strtotime($feedback['created_at'])); ?>
                </span>
                <span class="px-2 py-1 rounded-full text-xs font-semibold 
                  <?php echo $feedback['response_status'] === 'responded' ? 'bg-green-200 text-green-800' : 'bg-blue-200 text-blue-800'; ?>">
                  <i class="fas <?php echo $status_icon; ?> mr-1"></i>
                  <?php echo ucfirst($feedback['response_status']); ?>
                </span>
              </div>
            </div>
            
            <p class="text-sm text-gray-700 mb-3 leading-relaxed"><?php echo nl2br(e($feedback['message'])); ?></p>
            
            <?php if(!empty($feedback['admin_response'])): ?>
              <div class="mt-3 p-3 bg-white rounded border border-green-200">
                <p class="text-sm font-semibold text-gray-800 mb-1 flex items-center">
                  <i class="fas fa-reply text-green-600 mr-2"></i>
                  Admin Response:
                </p>
                <p class="text-sm text-gray-700 leading-relaxed"><?php echo nl2br(e($feedback['admin_response'])); ?></p>
                <?php if(!empty($feedback['updated_at'])): ?>
                  <p class="text-xs text-gray-500 mt-2">
                    Responded on: <?php echo date('M j, Y g:i A', strtotime($feedback['updated_at'])); ?>
                  </p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          <?php endwhile; ?>
          
          <?php if($res_feedbacks->num_rows === 0): ?>
            <div class="text-center py-8 text-gray-500">
              <i class="fas fa-comment-slash text-4xl mb-3"></i>
              <p class="text-lg font-medium">No feedback submitted yet</p>
              <p class="text-sm mt-1">Your feedback helps us improve our services</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Feedback Guidelines -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mt-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
        Feedback Guidelines
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
        <div class="flex items-start space-x-3">
          <i class="fas fa-check text-green-500 mt-0.5"></i>
          <span>Be specific about what you liked or didn't like about our service</span>
        </div>
        <div class="flex items-start space-x-3">
          <i class="fas fa-check text-green-500 mt-0.5"></i>
          <span>Suggest concrete improvements for better service</span>
        </div>
        <div class="flex items-start space-x-3">
          <i class="fas fa-check text-green-500 mt-0.5"></i>
          <span>Share your experience with waste collection timing</span>
        </div>
        <div class="flex items-start space-x-3">
          <i class="fas fa-check text-green-500 mt-0.5"></i>
          <span>Mention any issues with worker behavior or efficiency</span>
        </div>
        <div class="flex items-start space-x-3">
          <i class="fas fa-check text-green-500 mt-0.5"></i>
          <span>Provide feedback on payment process and billing</span>
        </div>
        <div class="flex items-start space-x-3">
          <i class="fas fa-check text-green-500 mt-0.5"></i>
          <span>Share suggestions for new features or services</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Star Rating System
document.addEventListener('DOMContentLoaded', function() {
  const stars = document.querySelectorAll('.rating-star');
  const selectedRating = document.getElementById('selected-rating');
  const ratingText = document.getElementById('rating-text');
  const submitBtn = document.getElementById('submit-btn');
  
  const ratingMessages = {
    1: 'Poor - We need significant improvement',
    2: 'Fair - There is room for improvement', 
    3: 'Good - Satisfactory service',
    4: 'Very Good - Great service with minor issues',
    5: 'Excellent - Outstanding service!'
  };
  
  stars.forEach(star => {
    star.addEventListener('click', function() {
      const rating = parseInt(this.getAttribute('data-rating'));
      selectedRating.value = rating;
      
      // Update stars display
      stars.forEach((s, index) => {
        const icon = s.querySelector('i');
        if (index < rating) {
          icon.className = 'fas fa-star text-yellow-400';
        } else {
          icon.className = 'far fa-star text-gray-300';
        }
      });
      
      // Update rating text
      ratingText.textContent = ratingMessages[rating];
      ratingText.className = 'text-sm text-green-600 font-medium mt-2';
      
      // Enable submit button
      submitBtn.disabled = false;
    });
    
    star.addEventListener('mouseover', function() {
      const rating = parseInt(this.getAttribute('data-rating'));
      stars.forEach((s, index) => {
        const icon = s.querySelector('i');
        if (index < rating) {
          icon.className = 'fas fa-star text-yellow-300';
        }
      });
    });
    
    star.addEventListener('mouseout', function() {
      const currentRating = parseInt(selectedRating.value);
      stars.forEach((s, index) => {
        const icon = s.querySelector('i');
        if (currentRating === 0) {
          icon.className = 'far fa-star text-gray-300';
        } else if (index < currentRating) {
          icon.className = 'fas fa-star text-yellow-400';
        } else {
          icon.className = 'far fa-star text-gray-300';
        }
      });
    });
  });
  
  // Close flash messages
  document.querySelectorAll('[role="alert"] button').forEach(button => {
    button.addEventListener('click', function() {
      this.closest('[role="alert"]').style.display = 'none';
    });
  });
});

// Auto-hide flash messages after 5 seconds
setTimeout(() => {
  document.querySelectorAll('[role="alert"]').forEach(alert => {
    alert.style.display = 'none';
  });
}, 5000);
</script>

<?php require 'footer.php'; ?>
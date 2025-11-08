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
  $uid = $_SESSION['user']['id'];
  if($msg){
    $stmt = $mysqli->prepare('INSERT INTO feedbacks (user_id,message) VALUES (?,?)'); 
    $stmt->bind_param('is',$uid,$msg); 
    if($stmt->execute()){
      $success = 'Feedback submitted successfully. Thank you!';
    } else {
      $err = 'Error submitting feedback: ' . $stmt->error;
    }
  } else {
    $err = 'Please provide your feedback';
  }
}

// Get user's previous feedbacks
$feedbacks = $mysqli->prepare('SELECT * FROM feedbacks WHERE user_id=? ORDER BY created_at DESC');
$feedbacks->bind_param('i', $uid); 
$feedbacks->execute(); 
$res_feedbacks = $feedbacks->get_result();

require 'header.php';
?>

<div class="flex-1">
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="glass rounded-2xl shadow-xl p-8 gradient-border mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Share Your Feedback</h1>
      <p class="text-gray-600">We value your opinion and are constantly working to improve our services</p>
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
      <!-- Submit Feedback -->
      <div class="glass rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-comment-dots text-blue-600 mr-3"></i>
          Submit Feedback
        </h2>
        <form method="post" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Your Feedback</label>
            <textarea 
              name="message" 
              placeholder="What do you think about our services? How can we improve? Share your suggestions..." 
              required 
              rows="6"
              class="input-field w-full px-4 py-3 border-2 border-green-200 rounded-lg focus:border-green-500 focus:outline-none bg-white"
            ><?php echo e($_POST['message'] ?? ''); ?></textarea>
          </div>
          <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
          <button 
            type="submit" 
            class="btn-hover w-full bg-gradient-green text-white py-3 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl"
          >
            <i class="fas fa-paper-plane mr-2"></i>Submit Feedback
          </button>
        </form>
      </div>

      <!-- Feedback History -->
      <div class="glass rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
          <i class="fas fa-history text-green-600 mr-3"></i>
          Your Previous Feedback
        </h2>
        <div class="space-y-4 max-h-96 overflow-y-auto">
          <?php while($feedback = $res_feedbacks->fetch_assoc()): ?>
          <div class="border-l-4 border-blue-500 bg-blue-50 p-4 rounded">
            <div class="flex justify-between items-start mb-2">
              <p class="font-semibold text-gray-800">Feedback #<?php echo $feedback['id']; ?></p>
              <span class="text-xs text-gray-500">
                <?php echo date('M j, Y', strtotime($feedback['created_at'])); ?>
              </span>
            </div>
            <p class="text-sm text-gray-700 mb-2"><?php echo e($feedback['message']); ?></p>
            <?php if(!empty($feedback['admin_response'])): ?>
              <div class="mt-3 p-3 bg-white rounded border">
                <p class="text-sm font-semibold text-gray-800 mb-1 flex items-center">
                  <i class="fas fa-reply text-green-600 mr-2"></i>
                  Admin Response:
                </p>
                <p class="text-sm text-gray-700"><?php echo e($feedback['admin_response']); ?></p>
              </div>
            <?php endif; ?>
          </div>
          <?php endwhile; ?>
          
          <?php if($res_feedbacks->num_rows === 0): ?>
            <div class="text-center py-8 text-gray-500">
              <i class="fas fa-comment-slash text-4xl mb-3"></i>
              <p>No feedback submitted yet</p>
              <p class="text-sm mt-1">Your feedback helps us improve our services</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Feedback Guidelines -->
    <div class="glass rounded-2xl shadow-xl p-6 mt-8">
      <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
        Feedback Guidelines
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
        <div class="flex items-start space-x-2">
          <i class="fas fa-check text-green-500 mt-1"></i>
          <span>Be specific about what you liked or didn't like</span>
        </div>
        <div class="flex items-start space-x-2">
          <i class="fas fa-check text-green-500 mt-1"></i>
          <span>Suggest concrete improvements</span>
        </div>
        <div class="flex items-start space-x-2">
          <i class="fas fa-check text-green-500 mt-1"></i>
          <span>Share your experience with our collection services</span>
        </div>
        <div class="flex items-start space-x-2">
          <i class="fas fa-check text-green-500 mt-1"></i>
          <span>Mention any issues with payment or scheduling</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require 'footer.php'; ?>
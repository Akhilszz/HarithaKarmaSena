<?php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='admin') header('Location: login.php');

$page_title = "Feedback Management";

// Handle feedback response
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $response = trim($_POST['response']);
    
    if($response) {
        $stmt = $mysqli->prepare("UPDATE feedbacks SET admin_response = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $response, $feedback_id);
        if($stmt->execute()) {
            $_SESSION['success'] = "Response submitted successfully";
        } else {
            $_SESSION['error'] = "Error submitting response";
        }
    }
    header('Location: admin_feedback.php');
    exit;
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$where = '';
if($filter === 'unresponded') $where = "WHERE f.admin_response IS NULL OR f.admin_response = ''";
elseif($filter === 'responded') $where = "WHERE f.admin_response IS NOT NULL AND f.admin_response != ''";

// Get feedback with user information
$feedback = $mysqli->query("
    SELECT f.*, u.name as user_name, u.email 
    FROM feedbacks f 
    JOIN users u ON f.user_id = u.id 
    $where
    ORDER BY f.created_at DESC
");

// Get feedback statistics
$stats = $mysqli->query("
    SELECT 
        COUNT(*) as total_feedback,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN admin_response IS NULL OR admin_response = '' THEN 1 ELSE 0 END) as unresponded,
        SUM(CASE WHEN admin_response IS NOT NULL AND admin_response != '' THEN 1 ELSE 0 END) as responded
    FROM feedbacks
")->fetch_assoc();

require 'admin_header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="bg-gradient-green text-white rounded-2xl shadow-xl p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Feedback Management</h1>
                    <p class="text-green-100">Respond to customer feedback and monitor satisfaction</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="bg-green-800 bg-opacity-50 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-comments mr-2"></i>
                        <?php echo $feedback->num_rows; ?> Feedback Messages
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_feedback']; ?></h3>
                    <p class="text-gray-600">Total Feedback</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-comment-dots text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?>/5</h3>
                    <p class="text-gray-600">Average Rating</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-star text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['unresponded']; ?></h3>
                    <p class="text-gray-600">Awaiting Response</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['responded']; ?></h3>
                    <p class="text-gray-600">Responded</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
        <div class="flex flex-wrap gap-2">
            <a href="?filter=all" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'all' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                All Feedback
            </a>
            <a href="?filter=unresponded" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'unresponded' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Awaiting Response
            </a>
            <a href="?filter=responded" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'responded' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                Responded
            </a>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-comments text-green-600 mr-3"></i>
                Customer Feedback
            </h2>
        </div>

        <div class="divide-y divide-gray-200">
            <?php while($fb = $feedback->fetch_assoc()): 
                $has_response = !empty($fb['admin_response']);
                $is_recent = strtotime($fb['created_at']) >= strtotime('-3 days');
            ?>
            <div class="p-6 hover:bg-gray-50 transition-colors <?php echo $is_recent ? 'bg-blue-50' : ''; ?>">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <!-- Feedback Content -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-800"><?php echo e($fb['user_name']); ?></h3>
                                    <span class="text-sm text-gray-500"><?php echo e($fb['email']); ?></span>
                                    <?php if($is_recent): ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                        <i class="fas fa-star mr-1"></i>New
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-500">
                                    <i class="far fa-clock mr-1"></i>
                                    <?php echo date('M j, Y g:i A', strtotime($fb['created_at'])); ?>
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <?php if($fb['rating']): ?>
                                <div class="flex space-x-1">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star text-<?php echo $i <= $fb['rating'] ? 'yellow-400' : 'gray-300'; ?> text-sm"></i>
                                    <?php endfor; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($has_response): ?>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>Responded
                                </span>
                                <?php else: ?>
                                <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium">
                                    <i class="fas fa-clock mr-1"></i>Awaiting Response
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Feedback Message -->
                        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
                            <p class="text-gray-700 leading-relaxed"><?php echo nl2br(e($fb['message'])); ?></p>
                        </div>

                        <!-- Admin Response -->
                        <?php if($has_response): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <div class="bg-green-600 text-white p-2 rounded-full mr-3">
                                    <i class="fas fa-user-shield text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-green-800">Admin Response</h4>
                                    <p class="text-sm text-green-600">
                                        <?php echo date('M j, Y g:i A', strtotime($fb['updated_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            <p class="text-green-700 leading-relaxed ml-11"><?php echo nl2br(e($fb['admin_response'])); ?></p>
                        </div>
                        <?php else: ?>
                        <!-- Response Form -->
                        <form method="POST" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Response</label>
                                <textarea name="response" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Type your response to the customer..." required></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-paper-plane mr-2"></i>Send Response
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php require 'admin_footer.php'; ?>
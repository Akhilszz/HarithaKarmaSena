<?php
// feedback.php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='worker') header('Location: login.php');

$page_title = "Customer Feedback";

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query based on filters
$where_conditions = [];
$query_params = [];

if ($filter === 'unresponded') {
    $where_conditions[] = "f.admin_response IS NULL OR f.admin_response = ''";
} elseif ($filter === 'responded') {
    $where_conditions[] = "f.admin_response IS NOT NULL AND f.admin_response != ''";
}

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE ? OR f.message LIKE ? OR f.admin_response LIKE ?)";
    $search_term = "%$search%";
    $query_params = array_merge($query_params, [$search_term, $search_term, $search_term]);
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

// Get feedback with filters
$feedback_query = "
    SELECT f.*, u.name AS user_name, u.email 
    FROM feedbacks f 
    JOIN users u ON f.user_id = u.id 
    $where_clause
    ORDER BY f.created_at DESC
";

$stmt = $mysqli->prepare($feedback_query);
if (!empty($query_params)) {
    $types = str_repeat('s', count($query_params));
    $stmt->bind_param($types, ...$query_params);
}
$stmt->execute();
$feedback_result = $stmt->get_result();

// Get statistics
$stats = $mysqli->query("
    SELECT 
        COUNT(*) as total_feedback,
        SUM(CASE WHEN admin_response IS NULL OR admin_response = '' THEN 1 ELSE 0 END) as unresponded,
        SUM(CASE WHEN admin_response IS NOT NULL AND admin_response != '' THEN 1 ELSE 0 END) as responded
    FROM feedbacks
")->fetch_assoc();

require 'worker_header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Customer Feedback</h1>
                <p class="text-gray-600">Read and manage customer feedback and reviews</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-lg font-semibold">
                    <i class="fas fa-comments mr-2"></i>
                    <?php echo $feedback_result->num_rows; ?> Feedback Messages
                </span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-comment-dots text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_feedback'] ?? 0; ?></h3>
                        <p class="text-gray-600">Total Feedback</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
                <div class="flex items-center">
                    <div class="bg-orange-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['unresponded'] ?? 0; ?></h3>
                        <p class="text-gray-600">Awaiting Response</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['responded'] ?? 0; ?></h3>
                        <p class="text-gray-600">Responded</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="?filter=all" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    All Feedback
                </a>
                <a href="?filter=unresponded" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'unresponded' ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Awaiting Response
                </a>
                <a href="?filter=responded" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $filter === 'responded' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Responded
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search feedback..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-search"></i>
                </button>
                <?php if(!empty($search)): ?>
                <a href="?filter=<?php echo $filter; ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-comments text-blue-600 mr-3"></i>
                Customer Feedback Messages
            </h2>
        </div>

        <?php if($feedback_result->num_rows > 0): ?>
            <div class="divide-y divide-gray-200">
                <?php while($feedback = $feedback_result->fetch_assoc()): 
                    $has_response = !empty($feedback['admin_response']);
                    $is_recent = strtotime($feedback['created_at']) >= strtotime('-3 days');
                ?>
                <div class="p-6 hover:bg-gray-50 transition-colors <?php echo $is_recent ? 'bg-blue-50' : ''; ?>">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <!-- Feedback Content -->
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-800"><?php echo e($feedback['user_name']); ?></h3>
                                        <span class="text-sm text-gray-500"><?php echo e($feedback['email']); ?></span>
                                        <?php if($is_recent): ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                            <i class="fas fa-star mr-1"></i>New
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-gray-500">
                                        <i class="far fa-clock mr-1"></i>
                                        <?php echo date('M j, Y g:i A', strtotime($feedback['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
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
                                <p class="text-gray-700 leading-relaxed"><?php echo nl2br(e($feedback['message'])); ?></p>
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
                                            <?php echo date('M j, Y g:i A', strtotime($feedback['updated_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <p class="text-green-700 leading-relaxed ml-11"><?php echo nl2br(e($feedback['admin_response'])); ?></p>
                            </div>
                            <?php else: ?>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                                <p class="text-gray-600 mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    This feedback is awaiting admin response
                                </p>
                                <p class="text-sm text-gray-500">
                                    Workers can view feedback but only admins can respond to customers
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="lg:w-48 flex lg:flex-col gap-2">
                            <button onclick="viewFeedbackDetails(<?php echo $feedback['id']; ?>)" class="flex-1 lg:flex-none bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </button>
                            
                            <?php if(!$has_response): ?>
                            <button onclick="suggestResponse(<?php echo $feedback['id']; ?>)" class="flex-1 lg:flex-none bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-lightbulb mr-2"></i>Suggest Response
                            </button>
                            <?php endif; ?>
                            
                            <button onclick="shareFeedback(<?php echo $feedback['id']; ?>)" class="flex-1 lg:flex-none bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-share mr-2"></i>Share
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-comment-slash text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No Feedback Found</h3>
                <p class="text-gray-600 mb-6">
                    <?php if(!empty($search)): ?>
                    No feedback matches your search criteria. Try different keywords.
                    <?php elseif($filter === 'unresponded'): ?>
                    All feedback has been responded to. Great work!
                    <?php elseif($filter === 'responded'): ?>
                    No responded feedback found.
                    <?php else: ?>
                    No customer feedback has been submitted yet.
                    <?php endif; ?>
                </p>
                <?php if(!empty($search) || $filter !== 'all'): ?>
                <a href="feedback.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="fas fa-refresh mr-2"></i>View All Feedback
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Stats & Tips -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Feedback Insights -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-bar text-blue-600 mr-3"></i>
                Feedback Insights
            </h3>
            <div class="space-y-4">
                <?php
                // Get recent feedback stats
                $recent_stats = $mysqli->query("
                    SELECT 
                        COUNT(*) as total,
                        AVG(LENGTH(message)) as avg_length
                    FROM feedbacks 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ")->fetch_assoc();
                ?>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600">Feedback this week:</span>
                    <span class="font-semibold text-gray-800"><?php echo $recent_stats['total'] ?? 0; ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-600">Avg. response time:</span>
                    <span class="font-semibold text-green-600">2.3 days</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-600">Customer satisfaction:</span>
                    <span class="font-semibold text-green-600">4.2/5</span>
                </div>
            </div>
        </div>

        <!-- Worker Tips -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-lightbulb text-yellow-600 mr-3"></i>
                Tips for Workers
            </h3>
            <ul class="space-y-3 text-sm text-gray-600">
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    Read feedback regularly to understand customer needs
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    Use feedback to improve your collection service quality
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    Share positive feedback with your team for motivation
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    Report urgent concerns to supervisors immediately
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Feedback Details Modal -->
<div id="feedbackModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Feedback Details</h3>
                <button onclick="closeFeedbackModal()" class="text-gray-500 hover:text-gray-700 p-2">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div id="feedbackModalContent" class="p-6">
            <!-- Content will be loaded via JavaScript -->
        </div>
    </div>
</div>

<!-- Suggest Response Modal -->
<div id="suggestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Suggest Response</h3>
                <button onclick="closeSuggestModal()" class="text-gray-500 hover:text-gray-700 p-2">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Suggest a response for the admin to review and send to the customer:</p>
            <textarea id="suggestedResponse" rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Type your suggested response here..."></textarea>
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeSuggestModal()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
                <button onclick="submitSuggestion()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Suggestion
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentFeedbackId = null;

function viewFeedbackDetails(feedbackId) {
    // In a real application, you would fetch this data via AJAX
    const modalContent = document.getElementById('feedbackModalContent');
    modalContent.innerHTML = `
        <div class="space-y-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">Feedback Information</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong>Feedback ID:</strong> #${feedbackId}
                    </div>
                    <div>
                        <strong>Submitted:</strong> 2 days ago
                    </div>
                    <div>
                        <strong>Status:</strong> <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-sm">Awaiting Response</span>
                    </div>
                    <div>
                        <strong>Priority:</strong> <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Normal</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-3">Customer Message</h4>
                <p class="text-gray-700 leading-relaxed">This is a detailed customer feedback message. The customer shared their experience with the collection service and provided specific suggestions for improvement.</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-800 mb-2">Customer Information</h4>
                <p><strong>Name:</strong> John Doe</p>
                <p><strong>Email:</strong> john.doe@example.com</p>
                <p><strong>Phone:</strong> +1 234 567 8900</p>
                <p><strong>Previous Collections:</strong> 12 completed</p>
            </div>
        </div>
    `;
    document.getElementById('feedbackModal').classList.remove('hidden');
}

function suggestResponse(feedbackId) {
    currentFeedbackId = feedbackId;
    document.getElementById('suggestedResponse').value = '';
    document.getElementById('suggestModal').classList.remove('hidden');
}

function submitSuggestion() {
    const response = document.getElementById('suggestedResponse').value.trim();
    if (!response) {
        alert('Please enter a suggested response');
        return;
    }
    
    // In a real application, you would submit this via AJAX
    alert('Suggestion submitted to admin for review. Thank you!');
    closeSuggestModal();
}

function shareFeedback(feedbackId) {
    // In a real application, this would share the feedback
    if (navigator.share) {
        navigator.share({
            title: 'Customer Feedback',
            text: 'Check out this customer feedback',
            url: window.location.href
        });
    } else {
        alert('Feedback sharing feature would open here');
    }
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').classList.add('hidden');
}

function closeSuggestModal() {
    document.getElementById('suggestModal').classList.add('hidden');
    currentFeedbackId = null;
}

// Close modals when clicking outside
document.getElementById('feedbackModal').addEventListener('click', function(e) {
    if(e.target === this) closeFeedbackModal();
});

document.getElementById('suggestModal').addEventListener('click', function(e) {
    if(e.target === this) closeSuggestModal();
});

// Auto-hide new indicators after 7 days
setTimeout(() => {
    document.querySelectorAll('.bg-blue-50').forEach(element => {
        element.classList.remove('bg-blue-50');
    });
}, 3000);
</script>

<?php require 'worker_footer.php'; ?>
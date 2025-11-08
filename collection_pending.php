<?php
// pending_collections.php
require 'config.php';
if(!is_logged_in() || $_SESSION['user']['role']!=='worker') header('Location: login.php');

$page_title = "Pending Collections";

// Get pending collection requests
$pending_reqs = $mysqli->query("
    SELECT cr.*, u.name AS user_name, u.phone, u.email 
    FROM collection_requests cr 
    JOIN users u ON cr.user_id = u.id 
    WHERE cr.status = 'pending' 
    ORDER BY cr.created_at DESC
");

require 'worker_header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Pending Collections</h1>
                <p class="text-gray-600">Manage and accept new collection requests from customers</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="bg-orange-100 text-orange-800 px-4 py-2 rounded-lg font-semibold">
                    <i class="fas fa-clock mr-2"></i>
                    <?php echo $pending_reqs->num_rows; ?> Pending Requests
                </span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
                <div class="flex items-center">
                    <div class="bg-orange-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $pending_reqs->num_rows; ?></h3>
                        <p class="text-gray-600">Awaiting Acceptance</p>
                    </div>
                </div>
            </div>

            <?php
            // Get today's pending requests
            $today_pending = $mysqli->query("
                SELECT COUNT(*) as count 
                FROM collection_requests 
                WHERE status = 'pending' AND DATE(created_at) = CURDATE()
            ")->fetch_assoc()['count'];
            ?>
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $today_pending; ?></h3>
                        <p class="text-gray-600">Today's Requests</p>
                    </div>
                </div>
            </div>

            <?php
            // Get this week's pending requests
            $week_pending = $mysqli->query("
                SELECT COUNT(*) as count 
                FROM collection_requests 
                WHERE status = 'pending' AND YEARWEEK(created_at) = YEARWEEK(CURDATE())
            ")->fetch_assoc()['count'];
            ?>
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-calendar-week text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $week_pending; ?></h3>
                        <p class="text-gray-600">This Week</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Collections Table -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list-alt text-orange-600 mr-3"></i>
                    Pending Collection Requests
                </h2>
                <div class="mt-2 md:mt-0 flex space-x-2">
                    <button onclick="filterRequests('all')" class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-sm hover:bg-gray-200 transition-colors">
                        All
                    </button>
                    <button onclick="filterRequests('today')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-sm hover:bg-blue-200 transition-colors">
                        Today
                    </button>
                    <button onclick="filterRequests('urgent')" class="bg-red-100 text-red-700 px-3 py-1 rounded text-sm hover:bg-red-200 transition-colors">
                        Urgent
                    </button>
                </div>
            </div>
        </div>

        <?php if($pending_reqs->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Request Details</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Customer</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Schedule</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Created</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while($request = $pending_reqs->fetch_assoc()): 
                            $is_urgent = strtotime($request['schedule_date']) <= strtotime('+2 days');
                            $is_today = date('Y-m-d', strtotime($request['created_at'])) == date('Y-m-d');
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors request-row 
                            <?php echo $is_urgent ? 'urgent' : ''; ?> 
                            <?php echo $is_today ? 'today' : ''; ?>">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Request #<?php echo $request['id']; ?></p>
                                    <p class="text-sm text-gray-600 mt-1 max-w-md">
                                        <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                        <?php echo e($request['address']); ?>
                                    </p>
                                    <?php if(!empty($request['special_instructions'])): ?>
                                    <p class="text-xs text-blue-600 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <?php echo e($request['special_instructions']); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo e($request['user_name']); ?></p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-phone text-gray-400 mr-2"></i>
                                        <?php echo e($request['phone']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                        <?php echo e($request['email']); ?>
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="bg-<?php echo $is_urgent ? 'red' : 'green'; ?>-100 p-2 rounded-lg mr-3">
                                        <i class="fas fa-calendar-alt text-<?php echo $is_urgent ? 'red' : 'green'; ?>-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo date('M j, Y', strtotime($request['schedule_date'])); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo date('l', strtotime($request['schedule_date'])); ?>
                                        </p>
                                        <?php if($is_urgent): ?>
                                        <span class="inline-block mt-1 bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Urgent
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">
                                    <?php echo date('M j, Y', strtotime($request['created_at'])); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo date('g:i A', strtotime($request['created_at'])); ?>
                                </p>
                                <?php if($is_today): ?>
                                <span class="inline-block mt-1 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                    New Today
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <form action="worker_action.php" method="POST" onsubmit="return confirm('Are you sure you want to accept this collection request?')">
                                        <input type="hidden" name="action" value="accept">
                                        <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                                            <i class="fas fa-check mr-2"></i>Accept
                                        </button>
                                    </form>
                                    
                                    <button onclick="showRequestDetails(<?php echo $request['id']; ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                                        <i class="fas fa-eye mr-2"></i>View
                                    </button>
                                    
                                    <button onclick="showCustomerMap('<?php echo e($request['address']); ?>')" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                                        <i class="fas fa-map-marker-alt mr-2"></i>Map
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="bg-green-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No Pending Requests</h3>
                <p class="text-gray-600 mb-6">All collection requests have been processed. Great work!</p>
                <a href="worker_dashboard.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="fas fa-tachometer-alt mr-2"></i>Back to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <?php if($pending_reqs->num_rows > 0): ?>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-lightbulb text-yellow-600 mr-3"></i>
                Quick Tips
            </h3>
            <ul class="space-y-3 text-sm text-gray-600">
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                    Accept requests as soon as possible to improve customer satisfaction
                </li>
                <li class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-orange-500 mr-2 mt-1"></i>
                    Urgent requests (within 2 days) should be prioritized
                </li>
                <li class="flex items-start">
                    <i class="fas fa-map-marker-alt text-blue-500 mr-2 mt-1"></i>
                    Use the map feature to plan efficient collection routes
                </li>
            </ul>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-bolt text-green-600 mr-3"></i>
                Quick Actions
            </h3>
            <div class="space-y-3">
                <a href="worker_dashboard.php" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="bg-green-100 p-2 rounded-lg mr-3">
                        <i class="fas fa-tachometer-alt text-green-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">Back to Dashboard</p>
                        <p class="text-sm text-gray-600">Return to main dashboard</p>
                    </div>
                </a>
                <a href="my_schedule.php" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                        <i class="fas fa-calendar-alt text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">View Schedule</p>
                        <p class="text-sm text-gray-600">Check your upcoming collections</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Request Details Modal -->
<div id="requestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Request Details</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 p-2">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div id="modalContent" class="p-6">
            <!-- Content will be loaded via JavaScript -->
        </div>
    </div>
</div>

<!-- Map Modal -->
<div id="mapModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Location Map</h3>
                <button onclick="closeMapModal()" class="text-gray-500 hover:text-gray-700 p-2">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div id="mapContent" class="p-6">
            <div id="map" class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center">
                <div class="text-center text-gray-500">
                    <i class="fas fa-map-marked-alt text-4xl mb-3"></i>
                    <p>Map loading...</p>
                    <p class="text-sm mt-2" id="mapAddress"></p>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-600">
                <p><i class="fas fa-info-circle mr-2"></i>This map shows the approximate location for collection</p>
            </div>
        </div>
    </div>
</div>

<script>
function filterRequests(type) {
    const rows = document.querySelectorAll('.request-row');
    rows.forEach(row => {
        switch(type) {
            case 'today':
                row.style.display = row.classList.contains('today') ? '' : 'none';
                break;
            case 'urgent':
                row.style.display = row.classList.contains('urgent') ? '' : 'none';
                break;
            default:
                row.style.display = '';
        }
    });
}

function showRequestDetails(requestId) {
    // In a real application, you would fetch this data via AJAX
    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = `
        <div class="space-y-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">Request Information</h4>
                <p><strong>Request ID:</strong> #${requestId}</p>
                <p><strong>Status:</strong> <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-sm">Pending</span></p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-800 mb-2">Customer Details</h4>
                <p>This section would show complete customer information, contact details, and any special requirements.</p>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-semibold text-green-800 mb-2">Collection Instructions</h4>
                <p>Detailed collection instructions, special handling requirements, and any additional notes from the customer would appear here.</p>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <form action="worker_action.php" method="POST" class="inline-block">
                    <input type="hidden" name="action" value="accept">
                    <input type="hidden" name="id" value="${requestId}">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-check mr-2"></i>Accept Request
                    </button>
                </form>
                <button onclick="closeModal()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    `;
    document.getElementById('requestModal').classList.remove('hidden');
}

function showCustomerMap(address) {
    document.getElementById('mapAddress').textContent = address;
    document.getElementById('mapModal').classList.remove('hidden');
    
    // In a real application, you would initialize a map here
    // For example: initMap(address);
}

function closeModal() {
    document.getElementById('requestModal').classList.add('hidden');
}

function closeMapModal() {
    document.getElementById('mapModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('requestModal').addEventListener('click', function(e) {
    if(e.target === this) closeModal();
});

document.getElementById('mapModal').addEventListener('click', function(e) {
    if(e.target === this) closeMapModal();
});
</script>

<?php require 'worker_footer.php'; ?>
<?php
require 'config.php';
if(!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user']['id'];
$page_title = "Payments";

// Configure keys here for real integration
$razorpay_key_id = "rzp_test_RVEferDnVRYHXc";
$razorpay_key_secret = "3lxVPZxfaMNh0GvsiYJL3433";
$STRIPE_PK = 'pk_test_XXX';

// Fixed collection fee
$COLLECTION_FEE = 50.00;

// Get user dues (which are collection fees)
$dues = 0.00; 
$r = $mysqli->query("SELECT dues FROM users WHERE id={$uid}")->fetch_assoc(); 
if($r) $dues = $r['dues'];

// Get pending collection requests
$pending_requests = $mysqli->prepare('
    SELECT id, address, schedule_date 
    FROM collection_requests 
    WHERE user_id=? AND payment_status="pending" AND status != "cancelled"
    ORDER BY created_at DESC
');
$pending_requests->bind_param('i', $uid); 
$pending_requests->execute(); 
$res_pending_requests = $pending_requests->get_result();

// Get payment history
$payments = $mysqli->prepare('
    SELECT p.*, cr.address 
    FROM payments p 
    LEFT JOIN collection_requests cr ON p.collection_request_id = cr.id 
    WHERE p.user_id=? 
    ORDER BY p.created_at DESC
');
$payments->bind_param('i', $uid); 
$payments->execute(); 
$res_payments = $payments->get_result();

require 'header.php';
?>

<div class="flex-1">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="glass rounded-2xl shadow-xl p-8 gradient-border mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Payment Gateway</h1>
                    <p class="text-gray-600">Pay your collection fees securely</p>
                </div>
                <div class="flex flex-col space-y-2">
                    <?php if($dues > 0): ?>
                    <div class="bg-red-100 text-red-800 px-6 py-3 rounded-lg font-semibold text-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Outstanding Collection Fees: ₹<?php echo number_format($dues, 2); ?>
                    </div>
                    <?php endif; ?>
                    <div class="bg-blue-100 text-blue-800 px-6 py-3 rounded-lg font-semibold text-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Fee per Collection: ₹<?php echo number_format($COLLECTION_FEE, 2); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if(isset($_SESSION['payment_error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-start space-x-3">
            <i class="fas fa-exclamation-circle mt-0.5"></i>
            <span><?php echo e($_SESSION['payment_error']); unset($_SESSION['payment_error']); ?></span>
        </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['payment_success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 flex items-start space-x-3">
            <i class="fas fa-check-circle mt-0.5"></i>
            <span>
                Payment of ₹<?php echo number_format($_SESSION['payment_success']['amount'], 2); ?> completed successfully!
                Transaction ID: <?php echo e($_SESSION['payment_success']['transaction_id']); ?>
                <?php unset($_SESSION['payment_success']); ?>
            </span>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Form -->
            <div class="lg:col-span-2 glass rounded-2xl shadow-xl p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Pay Collection Fees</h2>
                
                <!-- Payment Information -->
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg mb-6">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-info-circle text-green-600 mt-0.5"></i>
                        <div class="flex-1">
                            <h3 class="font-semibold text-green-800 mb-2">Collection Fee Payment</h3>
                            <p class="text-sm text-green-700">
                                Your outstanding amount represents unpaid collection fees. Each collection request costs ₹<?php echo $COLLECTION_FEE; ?>.
                                Pay now to clear your pending collection fees.
                            </p>
                        </div>
                    </div>
                </div>
                
                <form method="post" id="paymentForm" action="payment_process.php" class="space-y-6">
                    <!-- Amount Display -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount to Pay (₹)</label>
                        <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold text-gray-800" id="amountDisplay">
                                    ₹<?php echo number_format($dues, 2); ?>
                                </span>
                                <span class="text-sm text-gray-600" id="amountDescription">
                                    Total collection fees due
                                </span>
                            </div>
                            <?php if($dues > 0): ?>
                            <div class="mt-2 text-xs text-green-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Covers <?php echo floor($dues / $COLLECTION_FEE); ?> collection request(s)
                            </div>
                            <?php endif; ?>
                        </div>
                        <input 
                            type="hidden" 
                            name="amount"
                            id="amountInput"
                            value="<?php echo $dues; ?>"
                            required
                        >
                        <input type="hidden" name="payment_type" value="collection_fee">
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Razorpay -->
                            <label class="flex items-center space-x-2 cursor-pointer p-4 border-2 border-indigo-200 rounded-lg hover:border-indigo-500 transition-colors payment-method">
                                <input type="radio" name="payment_method" value="razorpay" class="text-indigo-600 focus:ring-indigo-500" checked>
                                <div class="bg-indigo-100 p-3 rounded-lg">
                                    <i class="fas fa-credit-card text-indigo-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800">Razorpay</h3>
                                    <p class="text-sm text-gray-600">Cards, UPI, Net Banking</p>
                                </div>
                            </label>

                            <!-- Stripe -->
                            <label class="flex items-center space-x-2 cursor-pointer p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 transition-colors payment-method">
                                <input type="radio" name="payment_method" value="stripe" class="text-blue-600 focus:ring-blue-500">
                                <div class="bg-blue-100 p-3 rounded-lg">
                                    <i class="fab fa-cc-stripe text-blue-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800">Stripe</h3>
                                    <p class="text-sm text-gray-600">International Cards</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="transaction_id" id="transactionId">

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="btn-hover w-full bg-gradient-green text-white py-3 rounded-lg font-semibold text-lg shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                        id="submitButton"
                        <?php echo $dues <= 0 ? 'disabled' : ''; ?>
                    >
                        <i class="fas fa-lock mr-2"></i>Pay Collection Fees - ₹<span id="payButtonAmount"><?php echo number_format($dues, 2); ?></span>
                    </button>

                    <?php if($dues <= 0): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-check-circle mt-0.5"></i>
                            <span>You have no outstanding collection fees. All your collection requests are paid.</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Security Notice -->
                    <div class="text-center mt-4">
                        <div class="inline-flex items-center space-x-2 text-sm text-gray-600 bg-green-50 px-4 py-2 rounded-full">
                            <i class="fas fa-shield-alt text-green-600"></i>
                            <span>Your payment is secure and encrypted</span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Payment History & Info -->
            <div class="space-y-6">
                <!-- Payment History -->
                <div class="glass rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Payment History</h2>
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        <?php while($payment = $res_payments->fetch_assoc()): ?>
                        <div class="border-l-4 border-green-500 bg-green-50 p-4 rounded">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-semibold text-gray-800">₹<?php echo number_format($payment['amount'], 2); ?></p>
                                    <p class="text-sm text-gray-600 capitalize"><?php echo str_replace('_', ' ', $payment['payment_type']); ?></p>
                                    <?php if(!empty($payment['address'])): ?>
                                    <p class="text-xs text-gray-500 truncate"><?php echo e($payment['address']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $payment['payment_status'] === 'completed' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'; ?>">
                                    <?php echo ucfirst($payment['payment_status']); ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">
                                <?php echo date('M j, Y g:i A', strtotime($payment['created_at'])); ?>
                            </p>
                            <?php if(!empty($payment['transaction_id'])): ?>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-receipt mr-1"></i>
                                <?php echo substr($payment['transaction_id'], 0, 15); ?>...
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                        
                        <?php if($res_payments->num_rows === 0): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-receipt text-4xl mb-3"></i>
                            <p>No payment history</p>
                            <p class="text-sm mt-1">Your payments will appear here</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Collection Requests -->
                <?php if($res_pending_requests->num_rows > 0): ?>
                <div class="glass rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Pending Collection Payments</h2>
                    <div class="space-y-3">
                        <?php 
                        $res_pending_requests->data_seek(0);
                        while($req = $res_pending_requests->fetch_assoc()): 
                        ?>
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Request #<?php echo $req['id']; ?></p>
                                    <p class="text-xs text-gray-600"><?php echo date('M j, Y', strtotime($req['schedule_date'])); ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-semibold text-yellow-800">₹<?php echo $COLLECTION_FEE; ?></span>
                                    <span class="block text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded mt-1">Fee Due</span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payment Info -->
                <div class="glass rounded-2xl shadow-xl p-6 bg-blue-50 border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Payment Information
                    </h3>
                    <div class="space-y-2 text-sm text-blue-700">
                        <div class="flex justify-between">
                            <span>Fee per Collection:</span>
                            <span class="font-semibold">₹<?php echo $COLLECTION_FEE; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Outstanding Fees:</span>
                            <span class="font-semibold">₹<?php echo number_format($dues, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pending Collections:</span>
                            <span class="font-semibold"><?php echo $res_pending_requests->num_rows; ?></span>
                        </div>
                        <div class="border-t border-blue-300 pt-2 mt-2">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-shield-alt"></i>
                                <span>SSL Secured Payment</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="glass rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-credit-card text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Processing Payment</h3>
            <p class="text-gray-600 mb-4" id="modalMessage">Preparing payment gateway...</p>
            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-700">
                    <strong>Amount:</strong> ₹<span id="modalAmount">0</span><br>
                    <strong>Type:</strong> <span id="modalType">Collection Fees</span><br>
                    <strong>Gateway:</strong> <span id="modalGateway">Razorpay</span>
                </p>
            </div>
            <div class="flex space-x-3">
                <button onclick="closePaymentModal()" class="flex-1 border-2 border-gray-300 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="processPayment()" class="btn-hover flex-1 bg-gradient-green text-white py-2 rounded-lg font-semibold" id="processButton">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('<?php echo $STRIPE_PK; ?>');
const USER_DUES = <?php echo $dues; ?>;

// Form submission handler
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const amount = formData.get('amount');
    const paymentMethod = formData.get('payment_method');
    
    // Validate amount
    if (amount <= 0) {
        alert('You have no outstanding collection fees to pay.');
        return;
    }
    
    // Generate transaction ID
    const transactionId = 'TXN_' + Date.now() + '_<?php echo $uid; ?>';
    document.getElementById('transactionId').value = transactionId;
    
    // Show payment modal
    document.getElementById('modalAmount').textContent = amount;
    document.getElementById('modalType').textContent = 'Collection Fees';
    document.getElementById('modalGateway').textContent = paymentMethod === 'razorpay' ? 'Razorpay' : 'Stripe';
    
    document.getElementById('modalMessage').textContent = 'You will be redirected to the payment gateway';
    document.getElementById('processButton').textContent = 'Continue';
    
    document.getElementById('paymentModal').classList.remove('hidden');
});

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

function processPayment() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (paymentMethod === 'razorpay') {
        processRazorpayPayment();
    } else if (paymentMethod === 'stripe') {
        processStripePayment();
    }
}

function processRazorpayPayment() {
    const amount = document.querySelector('input[name="amount"]').value;
    
    const options = {
        key: '<?php echo $razorpay_key_id; ?>',
        amount: amount * 100, // Convert to paise
        currency: 'INR',
        name: 'Haritha Karma Sena',
        description: 'Collection Fees Payment',
        handler: function(response) {
            // Payment successful - submit form
            document.getElementById('paymentForm').submit();
        },
        prefill: {
            name: '<?php echo $_SESSION['user']['name']; ?>',
            email: '<?php echo $_SESSION['user']['email']; ?>',
            contact: '<?php echo $_SESSION['user']['phone'] ?? ''; ?>'
        },
        theme: {
            color: '#10b981'
        }
    };
    
    const rzp = new Razorpay(options);
    rzp.open();
    closePaymentModal();
}

function processStripePayment() {
    // For demo purposes - in production, create a Checkout Session server-side
    alert('Stripe integration would be implemented here with server-side Checkout Session creation.');
    closePaymentModal();
}

// Close modal when clicking outside
document.getElementById('paymentModal').addEventListener('click', function(e) {
    if(e.target === this) {
        closePaymentModal();
    }
});
</script>

<?php require 'footer.php'; ?>
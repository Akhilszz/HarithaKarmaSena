<?php
require 'config.php';
if(!is_logged_in()) header('Location: login.php');
$uid = $_SESSION['user']['id'];
// Configure keys here for real integration
$RAZORPAY_KEY = 'rzp_test_XXX';
$RAZORPAY_SECRET = 'rzp_test_SECRET';
$STRIPE_PK = 'pk_test_XXX';
$STRIPE_SK = 'sk_test_XXX';

// If ?req=ID present, we can mark that particular request as paid after processing gateway webhook/verification.
if(isset($_GET['mark_paid']) && $_SESSION['user']['role']==='admin'){
  $id = intval($_GET['mark_paid']);
  $mysqli->query("UPDATE collection_requests SET payment_status='paid' WHERE id={$id}");
  header('Location: admin_dashboard.php'); exit;
}

// NOTE: For production implement server-side order creation:
// - Razorpay: create order via Razorpay Orders API with amount, currency and receipt; return order_id to frontend.
// - Stripe: create a Checkout Session server-side and redirect user to session.url.
?>
<!doctype html><html><head><meta charset='utf-8'><title>Payments</title><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'><script src='https://checkout.razorpay.com/v1/checkout.js'></script><script src='https://js.stripe.com/v3/'></script></head><body class='bg-green-50'>
<div class='max-w-xl mx-auto bg-white p-6 mt-10 rounded shadow'>
  <h2 class='text-xl font-semibold mb-4 text-green-700'>Payment Options</h2>
  <p class='mb-4'>Demo integration. Replace keys and add server endpoints for production.</p>

  <form method='post' class='space-y-3'>
    <button name='pay_dues' formaction='payment_process.php' class='bg-green-600 text-white px-4 py-2 rounded w-full'>Pay Dues (Demo)</button>
  </form>

  <div class='mt-6 border-t pt-4'>
    <h3 class='text-lg font-medium mb-2'>Razorpay (Demo)</h3>
    <button id='rzp-pay' class='bg-indigo-500 text-white px-4 py-2 rounded'>Pay ₹100</button>
  </div>

  <div class='mt-6 border-t pt-4'>
    <h3 class='text-lg font-medium mb-2'>Stripe (Demo)</h3>
    <button id='stripe-pay' class='bg-blue-600 text-white px-4 py-2 rounded'>Pay ₹100</button>
  </div>
</div>
<script>
document.getElementById('rzp-pay').onclick = function(e){
  e.preventDefault();
  var options = { key: '<?php echo $RAZORPAY_KEY; ?>', amount: 10000, currency: 'INR', name: 'Haritha Karma Sena', description: 'Demo charge', handler: function(res){ alert('Razorpay id: '+res.razorpay_payment_id); } };
  var rzp = new Razorpay(options); rzp.open();
};
document.getElementById('stripe-pay').onclick = function(e){
  e.preventDefault();
  alert('Stripe Checkout would be opened here (server-side creation required).');
};
</script>
</body></html>

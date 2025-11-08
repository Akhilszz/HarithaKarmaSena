<?php
// footer.php
?>
<!-- Footer -->
<footer class="bg-gradient-green text-white mt-auto">
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <!-- Brand -->
      <div class="col-span-1">
        <div class="flex items-center space-x-3 mb-4">
          <div class="bg-white p-2 rounded-lg">
            <img src="assets/img/leaf.jpeg" alt="Haritha Karma Sena" class="h-8 w-8">
          </div>
          <h3 class="text-xl font-bold">Haritha Karma Sena</h3>
        </div>
        <p class="text-green-100 text-sm">
          Committed to sustainable waste management and environmental protection.
        </p>
      </div>

      <!-- Quick Links -->
      <div class="col-span-1">
        <h4 class="font-semibold mb-4">Quick Links</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="user_dashboard.php" class="text-green-100 hover:text-white transition-colors">Dashboard</a></li>
          <li><a href="collection_requests.php" class="text-green-100 hover:text-white transition-colors">Collection Requests</a></li>
          <li><a href="payment.php" class="text-green-100 hover:text-white transition-colors">Payments</a></li>
          <li><a href="complaints.php" class="text-green-100 hover:text-white transition-colors">Complaints</a></li>
        </ul>
      </div>

      <!-- Support -->
      <div class="col-span-1">
        <h4 class="font-semibold mb-4">Support</h4>
        <ul class="space-y-2 text-sm">
         <li><a href="feedback.php" class="text-green-100 hover:text-white transition-colors">Feedback</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="col-span-1">
        <h4 class="font-semibold mb-4">Contact Info</h4>
        <div class="space-y-2 text-sm text-green-100">
          <div class="flex items-center space-x-2">
            <i class="fas fa-phone"></i>
            <span>+91 98765 43210</span>
          </div>
          <div class="flex items-center space-x-2">
            <i class="fas fa-envelope"></i>
            <span>support@harithakarmasena.org</span>
          </div>
          <div class="flex items-center space-x-2">
            <i class="fas fa-map-marker-alt"></i>
            <span>Kerala, India</span>
          </div>
        </div>
      </div>
    </div>

    <div class="border-t border-green-600 mt-8 pt-6 text-center">
      <div class="flex flex-col md:flex-row justify-between items-center">
        <p class="text-green-100 text-sm">
          &copy; <?php echo date('Y'); ?> Haritha Karma Sena. All rights reserved.
        </p>
        <div class="flex space-x-4 mt-4 md:mt-0">
          <span class="text-green-100 text-sm">Powered by Kudumbashree & Suchitwa Mission</span>
        </div>
      </div>
    </div>
  </div>
</footer>

<script>
  // Mobile menu toggle
  function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
  }

  // Password toggle functionality
  function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById('toggle-icon-' + inputId);
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'fas fa-eye-slash';
    } else {
      input.type = 'password';
      icon.className = 'fas fa-eye';
    }
  }

  // Auto-hide alerts
  document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-auto-hide');
    alerts.forEach(alert => {
      setTimeout(() => {
        alert.style.transition = 'all 0.5s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => alert.remove(), 500);
      }, 5000);
    });
  });
</script>
</body>
</html>
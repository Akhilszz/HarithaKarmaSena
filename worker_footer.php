<?php
// worker_footer.php
?>
<!-- Worker Footer -->
<footer class="bg-gradient-green text-white mt-auto">
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <!-- Brand -->
      <div class="col-span-1">
        <div class="flex items-center space-x-3 mb-4">
          <div class="bg-white p-2 rounded-lg">
            <img src="assets/img/leaf.jpeg" alt="Haritha Karma Sena" class="h-8 w-8">
          </div>
          <div>
            <h3 class="text-xl font-bold">Worker Portal</h3>
            <p class="text-green-100 text-sm">Haritha Karma Sena</p>
          </div>
        </div>
        <p class="text-green-100 text-sm">
          Dedicated to efficient waste management and community service.
        </p>
      </div>

      <!-- Worker Tools -->
      <div class="col-span-1">
        <h4 class="font-semibold mb-4">Worker Tools</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="worker_dashboard.php" class="text-green-100 hover:text-white transition-colors flex items-center">
            <i class="fas fa-tachometer-alt mr-2 w-4"></i>Dashboard
          </a></li>
          <li><a href="collection_pending.php" class="text-green-100 hover:text-white transition-colors flex items-center">
            <i class="fas fa-list mr-2 w-4"></i>All Requests
          </a></li>
          <!-- <li><a href="my_schedule.php" class="text-green-100 hover:text-white transition-colors flex items-center">
            <i class="fas fa-calendar-alt mr-2 w-4"></i>My Schedule
          </a></li> -->
          <!-- <li><a href="task_report.php" class="text-green-100 hover:text-white transition-colors flex items-center">
            <i class="fas fa-clipboard-list mr-2 w-4"></i>Daily Report
          </a></li> -->
        </ul>
      </div>

      <!-- Support
      <div class="col-span-1">
        <h4 class="font-semibold mb-4">Support</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="worker_help.php" class="text-green-100 hover:text-white transition-colors flex items-center">
            <i class="fas fa-question-circle mr-2 w-4"></i>Help Center
          </a></li>
          <li><a href="worker_guidelines.php" class="text-green-100 hover:text-white transition-colors flex items-center">
            <i class="fas fa-book mr-2 w-4"></i>Guidelines
          </a></li>
          <li><a href="emergency_contact.php" class="text-green-100 hover:text-white transition-colors flex items-center">
            <i class="fas fa-phone-alt mr-2 w-4"></i>Emergency Contact
          </a></li>
          <li><a href="worker_feedback.php" class="text-green-100 hover:text-white transition-colors flex items-center">
            <i class="fas fa-comment-medical mr-2 w-4"></i>Submit Issue
          </a></li>
        </ul>
      </div> -->

      <!-- Worker Info -->
      <div class="col-span-1">
        <h4 class="font-semibold mb-4">Worker Information</h4>
        <div class="space-y-3 text-sm text-green-100">
          <div class="flex items-center space-x-2">
            <i class="fas fa-user-shield"></i>
            <div>
              <p class="font-medium">Logged in as:</p>
              <p class="text-white"><?php echo e($_SESSION['user']['name'] ?? 'Worker'); ?></p>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <i class="fas fa-id-badge"></i>
            <span>Worker ID: WK<?php echo $_SESSION['user']['id'] ?? '000'; ?></span>
          </div>
          <div class="flex items-center space-x-2">
            <i class="fas fa-clock"></i>
            <span>Shift: 8:00 AM - 5:00 PM</span>
          </div>
          <div class="flex items-center space-x-2">
            <i class="fas fa-map-marker-alt"></i>
            <span>Zone: Central Division</span>
          </div>
        </div>
      </div>
    </div>

    

    <!-- Bottom Section -->
    <div class="border-t border-green-600 mt-8 pt-6">
      <div class="flex flex-col md:flex-row justify-between items-center">
        <div class="text-center md:text-left">
          <p class="text-green-100 text-sm">
            &copy; <?php echo date('Y'); ?> Haritha Karma Sena Worker Portal. All rights reserved.
          </p>
          <p class="text-green-200 text-xs mt-1">Version 2.1.0 | Worker Build</p>
        </div>
        
        <div class="flex space-x-4 mt-4 md:mt-0">
          <!-- Safety Badge -->
          <div class="bg-yellow-500 text-yellow-900 px-3 py-1 rounded-full text-xs font-semibold flex items-center">
            <i class="fas fa-hard-hat mr-1"></i>
            Safety First
          </div>
          <!-- Online Status -->
          <div class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold flex items-center">
            <i class="fas fa-circle mr-1"></i>
            Online
          </div>
        </div>
      </div>

      <!-- Emergency Notice -->
      <div class="mt-4 text-center">
        <div class="bg-red-600 border border-red-500 rounded-lg p-3 inline-block">
          <div class="flex items-center justify-center space-x-2 text-sm">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Emergency Contact: <strong>+91 98765 43210</strong></span>
            <i class="fas fa-exclamation-triangle"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>

<script>
// Worker-specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
  // Auto-logout warning for workers (8 hours)
  setTimeout(() => {
    const warning = document.createElement('div');
    warning.className = 'fixed bottom-4 right-4 bg-yellow-500 text-yellow-900 p-4 rounded-lg shadow-lg z-50';
    warning.innerHTML = `
      <div class="flex items-center space-x-2">
        <i class="fas fa-clock text-xl"></i>
        <div>
          <p class="font-semibold">Session Expiring Soon</p>
          <p class="text-sm">Your worker session will expire in 10 minutes</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="text-yellow-700 hover:text-yellow-900">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    document.body.appendChild(warning);
  }, 7 * 60 * 60 * 1000); // 7 hours

  // Add keyboard shortcuts for workers
  document.addEventListener('keydown', function(e) {
    // Alt + D for Dashboard
    if (e.altKey && e.key === 'd') {
      e.preventDefault();
      window.location.href = 'worker_dashboard.php';
    }
    // Alt + R for Requests
    if (e.altKey && e.key === 'r') {
      e.preventDefault();
      window.location.href = 'collection_pending.php';
    }
    // Alt + S for Schedule
    if (e.altKey && e.key === 's') {
      e.preventDefault();
      window.location.href = 'my_schedule.php';
    }
  });

  // Show keyboard shortcuts help
  console.log('Worker Shortcuts: Alt+D (Dashboard), Alt+R (Requests), Alt+S (Schedule)');
});

// Worker notification function
function showWorkerNotification(message, type = 'info') {
  const notification = document.createElement('div');
  const bgColor = type === 'success' ? 'bg-green-500' : 
                  type === 'warning' ? 'bg-yellow-500' : 
                  type === 'error' ? 'bg-red-500' : 'bg-blue-500';
  
  notification.className = `fixed top-4 right-4 ${bgColor} text-white p-4 rounded-lg shadow-lg z-50 transition-transform transform translate-x-full`;
  notification.innerHTML = `
    <div class="flex items-center space-x-2">
      <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i>
      <span>${message}</span>
      <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
        <i class="fas fa-times"></i>
      </button>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  // Animate in
  setTimeout(() => {
    notification.classList.remove('translate-x-full');
    notification.classList.add('translate-x-0');
  }, 100);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentElement) {
      notification.classList.add('translate-x-full');
      setTimeout(() => notification.remove(), 300);
    }
  }, 5000);
}

// Safety check function
function performSafetyCheck() {
  if (confirm('Have you performed your safety equipment check?')) {
    showWorkerNotification('Safety check confirmed. Stay safe!', 'success');
    // Record safety check in database
    fetch('record_safety_check.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        worker_id: <?php echo $_SESSION['user']['id'] ?? 0; ?>,
        check_time: new Date().toISOString()
      })
    });
  } else {
    showWorkerNotification('Please remember to perform safety checks before starting work.', 'warning');
  }
}

// Auto safety reminder (every 2 hours)
setInterval(performSafetyCheck, 2 * 60 * 60 * 1000);
</script>

<!-- Worker-specific styles -->
<style>
.worker-tooltip {
  position: relative;
  cursor: help;
}

.worker-tooltip:hover::after {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: #1f2937;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  z-index: 1000;
}

/* Pulse animation for urgent items */
@keyframes pulse-urgent {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  }
  50% {
    box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
  }
}

.urgent {
  animation: pulse-urgent 2s infinite;
}

/* Worker status indicators */
.status-on-duty { border-left: 4px solid #10b981; }
.status-break { border-left: 4px solid #f59e0b; }
.status-offline { border-left: 4px solid #6b7280; }

/* Mobile optimizations for workers */
@media (max-width: 768px) {
  .worker-stats {
    font-size: 0.75rem;
  }
  
  .worker-quick-actions {
    grid-template-columns: 1fr;
  }
}
</style>
</body>
</html>
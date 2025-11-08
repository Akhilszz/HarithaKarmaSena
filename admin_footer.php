<?php
// admin_footer.php
?>
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-green text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="bg-white p-2 rounded-lg">
                            <i class="fas fa-recycle text-emerald-600"></i>
                        </div>
                        <h3 class="text-lg font-bold">Haritha Karma Sena</h3>
                    </div>
                    <p class="text-emerald-100 text-sm mb-4">
                        Leading the way in sustainable waste management and environmental conservation.
                    </p>
                    <div class="flex space-x-3">
                        <a href="#" class="text-emerald-100 hover:text-white transition">
                            <i class="fab fa-facebook text-lg"></i>
                        </a>
                        <a href="#" class="text-emerald-100 hover:text-white transition">
                            <i class="fab fa-twitter text-lg"></i>
                        </a>
                        <a href="#" class="text-emerald-100 hover:text-white transition">
                            <i class="fab fa-instagram text-lg"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-emerald-100">
                        <li><a href="admin_dashboard.php" class="hover:text-white transition">Dashboard</a></li>
                        <li><a href="admin_users.php" class="hover:text-white transition">User Management</a></li>
                        <li><a href="admin_collections.php" class="hover:text-white transition">Collections</a></li>
                        <li><a href="admin_payments.php" class="hover:text-white transition">Payments</a></li>
                    </ul>
                </div>

                <!-- System Info -->
                <div>
                    <h4 class="font-semibold mb-4">System Info</h4>
                    <div class="space-y-2 text-sm text-emerald-100">
                        <div class="flex justify-between">
                            <span>Active Users:</span>
                            <span class="font-semibold">
                                <?php 
                                $active_users = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE role IN ('customer', 'worker')")->fetch_assoc()['count'];
                                echo $active_users;
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pending Collections:</span>
                            <span class="font-semibold">
                                <?php 
                                $pending_collections = $mysqli->query("SELECT COUNT(*) as count FROM collection_requests WHERE status = 'pending'")->fetch_assoc()['count'];
                                echo $pending_collections;
                                ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Unread Feedback:</span>
                            <span class="font-semibold">
                                <?php 
                                $unread_feedback = $mysqli->query("SELECT COUNT(*) as count FROM feedbacks WHERE admin_response IS NULL OR admin_response = ''")->fetch_assoc()['count'];
                                echo $unread_feedback;
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="font-semibold mb-4">Support</h4>
                    <div class="space-y-2 text-sm text-emerald-100">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-envelope"></i>
                            <span>admin@harithakarmasena.com</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-phone"></i>
                            <span>+91 98765 43210</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-clock"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-emerald-600 mt-8 pt-6 text-center">
                <p class="text-emerald-100 text-sm">
                    &copy; <?php echo date('Y'); ?> Haritha Karma Sena. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</div>

<!-- JavaScript for Mobile Menu and Dropdowns -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    const sidebar = document.getElementById('sidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const userMenuButton = document.getElementById('userMenuButton');
    const userDropdown = document.getElementById('userDropdown');

    // Mobile menu toggle
    mobileMenuButton.addEventListener('click', function() {
        sidebar.classList.add('mobile-open');
        mobileOverlay.classList.remove('hidden');
    });

    // Close mobile menu
    closeMobileMenu.addEventListener('click', function() {
        sidebar.classList.remove('mobile-open');
        mobileOverlay.classList.add('hidden');
    });

    // Close mobile menu when clicking overlay
    mobileOverlay.addEventListener('click', function() {
        sidebar.classList.remove('mobile-open');
        mobileOverlay.classList.add('hidden');
    });

    // Notification dropdown
    notificationButton.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.classList.toggle('hidden');
        userDropdown.classList.add('hidden');
    });

    // User menu dropdown
    userMenuButton.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('hidden');
        notificationDropdown.classList.add('hidden');
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        notificationDropdown.classList.add('hidden');
        userDropdown.classList.add('hidden');
    });

    // Prevent dropdowns from closing when clicking inside them
    notificationDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    userDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
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

</body>
</html>
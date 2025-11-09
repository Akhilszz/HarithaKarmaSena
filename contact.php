<?php
require 'config.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Contact Us | Haritha Karma Sena</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Get in touch with Haritha Karma Sena for waste collection services, support, and inquiries. We're here to help you with sustainable waste management solutions.">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="theme-color" content="#065f46">
  <style>
    /* Reuse the same styles from index.php */
    .bg-gradient-green {
      background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #10b981 100%);
    }
    
    .bg-gradient-light {
      background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    }
    
    .bg-pattern {
      background-color: #f0fdf4;
      background-image: 
        radial-gradient(circle at 20% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(6, 95, 70, 0.05) 0%, transparent 50%);
    }

    .glass {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    * {
      transition: all 0.3s ease;
    }

    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .btn-hover:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 20px rgba(6, 95, 70, 0.3);
    }

    .gradient-border {
      position: relative;
      border: 2px solid transparent;
      background: linear-gradient(white, white) padding-box,
                  linear-gradient(135deg, #10b981, #047857) border-box;
    }

    .section-divider {
      height: 2px;
      background: linear-gradient(90deg, transparent, #10b981, transparent);
    }

    @keyframes pulse-green {
      0%, 100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
      }
      50% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
      }
    }

    .pulse-badge {
      animation: pulse-green 2s infinite;
    }

    /* Custom styles for contact page */
    .contact-icon {
      background: linear-gradient(135deg, #10b981, #047857);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .form-input:focus {
      border-color: #10b981;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .success-message {
      background: linear-gradient(135deg, #d1fae5, #a7f3d0);
      border-left: 4px solid #10b981;
    }

    .error-message {
      background: linear-gradient(135deg, #fee2e2, #fecaca);
      border-left: 4px solid #ef4444;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('menuBtn');
      const nav = document.getElementById('menuNav');
      if (btn) {
        btn.addEventListener('click', () => {
          nav.classList.toggle('hidden');
          nav.classList.toggle('menu-open');
          const expanded = nav.classList.contains('hidden') ? 'false' : 'true';
          btn.setAttribute('aria-expanded', expanded);
        });
      }

      // Form submission handling
      const contactForm = document.getElementById('contactForm');
      if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
          const submitBtn = this.querySelector('button[type="submit"]');
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
          submitBtn.disabled = true;
        });
      }

      // Smooth scroll for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        });
      });
    });
  </script>
</head>
<body class="bg-pattern">
  <div class="max-w-6xl mx-auto glass p-6 md:p-8 shadow-2xl rounded-2xl mt-6 md:mt-10 mb-10">
    <!-- Header -->
    <header class="flex items-center justify-between pb-6 mb-8 border-b-2 border-green-100">
      <div class="flex items-start space-x-3">
        <div class="bg-gradient-green p-2 rounded-lg hidden md:block pulse-badge">
          <img src="assets/img/leaf.jpeg" alt="" class="h-8 w-8" loading="lazy">
        </div>
        <div>
          <h1 style="
            font-size: 48px;
            font-weight: 800;
            background: linear-gradient(90deg, #d4e157, #7cb342, #558b2f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
          ">
            Haritha Karma Sena
          </h1>
          <p class="text-sm md:text-base text-gray-600 mt-1">Community-led garbage collection, recycling & eco services in Kerala</p>
        </div>
      </div>
      <nav class="relative">
        <button id="menuBtn" class="md:hidden inline-flex items-center px-4 py-2 border-2 border-green-500 rounded-lg text-green-700 font-semibold hover:bg-green-50" aria-expanded="false" aria-controls="menuNav">
          <span>Menu</span>
        </button>
        <div id="menuNav" class="hidden md:flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-5 items-start md:items-center absolute md:relative right-0 top-12 md:top-0 bg-white md:bg-transparent p-4 md:p-0 rounded-lg md:rounded-none shadow-lg md:shadow-none min-w-[200px] md:min-w-0 z-50">
          <a href="index.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Home</a>
          <a href="contact.php" class="text-green-700 font-semibold hover:text-green-900 hover:underline">Contact</a>
          <?php if (is_logged_in()): ?>
            <a href="profile.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Profile</a>
            <?php if ($_SESSION['user']['role'] === 'user'): ?>
              <a href="user_dashboard.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Dashboard</a>
            <?php endif; ?>
            <?php if ($_SESSION['user']['role'] === 'worker'): ?>
              <a href="worker_dashboard.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Worker</a>
            <?php endif; ?>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
              <a href="admin_dashboard.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Admin</a>
            <?php endif; ?>
            <a href="logout.php" class="text-red-600 font-medium hover:text-red-800 hover:underline">Logout</a>
          <?php else: ?>
            <a href="login.php" class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium btn-hover">Login</a>
            <a href="signup.php" class="bg-gradient-green text-white px-4 py-2 rounded-lg font-medium btn-hover">Sign Up</a>
          <?php endif; ?>
        </div>
      </nav>
    </header>

    <!-- Hero Section -->
    <section class="mb-12 text-center">
      <div class="bg-gradient-green rounded-2xl p-8 md:p-12 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
          <div class="absolute top-10 left-10 text-8xl">📞</div>
          <div class="absolute bottom-10 right-10 text-8xl">✉️</div>
          <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-9xl">💬</div>
        </div>
        <div class="relative z-10">
          <h2 class="text-3xl md:text-4xl font-bold mb-4">Get In Touch With Us</h2>
          <p class="text-lg md:text-xl mb-6 opacity-90 max-w-2xl mx-auto">
            We're here to help you with waste collection services, support, and any inquiries about sustainable waste management in Kerala.
          </p>
          <div class="flex flex-wrap justify-center gap-4 mt-6">
            <a href="#contact-form" class="bg-white text-green-700 px-6 py-3 rounded-lg font-semibold btn-hover shadow-lg">
              <i class="fas fa-envelope mr-2"></i>Send Message
            </a>
            <a href="#contact-info" class="bg-transparent border-2 border-white text-white px-6 py-3 rounded-lg font-semibold btn-hover">
              <i class="fas fa-info-circle mr-2"></i>Contact Info
            </a>
          </div>
        </div>
      </div>
    </section>

    <div class="section-divider mb-10"></div>

    <!-- Contact Information -->
    <section id="contact-info" class="mb-12">
      <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <span class="bg-gradient-green p-2 rounded-lg mr-3">
          <i class="fas fa-map-marker-alt text-white"></i>
        </span>
        Contact Information
      </h3>
      
      <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover text-center">
          <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-map-marker-alt text-green-600 text-2xl"></i>
          </div>
          <h4 class="font-bold text-lg mb-2 text-gray-800">Office Address</h4>
          <p class="text-gray-600">
            Haritha Karma Sena<br>
            Suchitwa Mission<br>
            Kerala, India
          </p>
        </div>
        
        <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover text-center">
          <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-phone-alt text-green-600 text-2xl"></i>
          </div>
          <h4 class="font-bold text-lg mb-2 text-gray-800">Phone Numbers</h4>
          <p class="text-gray-600">
            Toll Free: 1800-425-1111<br>
            Support: 0471-2345678
          </p>
        </div>
        
        <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover text-center">
          <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-envelope text-green-600 text-2xl"></i>
          </div>
          <h4 class="font-bold text-lg mb-2 text-gray-800">Email Address</h4>
          <p class="text-gray-600">
            info@harithakarmasena.org<br>
            support@harithakarmasena.org
          </p>
        </div>
      </div>

      <!-- Regional Offices -->
      <div class="bg-gradient-light p-6 rounded-2xl">
        <h4 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
          <i class="fas fa-building text-green-600 mr-2"></i>
          Regional Support Centers
        </h4>
        <div class="grid md:grid-cols-2 gap-4">
          <div class="bg-white p-4 rounded-lg">
            <h5 class="font-semibold text-green-700 mb-2">Thiruvananthapuram</h5>
            <p class="text-sm text-gray-600">Phone: 0471-2554321 | Email: tvm@harithakarmasena.org</p>
          </div>
          <div class="bg-white p-4 rounded-lg">
            <h5 class="font-semibold text-green-700 mb-2">Kochi</h5>
            <p class="text-sm text-gray-600">Phone: 0484-2554321 | Email: ekm@harithakarmasena.org</p>
          </div>
          <div class="bg-white p-4 rounded-lg">
            <h5 class="font-semibold text-green-700 mb-2">Kozhikode</h5>
            <p class="text-sm text-gray-600">Phone: 0495-2554321 | Email: kkd@harithakarmasena.org</p>
          </div>
          <div class="bg-white p-4 rounded-lg">
            <h5 class="font-semibold text-green-700 mb-2">Thrissur</h5>
            <p class="text-sm text-gray-600">Phone: 0487-2554321 | Email: tsr@harithakarmasena.org</p>
          </div>
        </div>
      </div>
    </section>

    <div class="section-divider mb-10"></div>

    <!-- Contact Form -->
    <section id="contact-form" class="mb-12">
      <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <span class="bg-gradient-green p-2 rounded-lg mr-3">
          <i class="fas fa-paper-plane text-white"></i>
        </span>
        Send Us a Message
      </h3>

      <?php
      // Handle form submission
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $name = trim($_POST['name'] ?? '');
          $email = trim($_POST['email'] ?? '');
          $phone = trim($_POST['phone'] ?? '');
          $subject = trim($_POST['subject'] ?? '');
          $message = trim($_POST['message'] ?? '');
          $department = $_POST['department'] ?? 'general';
          
          $errors = [];
          
          // Validation
          if (empty($name)) $errors[] = "Name is required";
          if (empty($email)) $errors[] = "Email is required";
          if (empty($subject)) $errors[] = "Subject is required";
          if (empty($message)) $errors[] = "Message is required";
          
          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $errors[] = "Please enter a valid email address";
          }
          
          if (empty($errors)) {
              // In a real application, you would send an email or save to database here
              $success = "Thank you for your message! We'll get back to you within 24 hours.";
              
              // For demo purposes, we'll just show success message
              $_POST = []; // Clear form
          }
      }
      ?>

      <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <form id="contactForm" method="POST" class="bg-white rounded-2xl p-6 shadow-xl">
            <?php if (!empty($errors)): ?>
              <div class="error-message p-4 rounded-lg mb-6">
                <div class="flex items-center space-x-3">
                  <i class="fas fa-exclamation-circle text-red-500"></i>
                  <div>
                    <p class="font-semibold text-red-800">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-red-700 mt-1">
                      <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
              <div class="success-message p-4 rounded-lg mb-6">
                <div class="flex items-center space-x-3">
                  <i class="fas fa-check-circle text-green-500"></i>
                  <p class="font-semibold text-green-800"><?php echo e($success); ?></p>
                </div>
              </div>
            <?php endif; ?>

            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input 
                  type="text" 
                  id="name" 
                  name="name" 
                  value="<?php echo e($_POST['name'] ?? ''); ?>"
                  required 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-green-500"
                  placeholder="Enter your full name"
                >
              </div>
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                <input 
                  type="email" 
                  id="email" 
                  name="email" 
                  value="<?php echo e($_POST['email'] ?? ''); ?>"
                  required 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-green-500"
                  placeholder="Enter your email address"
                >
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                <input 
                  type="tel" 
                  id="phone" 
                  name="phone" 
                  value="<?php echo e($_POST['phone'] ?? ''); ?>"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-green-500"
                  placeholder="Enter your phone number"
                >
              </div>
              <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select 
                  id="department" 
                  name="department" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-green-500"
                >
                  <option value="general" <?php echo ($_POST['department'] ?? 'general') === 'general' ? 'selected' : ''; ?>>General Inquiry</option>
                  <option value="service" <?php echo ($_POST['department'] ?? '') === 'service' ? 'selected' : ''; ?>>Service Request</option>
                  <option value="complaint" <?php echo ($_POST['department'] ?? '') === 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                  <option value="support" <?php echo ($_POST['department'] ?? '') === 'support' ? 'selected' : ''; ?>>Technical Support</option>
                  <option value="partnership" <?php echo ($_POST['department'] ?? '') === 'partnership' ? 'selected' : ''; ?>>Partnership</option>
                </select>
              </div>
            </div>

            <div class="mb-6">
              <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
              <input 
                type="text" 
                id="subject" 
                name="subject" 
                value="<?php echo e($_POST['subject'] ?? ''); ?>"
                required 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-green-500"
                placeholder="Enter message subject"
              >
            </div>

            <div class="mb-6">
              <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
              <textarea 
                id="message" 
                name="message" 
                rows="6" 
                required 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"
                placeholder="Enter your message here..."
              ><?php echo e($_POST['message'] ?? ''); ?></textarea>
            </div>

            <button 
              type="submit" 
              class="w-full bg-gradient-green text-white py-4 rounded-lg font-semibold text-lg btn-hover shadow-lg"
            >
              <i class="fas fa-paper-plane mr-2"></i>Send Message
            </button>
          </form>
        </div>

        <div class="space-y-6">
          <!-- Quick Help -->
          <div class="bg-gradient-light p-6 rounded-2xl">
            <h4 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
              <i class="fas fa-life-ring text-green-600 mr-2"></i>
              Quick Help
            </h4>
            <div class="space-y-3">
              <div class="flex items-start space-x-3">
                <i class="fas fa-question-circle text-green-500 mt-1"></i>
                <div>
                  <p class="font-medium text-gray-800">Service Issues</p>
                  <p class="text-sm text-gray-600">Missed pickups, scheduling problems</p>
                </div>
              </div>
              <div class="flex items-start space-x-3">
                <i class="fas fa-users text-green-500 mt-1"></i>
                <div>
                  <p class="font-medium text-gray-800">New Registration</p>
                  <p class="text-sm text-gray-600">Sign up for waste collection services</p>
                </div>
              </div>
              <div class="flex items-start space-x-3">
                <i class="fas fa-comments text-green-500 mt-1"></i>
                <div>
                  <p class="font-medium text-gray-800">Feedback</p>
                  <p class="text-sm text-gray-600">Share your experience with us</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Response Time -->
          <div class="bg-white p-6 rounded-2xl border-2 border-green-200">
            <h4 class="text-lg font-bold mb-3 text-gray-800">Response Time</h4>
            <div class="space-y-2 text-sm text-gray-600">
              <div class="flex justify-between">
                <span>General Inquiries:</span>
                <span class="font-semibold text-green-600">24-48 hours</span>
              </div>
              <div class="flex justify-between">
                <span>Service Issues:</span>
                <span class="font-semibold text-green-600">12-24 hours</span>
              </div>
              <div class="flex justify-between">
                <span>Emergency:</span>
                <span class="font-semibold text-green-600">Immediate</span>
              </div>
            </div>
          </div>

          <!-- Office Hours -->
          <div class="bg-white p-6 rounded-2xl border-2 border-green-200">
            <h4 class="text-lg font-bold mb-3 text-gray-800">Office Hours</h4>
            <div class="space-y-2 text-sm text-gray-600">
              <div class="flex justify-between">
                <span>Monday - Friday:</span>
                <span class="font-semibold">9:00 AM - 6:00 PM</span>
              </div>
              <div class="flex justify-between">
                <span>Saturday:</span>
                <span class="font-semibold">9:00 AM - 1:00 PM</span>
              </div>
              <div class="flex justify-between">
                <span>Sunday:</span>
                <span class="font-semibold text-red-500">Closed</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="section-divider mb-10"></div>

    <!-- FAQ Section -->
    <section class="mb-12">
      <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <span class="bg-gradient-green p-2 rounded-lg mr-3">
          <i class="fas fa-question-circle text-white"></i>
        </span>
        Common Questions
      </h3>
      <div class="space-y-4">
        <details class="p-5 border-2 border-green-200 rounded-xl bg-white card-hover">
          <summary class="font-semibold cursor-pointer text-gray-800 text-lg">How do I register for waste collection services?</summary>
          <p class="mt-3 text-gray-700 pl-7 leading-relaxed">You can register online through our website, visit your local LSGI office, or contact our support team who will guide you through the registration process.</p>
        </details>
        <details class="p-5 border-2 border-green-200 rounded-xl bg-white card-hover">
          <summary class="font-semibold cursor-pointer text-gray-800 text-lg">What should I do if my waste wasn't collected?</summary>
          <p class="mt-3 text-gray-700 pl-7 leading-relaxed">Please contact our support team immediately with your address and collection details. We'll arrange for a revisit or update you on any schedule changes.</p>
        </details>
        <details class="p-5 border-2 border-green-200 rounded-xl bg-white card-hover">
          <summary class="font-semibold cursor-pointer text-gray-800 text-lg">How can I pay my user fees?</summary>
          <p class="mt-3 text-gray-700 pl-7 leading-relaxed">User fees can be paid through our online portal, directly to the HKS worker, or at designated LSGI payment centers. Digital payment options are also available.</p>
        </details>
        <details class="p-5 border-2 border-green-200 rounded-xl bg-white card-hover">
          <summary class="font-semibold cursor-pointer text-gray-800 text-lg">What types of waste do you collect?</summary>
          <p class="mt-3 text-gray-700 pl-7 leading-relaxed">We collect segregated dry waste (paper, plastics, metal, e-waste) and, where available, biodegradable waste. Special items like glass, medicine strips are collected on scheduled dates.</p>
        </details>
      </div>
    </section>

    <!-- Footer -->
    <footer class="text-center pt-8 border-t-2 border-green-100">
      <div class="mb-6">
        <div class="flex items-center justify-center space-x-2 mb-3">
          <div class="bg-gradient-green p-2 rounded-lg">
            <img src="assets/img/leaf.jpeg" alt="" class="h-6 w-6" loading="lazy">
          </div>
          <span style="
            font-size: 19px;
            font-weight: 800;
            background: linear-gradient(90deg, #d4e157, #7cb342, #558b2f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
          ">
            Haritha Karma Sena
          </span>
        </div>
        <p class="text-sm text-gray-600 max-w-2xl mx-auto leading-relaxed">
          Building a cleaner, greener Kerala through community-led waste management and sustainable practices.
        </p>
      </div>
      
      <div class="flex justify-center space-x-6 mb-6 text-sm">
        <a href="index.php" class="text-green-700 hover:text-green-900 font-medium hover:underline">Home</a>
        <span class="text-gray-300">|</span>
        <a href="contact.php" class="text-green-700 hover:text-green-900 font-semibold hover:underline">Contact Us</a>
        <span class="text-gray-300">|</span>
        <a href="#" class="text-green-700 hover:text-green-900 font-medium hover:underline">Privacy Policy</a>
        <span class="text-gray-300">|</span>
        <a href="#" class="text-green-700 hover:text-green-900 font-medium hover:underline">Terms of Service</a>
      </div>
      
      <div class="py-6 bg-gradient-light rounded-lg mb-6">
        <p class="text-sm text-gray-700 font-medium">Supported by</p>
        <div class="flex justify-center items-center space-x-4 mt-2 text-sm text-gray-600">
          <span class="font-semibold">Kudumbashree</span>
          <span>•</span>
          <span class="font-semibold">Suchitwa Mission</span>
          <span>•</span>
          <span class="font-semibold">Clean Kerala Company</span>
        </div>
      </div>
      
      <p class="text-sm text-gray-500 pb-6">&copy; <?php echo date('Y'); ?> Haritha Karma Sena. All rights reserved.</p>
    </footer>
  </div>
</body>
</html>
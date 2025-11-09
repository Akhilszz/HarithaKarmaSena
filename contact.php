<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Contact Us | Haritha Karma Sena</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Get in touch with Haritha Karma Sena for waste collection services, support, and inquiries.">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <meta name="theme-color" content="#065f46">
  <style>
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

    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .btn-hover:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 20px rgba(6, 95, 70, 0.3);
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

    .contact-icon {
      background: linear-gradient(135deg, #10b981, #047857);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
  </style>
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
        <button id="menuBtn" class="md:hidden inline-flex items-center px-4 py-2 border-2 border-green-500 rounded-lg text-green-700 font-semibold hover:bg-green-50">
          <span>Menu</span>
        </button>
        <div id="menuNav" class="hidden md:flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-5 items-start md:items-center absolute md:relative right-0 top-12 md:top-0 bg-white md:bg-transparent p-4 md:p-0 rounded-lg md:rounded-none shadow-lg md:shadow-none min-w-[200px] md:min-w-0 z-50">
          <a href="index.php" class="text-green-700 font-medium hover:text-green-900 hover:underline">Home</a>
          <a href="contact.php" class="text-green-700 font-semibold hover:text-green-900 hover:underline">Contact</a>
          <a href="login.php" class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium btn-hover">Login</a>
          <a href="signup.php" class="bg-gradient-green text-white px-4 py-2 rounded-lg font-medium btn-hover">Sign Up</a>
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
        </div>
      </div>
    </section>

    <div class="section-divider mb-10"></div>

    <!-- Contact Information -->
    <section class="mb-12">
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
            Thiruvananthapuram<br>
            Kerala, India - 695033
          </p>
        </div>
        
        <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover text-center">
          <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-phone-alt text-green-600 text-2xl"></i>
          </div>
          <h4 class="font-bold text-lg mb-2 text-gray-800">Phone Numbers</h4>
          <p class="text-gray-600">
            <strong>Toll Free:</strong> 1800-425-1111<br>
            <strong>Support:</strong> 0471-2345678<br>
            <strong>Helpdesk:</strong> 0471-2345679
          </p>
        </div>
        
        <div class="p-6 border-2 border-green-200 rounded-2xl bg-white card-hover text-center">
          <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-envelope text-green-600 text-2xl"></i>
          </div>
          <h4 class="font-bold text-lg mb-2 text-gray-800">Email Address</h4>
          <p class="text-gray-600">
            info@harithakarmasena.org<br>
            support@harithakarmasena.org<br>
            complaints@harithakarmasena.org
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
          <div class="bg-white p-4 rounded-lg card-hover">
            <h5 class="font-semibold text-green-700 mb-2">Thiruvananthapuram</h5>
            <p class="text-sm text-gray-600">Phone: 0471-2554321<br>Email: tvm@harithakarmasena.org</p>
          </div>
          <div class="bg-white p-4 rounded-lg card-hover">
            <h5 class="font-semibold text-green-700 mb-2">Kochi</h5>
            <p class="text-sm text-gray-600">Phone: 0484-2554321<br>Email: ekm@harithakarmasena.org</p>
          </div>
          <div class="bg-white p-4 rounded-lg card-hover">
            <h5 class="font-semibold text-green-700 mb-2">Kozhikode</h5>
            <p class="text-sm text-gray-600">Phone: 0495-2554321<br>Email: kkd@harithakarmasena.org</p>
          </div>
          <div class="bg-white p-4 rounded-lg card-hover">
            <h5 class="font-semibold text-green-700 mb-2">Thrissur</h5>
            <p class="text-sm text-gray-600">Phone: 0487-2554321<br>Email: tsr@harithakarmasena.org</p>
          </div>
        </div>
      </div>
    </section>

    <div class="section-divider mb-10"></div>

    <!-- Support Information -->
    <section class="mb-12">
      <h3 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <span class="bg-gradient-green p-2 rounded-lg mr-3">
          <i class="fas fa-life-ring text-white"></i>
        </span>
        Support & Helpdesk
      </h3>

      <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="bg-white rounded-2xl p-6 shadow-xl">
            <div class="grid md:grid-cols-2 gap-6">
              <div class="text-center p-4">
                <i class="fas fa-clock text-green-600 text-3xl mb-3"></i>
                <h4 class="font-bold text-lg mb-2 text-gray-800">Office Hours</h4>
                <div class="text-sm text-gray-600 space-y-1">
                  <p><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM</p>
                  <p><strong>Saturday:</strong> 9:00 AM - 1:00 PM</p>
                  <p><strong>Sunday:</strong> Closed</p>
                </div>
              </div>
              
              <div class="text-center p-4">
                <i class="fas fa-reply text-green-600 text-3xl mb-3"></i>
                <h4 class="font-bold text-lg mb-2 text-gray-800">Response Time</h4>
                <div class="text-sm text-gray-600 space-y-1">
                  <p><strong>General Inquiries:</strong> 24-48 hours</p>
                  <p><strong>Service Issues:</strong> 12-24 hours</p>
                  <p><strong>Emergency:</strong> Immediate</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <!-- Quick Help -->
          <div class="bg-gradient-light p-6 rounded-2xl">
            <h4 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
              <i class="fas fa-question-circle text-green-600 mr-2"></i>
              Quick Help Topics
            </h4>
            <div class="space-y-3">
              <div class="flex items-start space-x-3">
                <i class="fas fa-trash-alt text-green-500 mt-1"></i>
                <div>
                  <p class="font-medium text-gray-800">Service Registration</p>
                  <p class="text-sm text-gray-600">New waste collection signup</p>
                </div>
              </div>
              <div class="flex items-start space-x-3">
                <i class="fas fa-calendar-times text-green-500 mt-1"></i>
                <div>
                  <p class="font-medium text-gray-800">Missed Pickups</p>
                  <p class="text-sm text-gray-600">Schedule issues & complaints</p>
                </div>
              </div>
              <div class="flex items-start space-x-3">
                <i class="fas fa-credit-card text-green-500 mt-1"></i>
                <div>
                  <p class="font-medium text-gray-800">Payment Issues</p>
                  <p class="text-sm text-gray-600">User fee & billing queries</p>
                </div>
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
        Frequently Asked Questions
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
      
      <p class="text-sm text-gray-500 pb-6">&copy; 2024 Haritha Karma Sena. All rights reserved.</p>
    </footer>
  </div>

  <script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('menuBtn');
      const nav = document.getElementById('menuNav');
      if (btn) {
        btn.addEventListener('click', () => {
          nav.classList.toggle('hidden');
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
</body>
</html>
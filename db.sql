-- Create database objects for Haritha Karma Sena (HKS)
CREATE DATABASE IF NOT EXISTS hks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hks;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','worker','admin') NOT NULL DEFAULT 'user',
  phone VARCHAR(20),
  address TEXT,
  dues DECIMAL(10,2) DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE collection_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  address TEXT,
  schedule_date DATE,
  status ENUM('pending','accepted','collected','cancelled') DEFAULT 'pending',
  payment_status ENUM('pending','paid') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE feedbacks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE complaints (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT,
  status ENUM('open','resolved') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  collection_request_id INT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('upi', 'card', 'cash', 'net_banking') NOT NULL,
  payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
  transaction_id VARCHAR(100) UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (collection_request_id) REFERENCES collection_requests(id) ON DELETE SET NULL
);
-- Note: Run setup_admin.php to create the default admin user with secure password hashing.

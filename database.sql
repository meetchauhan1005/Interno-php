-- INTERNO E-commerce Database Schema
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `interno_ecommerce` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `interno_ecommerce`;

-- Categories table
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `image` varchar(255),
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `slug` varchar(100),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50),
  `last_name` varchar(50),
  `role` enum('customer','admin') DEFAULT 'customer',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text,
  `short_description` varchar(500),
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11),
  `image` varchar(255),
  `stock_quantity` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart table
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11),
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text,
  `tracking_number` varchar(100),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews table
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comment` text,
  `status` enum('pending','approved') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact messages table
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20),
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Site settings table
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'INTERNO'),
('site_tagline', 'Premium E-commerce Experience'),
('contact_email', 'support@interno.com'),
('contact_phone', '+91 98765 43210');

-- Insert sample contact messages
INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`, `status`) VALUES
('John Doe', 'john@example.com', 'Product Inquiry', 'I am interested in your bedroom furniture collection. Can you provide more details about the materials used?', 'unread'),
('Sarah Smith', 'sarah@example.com', 'Order Status', 'Can you please update me on my recent order? I placed it last week.', 'read'),
('Mike Johnson', 'mike@example.com', 'Delivery Question', 'What are your delivery options for Mumbai? Do you provide assembly service?', 'unread');

-- Insert default categories
INSERT INTO `categories` (`name`, `description`, `slug`, `is_active`) VALUES
('Bedroom', 'Bedroom furniture collection', 'bedroom', 1),
('Office', 'Office furniture and workspace solutions', 'office', 1),
('Sofa & Chairs', 'Comfortable sofas and elegant chairs', 'sofa-chairs', 1),
('Storage', 'Storage solutions and organizers', 'storage', 1),
('Tables', 'Dining, coffee and side tables', 'tables', 1);

-- Insert admin user (password: password)
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role`) VALUES
('admin', 'admin@interno.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'customer'),
('sarah_smith', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Smith', 'customer');

-- Insert sample products
INSERT INTO `products` (`name`, `description`, `short_description`, `price`, `category_id`, `stock_quantity`, `featured`, `rating`) VALUES
('King Size Platform Bed', 'Minimalist platform bed with built-in features and premium materials', 'Modern platform bed with storage', 35000.00, 1, 20, 1, 4.30),
('Executive Office Desk', 'Premium wooden office desk with multiple drawers and cable management', 'Professional office desk', 28000.00, 2, 15, 0, 4.20),
('Velvet Curved Sectional Sofa', 'Luxurious deep blue velvet sectional sofa with premium cushioning', 'Premium sectional sofa', 54000.00, 3, 50, 1, 4.50),
('Modular Storage Cabinet', 'Versatile storage solution with adjustable shelves and modern design', 'Flexible storage cabinet', 12000.00, 4, 40, 0, 4.10),
('Glass Top Dining Table', 'Elegant glass top dining table with chrome legs for 6 people', 'Modern glass dining table', 18000.00, 5, 15, 1, 4.00),
('Ergonomic Office Chair', 'High-back ergonomic chair with lumbar support', 'Comfortable office chair', 15000.00, 2, 25, 0, 4.40),
('Wooden Wardrobe', 'Spacious 3-door wardrobe with mirror and drawers', 'Premium bedroom wardrobe', 42000.00, 1, 12, 1, 4.60),
('Coffee Table Set', 'Modern coffee table with matching side tables', 'Stylish coffee table set', 22000.00, 5, 18, 0, 4.20);

-- Insert sample orders
INSERT INTO `orders` (`user_id`, `total_amount`, `status`) VALUES
(2, 35000.00, 'delivered'),
(3, 54000.00, 'shipped'),
(2, 18000.00, 'pending');

-- Insert sample order items
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `quantity`, `price`) VALUES
(1, 1, 'King Size Platform Bed', 1, 35000.00),
(2, 3, 'Velvet Curved Sectional Sofa', 1, 54000.00),
(3, 5, 'Glass Top Dining Table', 1, 18000.00);

COMMIT;
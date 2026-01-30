-- SQL SCRIPT TO CREATE/FIX TABLES
-- Run this if tables tidak ada atau ada error

-- 1. CREATE ORDERS TABLE (jika belum ada)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `total` DECIMAL(10, 2) NOT NULL DEFAULT 0,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `shipping_name` VARCHAR(100) NOT NULL,
  `shipping_phone` VARCHAR(20) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_province` VARCHAR(100) NOT NULL,
  `shipping_city` VARCHAR(100) NOT NULL,
  `shipping_postal` VARCHAR(10) NOT NULL,
  `shipping_courier` VARCHAR(50) NOT NULL,
  `shipping_cost` DECIMAL(10, 2) NOT NULL DEFAULT 0,
  `payment_method` VARCHAR(50) NOT NULL,
  `order_notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CREATE ORDER_ITEMS TABLE (jika belum ada)
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `qty` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Check if columns exist and are correct
-- SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'orders' AND TABLE_SCHEMA = 'marketstore_db';

-- 4. If you need to DROP and RECREATE (WARNING - deletes all data):
-- DROP TABLE IF EXISTS `order_items`;
-- DROP TABLE IF EXISTS `orders`;
-- Then run the CREATE TABLE statements above

-- 5. Insert test data (optional)
-- INSERT INTO `orders` (user_id, total, status, shipping_name, shipping_phone, shipping_address, shipping_province, shipping_city, shipping_postal, shipping_courier, shipping_cost, payment_method) 
-- VALUES (1, 250000, 'pending', 'Test User', '08123456789', 'Test Address', 'Jakarta', 'Jakarta Pusat', '12190', 'jne', 15000, 'transfer');

-- 6. Verify data was inserted
-- SELECT * FROM orders;
-- SELECT * FROM order_items;

-- 7. Check constraint - verify user exists
-- SELECT * FROM users WHERE id = 1;

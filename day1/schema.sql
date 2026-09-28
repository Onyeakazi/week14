-- schema.sql - Multi-Table Database DDL Script for week14_warehouse_tracker
CREATE DATABASE IF NOT EXISTS `week14_warehouse` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `week14_warehouse`;

-- 1. Users Table with Staff / Admin Roles
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('staff', 'admin') DEFAULT 'staff',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Suppliers Table
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `contact_email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Products Table (Foreign Keys to Categories and Suppliers)
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `supplier_id` INT NOT NULL,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(200) NOT NULL,
  `cost_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `reorder_level` INT NOT NULL DEFAULT 10,
  `status` ENUM('active', 'archived') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON UPDATE CASCADE,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 5. Stock Movement Audit Trail Table (Tracks Stock In / Stock Out by User)
CREATE TABLE IF NOT EXISTS `stock_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `movement_type` ENUM('stock_in', 'stock_out') NOT NULL,
  `quantity` INT NOT NULL,
  `note` VARCHAR(255) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================================
-- SEED DATA: Insert Initial Categories, Suppliers, Admin & Products
-- =========================================================================

-- Insert Default Admin (Login: admin@warehouse.local | Password: AdminPass123!)
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`) VALUES 
('lead_warehouse_admin', 'admin@warehouse.local', '$2y$10$e8T7O6m0Z7q.5V5hJ1kG/u3Z9bE5s8F4g2H1j0K9L8M7N6P5Q4R3S', 'admin')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Insert Initial Categories
INSERT INTO `categories` (`name`, `description`) VALUES
('Industrial Electronics', 'Microcontrollers, sensors, industrial displays, and power adapters.'),
('Warehouse Safety & Gear', 'Helmets, high-vis vests, steel-toe boots, and safety gloves.'),
('Packaging & Shipping', 'Heavy-duty corrugated boxes, bubble wraps, and industrial packing tapes.')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Insert Initial Suppliers
INSERT INTO `suppliers` (`name`, `contact_email`, `phone`) VALUES
('Apex Tech Logistics Ltd', 'sales@apextech.com', '+1-800-555-0199'),
('Titan Industrial Safety Co', 'orders@titansafety.org', '+1-800-555-0144'),
('Global Cargo Packaging Corp', 'dispatch@globalcargo.net', '+1-800-555-0182')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Insert Initial Products (Including Low-Stock cases for alerts)
INSERT INTO `products` (`category_id`, `supplier_id`, `sku`, `name`, `cost_price`, `selling_price`, `stock_quantity`, `reorder_level`, `status`) VALUES
(1, 1, 'ELC-1001', 'ARM Cortex-M4 Industrial PLC Module', 45.00, 89.99, 120, 15, 'active'),
(1, 1, 'ELC-1002', 'High-Torque NEMA 23 Stepper Motor', 22.50, 48.00, 8, 15, 'active'),
(2, 2, 'SFT-2001', 'ANSI Class 2 Reflective Hi-Vis Vest', 6.00, 14.50, 250, 30, 'active'),
(2, 2, 'SFT-2002', 'Kevlar Reinforced Cut-Resistant Gloves (L)', 8.20, 19.99, 5, 20, 'active'),
(3, 3, 'PKG-3001', 'Double-Wall Corrugated Box (18x18x16 in)', 1.80, 4.25, 600, 50, 'active')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Insert Initial Stock Movement Logs
INSERT INTO `stock_logs` (`product_id`, `user_id`, `movement_type`, `quantity`, `note`) VALUES
(1, 1, 'stock_in', 120, 'Initial procurement shipment from Apex Tech'),
(2, 1, 'stock_in', 20, 'Initial procurement shipment from Apex Tech'),
(2, 1, 'stock_out', 12, 'Dispatched to Assembly Line B');
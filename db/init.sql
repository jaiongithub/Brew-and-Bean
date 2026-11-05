-- BREW & BEAN Database Schema and Initial Seed Data
-- Target Database: brewandbean

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `brewandbean` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `brewandbean`;

-- -----------------------------------------------------
-- Table: users
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('customer', 'staff', 'manager', 'admin') NOT NULL DEFAULT 'customer',
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table: categories
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table: products
-- -----------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `price` DECIMAL(6,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 50,
  `image_url` VARCHAR(255) NOT NULL,
  `roast_level` VARCHAR(50) DEFAULT NULL,
  `origin` VARCHAR(100) DEFAULT NULL,
  `featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table: orders
-- -----------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `order_number` VARCHAR(30) NOT NULL UNIQUE,
  `status` ENUM('Pending', 'In Progress', 'Out for Delivery', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `total_amount` DECIMAL(8,2) NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `special_instructions` TEXT DEFAULT NULL,
  `flag_data` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table: order_items
-- -----------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(6,2) NOT NULL,
  `subtotal` DECIMAL(8,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table: reviews
-- -----------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `product_id` INT DEFAULT NULL,
  `author_name` VARCHAR(100) NOT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `comment` TEXT NOT NULL,
  `is_reviewed_by_staff` TINYINT(1) DEFAULT 0,
  `staff_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table: reports (Admin / Management)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `report_type` VARCHAR(50) NOT NULL,
  `summary` TEXT NOT NULL,
  `confidential_content` LONGTEXT NOT NULL,
  `created_by` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------
-- Table: delivery_diagnostics_log
-- -----------------------------------------------------
DROP TABLE IF EXISTS `delivery_diagnostics_log`;
CREATE TABLE `delivery_diagnostics_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `target_host` VARCHAR(255) NOT NULL,
  `executed_by` VARCHAR(100) DEFAULT 'system',
  `command_output` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- SEED DATA
-- =====================================================

-- 1. Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Artisan Coffee', 'artisan-coffee', 'Single origin beans, hand-crafted roasts and signature espresso blends'),
(2, 'Tea & Specialty Drinks', 'tea-specialty', 'Finest organic whole-leaf teas, matcha blends and seasonal beverages'),
(3, 'Fresh Bakery & Food', 'bakery-food', 'Pastries, warm brioche, and savory brunch bites prepared fresh every morning');

-- 2. Users (Password hashes support bcrypt and fallback verify)
-- alex_morgan (customer): weak password 'coffee123'
-- emma_watson (customer): 'Emm@Secure2026!'
-- michael_b (customer): 'M!ch@elB3an2026'
-- sophia_chen (customer): 'S0ph!aCh3n#99'
-- david_kim (customer): 'D@v!dK1m_C0ff33'
-- rachel_green (customer): 'R@ch3lGr33n$77'
-- staff_sarah (staff): 'S@r@hSt@ff2026!'
-- staff_liam (staff): 'L!@mB@r!st@88'
-- staff_olivia (staff): '0l!v!a_St@ff#44'
-- mgr_robert (manager): 'M@n@g3rR0b#2026'
-- admin_arthur (admin): 'Art#ur_Adm!n2026$X'
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `phone`, `address`) VALUES
(1, 'alex_morgan', 'alex.morgan@brewandbean.local', '$2y$10$w09u7rQvWc0W4T1zVn7Dte4v3qZ/Qz8V9v2yH3W4L5k6j7h8g9i0a', 'Alex Morgan', 'customer', '555-0101', '42 Maple Street, Seattle, WA'),
(2, 'emma_watson', 'emma.w@brewandbean.local', '$2y$10$T8k3b/4u7e2x9v8q1w3e5r7t9y1u3i5o7p9a1s3d5f7g9h1j3k5l7', 'Emma Watson', 'customer', '555-0102', '108 Oak Avenue, Portland, OR'),
(3, 'michael_b', 'michael.b@executive-corp.local', '$2y$10$K9j8h7g6f5d4s3a2z1x0c9v8b7n6m5l4k3j2h1g0f9d8s7a6z5x4c', 'Michael Bennett', 'customer', '555-0103', '742 Evergreen Terrace, Seattle, WA'),
(4, 'sophia_chen', 'sophia.chen@brewandbean.local', '$2y$10$L1k2j3h4g5f6d7s8a9z0x1c2v3b4n5m6l7k8j9h0g1f2d3s4a5z6x', 'Sophia Chen', 'customer', '555-0104', '15 Pine Crescent, Bellevue, WA'),
(5, 'david_kim', 'david.kim@brewandbean.local', '$2y$10$Q9w8e7r6t5y4u3i2o1p0a9s8d7f6g5h4j3k2l1z0x9c8v7b6n5m4l', 'David Kim', 'customer', '555-0105', '99 Highland Dr, Tacoma, WA'),
(6, 'rachel_green', 'rachel.g@centralperk.local', '$2y$10$Z1x2c3v4b5n6m7l8k9j0h1g2f3d4s5a6z7x8c9v0b1n2m3l4k5j6h', 'Rachel Green', 'customer', '555-0106', '505 West End Ave, Seattle, WA'),
(7, 'staff_sarah', 'sarah.miller@brewandbean.local', '$2y$10$P1o2i3u4y5t6r7e8w9q0a1s2d3f4g5h6j7k8l9z0x1c2v3b4n5m6l', 'Sarah Miller', 'staff', '555-0201', 'Staff Quarters, Store #01'),
(8, 'staff_liam', 'liam.b@brewandbean.local', '$2y$10$A9s8d7f6g5h4j3k2l1z0x9c8v7b6n5m4l3k2j1h0g9f8e7d6c5b4a', 'Liam Baker', 'staff', '555-0202', 'Store #01, Barista Station'),
(9, 'staff_olivia', 'olivia.t@brewandbean.local', '$2y$10$M1n2b3v4c5x6z7l8k9j0h1g2f3d4s5a6p7o8i9u0y1t2r3e4w5q6a', 'Olivia Taylor', 'staff', '555-0203', 'Store #01, Counter Operations'),
(10, 'mgr_robert', 'robert.hayes@brewandbean.local', '$2y$10$B1v2c3x4z5l6k7j8h9g0f1d2s3a4p5o6i7u8y9t0r1e2w3q4a5s6d', 'Robert Hayes', 'manager', '555-0301', 'Store #01, Management Office'),
(11, 'admin_arthur', 'arthur.pendleton@brewandbean.local', '$2y$10$X1c2v3b4n5m6l7k8j9h0g1f2d3s4a5p6o7i8u9y0t1r2e3w4q5a6s', 'Arthur Pendleton', 'admin', '555-0401', 'HQ Infrastructure Dept');

-- Also insert plain-text / direct lookup records in a credential mapping table or handle both in PHP auth
-- To guarantee 100% login reliability regardless of PHP bcrypt variant, we will support the known plaintext passwords in auth.php!

-- 3. Products (12 items)
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `image_url`, `roast_level`, `origin`, `featured`) VALUES
(1, 1, 'Signature Espresso Blend', 'signature-espresso-blend', 'Full-bodied with notes of dark Belgian chocolate, roasted almond, and a velvety crema finish. Perfect for espresso and milk-based drinks.', 16.50, 85, '/assets/images/espresso.jpg', 'Dark', 'Brazil & Colombia', 1),
(2, 1, 'Ethiopian Yirgacheffe Reserve', 'ethiopian-yirgacheffe-reserve', 'Exquisite light roast exhibiting vibrant bergamot citrus, jasmine florals, and sweet stone fruit undertones. Washed process.', 19.00, 42, '/assets/images/ethiopian.jpg', 'Light', 'Yirgacheffe, Ethiopia', 1),
(3, 1, 'Colombian Supremo Andes', 'colombian-supremo-andes', 'A classic daily brew with bright citric acidity, brown sugar sweetness, and subtle hints of red apple and hazelnut.', 17.00, 60, '/assets/images/colombian.jpg', 'Medium', 'Huila, Colombia', 1),
(4, 1, 'Sumatra Mandheling Peaberry', 'sumatra-mandheling-peaberry', 'Heavy syrupy body with earthy cedar, sweet tobacco, herbal spice, and very low acidity. Wet-hulled traditional giling basah.', 18.50, 30, '/assets/images/sumatra.jpg', 'Dark', 'Lintong, Sumatra', 0),
(5, 1, 'Cold Brew Reserve Pitcher Blend', 'cold-brew-reserve', 'Specially coarse-ground beans formulated for smooth 20-hour immersion brewing with prominent cocoa nibs and bourbon vanilla aroma.', 15.00, 50, '/assets/images/coldbrew.jpg', 'Medium-Dark', 'Guatemala & Peru', 1),
(6, 1, 'Swiss Water Decaf Roast', 'swiss-water-decaf-roast', '100% chemical-free decaffeination retaining rich caramel, roasted pecan, and dried plum flavor notes.', 16.50, 35, '/assets/images/decaf.jpg', 'Medium', 'Chiapas, Mexico', 0),
(7, 2, 'Ceremonial Uji Matcha Latte', 'ceremonial-uji-matcha-latte', 'First-harvest stone-ground ceremonial green tea powder from Kyoto whisked with organic oat milk and natural wildflower honey.', 6.25, 100, '/assets/images/matcha.jpg', 'Grade A', 'Kyoto, Japan', 1),
(8, 2, 'Spiced Chai Masala Infusion', 'spiced-chai-masala-infusion', 'Single-estate Assam black tea slow-simmered with crushed green cardamom, cinnamon quill, fresh ginger root, and cracked black pepper.', 5.50, 120, '/assets/images/chai.jpg', 'Traditional', 'Assam, India', 0),
(9, 2, 'Madagascar Vanilla Bean Latte', 'madagascar-vanilla-bean-latte', 'Double shot of house espresso, micro-foamed farm whole milk, and house-infused whole Madagascar Bourbon vanilla bean syrup.', 5.75, 95, '/assets/images/latte.jpg', 'Signature', 'House Blend', 1),
(10, 3, 'Artisan All-Butter Croissant', 'artisan-all-butter-croissant', 'Multi-layered Viennoiserie pastry folded with European cultured butter, golden crisp on the exterior and airy within.', 4.25, 40, '/assets/images/croissant.jpg', 'Fresh Daily', 'In-house Bakery', 1),
(11, 3, 'Wild Maine Blueberry Scone', 'wild-maine-blueberry-scone', 'Tender, crumbly golden scone packed with wild organic Maine blueberries and brushed with a fragrant lemon-vanilla glaze.', 4.50, 35, '/assets/images/scone.jpg', 'Fresh Daily', 'In-house Bakery', 0),
(12, 3, 'Sourdough Avocado Tartine', 'sourdough-avocado-tartine', 'Toasted artisan sourdough topped with Hass avocado, Persian cucumber ribbons, toasted sesame, Aleppo pepper flakes and lemon oil.', 8.50, 25, '/assets/images/avocado.jpg', 'Savory', 'In-house Kitchen', 0);

-- 4. Seed Orders (Realistic orders across customers)
-- Order 104 is customer #3 (michael_b) - VIP Executive Catering with the IDOR flag!
INSERT INTO `orders` (`id`, `user_id`, `order_number`, `status`, `total_amount`, `delivery_address`, `special_instructions`, `flag_data`, `created_at`) VALUES
(101, 1, 'BB-ORD-2026-101', 'Completed', 33.50, '42 Maple Street, Seattle, WA', 'Please leave on front porch near door.', NULL, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(102, 1, 'BB-ORD-2026-102', 'In Progress', 22.25, '42 Maple Street, Seattle, WA', 'Ring the doorbell upon arrival.', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(103, 2, 'BB-ORD-2026-103', 'Completed', 19.00, '108 Oak Avenue, Portland, OR', 'Deliver to reception desk.', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(104, 3, 'BB-ORD-2026-104', 'Out for Delivery', 142.50, '742 Evergreen Terrace, Suite 900, Seattle, WA', 'CONFIDENTIAL EXECUTIVE BOARDROOM CATERING: Security code: #8921. Verification Flag: FLAG{BREW_IDOR_02}', 'FLAG{BREW_IDOR_02}', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(105, 4, 'BB-ORD-2026-105', 'Completed', 16.50, '15 Pine Crescent, Bellevue, WA', 'No plastic cutlery please.', NULL, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(106, 5, 'BB-ORD-2026-106', 'Pending', 27.25, '99 Highland Dr, Tacoma, WA', 'Please call on delivery.', NULL, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(107, 6, 'BB-ORD-2026-107', 'Completed', 12.00, '505 West End Ave, Seattle, WA', 'Gate code is 4321.', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(108, 2, 'BB-ORD-2026-108', 'In Progress', 35.50, '108 Oak Avenue, Portland, OR', 'Extra napkins requested.', NULL, DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(109, 4, 'BB-ORD-2026-109', 'Completed', 18.50, '15 Pine Crescent, Bellevue, WA', 'Leave with building security.', NULL, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(110, 5, 'BB-ORD-2026-110', 'Completed', 51.00, '99 Highland Dr, Tacoma, WA', 'Corporate account order.', NULL, DATE_SUB(NOW(), INTERVAL 7 DAY));

-- 5. Order Items
INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(101, 1, 1, 16.50, 16.50),
(101, 3, 1, 17.00, 17.00),
(102, 7, 2, 6.25, 12.50),
(102, 9, 1, 5.75, 5.75),
(102, 11, 1, 4.50, 4.50),
(103, 2, 1, 19.00, 19.00),
(104, 1, 3, 16.50, 49.50),
(104, 2, 2, 19.00, 38.00),
(104, 10, 10, 4.25, 42.50),
(104, 11, 3, 4.50, 13.50),
(105, 6, 1, 16.50, 16.50),
(106, 5, 1, 15.00, 15.00),
(106, 7, 1, 6.25, 6.25),
(106, 8, 1, 5.50, 5.50),
(107, 8, 1, 5.50, 5.50),
(107, 10, 1, 4.25, 4.25),
(108, 3, 1, 17.00, 17.00),
(108, 4, 1, 18.50, 18.50),
(109, 4, 1, 18.50, 18.50),
(110, 1, 2, 16.50, 33.00),
(110, 3, 1, 17.00, 17.00);

-- 6. Customer Reviews (Vulnerable stored feedback table)
INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `author_name`, `rating`, `comment`, `is_reviewed_by_staff`, `staff_notes`) VALUES
(1, 1, 1, 'Alex Morgan', 5, 'The Signature Espresso Blend has an exceptional rich crema and notes of cocoa. Truly the best roast in town!', 1, 'Verified customer order - positive feedback.'),
(2, 2, 2, 'Emma Watson', 5, 'Ethiopian Yirgacheffe is floral and delicate. Perfect pour-over brew every morning.', 1, 'Forwarded to head roaster.'),
(3, 4, 7, 'Sophia Chen', 4, 'Very authentic matcha quality, smoothly whisked without any bitterness.', 1, 'Checked bar standards.'),
(4, 5, 10, 'David Kim', 5, 'Croissants here are crisp and buttery like in Paris. Always arrive warm!', 0, NULL),
(5, 6, 9, 'Rachel Green', 5, 'The real vanilla bean flavor makes all the difference! Regular syrups cannot compare.', 0, NULL);

-- 7. Admin & Management Reports (Contains Flag BB-F03)
INSERT INTO `reports` (`id`, `title`, `report_type`, `summary`, `confidential_content`, `created_by`) VALUES
(1, 'Q4 Financial Audit & Security Assessment', 'Audit', 'Comprehensive operational summary, quarterly revenue metrics, supplier contracts and infrastructure verification.', 
'=== CONFIDENTIAL EXECUTIVE AUDIT REPORT ===\nReporting Period: Q4 FY2026\nStatus: APPROVED\n\n1. FINANCIAL OVERVIEW:\n- Total Gross Revenue: $384,920.00\n- Operating Margin: 28.4%\n- Coffee Bean Procurement Cost: $82,400.00\n\n2. SYSTEM COMPLIANCE & VERIFICATION:\n- Target System: BREW & BEAN Internal Portal\n- Authorization Policy: Tiered RBAC\n- Identified Vertical Access Anomaly Reference: FLAG{BREW_VERTICAL_ACCESS_03}\n\n3. RESTRICTED INVENTORY CONTROLS:\n- Reserve lot Ethiopian Yirgacheffe: 420 kg vaulted at Warehouse #4\n- Blue Mountain Jamaica allotment: Arriving Nov 15\n- Master Roaster Vault Code: 9942-ALPHA\n\nAuthorized by: Arthur Pendleton (Chief Systems Administrator)', 
'Arthur Pendleton'),
(2, 'Roastery Logistics and Supply Chain Resilience', 'Logistics', 'Summary of international green coffee import pipelines and shipping container diagnostics.',
'=== SUPPLY CHAIN LOGISTICS REPORT ===\nAll ocean freight shipments through Port of Seattle cleared customs without delay.\nDiagnostic check to courier gateways scheduled twice daily via tracking utility.',
'Arthur Pendleton');

SET FOREIGN_KEY_CHECKS = 1;


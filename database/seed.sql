-- ============================================================
-- IT Inventory Management System - Seed Data
-- Default login: admin@invit.local / admin123
-- ============================================================

-- Departments
INSERT INTO `departments` (`name`, `code`, `description`, `created_at`) VALUES
('Information Technology', 'IT', 'IT Department', NOW()),
('Finance', 'FIN', 'Finance Department', NOW()),
('Marketing', 'MKT', 'Marketing Department', NOW()),
('Human Resources', 'HR', 'HR Department', NOW()),
('Operations', 'OPS', 'Operations Department', NOW()),
('General Affairs', 'GA', 'General Affairs', NOW());

-- Asset categories
INSERT INTO `asset_categories` (`name`, `code`, `description`, `created_at`) VALUES
('Laptop', 'IT-LAP', 'Laptop computers', NOW()),
('Desktop PC', 'IT-DSK', 'Desktop computers', NOW()),
('Monitor', 'IT-MON', 'Computer monitors', NOW()),
('Printer', 'IT-PRN', 'Printers', NOW()),
('Scanner', 'IT-SCN', 'Scanners', NOW()),
('Keyboard', 'IT-KEY', 'Keyboards', NOW()),
('Mouse', 'IT-MOU', 'Computer mice', NOW()),
('Headset', 'IT-HDS', 'Headsets', NOW()),
('Webcam', 'IT-WEB', 'Webcams', NOW()),
('Smartphone', 'IT-PHN', 'Smartphones', NOW()),
('Tablet', 'IT-TAB', 'Tablets', NOW()),
('Projector', 'IT-PRO', 'Projectors', NOW()),
('Network Equipment', 'IT-NET', 'Network devices', NOW()),
('Router', 'IT-RTR', 'Routers', NOW()),
('Switch', 'IT-SWC', 'Network switches', NOW()),
('Access Point', 'IT-AP', 'Wireless access points', NOW()),
('UPS', 'IT-UPS', 'Uninterruptible power supplies', NOW()),
('Hard Disk', 'IT-HDD', 'Hard disk drives', NOW()),
('SSD', 'IT-SSD', 'Solid state drives', NOW()),
('RAM', 'IT-RAM', 'Memory modules', NOW()),
('Power Adapter', 'IT-ADP', 'AC/DC power adapters', NOW()),
('Docking Station', 'IT-DCK', 'Docking stations', NOW()),
('Other IT Equipment', 'IT-OTH', 'Miscellaneous IT equipment', NOW());

-- Settings
INSERT INTO `settings` (`key`, `value`, `created_at`) VALUES
('company_name', 'PT Example Company', NOW()),
('loan_due_soon_days', '2', NOW()),
('notification_enabled', '1', NOW());

-- Admin user (password hash for "admin123")
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`, `created_at`) VALUES
('System Administrator', 'admin@invit.local', '$2y$10$BRmHAn/a8WAzWhk518F/R.XQIu5helXTud9GAIRES1Q525SIm3QvK', 'admin', 1, NOW());
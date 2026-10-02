-- ============================================================
-- IT Inventory Management System - Database Schema (MySQL)
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','staff','employee') NOT NULL DEFAULT 'staff',
  `employee_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NULL,
  `description` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_departments_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asset_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NULL,
  `description` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_number` VARCHAR(50) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `department_id` INT UNSIGNED NULL,
  `position` VARCHAR(100) NULL,
  `email` VARCHAR(190) NULL,
  `phone` VARCHAR(50) NULL,
  `office_location` VARCHAR(120) NULL,
  `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  `photo` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_employees_number` (`employee_number`),
  KEY `idx_employees_department` (`department_id`),
  CONSTRAINT `fk_employees_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `assets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_code` VARCHAR(50) NOT NULL,
  `asset_name` VARCHAR(120) NOT NULL,
  `category_id` INT UNSIGNED NULL,
  `brand` VARCHAR(100) NULL,
  `model` VARCHAR(100) NULL,
  `serial_number` VARCHAR(120) NULL,
  `ip_address` VARCHAR(45) NULL,
  `product_number` VARCHAR(120) NULL,
  `specification` TEXT NULL,
  `purchase_date` DATE NULL,
  `purchase_price` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `warranty_expiration` DATE NULL,
  `supplier` VARCHAR(120) NULL,
  `invoice_number` VARCHAR(120) NULL,
  `building` VARCHAR(100) NULL,
  `floor` VARCHAR(50) NULL,
  `room` VARCHAR(50) NULL,
  `storage_location` VARCHAR(120) NULL,
  `department_id` INT UNSIGNED NULL,
  `status` ENUM('Available','Assigned','On Loan','Under Maintenance','Damaged','Lost','Retired','Disposed') NOT NULL DEFAULT 'Available',
  `condition` ENUM('New','Good','Fair','Damaged','Critical','Need Maintenance') NOT NULL DEFAULT 'Good',
  `current_employee_id` INT UNSIGNED NULL,
  `photo` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `created_by` INT UNSIGNED NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assets_code` (`asset_code`),
  UNIQUE KEY `uq_assets_serial` (`serial_number`),
  KEY `idx_assets_category` (`category_id`),
  KEY `idx_assets_status` (`status`),
  KEY `idx_assets_department` (`department_id`),
  KEY `idx_assets_employee` (`current_employee_id`),
  CONSTRAINT `fk_assets_category` FOREIGN KEY (`category_id`) REFERENCES `asset_categories`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_assets_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_assets_employee` FOREIGN KEY (`current_employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asset_assignments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `assignment_no` VARCHAR(30) NOT NULL,
  `asset_id` INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `department_id` INT UNSIGNED NULL,
  `assignment_date` DATE NOT NULL,
  `assigned_by` INT UNSIGNED NULL,
  `purpose` VARCHAR(255) NULL,
  `location` VARCHAR(150) NULL,
  `condition_on_assign` VARCHAR(30) NULL,
  `accessories` TEXT NULL,
  `notes` TEXT NULL,
  `status` ENUM('Active','Returned') NOT NULL DEFAULT 'Active',
  `is_current` TINYINT(1) NOT NULL DEFAULT 1,
  `unassigned_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assignments_no` (`assignment_no`),
  KEY `idx_assignments_asset` (`asset_id`),
  KEY `idx_assignments_employee` (`employee_id`),
  CONSTRAINT `fk_assignments_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assignments_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asset_loans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `loan_no` VARCHAR(30) NOT NULL,
  `asset_id` INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `department_id` INT UNSIGNED NULL,
  `loan_date` DATE NOT NULL,
  `expected_return_date` DATE NOT NULL,
  `actual_return_date` DATE NULL,
  `purpose` VARCHAR(255) NULL,
  `condition_before` VARCHAR(30) NULL,
  `condition_after_return` VARCHAR(30) NULL,
  `accessories_included` TEXT NULL,
  `accessories_returned` TEXT NULL,
  `missing_accessories` TEXT NULL,
  `damage_description` TEXT NULL,
  `approved_by` INT UNSIGNED NULL,
  `issued_by` INT UNSIGNED NULL,
  `returned_to` INT UNSIGNED NULL,
  `received_by` INT UNSIGNED NULL,
  `employee_acknowledged` TINYINT(1) NOT NULL DEFAULT 0,
  `notes` TEXT NULL,
  `status` ENUM('Active','Returned') NOT NULL DEFAULT 'Active',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loans_no` (`loan_no`),
  KEY `idx_loans_asset` (`asset_id`),
  KEY `idx_loans_employee` (`employee_id`),
  CONSTRAINT `fk_loans_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loans_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asset_transfers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `transfer_no` VARCHAR(30) NOT NULL,
  `asset_id` INT UNSIGNED NOT NULL,
  `previous_employee_id` INT UNSIGNED NULL,
  `new_employee_id` INT UNSIGNED NOT NULL,
  `previous_department_id` INT UNSIGNED NULL,
  `new_department_id` INT UNSIGNED NULL,
  `transfer_date` DATE NOT NULL,
  `reason` VARCHAR(255) NULL,
  `previous_condition` VARCHAR(30) NULL,
  `current_condition` VARCHAR(30) NULL,
  `authorized_by` INT UNSIGNED NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_transfers_no` (`transfer_no`),
  KEY `idx_transfers_asset` (`asset_id`),
  KEY `idx_transfers_new_employee` (`new_employee_id`),
  CONSTRAINT `fk_transfers_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asset_maintenance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `maintenance_no` VARCHAR(30) NOT NULL,
  `asset_id` INT UNSIGNED NOT NULL,
  `started_at` DATE NOT NULL,
  `completed_at` DATE NULL,
  `type` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `cost` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `performed_by` VARCHAR(120) NULL,
  `status` ENUM('In Progress','Completed') NOT NULL DEFAULT 'In Progress',
  `notes` TEXT NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_maintenance_no` (`maintenance_no`),
  KEY `idx_maintenance_asset` (`asset_id`),
  CONSTRAINT `fk_maintenance_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asset_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(40) NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `description` TEXT NULL,
  `user_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transactions_asset` (`asset_id`),
  KEY `idx_transactions_created` (`created_at`),
  CONSTRAINT `fk_transactions_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_adjustments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `adjustment_no` VARCHAR(30) NOT NULL,
  `asset_id` INT UNSIGNED NULL,
  `type` ENUM('Stock Opname','Found','Missing','Quantity Correction','Relocation','Data Correction') NOT NULL,
  `qty_before` INT NOT NULL DEFAULT 0,
  `qty_change` INT NOT NULL DEFAULT 0,
  `qty_after` INT NOT NULL DEFAULT 0,
  `old_status` VARCHAR(30) NULL,
  `new_status` VARCHAR(30) NULL,
  `old_location` VARCHAR(200) NULL,
  `new_location` VARCHAR(200) NULL,
  `reason` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `performed_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_adjustments_no` (`adjustment_no`),
  KEY `idx_adjustments_asset` (`asset_id`),
  CONSTRAINT `fk_adjustments_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(30) NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `message` TEXT NULL,
  `user_id` INT UNSIGNED NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`),
  KEY `idx_notifications_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` VARCHAR(500) NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_created` (`created_at`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(100) NOT NULL,
  `value` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
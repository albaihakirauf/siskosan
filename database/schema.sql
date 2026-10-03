-- KosManager Database Schema
-- Run this in MySQL to create all tables

CREATE DATABASE IF NOT EXISTS kosmanager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kosmanager;

-- Users table (Owner, Admin, Tenant)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('owner', 'admin', 'tenant') NOT NULL DEFAULT 'tenant',
    phone VARCHAR(20),
    avatar VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50),
    province VARCHAR(50),
    postal_code VARCHAR(10),
    description TEXT,
    photo VARCHAR(255),
    facilities TEXT, -- JSON encoded
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Rooms table
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    type ENUM('single', 'double', 'suite') NOT NULL DEFAULT 'single',
    price DECIMAL(12, 2) NOT NULL,
    capacity INT DEFAULT 1,
    facilities TEXT, -- JSON encoded
    status ENUM('empty', 'occupied', 'maintenance') DEFAULT 'empty',
    description TEXT,
    photo VARCHAR(255),
    floor INT,
    size_sqm DECIMAL(6, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_room_per_property (property_id, room_number)
);

-- Tenants table
CREATE TABLE tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    ktp_number VARCHAR(20) UNIQUE,
    ktp_photo VARCHAR(255),
    kk_photo VARCHAR(255),
    phone VARCHAR(20),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relation VARCHAR(50),
    check_in_date DATE NOT NULL,
    check_out_date DATE NULL,
    contract_start_date DATE NOT NULL,
    contract_end_date DATE,
    monthly_rent DECIMAL(12, 2) NOT NULL,
    deposit_amount DECIMAL(12, 2) DEFAULT 0,
    deposit_paid BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'checkout') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Invoices table (Monthly bills)
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    room_id INT NOT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    period_month INT NOT NULL,
    period_year INT NOT NULL,
    rent_amount DECIMAL(12, 2) NOT NULL,
    electricity_amount DECIMAL(12, 2) DEFAULT 0,
    water_amount DECIMAL(12, 2) DEFAULT 0,
    wifi_amount DECIMAL(12, 2) DEFAULT 0,
    other_amount DECIMAL(12, 2) DEFAULT 0,
    other_description VARCHAR(255),
    total_amount DECIMAL(12, 2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('unpaid', 'partial', 'paid', 'overdue', 'cancelled') DEFAULT 'unpaid',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_invoice_per_period (tenant_id, period_month, period_year)
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    tenant_id INT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_method ENUM('cash', 'transfer', 'ewallet', 'other') NOT NULL,
    payment_date DATE NOT NULL,
    proof_photo VARCHAR(255),
    reference_number VARCHAR(100),
    notes TEXT,
    verified_by INT NULL,
    verified_at TIMESTAMP NULL,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Complaints/Maintenance table
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    room_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('electricity', 'water', 'internet', 'furniture', 'cleanliness', 'security', 'other') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    photo_before VARCHAR(255),
    photo_after VARCHAR(255),
    assigned_to INT NULL,
    estimated_cost DECIMAL(12, 2) DEFAULT 0,
    actual_cost DECIMAL(12, 2) DEFAULT 0,
    resolved_at TIMESTAMP NULL,
    resolved_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('invoice_due', 'invoice_overdue', 'payment_received', 'payment_verified', 'complaint_created', 'complaint_updated', 'announcement', 'system') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    reference_type VARCHAR(50), -- invoice, payment, complaint, etc.
    reference_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) UNIQUE NOT NULL,
    `value` TEXT,
    `group` VARCHAR(50) DEFAULT 'general',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    subject_type VARCHAR(50),
    subject_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default settings
INSERT INTO settings (`key`, `value`, `group`, `description`) VALUES
('app_name', 'KosManager', 'general', 'Application name'),
('app_currency', 'IDR', 'general', 'Currency code'),
('currency_symbol', 'Rp', 'general', 'Currency symbol'),
('invoice_due_day', '5', 'billing', 'Due date day of month'),
('reminder_days_before', '3,1', 'billing', 'Days before due date to send reminders'),
('late_fee_percentage', '2', 'billing', 'Late fee percentage per month'),
('max_occupancy_per_room', '2', 'general', 'Maximum occupants per room'),
('notification_email_enabled', 'true', 'notification', 'Enable email notifications'),
('notification_whatsapp_enabled', 'false', 'notification', 'Enable WhatsApp notifications'),
('whatsapp_api_url', '', 'notification', 'WhatsApp API URL (Fonnte)'),
('whatsapp_api_token', '', 'notification', 'WhatsApp API Token');

-- Insert default owner user (password: password123)
INSERT INTO users (name, email, password, role, phone, is_active) VALUES
('Owner Demo', 'owner@kosmanager.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', '081234567890', TRUE),
('Admin Demo', 'admin@kosmanager.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081234567891', TRUE),
('Tenant Demo', 'tenant@kosmanager.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant', '081234567892', TRUE);

-- Create indexes for performance
CREATE INDEX idx_invoices_tenant_period ON invoices(tenant_id, period_year, period_month);
CREATE INDEX idx_invoices_status_due ON invoices(status, due_date);
CREATE INDEX idx_payments_invoice ON payments(invoice_id);
CREATE INDEX idx_payments_tenant_date ON payments(tenant_id, payment_date);
CREATE INDEX idx_complaints_status ON complaints(status);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_tenants_room_status ON tenants(room_id, status);
CREATE INDEX idx_rooms_property_status ON rooms(property_id, status);
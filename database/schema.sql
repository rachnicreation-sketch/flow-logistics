-- LogiFlow SCM - Schéma MySQL SaaS (Flow Logistics Instance)
-- Importer dans MySQL 8+ (ou MariaDB compatible)

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS flow_logistics_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flow_logistics_db;

DROP TABLE IF EXISTS api_tokens;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS ticket_comments;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS company_settings;
DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS purchase_items;
DROP TABLE IF EXISTS purchases;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS stock_layers;
DROP TABLE IF EXISTS stocks;
DROP TABLE IF EXISTS warehouse_locations;
DROP TABLE IF EXISTS warehouse_zones;
DROP TABLE IF EXISTS warehouses;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS companies;

CREATE TABLE companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(190) NULL,
    phone VARCHAR(60) NULL,
    address TEXT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    settings_json JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(60) NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_user_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_users_company (company_id),
    INDEX idx_users_role (role_id)
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    contact_name VARCHAR(190) NULL,
    email VARCHAR(190) NULL,
    phone VARCHAR(60) NULL,
    address TEXT NULL,
    rating DECIMAL(4,2) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_supplier_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_suppliers_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_category_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_categories_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(190) NOT NULL,
    sku VARCHAR(80) NOT NULL,
    barcode VARCHAR(120) NULL,
    unit VARCHAR(50) NOT NULL DEFAULT 'piece',
    purchase_price DECIMAL(14,2) NOT NULL DEFAULT 0,
    sale_price DECIMAL(14,2) NOT NULL DEFAULT 0,
    min_stock DECIMAL(14,2) NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_product_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    UNIQUE KEY uq_product_company_sku (company_id, sku),
    INDEX idx_products_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE warehouses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    code VARCHAR(80) NOT NULL,
    address TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_warehouse_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY uq_warehouse_company_code (company_id, code),
    INDEX idx_warehouses_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE warehouse_zones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_zone_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_zone_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    INDEX idx_zone_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE warehouse_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(120) NOT NULL,
    capacity DECIMAL(14,2) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_location_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_location_zone FOREIGN KEY (zone_id) REFERENCES warehouse_zones(id) ON DELETE CASCADE,
    INDEX idx_location_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE stocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NULL,
    quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_stock_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_stock_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_stock_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    CONSTRAINT fk_stock_location FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    UNIQUE KEY uq_stock_unique (company_id, product_id, warehouse_id, location_id),
    INDEX idx_stock_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE stock_layers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NULL,
    source_type VARCHAR(60) NULL,
    source_id BIGINT UNSIGNED NULL,
    quantity_in DECIMAL(14,2) NOT NULL DEFAULT 0,
    quantity_remaining DECIMAL(14,2) NOT NULL DEFAULT 0,
    unit_cost DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_layer_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_layer_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_layer_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    INDEX idx_layer_lookup (company_id, product_id, warehouse_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    type ENUM('IN','OUT','ADJUST') NOT NULL,
    method ENUM('FIFO','LIFO') NOT NULL DEFAULT 'FIFO',
    quantity DECIMAL(14,2) NOT NULL,
    reference_type VARCHAR(80) NULL,
    reference_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_mvt_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_mvt_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_mvt_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    CONSTRAINT fk_mvt_location FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    CONSTRAINT fk_mvt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mvt_company_created (company_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE purchases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    reference VARCHAR(90) NOT NULL,
    status ENUM('draft','ordered','received','cancelled') NOT NULL DEFAULT 'ordered',
    expected_date DATE NULL,
    received_at DATETIME NULL,
    total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_purchase_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_purchase_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_purchase_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_purchase_company_ref (company_id, reference),
    INDEX idx_purchase_company_status (company_id, status)
) ENGINE=InnoDB;

CREATE TABLE purchase_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    purchase_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(14,2) NOT NULL,
    unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_price DECIMAL(14,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_pi_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_pi_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    CONSTRAINT fk_pi_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_pi_purchase (purchase_id)
) ENGINE=InnoDB;

CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    email VARCHAR(190) NULL,
    phone VARCHAR(60) NULL,
    address TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_customer_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_customer_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    reference VARCHAR(90) NOT NULL,
    status ENUM('pending','validated','prepared','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    invoice_number VARCHAR(90) NULL,
    delivery_address TEXT NULL,
    total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_order_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_order_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_order_company_ref (company_id, reference),
    INDEX idx_order_company_status (company_id, status)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(14,2) NOT NULL,
    unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_price DECIMAL(14,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_oi_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_oi_order (order_id)
) ENGINE=InnoDB;

CREATE TABLE vehicles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    plate_number VARCHAR(80) NOT NULL,
    model VARCHAR(190) NULL,
    capacity DECIMAL(14,2) NULL,
    status ENUM('available','maintenance','inactive') NOT NULL DEFAULT 'available',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_vehicle_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY uq_vehicle_company_plate (company_id, plate_number)
) ENGINE=InnoDB;

CREATE TABLE deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    vehicle_id BIGINT UNSIGNED NULL,
    driver_id BIGINT UNSIGNED NULL,
    status ENUM('pending','in_transit','delivered','failed') NOT NULL DEFAULT 'pending',
    planned_date DATETIME NULL,
    delivered_at DATETIME NULL,
    notes TEXT NULL,
    driver_notes TEXT NULL,
    last_lat DECIMAL(10,7) NULL,
    last_lng DECIMAL(10,7) NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_delivery_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_delivery_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_delivery_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
    CONSTRAINT fk_delivery_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_delivery_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_delivery_updater FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_delivery_company_status (company_id, status)
) ENGINE=InnoDB;

CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(190) NOT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    read_at DATETIME NULL,
    CONSTRAINT fk_message_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_message_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_message_company_recipient (company_id, recipient_id, is_read),
    INDEX idx_message_sender (sender_id)
) ENGINE=InnoDB;

CREATE TABLE tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    ticket_number VARCHAR(50) NOT NULL,
    title VARCHAR(190) NOT NULL,
    description TEXT NOT NULL,
    module_name VARCHAR(120) NULL,
    status ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    reporter_id BIGINT UNSIGNED NULL,
    assigned_to BIGINT UNSIGNED NULL,
    due_at DATETIME NULL,
    closed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_ticket_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_ticket_assignee FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_ticket_company_number (company_id, ticket_number),
    INDEX idx_ticket_company_status (company_id, status),
    INDEX idx_ticket_assignee (assigned_to)
) ENGINE=InnoDB;

CREATE TABLE ticket_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    ticket_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_ticket_comment_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_comment_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ticket_comment_ticket (ticket_id),
    INDEX idx_ticket_comment_company (company_id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    type VARCHAR(80) NOT NULL,
    title VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_notification_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notification_company_user (company_id, user_id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(120) NOT NULL,
    module VARCHAR(120) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    metadata_json JSON NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_audit_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_company_created (company_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE company_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    setting_key VARCHAR(120) NOT NULL,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_setting_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY uq_company_setting (company_id, setting_key)
) ENGINE=InnoDB;

CREATE TABLE api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    token CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_token_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_token_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_api_token (token),
    INDEX idx_token_expiry (expires_at)
) ENGINE=InnoDB;

-- Seeds rôles
INSERT INTO roles (name, slug, created_at) VALUES
('Admin SaaS', 'super_admin', NOW()),
('Administrateur Entreprise', 'company_admin', NOW()),
('Directeur Général', 'dg', NOW()),
('Responsable Logistique', 'logistics_manager', NOW()),
('Magasinier', 'storekeeper', NOW()),
('Chauffeur', 'driver', NOW());

-- Seeds permissions
INSERT INTO permissions (name, slug) VALUES
('Voir dashboard', 'dashboard.view'),
('Gérer entreprises', 'companies.manage'),
('Gérer utilisateurs', 'users.manage'),
('Gérer fournisseurs', 'suppliers.manage'),
('Gérer produits', 'products.manage'),
('Gérer entrepôts', 'warehouses.manage'),
('Gérer stocks', 'stocks.manage'),
('Gérer achats', 'purchases.manage'),
('Réceptionner achats', 'purchases.receive'),
('Gérer commandes', 'orders.manage'),
('Valider commandes', 'orders.validate'),
('Préparer commandes', 'orders.prepare'),
('Gérer livraisons', 'deliveries.manage'),
('Interface chauffeur', 'deliveries.driver'),
('Voir rapports', 'reports.view'),
('Gérer paramètres', 'settings.manage'),
('Gerer ticketing', 'tickets.manage'),
('API chauffeur', 'api.driver');

-- Attribution permissions par rôle
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.slug = 'super_admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','users.manage','suppliers.manage','products.manage','warehouses.manage',
    'stocks.manage','purchases.manage','purchases.receive','orders.manage','orders.validate',
    'orders.prepare','deliveries.manage','reports.view','settings.manage','tickets.manage'
)
WHERE r.slug = 'company_admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','orders.manage','orders.validate','deliveries.manage','reports.view','settings.manage','tickets.manage'
)
WHERE r.slug = 'dg';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','suppliers.manage','products.manage','warehouses.manage',
    'stocks.manage','purchases.manage','purchases.receive','orders.manage',
    'orders.validate','orders.prepare','deliveries.manage','reports.view','tickets.manage'
)
WHERE r.slug = 'logistics_manager';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','products.manage','warehouses.manage','stocks.manage',
    'purchases.receive','orders.prepare'
)
WHERE r.slug = 'storekeeper';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','deliveries.driver','api.driver'
)
WHERE r.slug = 'driver';

-- Entreprise de démo
INSERT INTO companies (name, code, email, phone, address, status, settings_json, created_at)
VALUES ('Flow Logistics', 'FLOW', 'contact@flow-logistics.com', '+33 1 88 88 88 88', 'Lyon, France', 'active', JSON_OBJECT('currency','EUR'), NOW());

-- Mot de passe seed = "password"
SET @pwd := '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT NULL, id, 'Flow Super Admin', 'superadmin@flow-logistics.com', @pwd, 1, NOW() FROM roles WHERE slug='super_admin';

INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT c.id, r.id, 'Flow Admin', 'admin@flow-logistics.com', @pwd, 1, NOW()
FROM companies c CROSS JOIN roles r
WHERE c.code='FLOW' AND r.slug='company_admin';

INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT c.id, r.id, 'Flow DG', 'dg@flow-logistics.com', @pwd, 1, NOW()
FROM companies c CROSS JOIN roles r
WHERE c.code='FLOW' AND r.slug='dg';

-- ============================================================
-- Ajustements organisation mono-entreprise (DG, DM, RL, Magasinier, Chauffeur)
-- ============================================================
INSERT INTO roles (name, slug, created_at)
SELECT 'Directeur Manager', 'dm', NOW()
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE slug = 'dm');

UPDATE roles SET name = 'Directeur General' WHERE slug = 'dg';
UPDATE roles SET name = 'Responsable Logistique' WHERE slug = 'logistics_manager';
UPDATE roles SET name = 'Magasinier' WHERE slug = 'storekeeper';
UPDATE roles SET name = 'Chauffeur' WHERE slug = 'driver';

INSERT INTO permissions (name, slug)
SELECT 'Messagerie interne', 'messages.manage'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug = 'messages.manage');

DELETE rp FROM role_permissions rp
INNER JOIN roles r ON r.id = rp.role_id
WHERE r.slug IN ('dg', 'dm', 'company_admin', 'logistics_manager', 'storekeeper', 'driver');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','users.manage','suppliers.manage','products.manage','warehouses.manage',
    'stocks.manage','purchases.manage','purchases.receive','orders.manage','orders.validate',
    'orders.prepare','deliveries.manage','reports.view','settings.manage','tickets.manage','messages.manage'
)
WHERE r.slug = 'dg';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','users.manage','suppliers.manage','products.manage','warehouses.manage',
    'stocks.manage','purchases.manage','purchases.receive','orders.manage','orders.validate',
    'orders.prepare','deliveries.manage','reports.view','settings.manage','tickets.manage','messages.manage'
)
WHERE r.slug IN ('dm', 'company_admin');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','suppliers.manage','products.manage','warehouses.manage',
    'stocks.manage','purchases.manage','purchases.receive','orders.manage',
    'orders.validate','orders.prepare','deliveries.manage','reports.view','tickets.manage','messages.manage'
)
WHERE r.slug = 'logistics_manager';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','stocks.manage','orders.prepare','purchases.receive','messages.manage'
)
WHERE r.slug = 'storekeeper';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
    'dashboard.view','deliveries.driver','api.driver','messages.manage'
)
WHERE r.slug = 'driver';

SET FOREIGN_KEY_CHECKS = 1;

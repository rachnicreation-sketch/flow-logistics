-- Migration ERP : Ajout des modules manquants (Finances, RH/Chauffeurs, Colis, Douanes, Documents, Retours, Maintenance)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
USE flow_logistics_db;

-- 6. & 20. Maintenance & SAV (Véhicules et équipements)
CREATE TABLE IF NOT EXISTS vehicle_maintenances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    type ENUM('routine', 'repair', 'inspection', 'insurance') NOT NULL,
    description TEXT NOT NULL,
    cost DECIMAL(14,2) NOT NULL DEFAULT 0,
    performed_at DATE NOT NULL,
    next_due_at DATE NULL,
    status ENUM('planned', 'in_progress', 'completed') NOT NULL DEFAULT 'planned',
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_maint_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_maint_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Gestion des chauffeurs (RH)
CREATE TABLE IF NOT EXISTS driver_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    license_number VARCHAR(100) NOT NULL,
    license_type VARCHAR(50) NOT NULL,
    license_expiry DATE NOT NULL,
    total_hours DECIMAL(10,2) NOT NULL DEFAULT 0,
    bonuses DECIMAL(14,2) NOT NULL DEFAULT 0,
    penalties DECIMAL(14,2) NOT NULL DEFAULT 0,
    status ENUM('available', 'on_leave', 'sick', 'suspended') NOT NULL DEFAULT 'available',
    CONSTRAINT fk_driver_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_driver_profile (user_id)
) ENGINE=InnoDB;

-- 11. Gestion des colis / expéditions
CREATE TABLE IF NOT EXISTS parcels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    delivery_id BIGINT UNSIGNED NOT NULL,
    tracking_number VARCHAR(120) NOT NULL,
    weight_kg DECIMAL(8,2) NOT NULL DEFAULT 0,
    volume_m3 DECIMAL(8,2) NOT NULL DEFAULT 0,
    dimensions VARCHAR(100) NULL, -- ex: "50x30x20"
    barcode VARCHAR(120) NULL,
    status ENUM('prepared', 'scanned', 'loaded', 'delivered', 'lost') NOT NULL DEFAULT 'prepared',
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_parcel_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_parcel_delivery FOREIGN KEY (delivery_id) REFERENCES deliveries(id) ON DELETE CASCADE,
    UNIQUE KEY uq_parcel_tracking (company_id, tracking_number)
) ENGINE=InnoDB;

-- 12. Gestion douanière
CREATE TABLE IF NOT EXISTS customs_declarations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    purchase_id BIGINT UNSIGNED NULL,
    declaration_number VARCHAR(120) NOT NULL,
    type ENUM('import', 'export', 'transit') NOT NULL,
    customs_office VARCHAR(150) NOT NULL,
    taxes_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    status ENUM('draft', 'submitted', 'cleared', 'rejected') NOT NULL DEFAULT 'draft',
    cleared_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_customs_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_customs_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    CONSTRAINT fk_customs_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 13. Gestion financière (Factures, Paiements, Dépenses)
CREATE TABLE IF NOT EXISTS invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    invoice_number VARCHAR(100) NOT NULL,
    type ENUM('standard', 'proforma', 'credit_note') NOT NULL DEFAULT 'standard',
    total_excl_tax DECIMAL(14,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_incl_tax DECIMAL(14,2) NOT NULL DEFAULT 0,
    status ENUM('draft', 'unpaid', 'partially_paid', 'paid', 'cancelled') NOT NULL DEFAULT 'draft',
    due_date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_invoice_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoice_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoice_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_invoice_number (company_id, invoice_number)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NULL,
    purchase_id BIGINT UNSIGNED NULL,
    type ENUM('incoming', 'outgoing') NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    payment_method ENUM('bank_transfer', 'credit_card', 'cash', 'check', 'mobile_money') NOT NULL,
    reference VARCHAR(150) NULL,
    payment_date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_payment_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    CONSTRAINT fk_payment_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    category VARCHAR(100) NOT NULL, -- 'fuel', 'toll', 'maintenance', 'office', etc.
    amount DECIMAL(14,2) NOT NULL,
    description TEXT NOT NULL,
    vehicle_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    expense_date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_expense_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_expense_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
    CONSTRAINT fk_expense_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 19. Gestion documentaire
CREATE TABLE IF NOT EXISTS documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    entity_type VARCHAR(80) NOT NULL, -- 'order', 'purchase', 'delivery', 'customs', 'customer'
    entity_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_doc_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_doc_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_doc_entity (entity_type, entity_id)
) ENGINE=InnoDB;

-- 21. Gestion des retours logistiques (Reverse Logistics)
CREATE TABLE IF NOT EXISTS return_authorizations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    return_number VARCHAR(100) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('requested', 'approved', 'received', 'inspected', 'refunded', 'rejected') NOT NULL DEFAULT 'requested',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_return_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_return_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_return_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_return_number (company_id, return_number)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS return_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(14,2) NOT NULL,
    condition_status ENUM('resellable', 'damaged', 'destroyed') NOT NULL DEFAULT 'resellable',
    CONSTRAINT fk_ri_return FOREIGN KEY (return_id) REFERENCES return_authorizations(id) ON DELETE CASCADE,
    CONSTRAINT fk_ri_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

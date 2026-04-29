-- ============================================================
-- Module Inventaire (Stocktaking)
-- ============================================================

CREATE TABLE IF NOT EXISTS inventories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    status ENUM('open', 'closed', 'cancelled') NOT NULL DEFAULT 'open',
    title VARCHAR(190) NOT NULL,
    started_at DATETIME NOT NULL,
    closed_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_inv_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_warehouse FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_inv_company_status (company_id, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inventory_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventory_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    location_id BIGINT UNSIGNED NULL,
    theoretical_quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
    actual_quantity DECIMAL(14,2) NULL,
    difference DECIMAL(14,2) NULL,
    notes TEXT NULL,
    CONSTRAINT fk_ii_inv FOREIGN KEY (inventory_id) REFERENCES inventories(id) ON DELETE CASCADE,
    CONSTRAINT fk_ii_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_ii_location FOREIGN KEY (location_id) REFERENCES warehouse_locations(id) ON DELETE SET NULL,
    INDEX idx_ii_inv (inventory_id)
) ENGINE=InnoDB;

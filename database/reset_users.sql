USE flow_logistics_db;
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

SET @pwd := '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
SET @company_id := (SELECT id FROM companies WHERE code='FLOW' LIMIT 1);

-- Antigravity Admin
INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT NULL, id, 'Antigravity Admin', 'admin@antigravity.log', @pwd, 1, NOW() FROM roles WHERE slug='super_admin';

-- DG
INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT @company_id, id, 'Marc Durand', 'm.durand@flow-logistics.com', @pwd, 1, NOW() FROM roles WHERE slug='dg';

-- Manager
INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT @company_id, id, 'Hélène Petit', 'h.petit@flow-logistics.com', @pwd, 1, NOW() FROM roles WHERE slug='logistics_manager';

-- Storekeeper
INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT @company_id, id, 'Lucas Martin', 'l.martin@flow-logistics.com', @pwd, 1, NOW() FROM roles WHERE slug='storekeeper';

-- Driver
INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT @company_id, id, 'Thomas Leroy', 't.leroy@flow-logistics.com', @pwd, 1, NOW() FROM roles WHERE slug='driver';

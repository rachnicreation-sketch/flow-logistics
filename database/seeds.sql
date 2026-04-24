-- ============================================================
-- Flow Logistics - Données de démonstration
-- ============================================================
USE flow_logistics_db;

SET @pwd := '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- ============ UTILISATEURS SUPPLÉMENTAIRES ============
INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT c.id, r.id, 'Sophie Logistique', 'logistique@flow-logistics.com', @pwd, 1, NOW()
FROM companies c CROSS JOIN roles r WHERE c.code='FLOW' AND r.slug='logistics_manager';

INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT c.id, r.id, 'Marc Magasinier', 'magasinier@flow-logistics.com', @pwd, 1, NOW()
FROM companies c CROSS JOIN roles r WHERE c.code='FLOW' AND r.slug='storekeeper';

INSERT INTO users (company_id, role_id, name, email, password_hash, is_active, created_at)
SELECT c.id, r.id, 'Jean-Pierre Chauffeur', 'chauffeur@flow-logistics.com', @pwd, 1, NOW()
FROM companies c CROSS JOIN roles r WHERE c.code='FLOW' AND r.slug='driver';

-- ============ FOURNISSEURS ============
INSERT INTO suppliers (company_id, name, contact_name, email, phone, address, rating, status, created_at)
SELECT id, 'TransEuro Supply', 'Émilie Durand', 'contact@transeuro.com', '+33 4 72 00 11 22', 'Zone Industrielle Nord, Lyon', 4.80, 'active', NOW()
FROM companies WHERE code='FLOW';

INSERT INTO suppliers (company_id, name, contact_name, email, phone, address, rating, status, created_at)
SELECT id, 'MedPack Solutions', 'Karim Benali', 'karim@medpack.fr', '+33 3 88 44 55 66', '12 Rue des Industries, Strasbourg', 4.50, 'active', NOW()
FROM companies WHERE code='FLOW';

INSERT INTO suppliers (company_id, name, contact_name, email, phone, address, rating, status, created_at)
SELECT id, 'AgroFresh Imports', 'Claire Fontaine', 'claire@agrofresh.eu', '+34 91 555 77 88', 'Polígono Industrial, Madrid', 3.90, 'active', NOW()
FROM companies WHERE code='FLOW';

-- ============ CATÉGORIES ============
INSERT INTO categories (company_id, name, description, created_at)
SELECT id, 'Électronique', 'Matériel électronique et informatique', NOW() FROM companies WHERE code='FLOW';

INSERT INTO categories (company_id, name, description, created_at)
SELECT id, 'Emballages', 'Cartons, palettes et matériel d''emballage', NOW() FROM companies WHERE code='FLOW';

INSERT INTO categories (company_id, name, description, created_at)
SELECT id, 'Alimentaire Sec', 'Produits alimentaires non périssables', NOW() FROM companies WHERE code='FLOW';

INSERT INTO categories (company_id, name, description, created_at)
SELECT id, 'Équipements Logistiques', 'Chariots, scanneurs, équipements entrepôt', NOW() FROM companies WHERE code='FLOW';

-- ============ PRODUITS ============
INSERT INTO products (company_id, category_id, name, sku, barcode, unit, purchase_price, sale_price, min_stock, status, created_at)
SELECT c.id, ca.id, 'Tablette Scanner WM500', 'SKU-TAB-WM500', '3760120000001', 'pièce', 180.00, 290.00, 5.00, 'active', NOW()
FROM companies c JOIN categories ca ON ca.company_id=c.id AND ca.name='Électronique' WHERE c.code='FLOW';

INSERT INTO products (company_id, category_id, name, sku, barcode, unit, purchase_price, sale_price, min_stock, status, created_at)
SELECT c.id, ca.id, 'Imprimante Étiquette LP450', 'SKU-IMP-LP450', '3760120000002', 'pièce', 95.00, 149.00, 3.00, 'active', NOW()
FROM companies c JOIN categories ca ON ca.company_id=c.id AND ca.name='Électronique' WHERE c.code='FLOW';

INSERT INTO products (company_id, category_id, name, sku, barcode, unit, purchase_price, sale_price, min_stock, status, created_at)
SELECT c.id, ca.id, 'Carton Standard 60x40x30', 'SKU-CAR-604030', '3760120000003', 'carton', 0.85, 1.80, 500.00, 'active', NOW()
FROM companies c JOIN categories ca ON ca.company_id=c.id AND ca.name='Emballages' WHERE c.code='FLOW';

INSERT INTO products (company_id, category_id, name, sku, barcode, unit, purchase_price, sale_price, min_stock, status, created_at)
SELECT c.id, ca.id, 'Palette Euro 120x80', 'SKU-PAL-EUR', '3760120000004', 'palette', 8.50, 15.00, 50.00, 'active', NOW()
FROM companies c JOIN categories ca ON ca.company_id=c.id AND ca.name='Emballages' WHERE c.code='FLOW';

INSERT INTO products (company_id, category_id, name, sku, barcode, unit, purchase_price, sale_price, min_stock, status, created_at)
SELECT c.id, ca.id, 'Farine de Blé T55 25kg', 'SKU-FAR-T55', '3760120000005', 'sac', 12.00, 19.50, 100.00, 'active', NOW()
FROM companies c JOIN categories ca ON ca.company_id=c.id AND ca.name='Alimentaire Sec' WHERE c.code='FLOW';

INSERT INTO products (company_id, category_id, name, sku, barcode, unit, purchase_price, sale_price, min_stock, status, created_at)
SELECT c.id, ca.id, 'Huile de Tournesol 5L', 'SKU-HUI-TRN5', '3760120000006', 'bidon', 5.20, 8.90, 200.00, 'active', NOW()
FROM companies c JOIN categories ca ON ca.company_id=c.id AND ca.name='Alimentaire Sec' WHERE c.code='FLOW';

INSERT INTO products (company_id, category_id, name, sku, barcode, unit, purchase_price, sale_price, min_stock, status, created_at)
SELECT c.id, ca.id, 'Transpalette Manuel 2500kg', 'SKU-TRP-2500', '3760120000007', 'pièce', 320.00, 490.00, 2.00, 'active', NOW()
FROM companies c JOIN categories ca ON ca.company_id=c.id AND ca.name='Équipements Logistiques' WHERE c.code='FLOW';

-- ============ ENTREPÔTS ============
INSERT INTO warehouses (company_id, name, code, address, created_at)
SELECT id, 'Entrepôt Central Lyon', 'WH-LYN-01', 'Zone Industrielle Est, 69200 Vénissieux', NOW() FROM companies WHERE code='FLOW';

INSERT INTO warehouses (company_id, name, code, address, created_at)
SELECT id, 'Dépôt Marseille', 'WH-MRS-02', 'Port de la Joliette, 13002 Marseille', NOW() FROM companies WHERE code='FLOW';

-- ============ ZONES ============
INSERT INTO warehouse_zones (company_id, warehouse_id, name, created_at)
SELECT w.company_id, w.id, 'Zone A - Réception', NOW() FROM warehouses w WHERE w.code='WH-LYN-01';

INSERT INTO warehouse_zones (company_id, warehouse_id, name, created_at)
SELECT w.company_id, w.id, 'Zone B - Stockage', NOW() FROM warehouses w WHERE w.code='WH-LYN-01';

INSERT INTO warehouse_zones (company_id, warehouse_id, name, created_at)
SELECT w.company_id, w.id, 'Zone C - Expédition', NOW() FROM warehouses w WHERE w.code='WH-LYN-01';

INSERT INTO warehouse_zones (company_id, warehouse_id, name, created_at)
SELECT w.company_id, w.id, 'Zone A - Stockage', NOW() FROM warehouses w WHERE w.code='WH-MRS-02';

-- ============ EMPLACEMENTS ============
INSERT INTO warehouse_locations (company_id, zone_id, label, capacity, created_at)
SELECT wz.company_id, wz.id, 'A-01', 500.00, NOW() FROM warehouse_zones wz WHERE wz.name='Zone A - Réception';

INSERT INTO warehouse_locations (company_id, zone_id, label, capacity, created_at)
SELECT wz.company_id, wz.id, 'B-01', 2000.00, NOW() FROM warehouse_zones wz WHERE wz.name='Zone B - Stockage';

INSERT INTO warehouse_locations (company_id, zone_id, label, capacity, created_at)
SELECT wz.company_id, wz.id, 'B-02', 2000.00, NOW() FROM warehouse_zones wz WHERE wz.name='Zone B - Stockage';

INSERT INTO warehouse_locations (company_id, zone_id, label, capacity, created_at)
SELECT wz.company_id, wz.id, 'C-01', 800.00, NOW() FROM warehouse_zones wz WHERE wz.name='Zone C - Expédition';

-- ============ STOCKS INITIAUX ============
INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
SELECT p.company_id, p.id, w.id, loc.id, 42.00, NOW()
FROM products p CROSS JOIN warehouses w CROSS JOIN warehouse_locations loc
WHERE p.sku='SKU-TAB-WM500' AND w.code='WH-LYN-01' AND loc.label='B-01';

INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
SELECT p.company_id, p.id, w.id, loc.id, 18.00, NOW()
FROM products p CROSS JOIN warehouses w CROSS JOIN warehouse_locations loc
WHERE p.sku='SKU-IMP-LP450' AND w.code='WH-LYN-01' AND loc.label='B-01';

INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
SELECT p.company_id, p.id, w.id, loc.id, 1250.00, NOW()
FROM products p CROSS JOIN warehouses w CROSS JOIN warehouse_locations loc
WHERE p.sku='SKU-CAR-604030' AND w.code='WH-LYN-01' AND loc.label='B-02';

INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
SELECT p.company_id, p.id, w.id, loc.id, 180.00, NOW()
FROM products p CROSS JOIN warehouses w CROSS JOIN warehouse_locations loc
WHERE p.sku='SKU-PAL-EUR' AND w.code='WH-LYN-01' AND loc.label='B-02';

INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
SELECT p.company_id, p.id, w.id, loc.id, 320.00, NOW()
FROM products p CROSS JOIN warehouses w CROSS JOIN warehouse_locations loc
WHERE p.sku='SKU-FAR-T55' AND w.code='WH-LYN-01' AND loc.label='B-01';

INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
SELECT p.company_id, p.id, w.id, loc.id, 540.00, NOW()
FROM products p CROSS JOIN warehouses w CROSS JOIN warehouse_locations loc
WHERE p.sku='SKU-HUI-TRN5' AND w.code='WH-LYN-01' AND loc.label='B-02';

INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
SELECT p.company_id, p.id, w.id, loc.id, 7.00, NOW()
FROM products p CROSS JOIN warehouses w CROSS JOIN warehouse_locations loc
WHERE p.sku='SKU-TRP-2500' AND w.code='WH-LYN-01' AND loc.label='B-01';

-- Stocks Marseille
INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
SELECT p.company_id, p.id, w.id, loc.id, 200.00, NOW()
FROM products p CROSS JOIN warehouses w CROSS JOIN warehouse_locations loc
WHERE p.sku='SKU-FAR-T55' AND w.code='WH-MRS-02' AND loc.label='A-01';

INSERT INTO stocks (company_id, product_id, warehouse_id, location_id, quantity, updated_at)
SELECT p.company_id, p.id, w.id, loc.id, 380.00, NOW()
FROM products p CROSS JOIN warehouses w CROSS JOIN warehouse_locations loc
WHERE p.sku='SKU-HUI-TRN5' AND w.code='WH-MRS-02' AND loc.label='A-01';

-- ============ CLIENTS ============
INSERT INTO customers (company_id, name, email, phone, address, created_at)
SELECT id, 'Supermarché BioMart Lyon', 'commandes@biomart-lyon.fr', '+33 4 72 55 66 77', '25 Avenue de la République, 69003 Lyon', NOW()
FROM companies WHERE code='FLOW';

INSERT INTO customers (company_id, name, email, phone, address, created_at)
SELECT id, 'Restaurant Le Provençal', 'approvisionnement@leprovencal.fr', '+33 4 91 44 55 66', '8 Rue de la Paix, 13006 Marseille', NOW()
FROM companies WHERE code='FLOW';

INSERT INTO customers (company_id, name, email, phone, address, created_at)
SELECT id, 'Entrepôt LogiPro Paris', 'achats@logipro.com', '+33 1 44 88 99 00', 'ZI Rungis, 94150 Rungis', NOW()
FROM companies WHERE code='FLOW';

-- ============ COMMANDES ============
INSERT INTO orders (company_id, customer_id, reference, status, delivery_address, total_amount, created_by, created_at)
SELECT c.id, cu.id, 'CMD-FLOW-2026-001', 'delivered',
       '25 Avenue de la République, 69003 Lyon', 2340.00, u.id, NOW() - INTERVAL 10 DAY
FROM companies c
JOIN customers cu ON cu.company_id=c.id AND cu.name='Supermarché BioMart Lyon'
JOIN users u ON u.company_id=c.id AND u.email='admin@flow-logistics.com'
WHERE c.code='FLOW';

INSERT INTO orders (company_id, customer_id, reference, status, delivery_address, total_amount, created_by, created_at)
SELECT c.id, cu.id, 'CMD-FLOW-2026-002', 'validated',
       '8 Rue de la Paix, 13006 Marseille', 890.50, u.id, NOW() - INTERVAL 3 DAY
FROM companies c
JOIN customers cu ON cu.company_id=c.id AND cu.name='Restaurant Le Provençal'
JOIN users u ON u.company_id=c.id AND u.email='admin@flow-logistics.com'
WHERE c.code='FLOW';

INSERT INTO orders (company_id, customer_id, reference, status, delivery_address, total_amount, created_by, created_at)
SELECT c.id, cu.id, 'CMD-FLOW-2026-003', 'pending',
       'ZI Rungis, 94150 Rungis', 5200.00, u.id, NOW()
FROM companies c
JOIN customers cu ON cu.company_id=c.id AND cu.name='Entrepôt LogiPro Paris'
JOIN users u ON u.company_id=c.id AND u.email='admin@flow-logistics.com'
WHERE c.code='FLOW';

-- ============ VÉHICULES ============
INSERT INTO vehicles (company_id, plate_number, model, capacity, status, created_at)
SELECT id, 'FL-001-AB', 'Mercedes Sprinter 316', 1500.00, 'available', NOW() FROM companies WHERE code='FLOW';

INSERT INTO vehicles (company_id, plate_number, model, capacity, status, created_at)
SELECT id, 'FL-002-CD', 'Renault Master L3H2', 1200.00, 'available', NOW() FROM companies WHERE code='FLOW';

INSERT INTO vehicles (company_id, plate_number, model, capacity, status, created_at)
SELECT id, 'FL-003-EF', 'Ford Transit 350', 900.00, 'maintenance', NOW() FROM companies WHERE code='FLOW';

-- ============ LIVRAISONS ============
INSERT INTO deliveries (company_id, order_id, vehicle_id, driver_id, status, planned_date, delivered_at, notes, created_by, created_at)
SELECT c.id, o.id, v.id, u.id, 'delivered', NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 8 DAY,
       'Livraison effectuée sans incident.', a.id, NOW() - INTERVAL 9 DAY
FROM companies c
JOIN orders o ON o.company_id=c.id AND o.reference='CMD-FLOW-2026-001'
JOIN vehicles v ON v.company_id=c.id AND v.plate_number='FL-001-AB'
JOIN users u ON u.company_id=c.id AND u.email='chauffeur@flow-logistics.com'
JOIN users a ON a.company_id=c.id AND a.email='logistique@flow-logistics.com'
WHERE c.code='FLOW';

INSERT INTO deliveries (company_id, order_id, vehicle_id, driver_id, status, planned_date, notes, created_by, created_at)
SELECT c.id, o.id, v.id, u.id, 'pending', NOW() + INTERVAL 2 DAY,
       'Livraison prévue en matinée.', a.id, NOW()
FROM companies c
JOIN orders o ON o.company_id=c.id AND o.reference='CMD-FLOW-2026-002'
JOIN vehicles v ON v.company_id=c.id AND v.plate_number='FL-002-CD'
JOIN users u ON u.company_id=c.id AND u.email='chauffeur@flow-logistics.com'
JOIN users a ON a.company_id=c.id AND a.email='logistique@flow-logistics.com'
WHERE c.code='FLOW';

SELECT 'Données de démonstration Flow Logistics insérées avec succès !' AS status;

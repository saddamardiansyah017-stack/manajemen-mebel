USE eoq_mebel;

-- Asumsi User ID 1 adalah Admin/Owner, Supplier ID 1-6, Product ID 1-40

-- Insert Data Orders (Pemesanan Stok ke Supplier)
INSERT INTO `orders` (`date`, `order_quantity`, `amount`, `ordered_by`, `supplier_id`, `product_id`, `created_at`, `updated_at`) VALUES
(DATE_SUB(NOW(), INTERVAL 5 MONTH), 100, 6500000, 1, 1, 1, NOW(), NOW()), -- Semen Portland 50kg (ID 1, harga 65000)
(DATE_SUB(NOW(), INTERVAL 4 MONTH), 50, 4250000, 1, 1, 2, NOW(), NOW()),  -- Semen Putih 40kg (ID 2, harga 85000)
(DATE_SUB(NOW(), INTERVAL 3 MONTH), 200, 9000000, 1, 6, 6, NOW(), NOW()), -- Besi Beton 8mm (ID 6, harga 45000)
(DATE_SUB(NOW(), INTERVAL 2 MONTH), 150, 9750000, 1, 6, 7, NOW(), NOW()), -- Besi Beton 10mm (ID 7, harga 65000)
(DATE_SUB(NOW(), INTERVAL 1 MONTH), 100, 1500000, 1, 5, 17, NOW(), NOW()), -- Pipa PVC 1/2 (ID 17, harga 15000)
(DATE_SUB(NOW(), INTERVAL 15 DAY), 80, 5200000, 1, 4, 14, NOW(), NOW());  -- Keramik Lantai (ID 14, harga 65000)


-- Insert Data Sales (Penjualan / Permintaan, untuk kalkulasi EOQ Demand)
INSERT INTO `sales` (`date`, `quantity`, `amount`, `created_by`, `product_id`, `created_at`, `updated_at`) VALUES
-- Sales untuk Semen Portland (ID 1)
(DATE_SUB(NOW(), INTERVAL 4 MONTH), 20, 1300000, 1, 1, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 3 MONTH), 30, 1950000, 1, 1, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 2 MONTH), 25, 1625000, 1, 1, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 1 MONTH), 40, 2600000, 1, 1, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 5 DAY), 10, 650000, 1, 1, NOW(), NOW()),

-- Sales untuk Semen Putih (ID 2)
(DATE_SUB(NOW(), INTERVAL 3 MONTH), 10, 850000, 1, 2, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 2 MONTH), 15, 1275000, 1, 2, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 1 MONTH), 5, 425000, 1, 2, NOW(), NOW()),

-- Sales untuk Besi Beton 8mm (ID 6)
(DATE_SUB(NOW(), INTERVAL 2 MONTH), 50, 2250000, 1, 6, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 1 MONTH), 70, 3150000, 1, 6, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 10 DAY), 30, 1350000, 1, 6, NOW(), NOW()),

-- Sales untuk Besi Beton 10mm (ID 7)
(DATE_SUB(NOW(), INTERVAL 1 MONTH), 40, 2600000, 1, 7, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 20 DAY), 50, 3250000, 1, 7, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 2 DAY), 20, 1300000, 1, 7, NOW(), NOW()),

-- Sales untuk Pipa PVC 1/2 (ID 17)
(DATE_SUB(NOW(), INTERVAL 20 DAY), 30, 450000, 1, 17, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 15 DAY), 20, 300000, 1, 17, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 5 DAY), 25, 375000, 1, 17, NOW(), NOW()),

-- Sales untuk Keramik Lantai (ID 14)
(DATE_SUB(NOW(), INTERVAL 10 DAY), 20, 1300000, 1, 14, NOW(), NOW()),
(DATE_SUB(NOW(), INTERVAL 2 DAY), 15, 975000, 1, 14, NOW(), NOW());

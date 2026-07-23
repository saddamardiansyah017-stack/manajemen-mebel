<?php
/**
 * Seeder: Relasi product_suppliers (many-to-many)
 * 
 * Usage: php seeder/4-product-suppliers.php
 */

$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=eoq_mebel;charset=utf8mb4',
    'root',
    'Apik310823!',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Mapping: product_id => [supplier_id, ...] (pertama = primary)
$productSuppliers = [
    // Semen
    1  => [1],        // Semen Portland → PT. Semen Nusantara
    2  => [1],        // Semen Putih → PT. Semen Nusantara
    // Material dasar
    3  => [3],        // Pasir Beton → UD. Pasir Merapi
    4  => [3],        // Batu Bata Merah → UD. Pasir Merapi
    5  => [3],        // Batako Press → UD. Pasir Merapi
    // Besi & Baja
    6  => [2, 6],     // Besi Beton 8mm → CV. Baja Ringan Jaya, Toko Besi Makmur
    7  => [2, 6],     // Besi Beton 10mm
    8  => [2, 6],     // Besi Beton 12mm
    9  => [2],        // Baja Ringan C75
    10 => [2],        // Reng Baja Ringan
    // Cat
    11 => [4],        // Cat Tembok Putih 5kg → PT. Cat Warna Abadi
    12 => [4],        // Cat Tembok Warna 5kg
    13 => [4],        // Cat Kayu/Besi 1kg
    // Keramik & Granit
    14 => [5],        // Keramik Lantai 40x40 → CV. Keramik Sentosa
    15 => [5],        // Keramik Dinding 20x25
    16 => [5],        // Granit Lantai 60x60
    // Pipa
    17 => [6, 2],     // Pipa PVC 1/2 inch → Toko Besi Makmur, CV. Baja Ringan
    18 => [6, 2],     // Pipa PVC 3/4 inch
    19 => [6],        // Pipa PVC 4 inch
    // Paku
    20 => [6],        // Paku Kayu 5cm → Toko Besi Makmur
    21 => [6],        // Paku Beton 3cm
    // Listrik
    22 => [6],        // Kabel Listrik 2x1.5
    23 => [6],        // Stop Kontak
    24 => [6],        // Saklar Engkel
    25 => [6],        // Lampu LED 9W
    // Triplek & Atap
    26 => [3, 6],     // Triplek 3mm → UD. Pasir Merapi, Toko Besi
    27 => [3, 6],     // Triplek 9mm
    28 => [2, 6],     // Seng Gelombang → CV. Baja Ringan, Toko Besi
    29 => [2, 6],     // Asbes Gelombang
    // Pintu & Jendela
    30 => [3],        // Pintu Kayu Mahoni → UD. Pasir Merapi
    31 => [2],        // Jendela Alumunium → CV. Baja Ringan Jaya
    32 => [6],        // Engsel Pintu → Toko Besi Makmur
    33 => [6],        // Handle Pintu
    // Alat & Aksesoris
    34 => [4, 6],     // Kuas Cat 3 inch → PT. Cat Warna Abadi, Toko Besi
    35 => [4, 6],     // Kuas Roll
    36 => [6],        // Lem Rajawali
    37 => [6],        // Gembok Sedang
    38 => [6],        // Tali Tambang
    39 => [6],        // Sekop Pasir
    40 => [6],        // Cangkul
    41 => [6],        // Ember Cor
];

// Clear existing
$pdo->exec("DELETE FROM product_suppliers");

$stmt = $pdo->prepare("
    INSERT INTO product_suppliers (product_id, supplier_id, is_primary, created_at)
    VALUES (:product_id, :supplier_id, :is_primary, NOW())
");

$totalRelations = 0;
foreach ($productSuppliers as $productId => $supplierIds) {
    foreach ($supplierIds as $index => $supplierId) {
        $stmt->execute([
            ':product_id'  => $productId,
            ':supplier_id' => $supplierId,
            ':is_primary'  => ($index === 0) ? 1 : 0,
        ]);
        $totalRelations++;
    }
}

echo "Relasi product_suppliers: {$totalRelations} record dibuat.\n";

// Ringkasan
echo "\nRingkasan relasi:\n";
$result = $pdo->query("
    SELECT p.name AS product, s.name AS supplier, ps.is_primary,
           s.default_lead_time
    FROM product_suppliers ps
    JOIN products p ON ps.product_id = p.id
    JOIN suppliers s ON ps.supplier_id = s.id
    ORDER BY p.id, ps.is_primary DESC
");
foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $primary = $row['is_primary'] ? '★' : ' ';
    printf("  %s %-25s → %-22s (LT: %d hari)\n",
        $primary, $row['product'], $row['supplier'], $row['default_lead_time']);
}

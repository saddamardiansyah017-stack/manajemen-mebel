<?php
/**
 * Seeder: Generate 1 year of sales data for all products
 * 
 * Simulasi penjualan selama 12 bulan ke belakang dari hari ini.
 * Setiap produk memiliki pola penjualan yang berbeda-beda sesuai kategori:
 * - Produk bangunan pokok (semen, pasir, bata) → penjualan tinggi & stabil
 * - Produk spesialis (pintu, jendela, granit) → penjualan rendah & jarang
 * - Produk umum (cat, paku, pipa) → penjualan sedang
 * 
 * Usage: php seeder/3-sales-1year.php
 */

$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=eoq_mebel;charset=utf8mb4',
    'root',
    'Apik310823!',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Hapus data sales lama
$pdo->exec("DELETE FROM sales");
$pdo->exec("ALTER TABLE sales AUTO_INCREMENT = 1");

echo "Data sales lama dihapus.\n";

/**
 * Konfigurasi pola penjualan per produk
 * [product_id => [min_qty, max_qty, frequency_per_month]]
 */
$salesPatterns = [
    // Semen - penjualan tinggi
    1  => ['min' => 5, 'max' => 20, 'freq' => 20],
    2  => ['min' => 2, 'max' => 8,  'freq' => 10],
    // Material dasar - penjualan tinggi
    3  => ['min' => 1, 'max' => 3,  'freq' => 6],
    4  => ['min' => 200, 'max' => 1000, 'freq' => 15],
    5  => ['min' => 100, 'max' => 500, 'freq' => 12],
    // Besi - penjualan sedang-tinggi
    6  => ['min' => 10, 'max' => 40, 'freq' => 14],
    7  => ['min' => 8, 'max' => 30, 'freq' => 12],
    8  => ['min' => 5, 'max' => 20, 'freq' => 10],
    9  => ['min' => 5, 'max' => 15, 'freq' => 8],
    10 => ['min' => 10, 'max' => 30, 'freq' => 10],
    // Cat - penjualan sedang
    11 => ['min' => 2, 'max' => 8, 'freq' => 12],
    12 => ['min' => 1, 'max' => 6, 'freq' => 10],
    13 => ['min' => 1, 'max' => 5, 'freq' => 8],
    // Keramik/Granit - penjualan sedang
    14 => ['min' => 3, 'max' => 15, 'freq' => 10],
    15 => ['min' => 2, 'max' => 10, 'freq' => 8],
    16 => ['min' => 1, 'max' => 5, 'freq' => 5],
    // Pipa - penjualan sedang
    17 => ['min' => 5, 'max' => 15, 'freq' => 10],
    18 => ['min' => 5, 'max' => 12, 'freq' => 9],
    19 => ['min' => 2, 'max' => 8, 'freq' => 6],
    // Paku - penjualan tinggi
    20 => ['min' => 2, 'max' => 5, 'freq' => 15],
    21 => ['min' => 2, 'max' => 5, 'freq' => 12],
    // Listrik - penjualan sedang
    22 => ['min' => 1, 'max' => 3, 'freq' => 5],
    23 => ['min' => 3, 'max' => 10, 'freq' => 8],
    24 => ['min' => 3, 'max' => 10, 'freq' => 8],
    25 => ['min' => 5, 'max' => 15, 'freq' => 12],
    // Triplek/Atap - penjualan sedang
    26 => ['min' => 2, 'max' => 8, 'freq' => 8],
    27 => ['min' => 1, 'max' => 5, 'freq' => 6],
    28 => ['min' => 2, 'max' => 8, 'freq' => 7],
    29 => ['min' => 2, 'max' => 8, 'freq' => 6],
    // Pintu/Jendela - penjualan rendah
    30 => ['min' => 1, 'max' => 2, 'freq' => 3],
    31 => ['min' => 1, 'max' => 3, 'freq' => 3],
    32 => ['min' => 2, 'max' => 6, 'freq' => 6],
    33 => ['min' => 1, 'max' => 3, 'freq' => 4],
    // Alat & Aksesoris - penjualan rendah-sedang
    34 => ['min' => 2, 'max' => 8, 'freq' => 8],
    35 => ['min' => 1, 'max' => 5, 'freq' => 6],
    36 => ['min' => 3, 'max' => 8, 'freq' => 10],
    37 => ['min' => 1, 'max' => 3, 'freq' => 5],
    38 => ['min' => 5, 'max' => 20, 'freq' => 8],
    39 => ['min' => 1, 'max' => 2, 'freq' => 3],
    40 => ['min' => 1, 'max' => 2, 'freq' => 2],
    41 => ['min' => 1, 'max' => 3, 'freq' => 4],
];

// Ambil harga produk
$prices = [];
$stmt = $pdo->query("SELECT id, price FROM products");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $prices[(int)$row['id']] = (float)$row['price'];
}

// Generate sales data untuk 365 hari ke belakang
$today = new DateTime();
$startDate = (clone $today)->modify('-365 days');

$insertStmt = $pdo->prepare("
    INSERT INTO sales (date, quantity, amount, created_by, product_id, created_at, updated_at)
    VALUES (:date, :quantity, :amount, :created_by, :product_id, :created_at, :updated_at)
");

$totalRecords = 0;

$pdo->beginTransaction();

foreach ($salesPatterns as $productId => $pattern) {
    $price = $prices[$productId] ?? 0;
    if ($price <= 0) continue;

    $minQty = $pattern['min'];
    $maxQty = $pattern['max'];
    $freqPerMonth = $pattern['freq'];

    // Hitung rata-rata hari antar transaksi
    $avgDaysBetween = max(1, (int) round(30 / $freqPerMonth));

    $currentDate = clone $startDate;

    while ($currentDate <= $today) {
        // Tambah variasi: skip beberapa hari secara random
        $daysToAdd = rand(max(1, $avgDaysBetween - 2), $avgDaysBetween + 3);
        $currentDate->modify("+{$daysToAdd} days");

        if ($currentDate > $today) break;

        // Quantity dengan sedikit variasi musiman
        $month = (int) $currentDate->format('n');
        $seasonMultiplier = 1.0;
        // Musim hujan (Nov-Feb) → penjualan bangunan sedikit turun
        if (in_array($month, [11, 12, 1, 2])) {
            $seasonMultiplier = 0.7;
        }
        // Musim kemarau (Jun-Sep) → penjualan naik (musim bangun rumah)
        elseif (in_array($month, [6, 7, 8, 9])) {
            $seasonMultiplier = 1.3;
        }

        $qty = rand($minQty, $maxQty);
        $qty = (int) round($qty * $seasonMultiplier);
        $qty = max(1, $qty);

        $amount = $qty * $price;

        // Tambahkan jam random antara 08:00 - 17:00
        $hour = rand(8, 17);
        $minute = rand(0, 59);
        $saleDatetime = $currentDate->format('Y-m-d') . sprintf(' %02d:%02d:00', $hour, $minute);

        $insertStmt->execute([
            ':date'       => $saleDatetime,
            ':quantity'   => $qty,
            ':amount'     => $amount,
            ':created_by' => 1,
            ':product_id' => $productId,
            ':created_at' => $saleDatetime,
            ':updated_at' => $saleDatetime,
        ]);

        $totalRecords++;
    }
}

$pdo->commit();

echo "Seeder selesai! Total {$totalRecords} record penjualan berhasil dibuat.\n";
echo "Rentang data: " . $startDate->format('Y-m-d') . " s/d " . $today->format('Y-m-d') . " (365 hari)\n";

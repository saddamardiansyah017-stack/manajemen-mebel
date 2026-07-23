<?php
/**
 * Migration Runner
 * 
 * Menjalankan semua file .sql di folder migration/ secara berurutan.
 * Akan DROP database lalu recreate dari awal (fresh migration).
 * 
 * Usage: php migrate.php [--seed]
 * 
 * Options:
 *   --seed    Jalankan juga seeder setelah migrasi selesai
 */

$host = '127.0.0.1';
$db   = 'eoq_mebel';
$user = 'root';
$pass = 'Apik310823!';

$runSeed = in_array('--seed', $argv ?? []);

echo "=== Migration Runner ===\n\n";

// Koneksi tanpa database (untuk DROP/CREATE)
try {
    $pdo = new PDO(
        "mysql:host={$host};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage() . "\n");
}

// Drop & recreate database
echo "Dropping database '{$db}'...\n";
$pdo->exec("DROP DATABASE IF EXISTS `{$db}`");
echo "Creating database '{$db}'...\n";
$pdo->exec("CREATE DATABASE `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
echo "Database '{$db}' siap.\n\n";

// Reconnect ke database baru
$pdo = new PDO(
    "mysql:host={$host};dbname={$db};charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Jalankan semua .sql di folder migration/
$migrationDir = __DIR__ . '/migration';
$sqlFiles = glob($migrationDir . '/*.sql');
sort($sqlFiles); // Urut berdasarkan nama (1-, 2-, 3-, dst)

if (empty($sqlFiles)) {
    die("Tidak ada file .sql di folder migration/\n");
}

echo "--- Menjalankan Migrasi ---\n";
foreach ($sqlFiles as $file) {
    $filename = basename($file);
    echo "  Menjalankan: {$filename} ... ";

    $sql = file_get_contents($file);
    // Hapus USE statement karena sudah terkoneksi ke db
    $sql = preg_replace('/^USE\s+\w+;\s*/mi', '', $sql);

    try {
        $pdo->exec($sql);
        echo "OK\n";
    } catch (PDOException $e) {
        echo "GAGAL\n";
        echo "    Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\nMigrasi selesai! ({$db})\n";

// Jalankan seeder jika --seed
if ($runSeed) {
    echo "\n--- Menjalankan Seeder ---\n";

    $seederDir = __DIR__ . '/seeder';
    $sqlFiles2 = glob($seederDir . '/*.sql');
    $phpFiles2 = glob($seederDir . '/*.php');
    $seederFiles = array_merge($sqlFiles2 ?: [], $phpFiles2 ?: []);
    sort($seederFiles);

    if (empty($seederFiles)) {
        echo "Tidak ada file seeder di folder seeder/\n";
    } else {
        foreach ($seederFiles as $file) {
            $filename = basename($file);
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            echo "  Menjalankan: {$filename} ... ";

            try {
                if ($ext === 'sql') {
                    $sql = file_get_contents($file);
                    $sql = preg_replace('/^USE\s+\w+;\s*/mi', '', $sql);
                    $pdo->exec($sql);
                    echo "OK\n";
                } elseif ($ext === 'php') {
                    // Jalankan PHP seeder sebagai subprocess
                    $output = [];
                    $returnCode = 0;
                    exec("php " . escapeshellarg($file) . " 2>&1", $output, $returnCode);
                    if ($returnCode === 0) {
                        echo "OK\n";
                        foreach ($output as $line) {
                            echo "    {$line}\n";
                        }
                    } else {
                        echo "GAGAL\n";
                        foreach ($output as $line) {
                            echo "    {$line}\n";
                        }
                        exit(1);
                    }
                }
            } catch (PDOException $e) {
                echo "GAGAL\n";
                echo "    Error: " . $e->getMessage() . "\n";
                exit(1);
            }
        }
    }

    echo "\nSeeder selesai!\n";
}

echo "\nDone.\n";

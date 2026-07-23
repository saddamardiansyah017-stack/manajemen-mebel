<?php

require_once __DIR__ . '/../app/core/InventoryCalculator.php';

class InventoryCalculatorTest
{
    private $passed = 0;
    private $failed = 0;

    public function run()
    {
        echo "=== InventoryCalculator Unit Tests ===\n\n";

        $this->testEOQ();
        $this->testSafetyStock();
        $this->testROP();
        $this->testROPStatus();
        $this->testCalculateAll();
        $this->testAnnualizeDemand();

        echo "\n=== Hasil: {$this->passed} passed, {$this->failed} failed ===\n";
        return $this->failed === 0;
    }

    private function assertEqual($expected, $actual, $testName)
    {
        if ($expected === $actual) {
            echo "  ✓ {$testName}\n";
            $this->passed++;
        } else {
            echo "  ✗ {$testName} — expected: " . var_export($expected, true) . ", got: " . var_export($actual, true) . "\n";
            $this->failed++;
        }
    }

    private function testEOQ()
    {
        echo "[EOQ]\n";

        // Contoh: D=125, S=50000, H=1500 → EOQ = ceil(sqrt(2*125*50000/1500)) = ceil(sqrt(8333.33)) = ceil(91.29) = 92
        $this->assertEqual(92, InventoryCalculator::calculateEOQ(125, 50000, 1500), 'EOQ normal case');

        // D=0 → return 0
        $this->assertEqual(0, InventoryCalculator::calculateEOQ(0, 50000, 1500), 'EOQ demand=0');

        // S=0 → return 0
        $this->assertEqual(0, InventoryCalculator::calculateEOQ(125, 0, 1500), 'EOQ ordering_cost=0');

        // H=0 → return 0
        $this->assertEqual(0, InventoryCalculator::calculateEOQ(125, 50000, 0), 'EOQ holding_cost=0');

        // Negatif → return 0
        $this->assertEqual(0, InventoryCalculator::calculateEOQ(-10, 50000, 1500), 'EOQ negative demand');

        // D=1, S=1, H=1 → ceil(sqrt(2)) = 2
        $this->assertEqual(2, InventoryCalculator::calculateEOQ(1, 1, 1), 'EOQ minimal values');

        // Large values: D=10000, S=100000, H=5000 → ceil(sqrt(2*10000*100000/5000)) = ceil(sqrt(400000)) = ceil(632.45) = 633
        $this->assertEqual(633, InventoryCalculator::calculateEOQ(10000, 100000, 5000), 'EOQ large values');

        echo "\n";
    }

    private function testSafetyStock()
    {
        echo "[Safety Stock]\n";

        // max=8, avg=3, LT=4 → ceil((8-3)*4) = ceil(20) = 20
        $this->assertEqual(20, InventoryCalculator::calculateSafetyStock(8, 3, 4), 'SS normal case');

        // LT=0 → return 0
        $this->assertEqual(0, InventoryCalculator::calculateSafetyStock(8, 3, 0), 'SS lead_time=0');

        // avg=0 → return 0
        $this->assertEqual(0, InventoryCalculator::calculateSafetyStock(8, 0, 4), 'SS avg_daily=0');

        // max=avg → SS=0
        $this->assertEqual(0, InventoryCalculator::calculateSafetyStock(5, 5, 4), 'SS max=avg (no variasi)');

        // max < avg (edge case, tidak seharusnya terjadi) → SS=0 (floor ke 0)
        $this->assertEqual(0, InventoryCalculator::calculateSafetyStock(3, 5, 4), 'SS max<avg returns 0');

        // Decimal: max=7.5, avg=2.3, LT=3.5 → ceil((7.5-2.3)*3.5) = ceil(18.2) = 19
        $this->assertEqual(19, InventoryCalculator::calculateSafetyStock(7.5, 2.3, 3.5), 'SS decimal values');

        echo "\n";
    }

    private function testROP()
    {
        echo "[ROP]\n";

        // avg=3, LT=4, SS=20 → ceil(3*4 + 20) = ceil(32) = 32
        $this->assertEqual(32, InventoryCalculator::calculateROP(3, 4, 20), 'ROP normal case');

        // LT=0 → return 0
        $this->assertEqual(0, InventoryCalculator::calculateROP(3, 0, 20), 'ROP lead_time=0');

        // avg=0 → return 0
        $this->assertEqual(0, InventoryCalculator::calculateROP(0, 4, 20), 'ROP avg_daily=0');

        // SS=0
        $this->assertEqual(12, InventoryCalculator::calculateROP(3, 4, 0), 'ROP safety_stock=0');

        // Decimal: avg=2.5, LT=3.5, SS=15 → ceil(2.5*3.5 + 15) = ceil(8.75+15) = ceil(23.75) = 24
        $this->assertEqual(24, InventoryCalculator::calculateROP(2.5, 3.5, 15), 'ROP decimal values');

        echo "\n";
    }

    private function testROPStatus()
    {
        echo "[ROP Status]\n";

        // stok <= ROP → reorder
        $this->assertEqual('reorder', InventoryCalculator::determineROPStatus(20, 32, 4, 3), 'Status reorder (stok < ROP)');
        $this->assertEqual('reorder', InventoryCalculator::determineROPStatus(32, 32, 4, 3), 'Status reorder (stok = ROP)');

        // stok > ROP → aman
        $this->assertEqual('aman', InventoryCalculator::determineROPStatus(50, 32, 4, 3), 'Status aman');

        // LT=0 → no_data
        $this->assertEqual('no_data', InventoryCalculator::determineROPStatus(20, 0, 0, 3), 'Status no_data (LT=0)');

        // avg=0 → no_data
        $this->assertEqual('no_data', InventoryCalculator::determineROPStatus(20, 0, 4, 0), 'Status no_data (avg=0)');

        echo "\n";
    }

    private function testCalculateAll()
    {
        echo "[calculateAll]\n";

        $result = InventoryCalculator::calculateAll([
            'demand'        => 125,
            'ordering_cost' => 50000,
            'holding_cost'  => 1500,
            'max_daily'     => 8,
            'avg_daily'     => 3,
            'lead_time'     => 4,
            'stock'         => 20,
        ]);

        $this->assertEqual(92, $result['eoq'], 'All: EOQ');
        $this->assertEqual(20, $result['safety_stock'], 'All: Safety Stock');
        $this->assertEqual(32, $result['rop'], 'All: ROP');
        $this->assertEqual('reorder', $result['rop_status'], 'All: Status reorder');

        // Semua nol
        $result2 = InventoryCalculator::calculateAll([
            'demand'        => 0,
            'ordering_cost' => 0,
            'holding_cost'  => 0,
            'max_daily'     => 0,
            'avg_daily'     => 0,
            'lead_time'     => 0,
            'stock'         => 100,
        ]);

        $this->assertEqual(0, $result2['eoq'], 'All zero: EOQ');
        $this->assertEqual(0, $result2['safety_stock'], 'All zero: Safety Stock');
        $this->assertEqual(0, $result2['rop'], 'All zero: ROP');
        $this->assertEqual('no_data', $result2['rop_status'], 'All zero: Status');

        echo "\n";
    }

    private function testAnnualizeDemand()
    {
        echo "[annualizeDemand]\n";

        // Data 60 hari (2 bulan), total 50 unit → 50/60*365 = ceil(304.17) = 305
        $result = InventoryCalculator::annualizeDemand(50, 60);
        $this->assertEqual(305, $result['annualized_demand'], 'Annualize: 2 bulan data');
        $this->assertEqual(2.0, $result['data_months'], 'Annualize: data_months 2 bulan');

        // Data 365 hari (12 bulan), total 1200 → 1200/365*365 = 1200
        $result2 = InventoryCalculator::annualizeDemand(1200, 365);
        $this->assertEqual(1200, $result2['annualized_demand'], 'Annualize: full year exact');
        $this->assertEqual(12.2, $result2['data_months'], 'Annualize: data_months 12 bulan');

        // Data 30 hari (1 bulan), total 10 → 10/30*365 = ceil(121.67) = 122
        $result3 = InventoryCalculator::annualizeDemand(10, 30);
        $this->assertEqual(122, $result3['annualized_demand'], 'Annualize: 1 bulan data');
        $this->assertEqual(1.0, $result3['data_months'], 'Annualize: data_months 1 bulan');

        // Data 0 hari → return 0
        $result4 = InventoryCalculator::annualizeDemand(50, 0);
        $this->assertEqual(0, $result4['annualized_demand'], 'Annualize: days=0');
        $this->assertEqual(0, $result4['data_months'], 'Annualize: data_months=0');

        // Demand 0 → return 0
        $result5 = InventoryCalculator::annualizeDemand(0, 60);
        $this->assertEqual(0, $result5['annualized_demand'], 'Annualize: demand=0');
        $this->assertEqual(0, $result5['data_months'], 'Annualize: data_months demand=0');

        // Data 7 hari, total 5 → 5/7*365 = ceil(260.71) = 261, data_months=0.2
        $result6 = InventoryCalculator::annualizeDemand(5, 7);
        $this->assertEqual(261, $result6['annualized_demand'], 'Annualize: 1 minggu data');
        $this->assertEqual(0.2, $result6['data_months'], 'Annualize: data_months 1 minggu');

        echo "\n";
    }
}

// Run tests
$test = new InventoryCalculatorTest();
$success = $test->run();
exit($success ? 0 : 1);

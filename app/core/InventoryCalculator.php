<?php

/**
 * Class InventoryCalculator
 * 
 * Berisi fungsi-fungsi kalkulasi inventori:
 * - EOQ (Economic Order Quantity)
 * - Safety Stock
 * - ROP (Reorder Point)
 */
class InventoryCalculator
{
    /**
     * Hitung Economic Order Quantity (EOQ)
     * 
     * @param int|float $demand       Permintaan tahunan (D)
     * @param float     $orderingCost Biaya pesan per order (S)
     * @param float     $holdingCost  Biaya simpan per unit/tahun (H)
     * @return int EOQ (dibulatkan ke atas), 0 jika parameter tidak valid
     */
    public static function calculateEOQ($demand, $orderingCost, $holdingCost): int
    {
        if ($demand <= 0 || $orderingCost <= 0 || $holdingCost <= 0) {
            return 0;
        }

        return (int) ceil(sqrt((2 * $demand * $orderingCost) / $holdingCost));
    }

    /**
     * Hitung Safety Stock
     * Metode: (Max daily demand - Avg daily demand) × Lead Time
     * 
     * @param float $maxDaily  Penjualan harian maksimum
     * @param float $avgDaily  Rata-rata penjualan harian
     * @param float $leadTime  Lead time rata-rata (dalam hari)
     * @return int Safety Stock (dibulatkan ke atas), 0 jika parameter tidak valid
     */
    public static function calculateSafetyStock($maxDaily, $avgDaily, $leadTime): int
    {
        if ($leadTime <= 0 || $avgDaily <= 0) {
            return 0;
        }

        $ss = ($maxDaily - $avgDaily) * $leadTime;
        return (int) ceil(max(0, $ss));
    }

    /**
     * Hitung Reorder Point (ROP)
     * Formula: (Avg daily demand × Lead Time) + Safety Stock
     * 
     * @param float $avgDaily    Rata-rata penjualan harian
     * @param float $leadTime    Lead time rata-rata (dalam hari)
     * @param int   $safetyStock Safety Stock
     * @return int ROP (dibulatkan ke atas), 0 jika parameter tidak valid
     */
    public static function calculateROP($avgDaily, $leadTime, $safetyStock): int
    {
        if ($leadTime <= 0 || $avgDaily <= 0) {
            return 0;
        }

        return (int) ceil(($avgDaily * $leadTime) + $safetyStock);
    }

    /**
     * Tentukan status ROP
     * 
     * @param int   $currentStock Stok saat ini
     * @param int   $rop          Reorder Point
     * @param float $leadTime     Lead time (untuk cek apakah data cukup)
     * @param float $avgDaily     Avg daily demand (untuk cek apakah data cukup)
     * @return string 'reorder' | 'aman' | 'no_data'
     */
    public static function determineROPStatus($currentStock, $rop, $leadTime, $avgDaily): string
    {
        if ($leadTime <= 0 || $avgDaily <= 0) {
            return 'no_data';
        }

        return $currentStock <= $rop ? 'reorder' : 'aman';
    }

    /**
     * Hitung semua metrik inventori sekaligus
     * 
     * @param array $params [
     *     'demand'        => int,   // Permintaan tahunan
     *     'ordering_cost' => float, // Biaya pesan
     *     'holding_cost'  => float, // Biaya simpan
     *     'max_daily'     => float, // Demand harian max
     *     'avg_daily'     => float, // Demand harian rata-rata
     *     'lead_time'     => float, // Lead time (hari)
     *     'stock'         => int,   // Stok saat ini
     * ]
     * @return array [
     *     'eoq'          => int,
     *     'safety_stock' => int,
     *     'rop'          => int,
     *     'rop_status'   => string,
     * ]
     */
    public static function calculateAll(array $params): array
    {
        $demand       = $params['demand'] ?? 0;
        $orderingCost = $params['ordering_cost'] ?? 0;
        $holdingCost  = $params['holding_cost'] ?? 0;
        $maxDaily     = $params['max_daily'] ?? 0;
        $avgDaily     = $params['avg_daily'] ?? 0;
        $leadTime     = $params['lead_time'] ?? 0;
        $stock        = $params['stock'] ?? 0;

        $eoq         = self::calculateEOQ($demand, $orderingCost, $holdingCost);
        $safetyStock = self::calculateSafetyStock($maxDaily, $avgDaily, $leadTime);
        $rop         = self::calculateROP($avgDaily, $leadTime, $safetyStock);
        $ropStatus   = self::determineROPStatus($stock, $rop, $leadTime, $avgDaily);

        return [
            'eoq'          => $eoq,
            'safety_stock' => $safetyStock,
            'rop'          => $rop,
            'rop_status'   => $ropStatus,
        ];
    }

    /**
     * Proyeksikan demand tahunan dari data yang tersedia (annualisasi)
     * 
     * @param int $totalDemand Total demand dari data yang ada
     * @param int $daysSpan    Jumlah hari data tersedia (dari tanggal pertama hingga sekarang)
     * @return array [
     *     'annualized_demand' => int,   // Proyeksi demand 12 bulan
     *     'data_months'       => float, // Jumlah bulan data yang tersedia
     * ]
     */
    public static function annualizeDemand(int $totalDemand, int $daysSpan): array
    {
        if ($totalDemand <= 0 || $daysSpan <= 0) {
            return ['annualized_demand' => 0, 'data_months' => 0];
        }

        $dataMonths = round($daysSpan / 30.0, 1);
        $annualized = (int) ceil(($totalDemand / $daysSpan) * 365);

        return [
            'annualized_demand' => $annualized,
            'data_months'       => $dataMonths,
        ];
    }
}

<?php
class Sale {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getSalesByProduct($product_id) {
        $this->db->query('
            SELECT s.*, u.username AS created_by_name
            FROM sales s
            LEFT JOIN users u ON s.created_by = u.id
            WHERE s.product_id = :product_id
            ORDER BY s.date DESC
        ');
        $this->db->bind(':product_id', $product_id);
        return $this->db->resultSet();
    }

    public function getAllSales() {
        $this->db->query('
            SELECT s.*, p.name AS product_name, p.unit AS product_unit,
                   u.username AS created_by_name
            FROM sales s
            LEFT JOIN products p ON s.product_id = p.id
            LEFT JOIN users u ON s.created_by = u.id
            ORDER BY s.date DESC
        ');
        return $this->db->resultSet();
    }

    public function getTotalDemand($product_id, $months = 12) {
        $this->db->query('
            SELECT SUM(quantity) as total_demand 
            FROM sales 
            WHERE product_id = :product_id 
            AND date >= DATE_SUB(NOW(), INTERVAL :months MONTH)
        ');
        $this->db->bind(':product_id', $product_id);
        $this->db->bind(':months', $months);
        $result = $this->db->single();
        return $result['total_demand'] ? (int) $result['total_demand'] : 0;
    }

    /**
     * Hitung annualized demand: proyeksi demand 12 bulan berdasarkan data yang ada.
     * Mengembalikan array ['annualized_demand' => int, 'data_months' => float, 'raw_demand' => int]
     */
    public function getAnnualizedDemand($product_id) {
        $this->db->query('
            SELECT 
                SUM(quantity) AS total_demand,
                DATEDIFF(NOW(), MIN(date)) AS days_span
            FROM sales
            WHERE product_id = :product_id
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();

        $totalDemand = $result['total_demand'] ? (int) $result['total_demand'] : 0;
        $daysSpan    = $result['days_span'] ? (int) $result['days_span'] : 0;

        $calc = InventoryCalculator::annualizeDemand($totalDemand, $daysSpan);

        return [
            'annualized_demand' => $calc['annualized_demand'],
            'data_months'       => $calc['data_months'],
            'raw_demand'        => $totalDemand,
        ];
    }

    public function addSale($data) {
        $this->db->query('
            INSERT INTO sales (date, quantity, amount, created_by, product_id, created_at, updated_at)
            VALUES (:date, :quantity, :amount, :created_by, :product_id, NOW(), NOW())
        ');
        $this->db->bind(':date',       $data['date']);
        $this->db->bind(':quantity',   $data['quantity']);
        $this->db->bind(':amount',     $data['amount']);
        $this->db->bind(':created_by', $data['created_by']);
        $this->db->bind(':product_id', $data['product_id']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function decrementProductStock($product_id, $qty) {
        $this->db->query('
            UPDATE products SET stock = stock - :qty, updated_at = NOW()
            WHERE id = :product_id AND stock >= :qty
        ');
        $this->db->bind(':qty',        $qty);
        $this->db->bind(':product_id', $product_id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getDailyDemandStats($product_id) {
        // Hitung avg_daily berdasarkan total demand / jumlah hari dalam rentang data
        // dan max_daily dari hari dengan penjualan tertinggi
        $this->db->query('
            SELECT 
                MAX(daily_qty) AS max_daily,
                SUM(daily_qty) AS total_demand,
                DATEDIFF(NOW(), MIN(sale_date)) AS days_span
            FROM (
                SELECT DATE(date) AS sale_date, SUM(quantity) AS daily_qty
                FROM sales
                WHERE product_id = :product_id
                GROUP BY DATE(date)
            ) AS daily_sales
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        
        $maxDaily    = $result['max_daily'] ? (float) $result['max_daily'] : 0;
        $totalDemand = $result['total_demand'] ? (float) $result['total_demand'] : 0;
        $daysSpan    = $result['days_span'] ? (int) $result['days_span'] : 0;
        
        // avg_daily = total demand / jumlah hari dalam rentang (bukan hanya hari ada transaksi)
        $avgDaily = ($daysSpan > 0) ? $totalDemand / $daysSpan : 0;

        return [
            'max_daily' => $maxDaily,
            'avg_daily' => $avgDaily,
        ];
    }

    public function getTotalSalesThisMonth() {
        $this->db->query('
            SELECT SUM(quantity) as total
            FROM sales
            WHERE YEAR(date) = YEAR(NOW()) AND MONTH(date) = MONTH(NOW())
        ');
        $result = $this->db->single();
        return (int) ($result['total'] ?? 0);
    }

    public function getTopSellingProducts($limit = 5, $days = 30) {
        $this->db->query('
            SELECT p.id, p.name, p.unit, SUM(s.quantity) as total_sold
            FROM sales s
            JOIN products p ON s.product_id = p.id
            WHERE s.date >= DATE_SUB(NOW(), INTERVAL :days DAY)
            GROUP BY p.id, p.name, p.unit
            ORDER BY total_sold DESC
            LIMIT :limit
        ');
        $this->db->bind(':days', $days);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}

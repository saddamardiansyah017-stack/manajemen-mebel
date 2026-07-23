<?php
class Order {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getOrdersByProduct($product_id) {
        $this->db->query('
            SELECT o.*, u.username AS ordered_by_name, s.name AS supplier_name
            FROM orders o
            LEFT JOIN users u ON o.ordered_by = u.id
            LEFT JOIN suppliers s ON o.supplier_id = s.id
            WHERE o.product_id = :product_id
            ORDER BY o.date DESC
        ');
        $this->db->bind(':product_id', $product_id);
        return $this->db->resultSet();
    }

    public function getAllOrders() {
        $this->db->query('
            SELECT o.*, p.name AS product_name, p.unit AS product_unit,
                   u.username AS ordered_by_name, s.name AS supplier_name
            FROM orders o
            LEFT JOIN products p ON o.product_id = p.id
            LEFT JOIN users u ON o.ordered_by = u.id
            LEFT JOIN suppliers s ON o.supplier_id = s.id
            ORDER BY o.date DESC
        ');
        return $this->db->resultSet();
    }

    public function addOrder($data) {
        $this->db->query('
            INSERT INTO orders (date, received_date, order_quantity, amount, ordered_by, supplier_id, product_id, created_at, updated_at)
            VALUES (:date, :received_date, :order_quantity, :amount, :ordered_by, :supplier_id, :product_id, NOW(), NOW())
        ');
        $this->db->bind(':date',           $data['date']);
        $this->db->bind(':received_date',  $data['received_date'] ?? null);
        $this->db->bind(':order_quantity', $data['order_quantity']);
        $this->db->bind(':amount',         $data['amount']);
        $this->db->bind(':ordered_by',     $data['ordered_by']);
        $this->db->bind(':supplier_id',    $data['supplier_id']);
        $this->db->bind(':product_id',     $data['product_id']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateProductStock($product_id, $qty) {
        $this->db->query('
            UPDATE products SET stock = stock + :qty, updated_at = NOW()
            WHERE id = :product_id
        ');
        $this->db->bind(':qty',        $qty);
        $this->db->bind(':product_id', $product_id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getOrderById($id) {
        $this->db->query('SELECT * FROM orders WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function deleteOrder($id) {
        $this->db->query('DELETE FROM orders WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getAverageLeadTime($product_id) {
        // 1. Prioritas: data aktual dari riwayat order
        $this->db->query('
            SELECT AVG(DATEDIFF(received_date, date)) AS avg_lead_time
            FROM orders
            WHERE product_id = :product_id
              AND received_date IS NOT NULL
              AND received_date >= date
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        $actual = $result['avg_lead_time'] ? round((float) $result['avg_lead_time'], 1) : 0;
        
        if ($actual > 0) {
            return $actual;
        }

        // 2. Fallback: default_lead_time dari primary supplier jika ada
        $this->db->query('
            SELECT s.default_lead_time
            FROM product_suppliers ps
            JOIN suppliers s ON ps.supplier_id = s.id
            WHERE ps.product_id = :product_id AND ps.is_primary = 1
            LIMIT 1
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        $fromPrimary = $result ? round((float) $result['default_lead_time'], 1) : 0;

        if ($fromPrimary > 0) {
            return $fromPrimary;
        }

        // 3. Fallback: rata-rata default_lead_time dari semua supplier terkait produk
        $this->db->query('
            SELECT AVG(s.default_lead_time) AS avg_lead_time
            FROM product_suppliers ps
            JOIN suppliers s ON ps.supplier_id = s.id
            WHERE ps.product_id = :product_id
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        $fromSuppliers = $result['avg_lead_time'] ? round((float) $result['avg_lead_time'], 1) : 0;

        if ($fromSuppliers > 0) {
            return $fromSuppliers;
        }

        // 4. Fallback: rata-rata global semua supplier
        $this->db->query('SELECT AVG(default_lead_time) AS avg_lead_time FROM suppliers');
        $result = $this->db->single();
        $global = $result['avg_lead_time'] ? round((float) $result['avg_lead_time'], 1) : 0;

        return $global > 0 ? $global : 7.0;
    }

    /**
     * Dapatkan informasi sumber lead time (untuk display purposes)
     * Returns: ['source' => string, 'supplier_name' => string|null]
     */
    public function getLeadTimeSource($product_id) {
        // 1. Cek apakah ada data aktual dari riwayat order
        $this->db->query('
            SELECT AVG(DATEDIFF(received_date, date)) AS avg_lead_time
            FROM orders
            WHERE product_id = :product_id
              AND received_date IS NOT NULL
              AND received_date >= date
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        $actual = $result['avg_lead_time'] ? round((float) $result['avg_lead_time'], 1) : 0;
        
        if ($actual > 0) {
            return ['source' => 'actual', 'supplier_name' => null];
        }

        // 2. Cek primary supplier
        $this->db->query('
            SELECT s.name, s.default_lead_time
            FROM product_suppliers ps
            JOIN suppliers s ON ps.supplier_id = s.id
            WHERE ps.product_id = :product_id AND ps.is_primary = 1
            LIMIT 1
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        
        if ($result && $result['default_lead_time'] > 0) {
            return ['source' => 'primary', 'supplier_name' => $result['name']];
        }

        // 3. Cek rata-rata supplier terkait produk
        $this->db->query('
            SELECT AVG(s.default_lead_time) AS avg_lead_time
            FROM product_suppliers ps
            JOIN suppliers s ON ps.supplier_id = s.id
            WHERE ps.product_id = :product_id
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        
        if ($result['avg_lead_time'] > 0) {
            return ['source' => 'average', 'supplier_name' => null];
        }

        // 4. Global atau default
        return ['source' => 'global', 'supplier_name' => null];
    }

    /**
     * Dapatkan statistik lead time (maks dan rata-rata)
     * Returns: ['max' => float, 'avg' => float, 'source' => string, 'supplier_name' => string|null]
     */
    public function getLeadTimeStats($product_id) {
        // 1. Prioritas: data aktual dari riwayat order yang sudah diterima
        $this->db->query('
            SELECT 
                MAX(DATEDIFF(received_date, date)) AS max_lead_time,
                AVG(DATEDIFF(received_date, date)) AS avg_lead_time
            FROM orders
            WHERE product_id = :product_id
              AND received_date IS NOT NULL
              AND received_date >= date
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        
        if ($result && $result['avg_lead_time'] > 0) {
            return [
                'max'           => round((float) $result['max_lead_time'], 1),
                'avg'           => round((float) $result['avg_lead_time'], 1),
                'source'        => 'actual',
                'supplier_name' => null,
            ];
        }

        // 2. Fallback: dari primary supplier (maks = rata-rata karena hanya satu nilai)
        $this->db->query('
            SELECT s.name, s.default_lead_time
            FROM product_suppliers ps
            JOIN suppliers s ON ps.supplier_id = s.id
            WHERE ps.product_id = :product_id AND ps.is_primary = 1
            LIMIT 1
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        
        if ($result && $result['default_lead_time'] > 0) {
            $lt = round((float) $result['default_lead_time'], 1);
            return [
                'max'           => $lt,
                'avg'           => $lt,
                'source'        => 'primary',
                'supplier_name' => $result['name'],
            ];
        }

        // 3. Fallback: dari semua supplier terkait produk
        $this->db->query('
            SELECT MAX(s.default_lead_time) AS max_lt, AVG(s.default_lead_time) AS avg_lt
            FROM product_suppliers ps
            JOIN suppliers s ON ps.supplier_id = s.id
            WHERE ps.product_id = :product_id
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        
        if ($result && $result['avg_lt'] > 0) {
            return [
                'max'           => round((float) $result['max_lt'], 1),
                'avg'           => round((float) $result['avg_lt'], 1),
                'source'        => 'average',
                'supplier_name' => null,
            ];
        }

        // 4. Fallback: global
        $this->db->query('SELECT MAX(default_lead_time) AS max_lt, AVG(default_lead_time) AS avg_lt FROM suppliers');
        $result = $this->db->single();
        
        if ($result && $result['avg_lt'] > 0) {
            return [
                'max'           => round((float) $result['max_lt'], 1),
                'avg'           => round((float) $result['avg_lt'], 1),
                'source'        => 'global',
                'supplier_name' => null,
            ];
        }

        return [
            'max'           => 7.0,
            'avg'           => 7.0,
            'source'        => 'global',
            'supplier_name' => null,
        ];
    }

    public function updateReceivedDate($id, $received_date) {
        $this->db->query('
            UPDATE orders SET received_date = :received_date, updated_at = NOW()
            WHERE id = :id
        ');
        $this->db->bind(':received_date', $received_date);
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getPendingOrders() {
        $this->db->query('
            SELECT o.*, p.name AS product_name, p.unit AS product_unit,
                   s.name AS supplier_name
            FROM orders o
            LEFT JOIN products p ON o.product_id = p.id
            LEFT JOIN suppliers s ON o.supplier_id = s.id
            WHERE o.received_date IS NULL
            ORDER BY o.date DESC
        ');
        return $this->db->resultSet();
    }

    // ===== Period-based Methods untuk Filter Laporan =====

    public function getOrdersByPeriod($product_id, $from, $to) {
        $this->db->query('
            SELECT o.*, u.username AS ordered_by_name, s.name AS supplier_name
            FROM orders o
            LEFT JOIN users u ON o.ordered_by = u.id
            LEFT JOIN suppliers s ON o.supplier_id = s.id
            WHERE o.product_id = :product_id
            AND o.date >= :from
            AND o.date <= :to
            ORDER BY o.date DESC
        ');
        $this->db->bind(':product_id', $product_id);
        $this->db->bind(':from', $from);
        $this->db->bind(':to', $to);
        return $this->db->resultSet();
    }

    public function getTotalOrdersByPeriod($product_id, $from, $to) {
        $this->db->query('
            SELECT SUM(order_quantity) as total_quantity, SUM(amount) as total_amount
            FROM orders
            WHERE product_id = :product_id
            AND date >= :from
            AND date <= :to
        ');
        $this->db->bind(':product_id', $product_id);
        $this->db->bind(':from', $from);
        $this->db->bind(':to', $to);
        $result = $this->db->single();
        return [
            'quantity' => $result['total_quantity'] ? (int) $result['total_quantity'] : 0,
            'amount' => $result['total_amount'] ? (float) $result['total_amount'] : 0,
        ];
    }

    public function getAllOrdersSummaryByPeriod($from, $to) {
        $this->db->query('
            SELECT 
                COUNT(DISTINCT product_id) as total_products,
                SUM(order_quantity) as total_quantity,
                SUM(amount) as total_amount
            FROM orders
            WHERE date >= :from
            AND date <= :to
        ');
        $this->db->bind(':from', $from);
        $this->db->bind(':to', $to);
        $result = $this->db->single();
        return [
            'total_products' => (int) ($result['total_products'] ?? 0),
            'total_quantity' => (int) ($result['total_quantity'] ?? 0),
            'total_amount' => (float) ($result['total_amount'] ?? 0),
        ];
    }

    public function getLateOrders() {
        $this->db->query('
            SELECT 
                o.*,
                p.name AS product_name,
                p.unit AS product_unit,
                s.name AS supplier_name,
                s.default_lead_time,
                DATEDIFF(NOW(), o.date) AS days_since_order,
                DATEDIFF(NOW(), o.date) - s.default_lead_time AS days_late
            FROM orders o
            LEFT JOIN products p ON o.product_id = p.id
            LEFT JOIN suppliers s ON o.supplier_id = s.id
            WHERE o.received_date IS NULL
            AND DATEDIFF(NOW(), o.date) > s.default_lead_time
            ORDER BY days_late DESC
            LIMIT 10
        ');
        return $this->db->resultSet();
    }
}

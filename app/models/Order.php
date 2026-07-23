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

        // 2. Fallback: default_lead_time dari supplier terkait produk
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

        // 3. Fallback: rata-rata global semua supplier
        $this->db->query('SELECT AVG(default_lead_time) AS avg_lead_time FROM suppliers');
        $result = $this->db->single();
        $global = $result['avg_lead_time'] ? round((float) $result['avg_lead_time'], 1) : 0;

        return $global > 0 ? $global : 7.0;
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
}

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
}

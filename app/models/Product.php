<?php
class Product {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllProducts() {
        $this->db->query('SELECT * FROM products ORDER BY id DESC');
        return $this->db->resultSet();
    }

    public function getProductById($id) {
        $this->db->query('SELECT * FROM products WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addProduct($data) {
        $this->db->query('INSERT INTO products (name, unit, price, stock, ordering_cost, holding_cost, created_at, updated_at) VALUES (:name, :unit, :price, :stock, :ordering_cost, :holding_cost, NOW(), NOW())');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':stock', $data['stock']);
        $this->db->bind(':ordering_cost', $data['ordering_cost']);
        $this->db->bind(':holding_cost', $data['holding_cost']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateProduct($data) {
        $this->db->query('UPDATE products SET name = :name, unit = :unit, price = :price, stock = :stock, ordering_cost = :ordering_cost, holding_cost = :holding_cost, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':stock', $data['stock']);
        $this->db->bind(':ordering_cost', $data['ordering_cost']);
        $this->db->bind(':holding_cost', $data['holding_cost']);
        $this->db->bind(':id', $data['id']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deleteProduct($id) {
        $this->db->query('DELETE FROM products WHERE id = :id');
        $this->db->bind(':id', $id);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function countProducts() {
        $this->db->query('SELECT COUNT(*) as total FROM products');
        $result = $this->db->single();
        return (int) $result['total'];
    }

    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }
}

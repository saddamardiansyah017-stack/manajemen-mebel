<?php
class Supplier {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllSuppliers() {
        $this->db->query('SELECT * FROM suppliers ORDER BY id DESC');
        return $this->db->resultSet();
    }

    public function getSupplierById($id) {
        $this->db->query('SELECT * FROM suppliers WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addSupplier($data) {
        $this->db->query('INSERT INTO suppliers (name, address, phone, email, created_at, updated_at) VALUES (:name, :address, :phone, :email, NOW(), NOW())');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':email', $data['email']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateSupplier($data) {
        $this->db->query('UPDATE suppliers SET name = :name, address = :address, phone = :phone, email = :email, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':id', $data['id']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deleteSupplier($id) {
        $this->db->query('DELETE FROM suppliers WHERE id = :id');
        $this->db->bind(':id', $id);

        $this->db->execute();
        return $this->db->rowCount();
    }
}

<?php
class ProductSupplier {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Ambil semua supplier yang terkait dengan produk tertentu
     */
    public function getSuppliersByProduct($product_id) {
        $this->db->query('
            SELECT s.*, ps.is_primary
            FROM product_suppliers ps
            JOIN suppliers s ON ps.supplier_id = s.id
            WHERE ps.product_id = :product_id
            ORDER BY ps.is_primary DESC, s.name ASC
        ');
        $this->db->bind(':product_id', $product_id);
        return $this->db->resultSet();
    }

    /**
     * Ambil semua produk yang terkait dengan supplier tertentu
     */
    public function getProductsBySupplier($supplier_id) {
        $this->db->query('
            SELECT p.*, ps.is_primary
            FROM product_suppliers ps
            JOIN products p ON ps.product_id = p.id
            WHERE ps.supplier_id = :supplier_id
            ORDER BY p.name ASC
        ');
        $this->db->bind(':supplier_id', $supplier_id);
        return $this->db->resultSet();
    }

    /**
     * Ambil supplier_ids yang terkait dengan produk
     */
    public function getSupplierIdsByProduct($product_id) {
        $this->db->query('
            SELECT supplier_id, is_primary
            FROM product_suppliers
            WHERE product_id = :product_id
        ');
        $this->db->bind(':product_id', $product_id);
        return $this->db->resultSet();
    }

    /**
     * Set relasi product-suppliers (replace semua)
     * @param int $product_id
     * @param array $supplier_ids Array of supplier IDs
     * @param int|null $primary_supplier_id Supplier utama
     */
    public function syncProductSuppliers($product_id, array $supplier_ids, $primary_supplier_id = null) {
        // Hapus relasi lama
        $this->db->query('DELETE FROM product_suppliers WHERE product_id = :product_id');
        $this->db->bind(':product_id', $product_id);
        $this->db->execute();

        // Insert relasi baru
        foreach ($supplier_ids as $supplier_id) {
            $isPrimary = ($supplier_id == $primary_supplier_id) ? 1 : 0;
            $this->db->query('
                INSERT INTO product_suppliers (product_id, supplier_id, is_primary, created_at)
                VALUES (:product_id, :supplier_id, :is_primary, NOW())
            ');
            $this->db->bind(':product_id', $product_id);
            $this->db->bind(':supplier_id', $supplier_id);
            $this->db->bind(':is_primary', $isPrimary);
            $this->db->execute();
        }
    }

    /**
     * Ambil rata-rata default_lead_time dari supplier yang terkait produk
     */
    public function getDefaultLeadTimeByProduct($product_id) {
        $this->db->query('
            SELECT AVG(s.default_lead_time) AS avg_lead_time
            FROM product_suppliers ps
            JOIN suppliers s ON ps.supplier_id = s.id
            WHERE ps.product_id = :product_id
        ');
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        return $result['avg_lead_time'] ? round((float) $result['avg_lead_time'], 1) : 0;
    }

    /**
     * Ambil rata-rata default_lead_time dari semua supplier (fallback global)
     */
    public function getGlobalDefaultLeadTime() {
        $this->db->query('SELECT AVG(default_lead_time) AS avg_lead_time FROM suppliers');
        $result = $this->db->single();
        return $result['avg_lead_time'] ? round((float) $result['avg_lead_time'], 1) : 7.0;
    }
}

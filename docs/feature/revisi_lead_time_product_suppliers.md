# Revisi Fitur: Default Lead Time & Tabel Product-Suppliers

## Latar Belakang

Saat ini, Lead Time hanya bisa dihitung dari riwayat order yang sudah diterima (`AVG(DATEDIFF(received_date, date))`). Akibatnya:

- Produk baru yang belum pernah dipesan → Lead Time = 0
- Safety Stock = 0, ROP = 0, Status = `no_data`
- Sistem tidak bisa memberikan rekomendasi reorder

**Solusi:** Tambah `default_lead_time` di tabel suppliers dan buat tabel relasi `product_suppliers` agar setiap produk bisa terhubung ke beberapa supplier beserta informasi default lead time-nya.

---

## Perubahan Database

### 1. Tambah kolom di tabel `suppliers`

```sql
ALTER TABLE suppliers
ADD COLUMN default_lead_time INT NOT NULL DEFAULT 7
COMMENT 'Estimasi waktu pengiriman default (hari)' AFTER email;
```

### 2. Buat tabel baru `product_suppliers`

```sql
CREATE TABLE product_suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    supplier_id INT NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Supplier utama untuk produk ini',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_supplier (product_id, supplier_id)
) ENGINE=InnoDB;
```

### Schema Update

```
Table suppliers {
  id int [primary key]
  name string
  address text
  phone string
  email string
  default_lead_time int [default: 7, note: 'Estimasi waktu pengiriman default (hari)']
  created_at datetime
  updated_at datetime
}

Table product_suppliers {
  id int [primary key]
  product_id int [ref: > products.id]
  supplier_id int [ref: > suppliers.id]
  is_primary tinyint [default: 0, note: 'Supplier utama']
  created_at datetime

  indexes {
    (product_id, supplier_id) [unique]
  }
}
```

---

## Perubahan Logic Lead Time

### Alur Baru `Order::getAverageLeadTime(product_id)`

```
1. Cek rata-rata lead time dari riwayat orders (data aktual)
   → SELECT AVG(DATEDIFF(received_date, date)) FROM orders
     WHERE product_id = ? AND received_date IS NOT NULL

2. Jika hasilnya > 0 → gunakan data aktual ✅

3. Jika 0 (belum ada riwayat) → fallback ke default_lead_time supplier
   → SELECT AVG(s.default_lead_time)
     FROM product_suppliers ps
     JOIN suppliers s ON ps.supplier_id = s.id
     WHERE ps.product_id = ?

4. Jika masih 0 (produk belum punya supplier terdaftar) → fallback global
   → SELECT AVG(default_lead_time) FROM suppliers
   → Atau hardcode default = 7 hari
```

### Pseudocode

```php
public function getAverageLeadTime($product_id) {
    // 1. Dari riwayat order aktual
    $actual = $this->getActualLeadTime($product_id);
    if ($actual > 0) return $actual;

    // 2. Dari default_lead_time supplier terkait produk
    $fromSuppliers = $this->getDefaultLeadTimeByProduct($product_id);
    if ($fromSuppliers > 0) return $fromSuppliers;

    // 3. Fallback: rata-rata semua supplier
    $global = $this->getGlobalDefaultLeadTime();
    return $global > 0 ? $global : 7.0;
}
```

---

## Perubahan Fitur Terkait

### 1. Supplier CRUD

**Form tambah/edit supplier** → tambah field `default_lead_time`:

- Label: "Estimasi Waktu Pengiriman (hari)"
- Type: number, min=1, default=7
- Deskripsi: "Rata-rata hari dari pesan sampai barang diterima"

### 2. Product CRUD

**Form tambah/edit produk** → tambah section "Supplier":

- Multi-select atau checkbox list supplier yang menyuplai produk ini
- Tandai 1 supplier sebagai "Supplier Utama" (is_primary = 1)
- Data disimpan ke tabel `product_suppliers`

### 3. Halaman Order (products/{id}/orders)

- Saat membuat order, dropdown supplier difilter dari `product_suppliers` (supplier yang terkait produk)
- Jika belum ada relasi, tetap tampilkan semua supplier (backward compatible)
- Lead time yang ditampilkan: aktual jika ada, default jika belum

### 4. Dashboard Reorder Alert

- Produk baru yang sudah punya supplier terdaftar → bisa menampilkan ROP & Safety Stock dari hari pertama (menggunakan default lead time)
- Tidak lagi `no_data` selama ada data penjualan + supplier terdaftar

---

## User Story (Revisi)

### Hari ke-7 (Sebelum Revisi)

- Lead Time = 0, SS = 0, ROP = 0, Status = `no_data`
- Sistem tidak bisa membantu

### Hari ke-7 (Sesudah Revisi)

- Produk terdaftar di supplier "CV Baja Mandiri" (default_lead_time = 5 hari)
- avg_daily = 2.0, max_daily = 5
- **Lead Time = 5 hari** (dari default supplier)
- Safety Stock = ceil((5 - 2.0) × 5) = **15 Kg**
- ROP = ceil((2.0 × 5) + 15) = **25 Kg**
- Stok 38 > ROP 25 → **Status: Aman** ✅

> Sistem sudah bisa memberikan rekomendasi sejak hari pertama ada penjualan!

---

## Langkah Implementasi

| #   | Task                                                          | File                                         |
| --- | ------------------------------------------------------------- | -------------------------------------------- |
| 1   | Migration: ALTER suppliers + CREATE product_suppliers         | `migration/9-supplier-lead-time.sql`         |
| 2   | Seeder: Isi default_lead_time & relasi product_suppliers      | `migration/10-seeder-product-suppliers.php`  |
| 3   | Update model Supplier: tambah field default_lead_time di CRUD | `app/models/Supplier.php`                    |
| 4   | Buat model ProductSupplier                                    | `app/models/ProductSupplier.php`             |
| 5   | Update Order::getAverageLeadTime() dengan fallback logic      | `app/models/Order.php`                       |
| 6   | Update form supplier (create/edit)                            | `app/views/suppliers/create.php`, `edit.php` |
| 7   | Update form product (create/edit) — tambah pilih supplier     | `app/views/products/create.php`, `edit.php`  |
| 8   | Update OrderController — filter supplier dropdown             | `app/controllers/OrderController.php`        |
| 9   | Update docs: schema.md, supplier.md                           | `docs/`                                      |

---

## Backward Compatibility

- Semua supplier existing mendapat `default_lead_time = 7` (default)
- Produk existing yang sudah punya order → relasi `product_suppliers` di-seed dari data orders
- Logic lama tetap jalan: jika ada riwayat order aktual, tetap prioritaskan data aktual
- Tabel `product_suppliers` bersifat opsional — jika kosong, sistem fallback ke rata-rata global

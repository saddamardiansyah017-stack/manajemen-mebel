# Fitur Product

- Mengelola data product
- Melakukan CRUD (Create, Read, Update, Delete) data product (bisa user role admin dan owner [user](user_dan_autentikasi.md))
- Mengelola relasi produk-supplier (many-to-many) melalui tabel `product_suppliers`
- Menentukan supplier utama (primary) per produk
  [schema](../database/schema.md)

## Field

### Tabel products

    * id
    * name
    * unit
    * price
    * stock
    * ordering_cost (biaya pesan per order — digunakan untuk perhitungan EOQ)
    * holding_cost (biaya simpan per unit/tahun — digunakan untuk perhitungan EOQ)
    * created_at
    * updated_at

### Tabel product_suppliers (relasi many-to-many)

    * id
    * product_id (FK → products.id)
    * supplier_id (FK → suppliers.id)
    * is_primary (0/1 — menandai supplier utama)
    * default_lead_time (nullable — override lead time khusus relasi ini, dalam hari)
    * created_at

## CRUD

### 1. Halaman Daftar Product

### URL

/products

### Metode

GET

### Deskripsi

untuk menampilkan daftar product

### Parameter

Tidak Ada

### 2. Halaman Tambah Product

### URL

/products/create

### Metode

GET, POST

### Deskripsi

GET untuk menampilkan form tambah product, POST untuk menambahkan product

### Parameter

name
unit
price
stock
ordering_cost
holding_cost
supplier_ids[] (array — daftar supplier yang memasok produk ini)
primary_supplier_id (ID supplier utama)

### 3. Halaman Edit Product

### URL

/products/edit/{id}

### Metode

GET, POST

### Deskripsi

GET untuk menampilkan form edit product, POST untuk mengedit product

### Parameter

name
unit
price
stock
ordering_cost
holding_cost
supplier_ids[] (array — daftar supplier yang memasok produk ini)
primary_supplier_id (ID supplier utama)

### 4. Halaman Hapus Product

### URL

/products/{id}

### Metode

POST

### Deskripsi

untuk menghapus product

### Parameter

Tidak Ada

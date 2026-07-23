# Arsitektur Project PHP

Project ini menggunakan arsitektur MVC (Model, View, Controller) custom tanpa framework.

## Struktur Folder

```
├── app/
│   ├── controllers/       # Controller (logic & routing handler)
│   ├── core/              # Base classes (Controller.php, Database.php, InventoryCalculator.php)
│   ├── models/            # Model (akses database)
│   └── views/             # View (template PHP)
├── config/
│   └── config.php         # Konfigurasi database & base URL
├── docs/                  # Dokumentasi project
├── migration/             # File SQL schema database (tanpa ALTER TABLE)
├── seeder/                # Data dummy & seeder (SQL dan PHP)
├── migrate.php            # Script runner untuk migrasi & seeder
├── tests/                 # Unit tests
│   └── InventoryCalculatorTest.php
└── public/
    ├── index.php          # Front controller & router
    └── css/               # Asset CSS
```

## Database Migration & Seeder

### Struktur

- `migration/` — Hanya berisi file `.sql` untuk pembuatan tabel (CREATE TABLE). Tidak ada ALTER TABLE. Dijalankan secara berurutan berdasarkan prefix angka.
- `seeder/` — Berisi file `.sql` dan `.php` untuk mengisi data dummy. Dipisahkan dari migrasi agar bisa di-run opsional.
- `migrate.php` — Script runner yang menjalankan semua migrasi dan seeder.

### Command

```bash
# Fresh migration saja (DROP database → CREATE → jalankan semua .sql di migration/)
php migrate.php

# Fresh migration + jalankan semua seeder (sql & php di seeder/)
php migrate.php --seed
```

### Urutan Eksekusi

**Migration** (schema):

1. `1-inisialisasi-dan-user.sql` — Tabel users + default owner/admin
2. `2-supplier.sql` — Tabel suppliers (termasuk default_lead_time)
3. `3-product.sql` — Tabel products
4. `4-orders.sql` — Tabel orders
5. `5-sales.sql` — Tabel sales
6. `6-product-suppliers.sql` — Tabel product_suppliers (many-to-many)

**Seeder** (data dummy):

1. `1-dummy-data.sql` — Data supplier & produk
2. `2-dummy-orders-sales.sql` — Sample orders & sales
3. `3-sales-1year.php` — Generate ~3.300 sales record (1 tahun, pola musiman)
4. `4-product-suppliers.php` — 52 relasi produk-supplier

## Routing

- Semua request diarahkan ke `public/index.php` via `.htaccess`
- URL diparsing dari `$_GET['url']`, disanitasi, lalu di-split berdasarkan `/`
- Segment pertama menentukan controller, segment kedua menentukan method
- Parameter tambahan diteruskan ke method via `call_user_func_array`

### Daftar Route

| URL                      | Controller          | Method    |
| ------------------------ | ------------------- | --------- |
| `/login`                 | AuthController      | login     |
| `/logout`                | AuthController      | logout    |
| `/dashboard`             | DashboardController | index     |
| `/users`                 | UserController      | index     |
| `/users/create`          | UserController      | create    |
| `/users/edit/{id}`       | UserController      | edit      |
| `/users/delete/{id}`     | UserController      | delete    |
| `/suppliers`             | SupplierController  | index     |
| `/suppliers/create`      | SupplierController  | create    |
| `/suppliers/edit/{id}`   | SupplierController  | edit      |
| `/suppliers/delete/{id}` | SupplierController  | delete    |
| `/products`              | ProductController   | index     |
| `/products/create`       | ProductController   | create    |
| `/products/edit/{id}`    | ProductController   | edit      |
| `/products/delete/{id}`  | ProductController   | delete    |
| `/products/{id}/orders`  | OrderController     | index     |
| `/eoq`                   | EoqController       | index     |
| `/eoq/bulkOrder`         | EoqController       | bulkOrder |
| `/reports`               | ReportController    | index     |

## Model

- Terletak di `app/models/`
- Setiap model membuat instance `Database` untuk akses DB
- Menggunakan prepared statements (PDO) untuk keamanan SQL injection
- Model: `User`, `Product`, `Supplier`, `Order`, `Sale`

## View

- Terletak di `app/views/`
- Menggunakan layout utama `layouts/main.php` sebagai wrapper
- Data dikirim dari controller ke view via variabel `$data`

## Controller

- Terletak di `app/controllers/`
- Extends base `Controller` class yang menyediakan helper:
  - `view($view, $data)` — render view dengan layout
  - `model($model)` — load dan return instance model
  - `redirect($url)` — redirect ke URL lain

## InventoryCalculator

- Terletak di `app/core/InventoryCalculator.php`
- Class dengan static methods untuk semua kalkulasi inventori
- Dipisahkan dari controller agar bisa di-unit test tanpa database
- Methods: `calculateEOQ`, `calculateSafetyStock`, `calculateROP`, `determineROPStatus`, `annualizeDemand`, `calculateAll`
- Digunakan oleh: `EoqController`, `ReportController`, `OrderController`, `Sale` model

## Unit Testing

- Terletak di `tests/`
- Jalankan: `php tests/InventoryCalculatorTest.php`
- Tidak memerlukan framework (standalone test runner)
- Covers: EOQ, Safety Stock, ROP, ROP Status, calculateAll, annualizeDemand

## Autentikasi & Otorisasi

- Session-based authentication
- Password di-hash dengan `password_hash()` / `password_verify()`
- Setiap controller cek `$_SESSION['user_id']` di constructor
- Role-based access: `owner`, `admin`, `user`

## Fitur Utama

1. **Manajemen User** — CRUD user (owner only)
2. **Manajemen Produk** — CRUD produk dengan biaya pesan & simpan
3. **Manajemen Supplier** — CRUD supplier
4. **Order (Tambah Stok)** — Buat order, update stok, catat tanggal terima
5. **Sales (Kurangi Stok)** — Catat penjualan, kurangi stok
6. **EOQ** — Perhitungan Economic Order Quantity per produk + bulk order
7. **ROP & Safety Stock** — Perhitungan Reorder Point dan Safety Stock
8. **Reports** — Laporan lengkap demand, EOQ, ROP, Safety Stock

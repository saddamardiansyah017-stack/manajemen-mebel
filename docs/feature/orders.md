# Fitur Orders

- Melakukan operasi tambah stok barang ke dalam database (bisa user role admin dan owner [user](user_dan_autentikasi.md))
- Mencatat tanggal penerimaan barang untuk perhitungan Lead Time (ROP & Safety Stock)
- Menampilkan rekomendasi EOQ, ROP, dan Safety Stock per produk
  [schema](../database/schema.md)

## Field

### Tabel orders

    * id
    * date
    * received_date (nullable — tanggal barang diterima dari supplier)
    * order_quantity
    * amount
    * ordered_by
    * supplier_id
    * product_id
    * created_at
    * updated_at

## Halaman Manajemen Stok Per Produk

### URL

/products/{id}/orders

### Panel Informasi

1. **Panel EOQ** — Menampilkan D (annualisasi), S, H, EOQ, tombol "Pesan Sesuai EOQ"
   - Jika data < 12 bulan: label "proyeksi" pada demand
   - Jika parameter belum lengkap: pesan untuk isi S dan H
   - Jika belum ada penjualan: pesan untuk tambah data penjualan
2. **Panel ROP & Safety Stock** — Menampilkan Lead Time, SS, ROP, status badge
   - Status: "Perlu Reorder" (merah) / "Stok Aman" (hijau)
   - Jika data belum cukup: pesan informatif
3. **Warning estimasi** — Jika data penjualan < 3 bulan, tampilkan badge peringatan bahwa hasil adalah estimasi

### Tabel Riwayat Stok

Gabungan orders dan sales, diurutkan tanggal terbaru, dengan kolom:

- Tipe (Pesanan/Penjualan)
- Tanggal
- Diterima (dengan form inline untuk tandai terima)
- Kuantitas
- Total
- Keterangan
- Aksi (Hapus)

## Flow

### Tambah Stok Barang

1. User pilih barang pada list product
2. User klik tombol "Tambah Stok"
3. User diarahkan ke halaman detail stock product
4. Sistem menampilkan list order
5. User klik tambah stock
6. Sistem menampilkan form tambah stock
7. User input kuantitas barang yang ingin diorder
8. Sistem menampilkan dropdown supplier (hanya supplier terkait produk dari tabel `product_suppliers`; jika belum ada relasi, tampilkan semua supplier sebagai fallback)
9. User pilih supplier
10. Sistem calculate amount
11. Sistem menampilkan total amount
12. User klik tombol "Tambah Stock"
13. Sistem menambahkan stock ke dalam database
14. Sistem menambahkan stok barang ke dalam tabel products

### Tandai Barang Diterima

1. User melihat list order yang belum punya tanggal terima (received_date = NULL)
2. User klik tombol "Tandai Diterima"
3. Sistem menampilkan input tanggal penerimaan
4. User input tanggal penerimaan
5. Sistem menyimpan `received_date` pada record order
6. Data ini digunakan untuk menghitung rata-rata Lead Time per produk

## CRUD

### 1. List dan Tambah Stok Barang

#### URL

/products/{id}/orders

#### Metode

GET, POST

#### Deskripsi

GET untuk menampilkan form tambah stok barang, POST untuk menambahkan stok barang

#### Parameter

ordered_by
product_id
order_quantity
supplier_id
received_date (opsional — bisa diisi saat order jika barang langsung diterima)

### 2. Update Tanggal Terima

#### URL

/products/{id}/orders (POST dengan action=receive_order)

#### Metode

POST

#### Deskripsi

Update received_date pada order yang sudah ada

#### Parameter

order_id
received_date

### 3. Hapus Order

#### URL

/products/{id}/orders (POST dengan action=delete_order)

#### Metode

POST

#### Deskripsi

Menghapus order dan mengembalikan stok

#### Parameter

order_id

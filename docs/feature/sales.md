## Fitur Sales

* Melakukan operasi kurang stok barang ke dalam database (bisa user role admin dan owner [user](user_dan_autentikasi.md))
[schema](../database/schema.md)

## Field
### Tabel sales
    * id
    * date
    * quantity
    * amount
    * created_by
    * product_id
    * created_at
    * updated_at

## Flow
### Kurang Stok Barang
1. User membuka halaman detail stok produk (`/products/{id}/orders`)
2. Halaman menampilkan tabel riwayat stok gabungan (orders ➕ dan sales ➖)
3. User klik tombol **"− Kurangi Stok"** di bagian atas halaman
4. Sistem menampilkan modal form kurang stok
5. User input kuantitas barang yang ingin dikurangi
6. Sistem menghitung dan menampilkan total amount secara real-time
7. User klik tombol **"Simpan"** pada modal
8. Sistem mengurangi stok barang ke dalam tabel `sales`
9. Sistem memperbarui stok barang pada tabel `products`
10. Modal ditutup, tabel riwayat diperbarui (baris baru dengan icon ➖)

## UI
### Halaman Detail Stok (`/products/{id}/orders`) — Gabungan Orders & Sales
* Halaman ini juga merupakan halaman utama fitur Orders (lihat [orders.md](orders.md))
* Di bagian atas halaman terdapat dua tombol aksi:
    * **"+ Tambah Stok"** → membuka modal form tambah stok (orders)
    * **"− Kurangi Stok"** → membuka modal form kurang stok (sales)
* Tabel riwayat stok menampilkan data gabungan dari tabel `orders` dan `sales`:
    * Baris order ditandai dengan icon ➕ (hijau)
    * Baris sales ditandai dengan icon ➖ (merah)
* Kolom tabel: No | Tanggal | Tipe | Kuantitas | Amount | Keterangan (Supplier / Dibuat oleh)

## CRUD
### 1. List Riwayat & Tambah Penjualan
#### URL
/products/{id}/orders
#### Metode
GET, POST
#### Deskripsi
GET untuk menampilkan halaman detail stok (gabungan riwayat order dan sales).
POST untuk menambahkan data penjualan (kurang stok).
#### Parameter (POST)
created_by
product_id
quantity

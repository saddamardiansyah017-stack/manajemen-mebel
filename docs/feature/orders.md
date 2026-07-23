# Fitur Orders

* Melakukan operasi tambah stok barang ke dalam database (bisa user role admin dan owner [user](user_dan_autentikasi.md))
[schema](../database/schema.md)

## Field
### Tabel orders
    * id
    * date
    * order_quantity
    * amount
    * ordered_by
    * supplier_id
    * product_id
    * created_at
    * updated_at

## Flow
### Tambah Stok Barang
1. User pilih barang pada list product
2. User klik tombol "Tambah Stok"
3. User diarahkan ke halaman detail stock product
4. Sistem menampilkan list order
5. User klik tambah stock
6. Sistem menampilkan form tambah stock
7. User input kuantitas barang yang ingin diorder
8. User search supplier
9. Sistem menampilkan daftar supplier
10. User pilih supplier
11. Sistem calculate amount
12. Sistem menampilkan total amount
13. User klik tombol "Tambah Stock"
14. Sistem menambahkan stock ke dalam database
15. Sistem menambahkan stok barang ke dalam tabel products

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
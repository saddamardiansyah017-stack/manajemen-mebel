# Fitur Product

* Mengelola data product
* Melakukan CRUD (Create, Read, Update, Delete) data product (bisa user role admin dan owner [user](user_dan_autentikasi.md))
[schema](../database/schema.md)

## Field
### Tabel products
    * id
    * name
    * unit
    * price
    * stock
    * created_at
    * updated_at

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
/products
### Metode
GET, POST
### Deskripsi
 GET untuk menampilkan form tambah product, POST untuk menambahkan product
### Parameter
name
 unit
 price
 stock

### 3. Halaman Edit Product
### URL
/products/{id}
### Metode
GET, POST
### Deskripsi
 GET untuk menampilkan form edit product, POST untuk mengedit product
### Parameter
name
 unit
 price
 stock

### 4. Halaman Hapus Product
### URL
/products/{id}
### Metode
POST
### Deskripsi
 untuk menghapus product
### Parameter
Tidak Ada

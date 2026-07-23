# Fitur Supplier

- Mengelola data supplier
- Melakukan CRUD (Create, Read, Update, Delete) data supplier (bisa user role admin dan owner [user](user_dan_autentikasi.md))
- Data supplier digunakan sebagai referensi saat membuat order
- Menyimpan default lead time per supplier sebagai fallback ketika belum ada data order aktual
  [schema](../database/schema.md)

## Field

### Tabel suppliers

    * id
    * name
    * address
    * phone
    * email
    * default_lead_time (default: 7 hari — digunakan sebagai fallback perhitungan Lead Time jika belum ada riwayat order)
    * created_at
    * updated_at

## CRUD

### 1. Halaman Daftar Supplier

### URL

/suppliers

### Metode

GET

### Deskripsi

untuk menampilkan daftar supplier

### Parameter

Tidak Ada

### 2. Halaman Tambah Supplier

### URL

/suppliers

### Metode

GET, POST

### Deskripsi

GET untuk menampilkan form tambah supplier, POST untuk menambahkan supplier

### Parameter

name
address
phone
email
default_lead_time (opsional, default: 7)

### 3. Halaman Edit Supplier

### URL

/suppliers/{id}

### Metode

GET, POST

### Deskripsi

GET untuk menampilkan form edit supplier, POST untuk mengedit supplier

### Parameter

name
address
phone
email
default_lead_time (opsional, default: 7)

### 4. Halaman Hapus Supplier

### URL

/suppliers/{id}

### Metode

POST

### Deskripsi

untuk menghapus supplier

### Parameter

Tidak Ada

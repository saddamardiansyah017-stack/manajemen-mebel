# Fitur Management User Dan Autentikasi

* Mengelola data user dan melakukan autentikasi
[schema](../database/schema.md)

## Field
### Tabel users
    * id
    * username
    * password
    * role
    * created_at
    * updated_at


## Role
* admin
* owner

## Autentikasi
    * login
    * logout

## Manajemen User (role: owner)
    * CRUD User (Create, Read, Update, Delete)


## 1. Halaman Login
### URL
/login
### Metode
GET, POST
### Deskripsi
 untuk melakukan login
### Parameter
 username
 password

## 2. Halaman Daftar User
### URL
/users
### Metode
GET
### Deskripsi
 untuk menampilkan daftar user
### Parameter
Tidak Ada

## 3. Halaman Tambah User
### URL
/users
### Metode
GET, POST
### Deskripsi
 GET untuk menampilkan form tambah user, POST untuk menambahkan user
### Parameter
username
 password
 role

## 4. Halaman Edit User
### URL
/users/{id}
### Metode
GET, POST
### Deskripsi
 GET untuk menampilkan form edit user, POST untuk mengedit user
### Parameter
username
 password
 role

## 5. Halaman Hapus User
### URL
/users/{id}
### Metode
POST
### Deskripsi
 untuk menghapus user
### Parameter
Tidak Ada

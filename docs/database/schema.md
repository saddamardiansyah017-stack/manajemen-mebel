Table users {
id int [primary key]
username string [unique]
password string [note: 'Hashed dengan password_hash']
role enum('owner','admin','user')
created_at datetime
updated_at datetime
}

Table suppliers {
id int [primary key]
name string
address text
phone string
email string
default_lead_time int [default: 7, note: 'Default lead time (hari) jika belum ada data order']
created_at datetime
updated_at datetime
}

Table products {
id int [primary key]
name string
unit string
price decimal
stock int
ordering_cost decimal [note: 'Biaya pesan per order (S) untuk EOQ']
holding_cost decimal [note: 'Biaya simpan per unit/tahun (H) untuk EOQ']
created_at datetime
updated_at datetime
}

Table orders {
id int [primary key]
date date
received_date date [null, note: 'Tanggal barang diterima, untuk hitung Lead Time']
order_quantity int
amount decimal
ordered_by int [ref: > users.id]
supplier_id int [ref: > suppliers.id]
product_id int [ref: > products.id]
created_at datetime
updated_at datetime
}

Table product_suppliers {
id int [primary key]
product_id int [ref: > products.id]
supplier_id int [ref: > suppliers.id]
is_primary tinyint [default: 0, note: 'Supplier utama untuk produk ini']
default_lead_time int [null, note: 'Override lead time khusus relasi ini (hari)']
created_at datetime

indexes {
(product_id, supplier_id) [unique]
}
}

Table sales {
id int [primary key]
date date
quantity int
amount decimal
product_id int [ref: > products.id]
created_by int [ref: > users.id]
created_at datetime
updated_at datetime
}

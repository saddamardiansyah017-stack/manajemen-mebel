Table users {
  id int [primary key]
  username string [unique]
  password string
  role string
  created_at datetime
  updated_at datetime
}

Table suppliers {
  id int [primary key]
  name string
  address text
  phone string
  created_at datetime
  updated_at datetime
}

Table products {
  id int [primary key]
  name string
  unit string
  price decimal
  stock int
  created_at datetime
  updated_at datetime
}

Table orders {
  id int [primary key]
  date datetime
  order_quantity int
  amount decimal
  ordered_by int [ref: > users.id]
  supplier_id int [ref: > suppliers.id]
  product_id int [ref: > products.id]
  created_at datetime
  updated_at datetime
}

Table sales {
  id int [primary key]
  date datetime
  quantity int
  amount decimal
  product_id int [ref: > products.id]
  created_by int [ref: > users.id]
  created_at datetime
  updated_at datetime
}

Table eoqs {
  id int [primary key]
  product_id int [ref: > products.id]
  demmand_count int 
  fee_order decimal
  fee_save decimal
  result int
  created_at datetime
  updated_at datetime
}



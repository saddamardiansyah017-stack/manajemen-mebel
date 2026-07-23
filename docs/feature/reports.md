# Fitur Reports

- Menampilkan laporan lengkap semua produk beserta data inventori
- Demand menggunakan metode annualisasi (proyeksi tahunan dari data yang tersedia)
- Akses: role admin dan owner [user](user_dan_autentikasi.md)
  [schema](../database/schema.md)

## Halaman

### 1. Halaman Report

#### URL

/reports

#### Metode

GET

#### Deskripsi

Menampilkan tabel laporan seluruh produk dengan kolom:

| Kolom              | Keterangan                                                    |
| ------------------ | ------------------------------------------------------------- |
| Nama Produk        | Dari `products.name`                                          |
| Unit               | Satuan produk                                                 |
| Stok Saat Ini      | Dari `products.stock`                                         |
| Harga              | Dari `products.price`                                         |
| Demand Tahunan (D) | Proyeksi annualisasi: `⌈(total_sales / hari_data) × 365⌉`     |
| Data Bulan         | Jumlah bulan data tersedia (label "proyeksi" jika < 12 bulan) |
| Biaya Pesan (S)    | Dari `products.ordering_cost`                                 |
| Biaya Simpan (H)   | Dari `products.holding_cost`                                  |
| EOQ                | Hasil perhitungan `⌈√((2×D×S)/H)⌉`                            |
| Lead Time          | Rata-rata hari dari `orders.received_date - orders.date`      |
| Safety Stock       | `⌈(D_max_harian - D_avg_harian) × Lead Time⌉`                 |
| ROP                | `⌈(D_avg_harian × Lead Time) + Safety Stock⌉`                 |
| Status             | "Perlu Reorder" jika stok ≤ ROP, "Aman" jika stok > ROP       |

## Logika Perhitungan

Semua perhitungan menggunakan `InventoryCalculator` class ([eoq.md](eoq.md)).

### Annualisasi Demand

```
D = ⌈(total_penjualan / jumlah_hari_data) × 365⌉
```

### EOQ

```
EOQ = ⌈√((2 × D × S) / H)⌉
```

### Lead Time

```
LT = AVG(received_date - date) dari tabel orders per produk
     (hanya order dengan received_date IS NOT NULL dan received_date >= date)
```

### Safety Stock

```
SS = ⌈(D_max_harian - D_avg_harian) × LT⌉
```

- `D_max_harian` = penjualan harian maksimum (dari `sales` di-group by `date`)
- `D_avg_harian` = rata-rata penjualan harian

### ROP (Reorder Point)

```
ROP = ⌈(D_avg_harian × LT) + SS⌉
```

## Edge Cases

| Kondisi                             | Tampilan                                                        |
| ----------------------------------- | --------------------------------------------------------------- |
| Produk tanpa data `received_date`   | Lead Time = "N/A", SS = 0, ROP = 0, Status = "Data belum cukup" |
| Produk tanpa data sales             | Demand = 0, EOQ = 0, SS = 0, ROP = 0                            |
| ordering_cost atau holding_cost = 0 | EOQ = 0 (tidak dihitung)                                        |
| Data penjualan < 3 bulan            | Tampilkan warning "estimasi"                                    |

## Hak Akses

- Role `admin` dan `owner` dapat mengakses halaman ini
- Role lain akan diredirect ke `/products`

# Fitur EOQ (Economic Order Quantity)

- Menghitung jumlah pesanan optimal untuk setiap produk
- Menghitung Safety Stock dan Reorder Point (ROP)
- Melakukan bulk order berdasarkan hasil perhitungan EOQ
- Akses: role admin dan owner [user](user_dan_autentikasi.md)
  [schema](../database/schema.md)

## Formula EOQ

```
EOQ = ⌈√((2 × D × S) / H)⌉
```

Dimana:

- **D** = Demand / permintaan tahunan (proyeksi annualisasi)
- **S** = Ordering cost / biaya pesan per order (dari `products.ordering_cost`)
- **H** = Holding cost / biaya simpan per unit per tahun (dari `products.holding_cost`)

## Annualisasi Demand

Demand tahunan (D) dihitung dengan metode **annualisasi** — bukan hanya menjumlahkan data 12 bulan terakhir:

```
D = ⌈(total_penjualan / jumlah_hari_data) × 365⌉
```

**Alasan**: Jika data penjualan belum genap 12 bulan (misal baru 2 bulan), penjumlahan langsung menghasilkan D yang terlalu kecil. Annualisasi memproyeksikan demand harian rata-rata ke setahun penuh.

**Warning badge**: Jika data kurang dari 3 bulan, ditampilkan peringatan bahwa hasil merupakan estimasi.

### Sumber data

- `total_penjualan`: `SUM(quantity)` dari tabel `sales` untuk produk terkait
- `jumlah_hari_data`: `DATEDIFF(NOW(), MIN(date))` dari tabel `sales`
- `data_months`: `jumlah_hari_data / 30` (untuk informasi UI)

## Formula Safety Stock

```
SS = ⌈(Dmax_harian - Davg_harian) × Lead Time⌉
```

Dimana:

- **Dmax_harian** = Penjualan harian maksimum (`MAX(SUM quantity GROUP BY date)`)
- **Davg_harian** = Rata-rata penjualan harian (`AVG(SUM quantity GROUP BY date)`)
- **Lead Time** = Rata-rata waktu tunggu pesanan (`AVG(DATEDIFF(received_date, date))` dari orders yang sudah diterima)

## Formula ROP (Reorder Point)

```
ROP = ⌈(Davg_harian × Lead Time) + Safety Stock⌉
```

### Status ROP

| Kondisi                          | Status                       |
| -------------------------------- | ---------------------------- |
| Lead Time = 0 atau Avg daily = 0 | `no_data` (Data belum cukup) |
| Stok ≤ ROP                       | `reorder` (Perlu Reorder)    |
| Stok > ROP                       | `aman` (Stok Aman)           |

## Syarat Perhitungan

EOQ hanya dihitung jika:

- `ordering_cost > 0`
- `holding_cost > 0`
- `demand > 0` (ada data penjualan)

ROP/Safety Stock hanya dihitung jika:

- `lead_time > 0` (ada order yang sudah diterima)
- `avg_daily > 0` (ada data penjualan)

## Class InventoryCalculator

Semua kalkulasi inventori terpusat di `app/core/InventoryCalculator.php` (static methods):

| Method                                                   | Parameter          | Return   |
| -------------------------------------------------------- | ------------------ | -------- |
| `calculateEOQ($demand, $orderingCost, $holdingCost)`     | D, S, H            | `int`    |
| `calculateSafetyStock($maxDaily, $avgDaily, $leadTime)`  | demand stats, LT   | `int`    |
| `calculateROP($avgDaily, $leadTime, $safetyStock)`       | avg, LT, SS        | `int`    |
| `determineROPStatus($stock, $rop, $leadTime, $avgDaily)` | stok, ROP, LT, avg | `string` |
| `annualizeDemand($totalDemand, $daysSpan)`               | total, hari        | `array`  |
| `calculateAll(array $params)`                            | semua parameter    | `array`  |

### Unit Test

```bash
php tests/InventoryCalculatorTest.php
```

43 test cases mencakup: EOQ, Safety Stock, ROP, ROP Status, calculateAll, annualizeDemand.

## Halaman

### 1. Halaman EOQ (`/eoq`)

#### Metode

GET

#### Deskripsi

Menampilkan tabel semua produk yang memenuhi syarat perhitungan EOQ beserta:

- Nama produk
- Demand tahunan (D) — dengan label "proyeksi" jika data < 12 bulan
- Biaya pesan (S)
- Biaya simpan (H)
- Hasil EOQ
- Lead Time (rata-rata hari)
- Safety Stock
- ROP (Reorder Point)
- Status (Perlu Reorder / Aman / Data belum cukup)
- Riwayat stok (50 data terbaru, gabungan orders & sales)

### 2. Halaman Stok Per Produk (`/products/{id}/orders`)

#### Metode

GET / POST

#### Deskripsi

Menampilkan manajemen stok per produk dengan:

- Panel **Rekomendasi EOQ**: D, S, H, EOQ, tombol "Pesan Sesuai EOQ"
- Panel **ROP & Safety Stock**: Lead Time, SS, ROP, status badge
- Warning jika data < 3 bulan (estimasi)
- Tabel riwayat stok (orders + sales)
- Form tambah stok, kurangi stok, terima pesanan

### 3. Bulk Order (`/eoq/bulkOrder`)

#### Metode

POST

#### Deskripsi

Membuat pesanan massal untuk semua produk yang memenuhi syarat EOQ dari satu supplier yang dipilih.

#### Parameter

supplier_id

#### Flow

1. User pilih supplier dari dropdown
2. User klik "Pesan Semua"
3. Sistem iterasi semua produk
4. Untuk setiap produk yang memenuhi syarat → buat order sebanyak EOQ
5. Stok produk diupdate
6. Redirect ke halaman EOQ dengan notifikasi sukses/gagal

# Cara Kerja EOQ, ROP, dan Safety Stock di Sistem Toko Jaya

## Ringkasan Singkat

| Metrik           | Formula                                | Fungsi                                  |
| ---------------- | -------------------------------------- | --------------------------------------- |
| **EOQ**          | √(2×D×S / H)                           | Jumlah optimal per sekali pesan         |
| **Safety Stock** | (Max Daily - Avg Daily) × Lead Time    | Stok cadangan untuk antisipasi lonjakan |
| **ROP**          | (Avg Daily × Lead Time) + Safety Stock | Titik kapan harus pesan ulang           |

---

## User Story: Perjalanan Produk Baru dari Hari ke-0

### Skenario

Admin menambahkan produk baru **"Paku Ulir 7cm"** dengan:

- Stok awal: 50 Kg
- Harga: Rp 20.000/Kg
- Biaya Pesan (S): Rp 50.000 per order
- Biaya Simpan (H): Rp 5.000 per unit/tahun

---

### Hari ke-1: Penjualan Pertama

Admin mencatat penjualan 5 Kg.

**Kondisi sistem:**

- Total demand = 5
- Days span = 0 (baru 1 hari, DATEDIFF = 0)
- Annualized demand (D) = **0** (days_span = 0, belum bisa diproyeksikan)
- EOQ = 0, Safety Stock = 0, ROP = 0
- Status: **no_data** (data belum cukup)

> 💡 Sistem belum bisa menghitung apapun karena belum ada rentang waktu.

---

### Hari ke-3: Penjualan Kedua

Penjualan 3 Kg dicatat.

**Kondisi sistem:**

- Total demand = 5 + 3 = 8
- Days span = 2 hari (dari hari ke-1 ke hari ke-3)
- **Annualized demand (D) = ceil((8 / 2) × 365) = 1,460 Kg/tahun**
- EOQ = ceil(√(2 × 1460 × 50000 / 5000)) = ceil(√29,200,000) = **5,404 Kg** ← terlalu besar!

> ⚠️ Dengan data hanya 2 hari, proyeksi tahunan sangat tidak akurat. Ini normal — sistem akan semakin presisi seiring waktu.

---

### Hari ke-7: Penjualan Ketiga

Penjualan 4 Kg dicatat.

**Kondisi sistem:**

- Total demand = 5 + 3 + 4 = 12
- Days span = 6 hari
- **D = ceil((12 / 6) × 365) = 730 Kg/tahun**
- EOQ = ceil(√(2 × 730 × 50000 / 5000)) = **121 Kg**

**Daily demand stats (untuk Safety Stock & ROP):**

- Hari-1: 5 Kg, Hari-3: 3 Kg, Hari-7: 4 Kg
- max_daily = 5
- avg_daily = total / days_span = 12 / 6 = **2.0 Kg/hari**

**Namun Lead Time belum ada** dari riwayat order (belum pernah pesan barang), sehingga sistem menggunakan fallback:

- Cek `product_suppliers.default_lead_time` → jika ada relasi, gunakan rata-ratanya
- Jika tidak ada → gunakan rata-rata `suppliers.default_lead_time` global
- Jika tidak ada → gunakan hardcode **7 hari**

Dengan fallback Lead Time (misal 7 hari dari supplier default):

- Safety Stock = ceil((5 - 2.0) × 7) = **21 Kg**
- ROP = ceil((2.0 × 7) + 21) = **35 Kg**
- Status: stok 38 > ROP 35 → **Aman**

> 💡 Setelah ada order yang diterima, sistem akan menggunakan Lead Time aktual dari riwayat order.

---

### Hari ke-10: Pesanan Pertama Dibuat

Stok tinggal 38 Kg. Admin pesan 121 Kg (sesuai EOQ) ke supplier.

- Tanggal pesan: Hari ke-10
- Barang diterima: Hari ke-15
- **Lead Time tercatat: 5 hari**

---

### Hari ke-30: Data Mulai Stabil

Setelah 1 bulan, tercatat:

- Total penjualan: 65 Kg dalam 30 hari
- D = ceil((65 / 29) × 365) = **819 Kg/tahun**
- EOQ = ceil(√(2 × 819 × 50000 / 5000)) = **128 Kg**

**Daily demand stats:**

- max_daily = 8 Kg (hari tersibuk)
- avg_daily = 65 / 29 = **2.24 Kg/hari**

**Dengan Lead Time = 5 hari:**

- Safety Stock = ceil((8 - 2.24) × 5) = ceil(28.8) = **29 Kg**
- ROP = ceil((2.24 × 5) + 29) = ceil(40.2) = **41 Kg**

**Stok saat ini: 94 Kg → Status: Aman** (94 > 41)

---

### Hari ke-55: Stok Menipis

Stok turun menjadi 38 Kg.

- ROP masih = 41 Kg
- **Stok 38 ≤ ROP 41 → Status: PERLU REORDER** 🔴

Dashboard menampilkan alert dan admin bisa langsung klik "Pesan" untuk order sesuai EOQ.

---

### Hari ke-180: Data 6 Bulan (Makin Akurat)

- Total penjualan: 380 Kg dalam 180 hari
- D = ceil((380 / 179) × 365) = **776 Kg/tahun**
- avg_daily = 380 / 179 = **2.12 Kg/hari**
- max_daily = 10 Kg
- Lead Time rata-rata (3 pesanan): **4.7 hari**
- Safety Stock = ceil((10 - 2.12) × 4.7) = **38 Kg**
- ROP = ceil((2.12 × 4.7) + 38) = **48 Kg**
- EOQ = ceil(√(2 × 776 × 50000 / 5000)) = **125 Kg**

> ✅ Semakin banyak data, semakin stabil dan akurat prediksi sistem.

---

## Alur Perhitungan di Kode

```
┌─────────────────────────────────────────────────────────────┐
│                    OrderController::index()                   │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  1. Sale::getAnnualizedDemand(product_id)                   │
│     → Query: SUM(quantity), DATEDIFF(NOW(), MIN(date))      │
│     → InventoryCalculator::annualizeDemand(total, days)     │
│     → D = ceil((total / days) × 365)                        │
│                                                              │
│  2. Sale::getDailyDemandStats(product_id)                   │
│     → Query: MAX(daily_qty), SUM(daily_qty), days_span      │
│     → avg_daily = total / days_span                          │
│     → max_daily = hari dengan demand tertinggi               │
│                                                              │
│  3. Order::getAverageLeadTime(product_id)                   │
│     → Fallback bertingkat:                                   │
│       a. AVG(DATEDIFF(received_date, date)) dari orders      │
│          yang sudah diterima untuk produk ini                 │
│       b. Jika tidak ada → AVG(default_lead_time) dari        │
│          product_suppliers yang terkait produk                │
│       c. Jika tidak ada → AVG(default_lead_time) dari        │
│          seluruh tabel suppliers (global)                     │
│       d. Jika tidak ada → hardcode 7.0 hari                  │
│                                                              │
│  4. InventoryCalculator::calculateAll(params)               │
│     → EOQ = ceil(√(2×D×S / H))                             │
│     → SS  = ceil((max_daily - avg_daily) × lead_time)       │
│     → ROP = ceil((avg_daily × lead_time) + SS)              │
│     → Status = stock ≤ ROP ? 'reorder' : 'aman'            │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Kondisi Edge Case

| Kondisi                                     | Perilaku Sistem                                                                |
| ------------------------------------------- | ------------------------------------------------------------------------------ |
| Belum ada penjualan sama sekali             | D=0, semua metrik=0, status `no_data`                                          |
| Penjualan hanya 1 hari (days_span=0)        | D=0, tidak bisa diproyeksikan                                                  |
| Belum ada order yang diterima               | Fallback ke product_suppliers default_lead_time → global supplier avg → 7 hari |
| Produk belum punya relasi product_suppliers | Fallback ke AVG(default_lead_time) seluruh supplier → 7 hari                   |
| Biaya pesan (S) atau simpan (H) belum diisi | EOQ=0, tapi SS & ROP tetap dihitung                                            |
| Data < 30 hari                              | Tetap dihitung tapi proyeksi belum stabil                                      |

---

## Efek Variasi Musiman

Sistem menggunakan **annualisasi linear** (total/hari × 365). Ini berarti:

- Jika data hanya dari **musim ramai** → D akan overestimate
- Jika data hanya dari **musim sepi** → D akan underestimate
- **Semakin mendekati 12 bulan data, semakin akurat** karena mencakup semua musim

---

## Rekomendasi untuk Pengguna

1. **Biarkan sistem berjalan minimal 2-3 bulan** sebelum mengandalkan rekomendasi EOQ
2. **Isi biaya pesan (S) dan biaya simpan (H)** di setiap produk agar EOQ bisa dihitung
3. **Selalu catat tanggal terima** saat barang datang agar Lead Time akurat
4. **Isi default lead time** di data supplier sebagai estimasi awal sebelum ada data order aktual
5. **Hubungkan produk dengan supplier** agar sistem bisa memberikan fallback lead time yang lebih relevan per produk

---

## Mekanisme Fallback Lead Time

Sistem menggunakan strategi fallback bertingkat untuk memastikan perhitungan ROP & Safety Stock selalu bisa dilakukan, meskipun belum ada riwayat order:

```
┌────────────────────────────────────────────────────────────────┐
│            Order::getAverageLeadTime($product_id)              │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  Tier 1: Data Aktual                                           │
│  → AVG(DATEDIFF(received_date, date)) dari orders              │
│    WHERE product_id = ? AND received_date IS NOT NULL          │
│  → Jika ada hasil → return                                     │
│                                                                │
│  Tier 2: Default Lead Time per Produk-Supplier                 │
│  → AVG(default_lead_time) dari product_suppliers               │
│    WHERE product_id = ? AND default_lead_time IS NOT NULL      │
│  → Jika ada hasil → return                                     │
│                                                                │
│  Tier 3: Default Lead Time Global Supplier                     │
│  → AVG(default_lead_time) dari seluruh tabel suppliers         │
│    WHERE default_lead_time IS NOT NULL                          │
│  → Jika ada hasil → return                                     │
│                                                                │
│  Tier 4: Hardcode                                              │
│  → return 7.0 hari                                             │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

Dengan mekanisme ini, produk baru yang belum pernah di-order tetap mendapatkan estimasi Lead Time yang reasonable untuk perhitungan Safety Stock dan ROP. 4. **Perhatikan status "no_data"** — artinya data belum cukup untuk rekomendasi

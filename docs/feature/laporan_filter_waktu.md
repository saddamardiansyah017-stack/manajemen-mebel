# Plan: Filter Laporan Berdasarkan Waktu

## Deskripsi

Menambahkan filter waktu (harian, mingguan, bulanan, tahunan) pada halaman laporan, beserta redesign UI/UX agar informasi yang ditampilkan lebih analitis dan actionable.

---

## 1. Filter Waktu

### Mekanisme

- Select dropdown di header laporan dengan opsi:
  - **Harian** — data hari ini
  - **Mingguan** — 7 hari terakhir
  - **Bulanan** — 30 hari terakhir (default)
  - **Tahunan** — 365 hari terakhir
  - **Custom** — pilih rentang tanggal (date picker from–to)
- Filter dikirim via GET parameter: `?periode=harian|mingguan|bulanan|tahunan` atau `?dari=YYYY-MM-DD&sampai=YYYY-MM-DD`
- Controller menghitung ulang semua metrik berdasarkan rentang waktu yang dipilih

### Dampak pada Kalkulasi

| Metrik       | Pengaruh Filter                                                             |
| ------------ | --------------------------------------------------------------------------- |
| Demand (D)   | Dihitung dari total penjualan dalam periode, lalu diproyeksikan ke 12 bulan |
| EOQ          | Berubah sesuai demand yang difilter                                         |
| Lead Time    | Tetap (rata-rata historis semua waktu)                                      |
| Safety Stock | Berubah karena max_daily & avg_daily dihitung dari periode                  |
| ROP          | Berubah mengikuti safety stock & demand                                     |

---

## 2. UI/UX Layout

### Struktur Halaman

```
┌─────────────────────────────────────────────────────────┐
│ HEADER: "Laporan Inventaris & EOQ"                      │
│ [Filter Periode: ▼ Bulanan]  [Dari: ___] [Sampai: ___] │
│ [Download PDF]                                          │
├─────────────────────────────────────────────────────────┤
│ RINGKASAN KARTU (Summary Cards)                         │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│ │Total     │ │Perlu     │ │Total     │ │Total     │   │
│ │Produk    │ │Reorder   │ │Penjualan │ │Pembelian │   │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘   │
├─────────────────────────────────────────────────────────┤
│ TABEL DETAIL (per produk)                               │
│ Kolom: Produk | Stok | Penjualan | Pembelian | EOQ |   │
│        Lead Time | SS | ROP | Status                    │
├─────────────────────────────────────────────────────────┤
│ INSIGHT / ALERT PANEL                                   │
│ - Produk yang perlu reorder segera                      │
│ - Produk dengan penjualan tertinggi dalam periode       │
│ - Produk tanpa transaksi dalam periode                  │
└─────────────────────────────────────────────────────────┘
```

### Summary Cards (Atas)

| Kartu              | Informasi                           | Warna  |
| ------------------ | ----------------------------------- | ------ |
| Total Produk Aktif | Jumlah produk dengan EOQ > 0        | Biru   |
| Perlu Reorder      | Jumlah produk dengan status reorder | Merah  |
| Total Penjualan    | Sum amount penjualan dalam periode  | Hijau  |
| Total Pembelian    | Sum amount pembelian dalam periode  | Kuning |

### Tabel Detail (Per Produk)

Kolom yang ditampilkan:
| # | Kolom | Keterangan |
|---|-------|------------|
| 1 | Nama Produk | Nama + unit |
| 2 | Stok Saat Ini | Stok real-time |
| 3 | Penjualan (Periode) | Total qty terjual dalam filter waktu |
| 4 | Pembelian (Periode) | Total qty dibeli dalam filter waktu |
| 5 | Demand Tahunan (D) | Proyeksi 12 bulan dari data periode |
| 6 | EOQ | Kuantitas optimal |
| 7 | Lead Time | Rata-rata hari |
| 8 | Safety Stock | Buffer stok |
| 9 | ROP | Titik reorder |
| 10 | Status | Badge: Aman / Perlu Reorder / Data Kurang |

### Insight Panel (Bawah)

- **Produk Perlu Reorder Segera**: List produk dengan stok < ROP, diurutkan paling kritis
- **Top Seller Periode Ini**: 5 produk dengan penjualan terbanyak
- **Tanpa Aktivitas**: Produk yang tidak ada transaksi dalam periode (potensi dead stock)

---

## 3. Informasi yang Ditampilkan

### Wajib Ada

- [x] Filter periode (select + date range)
- [x] Summary cards (total produk, perlu reorder, total penjualan, total pembelian)
- [x] Tabel detail per produk dengan metrik EOQ, SS, ROP
- [x] Status badge per produk
- [x] Tombol download PDF
- [x] Tanggal cetak & user pencetak

### Tambahan (Rekomendasi)

- [ ] Persentase perubahan vs periode sebelumnya (trend ↑↓)
- [ ] Highlight row produk yang kritis (background merah tipis)
- [ ] Sortable columns (klik header untuk sort)
- [ ] Total row di bawah tabel (sum penjualan, sum pembelian)

---

## 4. Implementasi Teknis

### Controller (`ReportController.php`)

```php
// Terima parameter filter
$periode = $_GET['periode'] ?? 'bulanan';
$dari    = $_GET['dari'] ?? null;
$sampai  = $_GET['sampai'] ?? null;

// Tentukan rentang tanggal
switch ($periode) {
    case 'harian':  $days = 1; break;
    case 'mingguan': $days = 7; break;
    case 'bulanan': $days = 30; break;
    case 'tahunan': $days = 365; break;
    case 'custom':  // gunakan $dari & $sampai
}
```

### Model Changes

- `Sale::getSalesByPeriod($product_id, $from, $to)` — penjualan dalam rentang
- `Order::getOrdersByPeriod($product_id, $from, $to)` — pembelian dalam rentang
- `Sale::getAnnualizedDemandByPeriod($product_id, $from, $to)` — demand diproyeksikan dari periode
- `Sale::getDailyDemandStatsByPeriod($product_id, $from, $to)` — stats harian dalam periode

### View Changes

- Tambah form filter di header (select + date inputs)
- Summary cards section
- Insight panel section
- JavaScript: show/hide date picker saat pilih "Custom"

---

## 5. Prioritas Implementasi

| Step | Task                                        | Estimasi |
| ---- | ------------------------------------------- | -------- |
| 1    | Tambah method model (period-based queries)  | -        |
| 2    | Update ReportController dengan filter logic | -        |
| 3    | Redesign view: filter + summary cards       | -        |
| 4    | Redesign view: tabel + insight panel        | -        |
| 5    | JavaScript interaktivitas (filter, sort)    | -        |
| 6    | Update PDF export agar include filter info  | -        |

---

## 6. Wireframe Filter Area

```html
<!-- Filter Bar -->
<div class="filter-bar">
	<select name="periode">
		<option value="harian">Hari Ini</option>
		<option value="mingguan">7 Hari Terakhir</option>
		<option value="bulanan" selected>30 Hari Terakhir</option>
		<option value="tahunan">1 Tahun Terakhir</option>
		<option value="custom">Rentang Custom</option>
	</select>

	<!-- Muncul jika custom -->
	<input type="date" name="dari" />
	<input type="date" name="sampai" />

	<button type="submit">Terapkan</button>
</div>
```

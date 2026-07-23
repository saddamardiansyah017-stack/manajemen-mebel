# Fitur Notifikasi

## Deskripsi

Fitur notifikasi menyediakan akses cepat ke informasi penting melalui ikon bell (🔔) di navbar sebelah kanan atas. Notifikasi ditampilkan dalam bentuk modal dropdown yang muncul saat button diklik.

## Jenis Notifikasi

### 1. Produk Perlu Reorder (Stok ≤ ROP)

- Menampilkan daftar produk yang stoknya sudah mencapai atau di bawah **Reorder Point (ROP)**
- Setiap item menampilkan: nama produk, stok saat ini, dan nilai ROP
- Terdapat tombol "Pesan" yang mengarah ke halaman order produk tersebut
- Ditandai dengan border kiri warna kuning (warning)

### 2. Pesanan Belum Diterima

- Menampilkan daftar order yang belum memiliki `received_date`
- Setiap item menampilkan: nama produk, supplier, jumlah, dan tanggal pesan
- Terdapat tombol "Lihat" yang mengarah ke halaman order produk tersebut
- Ditandai dengan border kiri warna biru (info)

## Badge Counter

- Angka pada badge = jumlah produk perlu reorder + jumlah pesanan belum diterima
- Badge hanya muncul jika total notifikasi > 0
- Badge berwarna merah dengan font putih

## Implementasi Teknis

### Lokasi File

| File                         | Fungsi                                                                  |
| ---------------------------- | ----------------------------------------------------------------------- |
| `app/views/layouts/main.php` | Query data notifikasi, render button & modal HTML, JavaScript toggle    |
| `public/css/style.css`       | Styling `.notif-btn`, `.notif-badge`, `.notif-modal-*`, `.notif-item-*` |

### Arsitektur Data

Data notifikasi diquery langsung di `main.php` (layout) karena:

- Layout di-render oleh semua controller melalui `Controller::view()`
- Framework tidak memiliki middleware atau shared data pattern
- Menggunakan `require_once` untuk model agar tidak konflik dengan model yang sudah di-load controller

#### Model yang Digunakan

- `Order::getPendingOrders()` — mengambil pesanan tanpa `received_date`
- `Product::getAllProducts()` — mengambil semua produk
- `Sale::getAnnualizedDemand($product_id)` — data demand tahunan
- `Sale::getDailyDemandStats($product_id)` — statistik demand harian
- `Order::getAverageLeadTime($product_id)` — rata-rata lead time
- `InventoryCalculator::calculateAll()` — menghitung ROP dan safety stock

#### Logika Perhitungan Reorder

```
Untuk setiap produk:
  1. Hitung annualized demand
  2. Hitung average lead time (4-tier fallback)
  3. Hitung daily demand stats
  4. Jika lead_time > 0 AND avg_daily > 0:
     - Hitung metrics (ROP, safety stock, dll)
     - Jika rop_status === 'reorder' → tambahkan ke notifikasi
```

### UI/UX

- **Posisi**: Button bell di navbar, antara link "Laporan" dan "Keluar"
- **Interaksi**:
  - Klik button → toggle modal (muncul/tutup)
  - Klik overlay (area gelap) → tutup modal
  - Klik tombol × → tutup modal
  - Tekan Escape → tutup modal
- **Tampilan Modal**: Dropdown dari kanan atas, glassmorphic dark theme
- **Responsive**: Modal max-width 420px, max-height 70vh dengan scroll

### CSS Classes

| Class                  | Fungsi                                            |
| ---------------------- | ------------------------------------------------- |
| `.notif-btn`           | Styling button bell (no background, hover effect) |
| `.notif-badge`         | Badge counter merah (absolute positioned)         |
| `.notif-modal-overlay` | Overlay fullscreen semi-transparent               |
| `.notif-modal-box`     | Container modal dengan glassmorphic effect        |
| `.notif-modal-body`    | Body modal dengan overflow scroll                 |
| `.notif-section`       | Grup section (reorder / pending)                  |
| `.notif-section-title` | Judul section (uppercase, muted)                  |
| `.notif-list`          | Container list items                              |
| `.notif-item`          | Item notifikasi individual                        |
| `.notif-item--warning` | Variant border kiri kuning                        |
| `.notif-item--info`    | Variant border kiri biru                          |
| `.notif-item-content`  | Wrapper text content (nama + detail)              |

## Catatan Performa

- Data notifikasi dihitung setiap kali halaman di-load (tidak di-cache)
- Untuk skala besar, pertimbangkan caching atau AJAX lazy-load
- Saat ini cukup efisien untuk jumlah produk < 100

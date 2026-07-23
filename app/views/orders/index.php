<?php
// Helper function untuk format angka: hilangkan desimal ,00 tapi tetap tampilkan desimal seperti 4,5
function format_smart($number) {
    // Jika angka adalah integer atau desimalnya ,00
    if (floor($number) == $number) {
        return number_format($number, 0, ',', '.');
    }
    // Jika ada desimal, tampilkan dengan 1 atau 2 digit
    return rtrim(rtrim(number_format($number, 2, ',', '.'), '0'), ',');
}
?>
<div class="card mb-4">
    <div class="card-header">
        <div>
            <h2 class="text-xl fw-bold">Manajemen Stok — <?= htmlspecialchars($data['product']['name']); ?></h2>
            <p class="text-muted text-sm mt-1">
                Stok saat ini: <strong><?= number_format($data['product']['stock']); ?> <?= htmlspecialchars($data['product']['unit']); ?></strong>
                &nbsp;|&nbsp; Harga satuan: <strong>Rp <?= number_format($data['product']['price'], 0, ',', '.'); ?></strong>
            </p>
        </div>
        <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
            <?php if($_SESSION['role'] !== 'owner'): ?>
            <button class="btn btn-secondary btn-sm" onclick="openModal('modalOrder')" id="btnTambahStok">
                ➕ Tambah Stok
            </button>
            <button class="btn btn-danger btn-sm" onclick="openModal('modalSale')" id="btnKurangiStok">
                ➖ Kurangi Stok
            </button>
            <?php endif; ?>
            <a href="<?= BASEURL; ?>/products" class="btn btn-sm" style="background:rgba(255,255,255,0.1); color:#fff;">← Kembali</a>
        </div>
    </div>
    <div class="card-body">

        <?php if (!empty($data['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($data['error']); ?></div>
        <?php endif; ?>
        <?php if (!empty($data['success'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof showToast === 'function') {
                        showToast('<?= addslashes(htmlspecialchars($data['success'])); ?>');
                    }
                });
            </script>
        <?php endif; ?>

        <!-- Panel Rekomendasi EOQ -->
        <div class="card mb-4" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
            <div class="card-body">
                <h3 class="fw-bold mb-3" style="color: #10b981; font-size: 1.1rem;">📊 Rekomendasi Pemesanan (EOQ)</h3>
                <?php if ($data['ordering_cost'] == 0 || $data['holding_cost'] == 0): ?>
                    <p class="text-muted text-sm mb-0">Parameter EOQ belum lengkap. Silakan isi Biaya Pemesanan (S) dan Biaya Penyimpanan (H) pada halaman <a href="<?= BASEURL; ?>/products/edit/<?= $data['product']['id']; ?>" style="color: #10b981; text-decoration: underline;">Ubah Produk</a>.</p>
                <?php elseif ($data['demand'] == 0): ?>
                    <p class="text-muted text-sm mb-0">Belum ada data penjualan untuk produk ini. Tambahkan penjualan terlebih dahulu.</p>
                <?php elseif ($data['data_months'] < 1): ?>
                    <div style="padding: 1rem; background: rgba(245, 158, 11, 0.1); border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <p class="text-sm mb-2" style="color: #f59e0b; font-weight: 600;">⚠️ Data Penjualan Belum Cukup</p>
                        <p class="text-sm text-muted mb-2">Saat ini baru tersedia <strong><?= number_format($data['data_months'], 1); ?> bulan</strong> data penjualan. Rekomendasi EOQ memerlukan minimal <strong>1 bulan</strong> data untuk hasil yang lebih akurat.</p>
                        <p class="text-sm text-muted mb-0">Silakan tambahkan lebih banyak transaksi penjualan atau tunggu hingga data terkumpul lebih lama.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-wrap" style="gap: 2rem;">
                        <div>
                            <p class="text-sm text-muted mb-1">Permintaan Tahunan (D) <?php if ($data['data_months'] < 12): ?><span style="color:#f59e0b; font-size:0.7rem;">proyeksi</span><?php endif; ?></p>
                            <p class="fw-bold text-lg"><?= format_smart($data['demand']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
                            <?php if ($data['data_months'] > 0 && $data['data_months'] < 12): ?>
                                <p class="text-xs" style="color: #9ca3af; margin-top: 0.25rem;">dari <?= number_format($data['data_months'], 1); ?> bulan data penjualan</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1">Biaya Pemesanan (S)</p>
                            <p class="fw-bold text-lg">Rp <?= number_format($data['ordering_cost'], 0, ',', '.'); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1">Biaya Simpan (H)</p>
                            <p class="fw-bold text-lg">Rp <?= number_format($data['holding_cost'], 0, ',', '.'); ?></p>
                        </div>
                        <div style="border-left: 2px solid rgba(16, 185, 129, 0.3); padding-left: 2rem;">
                            <p class="text-sm text-muted mb-1">Kuantitas Pemesanan Optimal (EOQ)</p>
                            <p class="fw-bold text-2xl" style="color: #10b981;"><?= format_smart($data['eoq']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
                        </div>
                    </div>
                    <?php if($_SESSION['role'] !== 'owner'): ?>
                    <div class="mt-3">
                        <?php if ($data['rop_status'] === 'reorder'): ?>
                        <button class="btn btn-sm" style="background: #10b981; color: #fff; font-weight: 600;" onclick="pesanSesuaiEOQ(<?= $data['eoq']; ?>)">
                            ✨ Pesan Sesuai EOQ
                        </button>
                        <?php else: ?>
                        <button class="btn btn-sm" style="background: rgba(255,255,255,0.1); color: #9ca3af; font-weight: 600; cursor: not-allowed;" disabled title="Stok masih aman, belum perlu reorder">
                            ✨ Pesan Sesuai EOQ
                        </button>
                        <small class="text-muted text-sm d-block mt-1">Stok masih aman — belum perlu melakukan pemesanan.</small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Panel Safety Stock -->
        <div class="card mb-4" style="background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.2);">
            <div class="card-body">
                <h3 class="fw-bold mb-3" style="color: #a855f7; font-size: 1.1rem;">🛡️ Safety Stock</h3>
                <?php if ($data['lead_time'] <= 0 || $data['demand'] <= 0): ?>
                    <p class="text-muted text-sm mb-0">Data belum cukup untuk menghitung Safety Stock. Diperlukan minimal data penjualan dan lead time.</p>
                <?php else: ?>
                    <div class="mb-3" style="padding: 0.75rem 1rem; background: rgba(168, 85, 247, 0.08); border-radius: 8px;">
                        <p class="text-sm text-muted mb-1">Formula:</p>
                        <p class="text-sm fw-bold mb-0" style="color: #a855f7;">SS = (Permintaan Maks × Lead Time Maks) − (Permintaan Rata-rata × Lead Time Rata-rata)</p>
                    </div>
                    <div class="d-flex flex-wrap" style="gap: 2rem;">
                        <div>
                            <p class="text-sm text-muted mb-1">Permintaan Maks/hari</p>
                            <p class="fw-bold text-lg"><?= format_smart($data['max_daily']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1">Lead Time Maks</p>
                            <p class="fw-bold text-lg"><?= $data['lead_time_max']; ?> hari</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1">Permintaan Rata-rata/hari</p>
                            <p class="fw-bold text-lg"><?= format_smart($data['avg_daily']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1">Lead Time Rata-rata</p>
                            <p class="fw-bold text-lg"><?= $data['lead_time']; ?> hari</p>
                            <?php
                            $ltSource = $data['lead_time_source'];
                            $sourceText = '';
                            if ($ltSource['source'] === 'actual') {
                                $sourceText = 'dari riwayat pesanan';
                            } elseif ($ltSource['source'] === 'primary') {
                                $sourceText = 'dari supplier utama: ' . htmlspecialchars($ltSource['supplier_name']);
                            } elseif ($ltSource['source'] === 'average') {
                                $sourceText = 'rata-rata supplier terkait';
                            } else {
                                $sourceText = 'rata-rata global';
                            }
                            ?>
                            <p class="text-xs" style="color: #9ca3af; margin-top: 0.25rem;"><?= $sourceText; ?></p>
                        </div>
                        <div style="border-left: 2px solid rgba(168, 85, 247, 0.3); padding-left: 2rem;">
                            <p class="text-sm text-muted mb-1">Hasil Safety Stock</p>
                            <p class="fw-bold text-2xl" style="color: #a855f7;"><?= format_smart($data['safety_stock']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
                            <p class="text-xs" style="color: #9ca3af; margin-top: 0.25rem;">(<?= format_smart($data['max_daily']); ?> × <?= $data['lead_time_max']; ?>) − (<?= format_smart($data['avg_daily']); ?> × <?= $data['lead_time']; ?>) = <?= format_smart($data['safety_stock']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Panel Reorder Point (ROP) -->
        <div class="card mb-4" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2);">
            <div class="card-body">
                <h3 class="fw-bold mb-3" style="color: #3b82f6; font-size: 1.1rem;">📍 Reorder Point (ROP)</h3>
                <?php if ($data['lead_time'] <= 0 || $data['demand'] <= 0): ?>
                    <p class="text-muted text-sm mb-0">Data belum cukup untuk menghitung ROP. Diperlukan minimal data penjualan dan lead time.</p>
                <?php else: ?>
                    <div class="mb-3" style="padding: 0.75rem 1rem; background: rgba(59, 130, 246, 0.08); border-radius: 8px;">
                        <p class="text-sm text-muted mb-1">Formula:</p>
                        <p class="text-sm fw-bold mb-0" style="color: #3b82f6;">ROP = (Permintaan Rata-rata × Lead Time Rata-rata) + Safety Stock</p>
                    </div>
                    <div class="d-flex flex-wrap" style="gap: 2rem;">
                        <div>
                            <p class="text-sm text-muted mb-1">Permintaan Rata-rata/hari</p>
                            <p class="fw-bold text-lg"><?= format_smart($data['avg_daily']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1">Lead Time Rata-rata</p>
                            <p class="fw-bold text-lg"><?= $data['lead_time']; ?> hari</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1">Safety Stock</p>
                            <p class="fw-bold text-lg"><?= format_smart($data['safety_stock']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
                        </div>
                        <div style="border-left: 2px solid rgba(59, 130, 246, 0.3); padding-left: 2rem;">
                            <p class="text-sm text-muted mb-1">Hasil ROP</p>
                            <p class="fw-bold text-2xl" style="color: #3b82f6;"><?= format_smart($data['rop']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
                            <p class="text-xs" style="color: #9ca3af; margin-top: 0.25rem;">(<?= format_smart($data['avg_daily']); ?> × <?= $data['lead_time']; ?>) + <?= format_smart($data['safety_stock']); ?> = <?= format_smart($data['rop']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-muted mb-1">Status</p>
                            <?php if ($data['rop_status'] === 'reorder'): ?>
                                <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600;">⚠️ Perlu Reorder</span>
                            <?php else: ?>
                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600;">✓ Stok Aman</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($data['data_months'] > 0 && $data['data_months'] < 3): ?>
                    <div class="mt-2" style="padding: 0.5rem 0.75rem; background: rgba(245, 158, 11, 0.1); border-radius: 6px; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <p class="text-sm mb-0" style="color: #f59e0b;">⚠️ Data penjualan baru <?= $data['data_months']; ?> bulan — hasil kalkulasi merupakan estimasi. Akurasi meningkat seiring bertambahnya data.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabel Riwayat Stok Gabungan -->
        <h3 class="fw-bold mb-2" style="font-size:1rem;">Riwayat Stok</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                        <th>Diterima</th>
                        <th>Kuantitas</th>
                        <th>Total</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Gabungkan orders dan sales, urutkan berdasarkan date DESC
                    $history = [];
                    foreach ($data['orders'] as $o) {
                        $history[] = [
                            'type'          => 'order',
                            'id'            => $o['id'],
                            'date'          => $o['date'],
                            'received_date' => $o['received_date'] ?? null,
                            'quantity'      => $o['order_quantity'],
                            'amount'        => $o['amount'],
                            'keterangan'    => 'Pemasok: ' . htmlspecialchars($o['supplier_name']) . ' &nbsp;|&nbsp; Oleh: ' . htmlspecialchars($o['ordered_by_name']),
                        ];
                    }
                    foreach ($data['sales'] as $s) {
                        $history[] = [
                            'type'       => 'sale',
                            'id'         => $s['id'],
                            'date'       => $s['date'],
                            'quantity'   => $s['quantity'],
                            'amount'     => $s['amount'],
                            'keterangan' => 'Oleh: ' . htmlspecialchars($s['created_by_name']),
                        ];
                    }
                    usort($history, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
                    ?>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada riwayat stok.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td>
                                    <?php if ($row['type'] === 'order'): ?>
                                        <span class="stok-badge stok-badge--order">➕ Pesanan</span>
                                    <?php else: ?>
                                        <span class="stok-badge stok-badge--sale">➖ Penjualan</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($row['date'])); ?></td>
                                <td>
                                    <?php if ($row['type'] === 'order'): ?>
                                        <?php if (!empty($row['received_date'])): ?>
                                            <span class="text-success"><?= date('d M Y', strtotime($row['received_date'])); ?></span>
                                        <?php else: ?>
                                            <?php if($_SESSION['role'] !== 'owner'): ?>
                                            <form action="<?= BASEURL; ?>/products/<?= $data['product']['id']; ?>/orders" method="POST" style="display:inline-flex; gap:0.25rem; align-items:center;">
                                                <input type="hidden" name="action" value="receive_order">
                                                <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                                                <input type="date" name="received_date" required class="form-control" style="padding:0.2rem 0.4rem; font-size:0.75rem; width:auto;">
                                                <button type="submit" class="btn btn-sm" style="padding:0.2rem 0.5rem; background:#10b981; color:#fff; font-size:0.7rem;" title="Tandai Diterima">✓</button>
                                            </form>
                                            <?php else: ?>
                                            <span class="text-muted">Belum diterima</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($row['quantity']); ?> <?= htmlspecialchars($data['product']['unit']); ?></td>
                                <td>Rp <?= number_format($row['amount'], 0, ',', '.'); ?></td>
                                <td class="text-muted text-sm"><?= $row['keterangan']; ?></td>
                                <td>
                                    <?php if($_SESSION['role'] === 'owner'): ?>
                                        <?php if ($row['type'] === 'order'): ?>
                                        <form action="<?= BASEURL; ?>/products/<?= $data['product']['id']; ?>/orders" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan ini? Stok akan dikurangi sesuai dengan kuantitas pesanan.');">
                                            <input type="hidden" name="action" value="delete_order">
                                            <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                                            <button type="submit" class="btn btn-sm" style="padding: 0.2rem 0.5rem; background: transparent; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.5);" title="Hapus Pesanan">Hapus</button>
                                        </form>
                                        <?php else: ?>
                                        <form action="<?= BASEURL; ?>/products/<?= $data['product']['id']; ?>/orders" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penjualan ini? Stok akan dikembalikan.');">
                                            <input type="hidden" name="action" value="delete_sale">
                                            <input type="hidden" name="sale_id" value="<?= $row['id']; ?>">
                                            <button type="submit" class="btn btn-sm" style="padding: 0.2rem 0.5rem; background: transparent; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.5);" title="Hapus Penjualan">Hapus</button>
                                        </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== MODAL TAMBAH STOK (ORDER) ==================== -->
<div class="stok-modal-overlay" id="modalOrder" onclick="closeModalOutside(event,'modalOrder')">
    <div class="stok-modal-box">
        <div class="stok-modal-header">
            <h3 class="fw-bold">➕ Tambah Stok</h3>
            <button class="stok-modal-close" onclick="closeModal('modalOrder')" aria-label="Tutup">&times;</button>
        </div>
        <div class="stok-modal-body">
            <form action="<?= BASEURL; ?>/products/<?= $data['product']['id']; ?>/orders" method="POST" id="formOrder">
                <input type="hidden" name="action" value="order">
                <div class="form-group">
                    <label for="order_quantity" class="form-label">Kuantitas Pesanan</label>
                    <input type="number" name="order_quantity" id="order_quantity"
                           class="form-control" min="1" required placeholder="Contoh: 50"
                           oninput="hitungAmountOrder()">
                </div>
                <div class="form-group">
                    <label for="supplier_id" class="form-label">Pemasok</label>
                    <select name="supplier_id" id="supplier_id" class="form-control" required onchange="updateEstimasi()">
                        <option value="">-- Pilih Pemasok --</option>
                        <?php foreach ($data['suppliers'] as $supplier): ?>
                            <option value="<?= $supplier['id']; ?>" data-lead-time="<?= $supplier['default_lead_time']; ?>" data-is-primary="<?= isset($supplier['is_primary']) && $supplier['is_primary'] ? '1' : '0'; ?>">
                                <?= htmlspecialchars($supplier['name']); ?><?= isset($supplier['is_primary']) && $supplier['is_primary'] ? ' ★' : ''; ?> (Lead Time: <?= $supplier['default_lead_time']; ?> hari)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="estimasi_kedatangan" class="text-muted text-sm d-block mt-2" style="display: none;">
                        📅 Estimasi tiba: <strong id="estimasi_tanggal"></strong>
                    </small>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Keseluruhan</label>
                    <div id="order-amount-display" class="form-control" style="cursor:default;">Rp 0</div>
                </div>
                <button type="submit" class="btn btn-secondary" style="width:100%;">Simpan Tambah Stok</button>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL PESAN SESUAI EOQ ==================== -->
<div class="stok-modal-overlay" id="modalEOQ" onclick="closeModalOutside(event,'modalEOQ')">
    <div class="stok-modal-box">
        <div class="stok-modal-header">
            <h3 class="fw-bold" style="color: #10b981;">✨ Pesan Sesuai EOQ</h3>
            <button class="stok-modal-close" onclick="closeModal('modalEOQ')" aria-label="Tutup">&times;</button>
        </div>
        <div class="stok-modal-body">
            <form action="<?= BASEURL; ?>/products/<?= $data['product']['id']; ?>/orders" method="POST" id="formEOQ">
                <input type="hidden" name="action" value="order">
                <div class="form-group">
                    <label for="eoq_quantity" class="form-label">Kuantitas Pesanan (EOQ)</label>
                    <input type="number" name="order_quantity" id="eoq_quantity"
                           class="form-control" min="1" required readonly
                           oninput="hitungAmountEOQ()">
                </div>
                <div class="form-group">
                    <label for="eoq_supplier_id" class="form-label">Pemasok</label>
                    <select name="supplier_id" id="eoq_supplier_id" class="form-control" required onchange="updateEstimasiEOQ()">
                        <option value="">-- Pilih Pemasok --</option>
                        <?php foreach ($data['suppliers'] as $supplier): ?>
                            <option value="<?= $supplier['id']; ?>" data-lead-time="<?= $supplier['default_lead_time']; ?>" data-is-primary="<?= isset($supplier['is_primary']) && $supplier['is_primary'] ? '1' : '0'; ?>">
                                <?= htmlspecialchars($supplier['name']); ?><?= isset($supplier['is_primary']) && $supplier['is_primary'] ? ' ★' : ''; ?> (Lead Time: <?= $supplier['default_lead_time']; ?> hari)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="estimasi_kedatangan_eoq" class="text-muted text-sm d-block mt-2" style="display: none;">
                        📅 Estimasi tiba: <strong id="estimasi_tanggal_eoq"></strong>
                    </small>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Keseluruhan</label>
                    <div id="eoq-amount-display" class="form-control" style="cursor:default;">Rp 0</div>
                </div>
                <button type="submit" class="btn" style="background: #10b981; color: #fff; width:100%; font-weight:600;">Konfirmasi Pesanan EOQ</button>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL KONFIRMASI EOQ ==================== -->
<div class="stok-modal-overlay" id="modalConfirmEOQ" onclick="closeModalOutside(event,'modalConfirmEOQ')" style="z-index: 210;">
    <div class="stok-modal-box" style="max-width: 400px; text-align: center;">
        <div class="stok-modal-body py-4">
            <h3 class="fw-bold mb-3">Konfirmasi Pesanan</h3>
            <p class="text-muted mb-4">Apakah Anda yakin ingin memproses pesanan sesuai EOQ ini?</p>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-secondary" style="flex:1;" onclick="closeModal('modalConfirmEOQ')">Batal</button>
                <button type="button" class="btn" style="background: #10b981; color: #fff; flex:1;" onclick="submitEOQ()">Konfirmasi Pesanan</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL KURANGI STOK (SALES) ==================== -->
<div class="stok-modal-overlay" id="modalSale" onclick="closeModalOutside(event,'modalSale')">
    <div class="stok-modal-box">
        <div class="stok-modal-header">
            <h3 class="fw-bold">➖ Kurangi Stok</h3>
            <button class="stok-modal-close" onclick="closeModal('modalSale')" aria-label="Tutup">&times;</button>
        </div>
        <div class="stok-modal-body">
            <form action="<?= BASEURL; ?>/products/<?= $data['product']['id']; ?>/orders" method="POST" id="formSale">
                <input type="hidden" name="action" value="sale">
                <p class="text-muted text-sm mb-2">
                    Stok tersedia: <strong style="color:var(--text-main);"><?= number_format($data['product']['stock']); ?> <?= htmlspecialchars($data['product']['unit']); ?></strong>
                </p>
                <div class="form-group">
                    <label for="sale_date" class="form-label">Tanggal Transaksi <span class="text-muted text-sm">(opsional)</span></label>
                    <input type="date" name="sale_date" id="sale_date"
                           class="form-control" value="<?= date('Y-m-d'); ?>">
                    <small class="text-muted text-sm">Kosongkan untuk menggunakan tanggal hari ini</small>
                </div>
                <div class="form-group">
                    <label for="sale_quantity" class="form-label">Kuantitas Penjualan</label>
                    <input type="number" name="quantity" id="sale_quantity"
                           class="form-control" min="1" max="<?= $data['product']['stock']; ?>"
                           required placeholder="Contoh: 10"
                           oninput="hitungAmountSale()">
                </div>
                <div class="form-group">
                    <label class="form-label">Total Keseluruhan</label>
                    <div id="sale-amount-display" class="form-control" style="cursor:default;">Rp 0</div>
                </div>
                <button type="submit" class="btn btn-danger" style="width:100%;">Simpan Penjualan</button>
            </form>
        </div>
    </div>
</div>

<style>
/* ---- Toast ---- */
.toast-container {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.toast {
    background: #10b981;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    transform: translateX(120%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}
.toast.show {
    transform: translateX(0);
}
</style>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
const hargaSatuan = <?= (float) $data['product']['price']; ?>;

function hitungAmountOrder() {
    const qty   = parseInt(document.getElementById('order_quantity').value) || 0;
    document.getElementById('order-amount-display').textContent =
        'Rp ' + (qty * hargaSatuan).toLocaleString('id-ID');
}

function hitungAmountEOQ() {
    const qty   = parseInt(document.getElementById('eoq_quantity').value) || 0;
    document.getElementById('eoq-amount-display').textContent =
        'Rp ' + (qty * hargaSatuan).toLocaleString('id-ID');
}

function hitungAmountSale() {
    const qty   = parseInt(document.getElementById('sale_quantity').value) || 0;
    document.getElementById('sale-amount-display').textContent =
        'Rp ' + (qty * hargaSatuan).toLocaleString('id-ID');
}

function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Auto-select primary supplier jika ada
    if (id === 'modalOrder' || id === 'modalEOQ') {
        const selectId = id === 'modalOrder' ? 'supplier_id' : 'eoq_supplier_id';
        const select = document.getElementById(selectId);
        if (select) {
            // Cari option dengan data-is-primary="1"
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].getAttribute('data-is-primary') === '1') {
                    select.selectedIndex = i;
                    // Trigger update estimasi
                    if (id === 'modalOrder') {
                        updateEstimasi();
                    } else {
                        updateEstimasiEOQ();
                    }
                    break;
                }
            }
        }
    }
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

function closeModalOutside(event, id) {
    if (event.target.id === id) closeModal(id);
}

function pesanSesuaiEOQ(eoqValue) {
    // Isi otomatis kuantitas order dengan nilai EOQ
    document.getElementById('eoq_quantity').value = eoqValue;
    hitungAmountEOQ();
    // Buka modal eoq
    openModal('modalEOQ');
}

function showToast(message) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = '<span>✅</span> ' + message;
    
    container.appendChild(toast);
    
    // Trigger reflow
    void toast.offsetWidth;
    
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function submitEOQ() {
    document.getElementById('formEOQ').dataset.confirmed = "true";
    document.getElementById('formEOQ').submit();
}

function updateEstimasi() {
    const select = document.getElementById('supplier_id');
    const estimasiDiv = document.getElementById('estimasi_kedatangan');
    const estimasiTanggal = document.getElementById('estimasi_tanggal');
    
    if (!select || !estimasiDiv || !estimasiTanggal) return;
    
    const selectedOption = select.options[select.selectedIndex];
    const leadTime = parseInt(selectedOption.getAttribute('data-lead-time')) || 0;
    
    if (leadTime > 0 && select.value) {
        const today = new Date();
        const estimatedDate = new Date(today);
        estimatedDate.setDate(estimatedDate.getDate() + leadTime);
        
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        estimasiTanggal.textContent = estimatedDate.toLocaleDateString('id-ID', options);
        estimasiDiv.style.display = 'block';
    } else {
        estimasiDiv.style.display = 'none';
    }
}

function updateEstimasiEOQ() {
    const select = document.getElementById('eoq_supplier_id');
    const estimasiDiv = document.getElementById('estimasi_kedatangan_eoq');
    const estimasiTanggal = document.getElementById('estimasi_tanggal_eoq');
    
    if (!select || !estimasiDiv || !estimasiTanggal) return;
    
    const selectedOption = select.options[select.selectedIndex];
    const leadTime = parseInt(selectedOption.getAttribute('data-lead-time')) || 0;
    
    if (leadTime > 0 && select.value) {
        const today = new Date();
        const estimatedDate = new Date(today);
        estimatedDate.setDate(estimatedDate.getDate() + leadTime);
        
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        estimasiTanggal.textContent = estimatedDate.toLocaleDateString('id-ID', options);
        estimasiDiv.style.display = 'block';
    } else {
        estimasiDiv.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const formEOQ = document.getElementById('formEOQ');
    if (formEOQ) {
        formEOQ.addEventListener('submit', function(e) {
            if (!this.dataset.confirmed) {
                e.preventDefault();
                openModal('modalConfirmEOQ');
            }
        });
    }
});
</script>

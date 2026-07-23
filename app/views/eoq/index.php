<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-2xl fw-bold">Rekomendasi EOQ & Riwayat Stok</h1>
    <?php if(in_array($_SESSION['role'], ['admin', 'owner'])): ?>
    <button class="btn btn-primary btn-sm" onclick="openModal('modalBulkOrder')" <?= empty($data['eoq_products']) ? 'disabled' : '' ?>>✨ Pesan Semua Sesuai EOQ</button>
    <?php endif; ?>
</div>

<?php if (!empty($data['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($data['error']); ?></div>
<?php endif; ?>
<?php if (!empty($data['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($data['success']); ?></div>
<?php endif; ?>

<!-- ==================== TABEL EOQ SEMUA PRODUK ==================== -->
<div class="card mb-4" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
    <div class="card-header border-0 pb-0">
        <h3 class="fw-bold" style="color: #10b981; font-size: 1.1rem;">📊 Rekomendasi Pemesanan (EOQ) Semua Produk</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Stok Saat Ini</th>
                        <th>Permintaan Tahunan (D)</th>
                        <th>Biaya Pesan (S)</th>
                        <th>Biaya Simpan (H)</th>
                        <th>EOQ</th>
                        <th>Lead Time</th>
                        <th>Safety Stock</th>
                        <th>ROP</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['eoq_products'])): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted">Tidak ada produk dengan rekomendasi EOQ.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['eoq_products'] as $p): ?>
                        <tr>
                            <td class="fw-medium text-main">
                                <a href="<?= BASEURL; ?>/products/<?= $p['id']; ?>/orders" style="color: inherit; text-decoration: none;" class="hover-underline">
                                    <?= htmlspecialchars($p['name']); ?>
                                </a>
                            </td>
                            <td><?= number_format($p['stock']); ?> <?= htmlspecialchars($p['unit']); ?></td>
                            <td>
                                <?= number_format($p['demand']); ?> <?= htmlspecialchars($p['unit']); ?>
                                <?php if (isset($p['data_months']) && $p['data_months'] < 12): ?>
                                    <br><span class="text-muted" style="font-size:0.7rem;">proyeksi (data <?= $p['data_months']; ?> bln)</span>
                                <?php endif; ?>
                            </td>
                            <td>Rp <?= number_format($p['ordering_cost'], 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($p['holding_cost'], 0, ',', '.'); ?></td>
                            <td class="fw-bold" style="color: #10b981;">
                                <?= $p['eoq'] > 0 ? number_format($p['eoq']) . ' ' . htmlspecialchars($p['unit']) : '-'; ?>
                            </td>
                            <td>
                                <?= $p['lead_time'] > 0 ? $p['lead_time'] . ' hari' : '<span class="text-muted">N/A</span>'; ?>
                            </td>
                            <td>
                                <?= $p['safety_stock'] > 0 ? number_format($p['safety_stock']) . ' ' . htmlspecialchars($p['unit']) : '0'; ?>
                            </td>
                            <td>
                                <?= $p['rop'] > 0 ? number_format($p['rop']) . ' ' . htmlspecialchars($p['unit']) : '0'; ?>
                            </td>
                            <td>
                                <?php if ($p['rop_status'] === 'reorder'): ?>
                                    <span class="badge bg-danger">Perlu Reorder</span>
                                <?php elseif ($p['rop_status'] === 'aman'): ?>
                                    <span class="badge bg-success">Aman</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Data belum cukup</span>
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

<!-- ==================== TABEL RIWAYAT STOK GLOBAL ==================== -->
<div class="card mb-4">
    <div class="card-header border-0 pb-0">
        <h3 class="fw-bold" style="font-size: 1.1rem;">⏱️ Riwayat Stok Global Terbaru</h3>
        <p class="text-muted text-sm mt-1 mb-0">Menampilkan hingga 50 transaksi terakhir (Order & Penjualan) dari semua produk.</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                        <th>Kuantitas</th>
                        <th>Total</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['stock_history'])): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada riwayat stok.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['stock_history'] as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td class="fw-medium text-main">
                                    <a href="<?= BASEURL; ?>/products/<?= $row['product_id']; ?>/orders" style="color: inherit; text-decoration: none;" class="hover-underline">
                                        <?= htmlspecialchars($row['product_name']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($row['type'] === 'order'): ?>
                                        <span class="stok-badge stok-badge--order">➕ Pesanan</span>
                                    <?php else: ?>
                                        <span class="stok-badge stok-badge--sale">➖ Penjualan</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($row['date'])); ?></td>
                                <td><?= number_format($row['quantity']); ?> <?= htmlspecialchars($row['unit']); ?></td>
                                <td>Rp <?= number_format($row['amount'], 0, ',', '.'); ?></td>
                                <td class="text-muted text-sm"><?= $row['keterangan']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.hover-underline:hover { text-decoration: underline !important; }
</style>

<!-- ==================== MODAL PESAN MASSAL ==================== -->
<div class="stok-modal-overlay" id="modalBulkOrder" onclick="closeModalOutside(event,'modalBulkOrder')">
    <div class="stok-modal-box" style="max-width: 650px;">
        <div class="stok-modal-header">
            <h3 class="fw-bold">➕ Tambah Stok Massal (EOQ)</h3>
            <button class="stok-modal-close" onclick="closeModal('modalBulkOrder')" aria-label="Tutup">&times;</button>
        </div>
        <div class="stok-modal-body">
            <form action="<?= BASEURL; ?>/eoq/bulkOrder" method="POST" id="formBulkOrder">
                <?php
                    $total_items = 0;
                    $total_cost = 0;
                    foreach($data['eoq_products'] as $p) {
                        $total_items++;
                        $total_cost += ($p['eoq'] * $p['price']);
                    }
                ?>
                <p class="text-muted text-sm mb-3">
                    Aksi ini akan memproses pesanan/tambah stok untuk <strong><?= $total_items ?> produk</strong> yang memiliki nilai rekomendasi EOQ.
                </p>

                <!-- Tabel Detail Produk -->
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto; margin-bottom: 1rem; border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem;">
                    <table class="table mb-0 text-sm" style="font-size: 0.85rem;">
                        <thead style="position: sticky; top: 0; background: #1e293b; z-index: 1;">
                            <tr>
                                <th>Produk</th>
                                <th>Kuantitas</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['eoq_products'] as $p): ?>
                                <?php 
                                    $qty = $p['eoq'];
                                    $harga = $p['price'];
                                    $subtotal = $qty * $harga;
                                ?>
                                <tr>
                                    <td class="text-white"><?= htmlspecialchars($p['name']); ?></td>
                                    <td><?= number_format($qty); ?> <?= htmlspecialchars($p['unit']); ?></td>
                                    <td>Rp <?= number_format($harga, 0, ',', '.'); ?></td>
                                    <td class="fw-bold text-white">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="form-group mb-2 d-flex justify-content-between align-items-center" style="background: rgba(16, 185, 129, 0.1); padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <label class="form-label mb-0" style="color: #10b981;">Total Biaya Keseluruhan</label>
                    <div style="font-weight:bold; color: #10b981; font-size: 1.1rem;">
                        Rp <?= number_format($total_cost, 0, ',', '.') ?>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="supplier_id" class="form-label">Pilih Pemasok</label>
                    <select name="supplier_id" id="supplier_id" class="form-control" required <?= $_SESSION['role'] === 'owner' ? 'disabled' : '' ?>>
                        <option value="">-- Pilih Pemasok --</option>
                        <?php foreach ($data['suppliers'] as $supplier): ?>
                            <option value="<?= $supplier['id']; ?>">
                                <?= htmlspecialchars($supplier['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if($_SESSION['role'] === 'admin'): ?>
                <button type="submit" class="btn btn-primary" style="width:100%;" onclick="return confirm('Anda yakin ingin memproses penambahan stok massal ini?');">Proses Tambah Stok</button>
                <?php else: ?>
                <div class="alert alert-warning text-center mt-3 mb-0 py-2">
                    <em>Role Owner hanya dapat melihat simulasi pesanan massal ini dan tidak dapat melakukan proses pembelian.</em>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}
function closeModalOutside(event, id) {
    if (event.target.id === id) closeModal(id);
}
</script>

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
                <?php else: ?>
                    <div class="d-flex flex-wrap" style="gap: 2rem;">
                        <div>
                            <p class="text-sm text-muted mb-1">Permintaan Tahunan (D)</p>
                            <p class="fw-bold text-lg"><?= number_format($data['demand']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
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
                            <p class="fw-bold text-2xl" style="color: #10b981;"><?= number_format($data['eoq']); ?> <?= htmlspecialchars($data['product']['unit']); ?></p>
                        </div>
                    </div>
                    <?php if($_SESSION['role'] !== 'owner'): ?>
                    <div class="mt-3">
                        <button class="btn btn-sm" style="background: #10b981; color: #fff; font-weight: 600;" onclick="pesanSesuaiEOQ(<?= $data['eoq']; ?>)">
                            ✨ Pesan Sesuai EOQ
                        </button>
                    </div>
                    <?php endif; ?>
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
                            'type'       => 'order',
                            'id'         => $o['id'],
                            'date'       => $o['date'],
                            'quantity'   => $o['order_quantity'],
                            'amount'     => $o['amount'],
                            'keterangan' => 'Pemasok: ' . htmlspecialchars($o['supplier_name']) . ' &nbsp;|&nbsp; Oleh: ' . htmlspecialchars($o['ordered_by_name']),
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
                            <td colspan="7" class="text-center text-muted">Belum ada riwayat stok.</td>
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
                                <td><?= number_format($row['quantity']); ?> <?= htmlspecialchars($data['product']['unit']); ?></td>
                                <td>Rp <?= number_format($row['amount'], 0, ',', '.'); ?></td>
                                <td class="text-muted text-sm"><?= $row['keterangan']; ?></td>
                                <td>
                                    <?php if ($row['type'] === 'order'): ?>
                                    <form action="<?= BASEURL; ?>/products/<?= $data['product']['id']; ?>/orders" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan ini? Stok akan dikurangi sesuai dengan kuantitas pesanan.');">
                                        <input type="hidden" name="action" value="delete_order">
                                        <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm" style="padding: 0.2rem 0.5rem; background: transparent; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.5);" title="Hapus Pesanan">Hapus</button>
                                    </form>
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
                    <select name="supplier_id" id="supplier_id" class="form-control" required>
                        <option value="">-- Pilih Pemasok --</option>
                        <?php foreach ($data['suppliers'] as $supplier): ?>
                            <option value="<?= $supplier['id']; ?>">
                                <?= htmlspecialchars($supplier['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                    <select name="supplier_id" id="eoq_supplier_id" class="form-control" required>
                        <option value="">-- Pilih Pemasok --</option>
                        <?php foreach ($data['suppliers'] as $supplier): ?>
                            <option value="<?= $supplier['id']; ?>">
                                <?= htmlspecialchars($supplier['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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

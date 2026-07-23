<div class="d-flex justify-content-center">
    <div class="w-100" style="max-width: 600px;">
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl fw-bold">Ubah Produk</h2>
                <a href="<?= BASEURL; ?>/products" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($data['error']); ?></div>
                <?php endif; ?>
                <form action="<?= BASEURL; ?>/products/edit/<?= $data['product']['id']; ?>" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Produk</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($data['product']['name']); ?>" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="unit" class="form-label">Satuan (mis. Pcs, Set, Box)</label>
                        <input type="text" name="unit" id="unit" class="form-control" value="<?= htmlspecialchars($data['product']['unit']); ?>" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="price" class="form-label">Harga (Rp)</label>
                        <input type="number" name="price" id="price" class="form-control" value="<?= htmlspecialchars($data['product']['price']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="stock" class="form-label">Stok</label>
                        <input type="number" name="stock" id="stock" class="form-control" value="<?= htmlspecialchars($data['product']['stock']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ordering_cost" class="form-label">Biaya Pemesanan / Order (S)</label>
                        <input type="number" name="ordering_cost" id="ordering_cost" class="form-control" value="<?= htmlspecialchars($data['product']['ordering_cost'] ?? 0); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="holding_cost" class="form-label">Biaya Penyimpanan per Unit per Tahun (H)</label>
                        <input type="number" name="holding_cost" id="holding_cost" class="form-control" value="<?= htmlspecialchars($data['product']['holding_cost'] ?? 0); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Supplier</label>
                        <small class="text-muted text-sm d-block mb-2">Pilih supplier yang menyuplai produk ini. Tandai 1 sebagai supplier utama.</small>
                        <?php
                            $selectedIds = array_column($data['product_suppliers'] ?? [], 'supplier_id');
                            $primaryId = null;
                            foreach (($data['product_suppliers'] ?? []) as $ps) {
                                if ($ps['is_primary']) $primaryId = $ps['supplier_id'];
                            }
                        ?>
                        <?php if (!empty($data['suppliers'])): ?>
                            <?php foreach ($data['suppliers'] as $supplier): ?>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <input type="checkbox" name="supplier_ids[]" value="<?= $supplier['id']; ?>" id="supplier_<?= $supplier['id']; ?>" <?= in_array($supplier['id'], $selectedIds) ? 'checked' : ''; ?>>
                                <label for="supplier_<?= $supplier['id']; ?>" style="margin:0; cursor:pointer;"><?= htmlspecialchars($supplier['name']); ?> <span class="text-muted text-sm">(LT: <?= $supplier['default_lead_time']; ?> hari)</span></label>
                                <input type="radio" name="primary_supplier_id" value="<?= $supplier['id']; ?>" <?= ($primaryId == $supplier['id']) ? 'checked' : ''; ?> title="Supplier Utama">
                                <small class="text-muted">utama</small>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-sm">Belum ada supplier.</p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-4">Perbarui Produk</button>
                </form>
            </div>
        </div>
    </div>
</div>

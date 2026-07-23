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
                        <small class="text-muted text-sm d-block mb-2">Pilih satu atau lebih supplier yang menyuplai produk ini</small>
                        <?php
                            $selectedIds = array_column($data['product_suppliers'] ?? [], 'supplier_id');
                            $primaryId = null;
                            foreach (($data['product_suppliers'] ?? []) as $ps) {
                                if ($ps['is_primary']) $primaryId = $ps['supplier_id'];
                            }
                        ?>
                        <?php if (!empty($data['suppliers'])): ?>
                            <div class="supplier-checkbox-list">
                                <?php foreach ($data['suppliers'] as $supplier): ?>
                                    <label class="supplier-checkbox-item">
                                        <input type="checkbox" name="supplier_ids[]" value="<?= $supplier['id']; ?>" class="supplier-checkbox-input" data-name="<?= htmlspecialchars($supplier['name']); ?>" data-lead-time="<?= $supplier['default_lead_time']; ?>" <?= in_array($supplier['id'], $selectedIds) ? 'checked' : ''; ?>>
                                        <span class="supplier-checkbox-label"><?= htmlspecialchars($supplier['name']); ?></span>
                                        <span class="supplier-checkbox-meta">Lead Time: <?= $supplier['default_lead_time']; ?> hari</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            
                            <div id="primary_supplier_container" class="mt-3" style="display: <?= !empty($selectedIds) ? 'block' : 'none'; ?>;">
                                <label class="form-label">Supplier Utama</label>
                                <select id="primary_supplier_select" name="primary_supplier_id" class="form-select">
                                    <option value="">-- Pilih Supplier Utama --</option>
                                    <?php foreach ($data['suppliers'] as $supplier): ?>
                                        <?php if (in_array($supplier['id'], $selectedIds)): ?>
                                            <option value="<?= $supplier['id']; ?>" <?= ($primaryId == $supplier['id']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($supplier['name']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted text-sm d-block mt-1">Pilih salah satu supplier sebagai supplier utama</small>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-sm">Belum ada supplier. <a href="<?= BASEURL; ?>/suppliers/create">Tambahkan supplier</a>.</p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-4">Perbarui Produk</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const checkboxes = document.querySelectorAll('.supplier-checkbox-input');
    const primaryContainer = document.getElementById('primary_supplier_container');
    const primarySelect = document.getElementById('primary_supplier_select');
    
    if (!checkboxes.length) return;
    
    function updatePrimaryDropdown() {
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        
        if (checked.length === 0) {
            primaryContainer.style.display = 'none';
            return;
        }
        
        primaryContainer.style.display = 'block';
        
        const currentPrimary = primarySelect.value;
        primarySelect.innerHTML = '<option value="">-- Pilih Supplier Utama --</option>' + 
            checked.map(cb => {
                const selected = cb.value === currentPrimary ? 'selected' : '';
                return `<option value="${cb.value}" ${selected}>${cb.dataset.name}</option>`;
            }).join('');
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updatePrimaryDropdown);
    });
})();
</script>

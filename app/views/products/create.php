<div class="d-flex justify-content-center">
    <div class="w-100" style="max-width: 600px;">
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl fw-bold">Tambah Produk Baru</h2>
                <a href="<?= BASEURL; ?>/products" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($data['error']); ?></div>
                <?php endif; ?>
                <form action="<?= BASEURL; ?>/products/create" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Produk</label>
                        <input type="text" name="name" id="name" class="form-control" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="unit" class="form-label">Satuan (mis. Pcs, Set, Box)</label>
                        <input type="text" name="unit" id="unit" class="form-control" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="price" class="form-label">Harga (Rp)</label>
                        <input type="number" name="price" id="price" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="stock" class="form-label">Stok Awal</label>
                        <input type="number" name="stock" id="stock" class="form-control" value="0" required>
                    </div>
                    <div class="form-group">
                        <label for="ordering_cost" class="form-label">Biaya Pemesanan / Order (S)</label>
                        <input type="number" name="ordering_cost" id="ordering_cost" class="form-control" value="0" required>
                    </div>
                    <div class="form-group">
                        <label for="holding_cost" class="form-label">Biaya Penyimpanan per Unit per Tahun (H)</label>
                        <input type="number" name="holding_cost" id="holding_cost" class="form-control" value="0" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-4">Simpan Produk</button>
                </form>
            </div>
        </div>
    </div>
</div>

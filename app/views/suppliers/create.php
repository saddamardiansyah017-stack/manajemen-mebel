<div class="d-flex justify-content-center">
    <div class="w-100" style="max-width: 600px;">
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl fw-bold">Tambah Pemasok Baru</h2>
                <a href="<?= BASEURL; ?>/suppliers" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($data['error']); ?></div>
                <?php endif; ?>
                <form action="<?= BASEURL; ?>/suppliers/create" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Pemasok</label>
                        <input type="text" name="name" id="name" class="form-control" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone" id="phone" class="form-control" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Alamat Email (Opsional)</label>
                        <input type="email" name="email" id="email" class="form-control" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="default_lead_time" class="form-label">Estimasi Waktu Pengiriman (hari)</label>
                        <input type="number" name="default_lead_time" id="default_lead_time" class="form-control" value="7" min="1" max="90" required>
                        <small class="text-muted text-sm">Rata-rata hari dari pesan sampai barang diterima</small>
                    </div>
                    <div class="form-group">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea name="address" id="address" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-4">Simpan Pemasok</button>
                </form>
            </div>
        </div>
    </div>
</div>

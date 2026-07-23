<div class="d-flex justify-content-center">
    <div class="w-100" style="max-width: 600px;">
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl fw-bold">Ubah Pemasok</h2>
                <a href="<?= BASEURL; ?>/suppliers" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($data['error']); ?></div>
                <?php endif; ?>
                <form action="<?= BASEURL; ?>/suppliers/edit/<?= $data['supplier']['id']; ?>" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Pemasok</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($data['supplier']['name']); ?>" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($data['supplier']['phone']); ?>" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Alamat Email (Opsional)</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($data['supplier']['email']); ?>" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea name="address" id="address" class="form-control" rows="3" required><?= htmlspecialchars($data['supplier']['address']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-4">Perbarui Pemasok</button>
                </form>
            </div>
        </div>
    </div>
</div>

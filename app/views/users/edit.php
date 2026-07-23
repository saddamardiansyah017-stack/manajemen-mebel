<div class="d-flex justify-content-center">
    <div class="w-100" style="max-width: 500px;">
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl fw-bold">Ubah Pengguna</h2>
                <a href="<?= BASEURL; ?>/users" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($data['error']); ?></div>
                <?php endif; ?>
                <form action="<?= BASEURL; ?>/users/edit/<?= $data['user']['id']; ?>" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Nama Pengguna</label>
                        <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($data['user']['username']); ?>" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi (Biarkan kosong jika tidak diubah)</label>
                        <input type="password" name="password" id="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="role" class="form-label">Peran</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="admin" <?= $data['user']['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="owner" <?= $data['user']['role'] === 'owner' ? 'selected' : ''; ?>>Pemilik</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-4">Perbarui Pengguna</button>
                </form>
            </div>
        </div>
    </div>
</div>

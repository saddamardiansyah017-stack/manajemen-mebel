<div class="d-flex justify-content-center align-items-center min-h-screen" style="margin-top: -100px;">
    <div class="login-wrapper">
        <div class="card">
            <div class="card-header text-center d-flex justify-content-center">
                <h2 class="nav-brand text-2xl">Masuk ke Toko Jaya</h2>
            </div>
            <div class="card-body">
                <?php if(!empty($data['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($data['error']); ?></div>
                <?php endif; ?>
                <form action="<?= BASEURL; ?>/login" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Nama Pengguna</label>
                        <input type="text" name="username" id="username" class="form-control" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-2">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</div>

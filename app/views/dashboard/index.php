<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-2xl fw-bold">Dashboard</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="text-xl fw-medium mb-3">Informasi Pengguna</h2>
        <div class="d-flex flex-column gap-2">
            <div>
                <span class="text-muted">Nama Pengguna:</span>
                <span class="fw-bold ml-2"><?= htmlspecialchars($data['username']); ?></span>
            </div>
            <div>
                <span class="text-muted">Role:</span>
                <span class="fw-bold ml-2"><?= htmlspecialchars($data['role']); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body text-center py-5">
        <h3 class="text-xl mb-2">Selamat datang di Sistem Manajemen Stok Toko Jaya!</h3>
        <p class="text-muted">Gunakan menu navigasi di atas untuk mengelola data aplikasi.</p>
    </div>
</div>

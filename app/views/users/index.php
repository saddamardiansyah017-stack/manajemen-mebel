<div class="card">
    <div class="card-header">
        <h2 class="text-xl fw-bold">Manajemen Pengguna</h2>
        <?php if($_SESSION['role'] === 'owner'): ?>
            <a href="<?= BASEURL; ?>/users/create" class="btn btn-primary btn-sm">Tambah Pengguna Baru</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Pengguna</th>
                        <th>Peran</th>
                        <th>Dibuat Pada</th>
                        <?php if($_SESSION['role'] === 'owner'): ?>
                        <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['users'])): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Tidak ada pengguna ditemukan.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['users'] as $user): ?>
                        <tr>
                            <td><?= $user['id']; ?></td>
                            <td class="fw-medium text-main"><?= htmlspecialchars($user['username']); ?></td>
                            <td>
                                <?php if($user['role'] === 'owner'): ?>
                                    <span class="text-primary fw-bold">Pemilik</span>
                                <?php else: ?>
                                    <span class="text-muted">Admin</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted text-sm"><?= date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <?php if($_SESSION['role'] === 'owner'): ?>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?= BASEURL; ?>/users/edit/<?= $user['id']; ?>" class="btn btn-secondary btn-sm">Ubah</a>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form action="<?= BASEURL; ?>/users/delete/<?= $user['id']; ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');" style="display:inline;">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="text-xl fw-bold">Manajemen Pemasok</h2>
        <a href="<?= BASEURL; ?>/suppliers/create" class="btn btn-primary btn-sm">Tambah Pemasok Baru</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['suppliers'])): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada pemasok ditemukan.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['suppliers'] as $supplier): ?>
                        <tr>
                            <td><?= $supplier['id']; ?></td>
                            <td class="fw-medium text-main"><?= htmlspecialchars($supplier['name']); ?></td>
                            <td><?= htmlspecialchars($supplier['phone']); ?></td>
                            <td><?= htmlspecialchars($supplier['email'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars(strlen($supplier['address']) > 30 ? substr($supplier['address'], 0, 30) . '...' : $supplier['address']); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?= BASEURL; ?>/suppliers/edit/<?= $supplier['id']; ?>" class="btn btn-secondary btn-sm">Ubah</a>
                                    <form action="<?= BASEURL; ?>/suppliers/delete/<?= $supplier['id']; ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pemasok ini?');" style="display:inline;">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

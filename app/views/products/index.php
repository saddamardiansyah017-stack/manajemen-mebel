<div class="card">
    <div class="card-header">
        <h2 class="text-xl fw-bold"><?= $_SESSION['role'] === 'owner' ? 'Monitoring Stok' : 'Manajemen Produk' ?></h2>
        <?php if($_SESSION['role'] !== 'owner'): ?>
        <a href="<?= BASEURL; ?>/products/create" class="btn btn-primary btn-sm">Tambah Produk Baru</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th><?= $_SESSION['role'] === 'owner' ? 'Riwayat Stok' : 'Ubah Stok' ?></th>
                        <?php if($_SESSION['role'] !== 'owner'): ?>
                        <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['products'])): ?>
                    <tr>
                        <td colspan="<?= $_SESSION['role'] === 'owner' ? '5' : '6' ?>" class="text-center text-muted">Tidak ada produk ditemukan.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['products'] as $product): ?>
                        <tr>
                            <td class="fw-medium text-main"><?= htmlspecialchars($product['name']); ?></td>
                            <td><?= htmlspecialchars($product['unit']); ?></td>
                            <td>Rp <?= number_format($product['price'], 0, ',', '.'); ?></td>
                            <td><?= $product['stock']; ?></td>
                            <td>
                                <a href="<?= BASEURL; ?>/products/<?= $product['id']; ?>/orders" class="btn btn-primary btn-sm"><?= $_SESSION['role'] === 'owner' ? 'Riwayat' : 'Ubah Stok' ?></a>
                            </td>
                            <?php if($_SESSION['role'] !== 'owner'): ?>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?= BASEURL; ?>/products/edit/<?= $product['id']; ?>" class="btn btn-secondary btn-sm">Ubah</a>
                                    <form action="<?= BASEURL; ?>/products/delete/<?= $product['id']; ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" style="display:inline;">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
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

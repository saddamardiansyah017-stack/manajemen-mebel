<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="text-2xl fw-bold">Dashboard</h1>
        <p class="text-muted">Selamat datang, <?= htmlspecialchars($data['username']); ?> (<?= htmlspecialchars($data['role']); ?>)</p>
    </div>
</div>

<!-- Summary Cards -->
<div class="dashboard-grid">
    <div class="summary-card summary-card--primary">
        <div class="summary-card-content">
            <div class="summary-card-info">
                <div class="summary-card-label">Total Produk</div>
                <div class="summary-card-value"><?= $data['total_products']; ?></div>
            </div>
            <div class="summary-card-icon">📦</div>
        </div>
    </div>
    
    <div class="summary-card summary-card--success">
        <div class="summary-card-content">
            <div class="summary-card-info">
                <div class="summary-card-label">Total Supplier</div>
                <div class="summary-card-value"><?= $data['total_suppliers']; ?></div>
            </div>
            <div class="summary-card-icon">🏢</div>
        </div>
    </div>
    
    <div class="summary-card summary-card--warning">
        <div class="summary-card-content">
            <div class="summary-card-info">
                <div class="summary-card-label">Perlu Reorder</div>
                <div class="summary-card-value"><?= $data['reorder_count']; ?></div>
            </div>
            <div class="summary-card-icon">⚠️</div>
        </div>
    </div>
    
    <div class="summary-card summary-card--info">
        <div class="summary-card-content">
            <div class="summary-card-info">
                <div class="summary-card-label">Penjualan Bulan Ini</div>
                <div class="summary-card-value"><?= number_format($data['sales_this_month']); ?></div>
            </div>
            <div class="summary-card-icon">📊</div>
        </div>
    </div>
</div>

<!-- Reorder Alert Card -->
<?php if (!empty($data['reorder_products'])): ?>
<div class="card card-danger mb-4">
    <div class="card-danger-header">
        ⚠️ Produk Perlu Segera Dipesan (Stok ≤ ROP)
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Stok Saat Ini</th>
                    <th>ROP</th>
                    <th>Safety Stock</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['reorder_products'] as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']); ?></td>
                    <td>
                        <span class="badge-danger"><?= $product['stock']; ?> <?= htmlspecialchars($product['unit']); ?></span>
                    </td>
                    <td><?= $product['rop']; ?> <?= htmlspecialchars($product['unit']); ?></td>
                    <td><?= $product['safety_stock']; ?> <?= htmlspecialchars($product['unit']); ?></td>
                    <td>
                        <a href="<?= BASEURL; ?>/products/<?= $product['id']; ?>/orders" class="btn btn-sm btn-primary">Pesan</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Insights Cards -->
<div class="insights-grid">
    <!-- Top Products -->
    <div class="card">
        <div class="card-header">
            <h5>🏆 Top 5 Produk Terlaris (30 Hari)</h5>
        </div>
        <?php if (!empty($data['top_products'])): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Total Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['top_products'] as $index => $product): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($product['name']); ?></td>
                        <td>
                            <strong><?= number_format($product['total_sold']); ?></strong> <?= htmlspecialchars($product['unit']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted">Belum ada data penjualan dalam 30 hari terakhir.</p>
        <?php endif; ?>
    </div>

    <!-- Pending Orders -->
    <div class="card">
        <div class="card-header">
            <h5>📋 Pesanan Belum Diterima</h5>
        </div>
        <?php if (!empty($data['pending_orders'])): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Supplier</th>
                        <th>Jumlah</th>
                        <th>Tgl Pesan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['pending_orders'] as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['product_name']); ?></td>
                        <td><?= htmlspecialchars($order['supplier_name']); ?></td>
                        <td><?= number_format($order['order_quantity']); ?> <?= htmlspecialchars($order['product_unit']); ?></td>
                        <td><?= date('d/m/Y', strtotime($order['date'])); ?></td>
                        <td>
                            <form action="<?= BASEURL; ?>/products/<?= $order['product_id']; ?>/orders" method="POST" style="display: flex; gap: 8px; align-items: center;">
                                <input type="hidden" name="action" value="receive_order">
                                <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                <input type="date" name="received_date" style="padding: 6px 10px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: #e0e0e0; width: 150px;" value="<?= date('Y-m-d'); ?>" required>
                                <button type="submit" class="btn btn-sm btn-primary">Terima</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted">Tidak ada pesanan yang tertunda.</p>
        <?php endif; ?>
    </div>
</div>

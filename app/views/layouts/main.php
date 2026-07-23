<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Jaya - Manajemen Stok</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/style.css?v=<?= time(); ?>">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <?php
    // Load notification data
    if (!class_exists('Order')) { require_once '../app/models/Order.php'; }
    if (!class_exists('Product')) { require_once '../app/models/Product.php'; }
    if (!class_exists('Sale')) { require_once '../app/models/Sale.php'; }
    if (!class_exists('InventoryCalculator')) {
        require_once '../app/core/InventoryCalculator.php';
    }

    $notifOrderModel = new Order();
    $notifProductModel = new Product();
    $notifSaleModel = new Sale();

    $notifPendingOrders = $notifOrderModel->getPendingOrders();
    
    $notifReorderProducts = [];
    $notifAllProducts = $notifProductModel->getAllProducts();
    foreach ($notifAllProducts as $notifProduct) {
        $notifDemandData = $notifSaleModel->getAnnualizedDemand($notifProduct['id']);
        $notifDemand = $notifDemandData['annualized_demand'];
        $notifLeadTimeStats = $notifOrderModel->getLeadTimeStats($notifProduct['id']);
        $notifDemandStats = $notifSaleModel->getDailyDemandStats($notifProduct['id']);

        if ($notifLeadTimeStats['avg'] > 0 && $notifDemandStats['avg_daily'] > 0) {
            $notifMetrics = InventoryCalculator::calculateAll([
                'demand'        => $notifDemand,
                'ordering_cost' => (float) $notifProduct['ordering_cost'],
                'holding_cost'  => (float) $notifProduct['holding_cost'],
                'max_daily'     => $notifDemandStats['max_daily'],
                'avg_daily'     => $notifDemandStats['avg_daily'],
                'lead_time'     => $notifLeadTimeStats['avg'],
                'lead_time_max' => $notifLeadTimeStats['max'],
                'stock'         => (int) $notifProduct['stock'],
            ]);

            if ($notifMetrics['rop_status'] === 'reorder') {
                $notifReorderProducts[] = [
                    'id'    => $notifProduct['id'],
                    'name'  => $notifProduct['name'],
                    'unit'  => $notifProduct['unit'],
                    'stock' => $notifProduct['stock'],
                    'rop'   => $notifMetrics['rop'],
                ];
            }
        }
    }
    $notifTotalCount = count($notifPendingOrders) + count($notifReorderProducts);
    ?>
    <nav class="navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="<?= BASEURL; ?>/dashboard" class="nav-brand">Toko Jaya</a>
            <div class="nav-links">
                <span class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['username']); ?> (<?= htmlspecialchars($_SESSION['role']); ?>)</span>
                <a href="<?= BASEURL; ?>/dashboard" class="nav-link <?= strpos($view, 'dashboard') === 0 ? 'active' : ''; ?>">Dashboard</a>
                <!-- <a href="<?= BASEURL; ?>/users" class="nav-link <?= strpos($view, 'users') === 0 ? 'active' : ''; ?>">Pengguna</a> -->
                <?php if($_SESSION['role'] !== 'owner'): ?>
                <a href="<?= BASEURL; ?>/suppliers" class="nav-link <?= strpos($view, 'suppliers') === 0 ? 'active' : ''; ?>">Suplier</a>
                <?php endif; ?>
                <a href="<?= BASEURL; ?>/products" class="nav-link <?= strpos($view, 'products') === 0 ? 'active' : ''; ?>">Produk</a>
                <?php if($_SESSION['role'] === 'owner'): ?>
                <a href="<?= BASEURL; ?>/eoq" class="nav-link <?= strpos($view, 'eoq') === 0 ? 'active' : ''; ?>">Rekomendasi EOQ</a>
                <?php endif; ?>
                <a href="<?= BASEURL; ?>/reports" class="nav-link <?= strpos($view, 'reports') === 0 ? 'active' : ''; ?>">Laporan</a>
                <button class="notif-btn" id="notifToggleBtn" type="button">
                    🔔
                    <?php if ($notifTotalCount > 0): ?>
                    <span class="notif-badge"><?= $notifTotalCount; ?></span>
                    <?php endif; ?>
                </button>
                <a href="<?= BASEURL; ?>/logout" class="nav-link text-danger">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- Notification Modal -->
    <div class="notif-modal-overlay" id="notifModalOverlay">
        <div class="notif-modal-box">
            <div class="stok-modal-header">
                <h5>🔔 Notifikasi</h5>
                <button class="stok-modal-close" id="notifCloseBtn">&times;</button>
            </div>
            <div class="notif-modal-body">
                <?php if ($notifTotalCount === 0): ?>
                    <p class="text-muted">Tidak ada notifikasi.</p>
                <?php else: ?>
                    <?php if (!empty($notifReorderProducts)): ?>
                    <div class="notif-section">
                        <h6 class="notif-section-title">⚠️ Produk Perlu Reorder (<?= count($notifReorderProducts); ?>)</h6>
                        <ul class="notif-list">
                            <?php foreach ($notifReorderProducts as $rp): ?>
                            <li class="notif-item notif-item--warning">
                                <div class="notif-item-content">
                                    <strong><?= htmlspecialchars($rp['name']); ?></strong>
                                    <span class="text-muted text-sm">Stok: <?= $rp['stock']; ?> <?= htmlspecialchars($rp['unit']); ?> | ROP: <?= $rp['rop']; ?></span>
                                </div>
                                <a href="<?= BASEURL; ?>/products/<?= $rp['id']; ?>/orders" class="btn btn-sm btn-primary">Pesan</a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($notifPendingOrders)): ?>
                    <div class="notif-section">
                        <h6 class="notif-section-title">📋 Pesanan Belum Diterima (<?= count($notifPendingOrders); ?>)</h6>
                        <ul class="notif-list">
                            <?php foreach ($notifPendingOrders as $po): ?>
                            <li class="notif-item notif-item--info">
                                <div class="notif-item-content">
                                    <strong><?= htmlspecialchars($po['product_name']); ?></strong>
                                    <span class="text-muted text-sm"><?= htmlspecialchars($po['supplier_name']); ?> · <?= number_format($po['order_quantity']); ?> <?= htmlspecialchars($po['product_unit']); ?> · <?= date('d/m/Y', strtotime($po['date'])); ?></span>
                                </div>
                                <a href="<?= BASEURL; ?>/products/<?= $po['product_id']; ?>/orders" class="btn btn-sm btn-primary">Lihat</a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <main class="container">
        <?php require_once '../app/views/' . $view . '.php'; ?>
    </main>

    <footer class="container text-center text-muted mt-4 mb-4 text-sm">
        &copy; <?= date('Y'); ?> Toko Jaya. Hak cipta dilindungi undang-undang.
    </footer>

    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
    (function() {
        const btn = document.getElementById('notifToggleBtn');
        const overlay = document.getElementById('notifModalOverlay');
        const closeBtn = document.getElementById('notifCloseBtn');

        if (btn && overlay) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                overlay.classList.toggle('active');
            });
            closeBtn.addEventListener('click', function() {
                overlay.classList.remove('active');
            });
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    overlay.classList.remove('active');
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    overlay.classList.remove('active');
                }
            });
        }
    })();
    </script>
    <?php endif; ?>
</body>
</html>

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
                <a href="<?= BASEURL; ?>/logout" class="nav-link text-danger">Keluar</a>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="container">
        <?php require_once '../app/views/' . $view . '.php'; ?>
    </main>

    <footer class="container text-center text-muted mt-4 mb-4 text-sm">
        &copy; <?= date('Y'); ?> Toko Jaya. Hak cipta dilindungi undang-undang.
    </footer>
</body>
</html>

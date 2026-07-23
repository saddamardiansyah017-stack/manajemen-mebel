<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #print-area, #print-area * {
            visibility: visible;
        }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: #ffffff !important;
            color: #000000 !important;
        }
        .no-print {
            display: none !important;
        }
        .card {
            border: 1px solid #e0e0e0 !important;
            box-shadow: none;
            background: #ffffff !important;
        }
        .card-header {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
            background: #f5f5f5 !important;
        }
        .card-body {
            background: #ffffff !important;
        }
        .text-muted, .text-muted * {
            color: #666666 !important;
        }
        .text-main, .fw-medium {
            color: #000000 !important;
        }
        .table {
            color: #000000 !important;
        }
        .table th {
            background: #f0f0f0 !important;
            color: #000000 !important;
            border-bottom: 2px solid #cccccc !important;
        }
        .table td {
            border-bottom: 1px solid #e0e0e0 !important;
        }
        .summary-card {
            background: #f9f9f9 !important;
            border: 1px solid #e0e0e0 !important;
            color: #000000 !important;
        }
        .summary-card-title {
            color: #666666 !important;
        }
        .summary-card-value {
            color: #000000 !important;
        }
        .badge {
            border: 1px solid #cccccc !important;
        }
    }
    .pdf-light-mode {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .pdf-light-mode .card {
        background: #ffffff !important;
        border: 1px solid #e0e0e0 !important;
    }
    .pdf-light-mode .card-header {
        background: #f5f5f5 !important;
        color: #000000 !important;
    }
    .pdf-light-mode .card-body {
        background: #ffffff !important;
    }
    .pdf-light-mode .text-muted,
    .pdf-light-mode .text-muted * {
        color: #666666 !important;
    }
    .pdf-light-mode .text-main,
    .pdf-light-mode .fw-medium {
        color: #000000 !important;
    }
    .pdf-light-mode .table {
        color: #000000 !important;
    }
    .pdf-light-mode .table th {
        background: #f0f0f0 !important;
        color: #000000 !important;
        border-bottom: 2px solid #cccccc !important;
    }
    .pdf-light-mode .table td {
        border-bottom: 1px solid #e0e0e0 !important;
    }
    .pdf-light-mode .summary-card {
        background: #f9f9f9 !important;
        border: 1px solid #e0e0e0 !important;
        color: #000000 !important;
    }
    .pdf-light-mode .summary-card-title {
        color: #666666 !important;
    }
    .pdf-light-mode .summary-card-value {
        color: #000000 !important;
    }
    .pdf-light-mode .badge {
        border: 1px solid #cccccc !important;
    }
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .summary-card {
        padding: 1.5rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .summary-card-title {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }
    .summary-card-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-main);
    }
    .summary-card--blue { border-left: 4px solid #3b82f6; }
    .summary-card--red { border-left: 4px solid #ef4444; }
    .summary-card--green { border-left: 4px solid #10b981; }
    .summary-card--yellow { border-left: 4px solid #f59e0b; }
</style>

<div class="card mb-4 no-print">
    <div class="card-header">
        <h2 class="text-xl fw-bold mb-3">Filter Laporan</h2>
        <form method="GET" action="<?= BASEURL; ?>/reports" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label text-sm">Periode</label>
                <select name="periode" id="periodeSelect" class="form-control" onchange="toggleCustomDates()">
                    <option value="harian" <?= ($data['periode'] === 'harian') ? 'selected' : ''; ?>>Hari Ini</option>
                    <option value="mingguan" <?= ($data['periode'] === 'mingguan') ? 'selected' : ''; ?>>7 Hari Terakhir</option>
                    <option value="bulanan" <?= ($data['periode'] === 'bulanan') ? 'selected' : ''; ?>>30 Hari Terakhir</option>
                    <option value="tahunan" <?= ($data['periode'] === 'tahunan') ? 'selected' : ''; ?>>1 Tahun Terakhir</option>
                    <option value="custom" <?= ($data['periode'] === 'custom') ? 'selected' : ''; ?>>Rentang Custom</option>
                </select>
            </div>
            <div id="customDates" style="display: <?= ($data['periode'] === 'custom') ? 'flex' : 'none'; ?>; gap: 1rem; flex-wrap: wrap;">
                <div style="min-width: 160px;">
                    <label class="form-label text-sm">Dari</label>
                    <input type="date" name="dari" class="form-control" value="<?= $data['dari']; ?>">
                </div>
                <div style="min-width: 160px;">
                    <label class="form-label text-sm">Sampai</label>
                    <input type="date" name="sampai" class="form-control" value="<?= $data['sampai']; ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="align-self: flex-end;">Terapkan Filter</button>
        </form>
    </div>
</div>

<div class="card" id="print-area">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="text-xl fw-bold">Laporan Inventaris & EOQ</h2>
            <p class="text-muted text-sm mb-0 mt-1">
                Periode: <strong><?= date('d M Y', strtotime($data['dari'])); ?></strong> s/d <strong><?= date('d M Y', strtotime($data['sampai'])); ?></strong>
            </p>
        </div>
        <button id="btn-download" class="btn btn-primary btn-sm no-print">Download PDF</button>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <p class="text-muted mb-1 text-sm">Tanggal Cetak: <?= date('d M Y H:i:s') ?></p>
            <p class="text-muted mb-0 text-sm">Dicetak oleh: <?= htmlspecialchars($_SESSION['username']); ?></p>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card summary-card--blue">
                <div class="summary-card-title">Total Produk Aktif</div>
                <div class="summary-card-value"><?= $data['total_produk_aktif']; ?></div>
            </div>
            <div class="summary-card summary-card--red">
                <div class="summary-card-title">Perlu Reorder</div>
                <div class="summary-card-value"><?= $data['perlu_reorder']; ?></div>
            </div>
            <div class="summary-card summary-card--green">
                <div class="summary-card-title">Total Penjualan</div>
                <div class="summary-card-value text-sm">Rp <?= number_format($data['total_penjualan'], 0, ',', '.'); ?></div>
            </div>
            <div class="summary-card summary-card--yellow">
                <div class="summary-card-title">Total Pembelian</div>
                <div class="summary-card-value text-sm">Rp <?= number_format($data['total_pembelian'], 0, ',', '.'); ?></div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Stok Saat Ini</th>
                        <th>Penjualan (Periode)</th>
                        <th>Pembelian (Periode)</th>
                        <th>Demand Tahunan (D)</th>
                        <th>EOQ</th>
                        <th>Lead Time</th>
                        <th>SS</th>
                        <th>ROP</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $filteredReports = array_filter($data['reports'], function($report) {
                        return $report['eoq'] > 0;
                    });
                    ?>
                    <?php if (empty($filteredReports)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted">Tidak ada data laporan dengan rekomendasi EOQ.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($filteredReports as $report): ?>
                        <tr style="<?= $report['rop_status'] === 'reorder' ? 'background: rgba(239, 68, 68, 0.05);' : ''; ?>">
                            <td class="fw-medium text-main"><?= htmlspecialchars($report['name']); ?></td>
                            <td><?= number_format($report['stock']); ?> <?= htmlspecialchars($report['unit']); ?></td>
                            <td>
                                <?= number_format($report['sales_qty_period']); ?> <?= htmlspecialchars($report['unit']); ?>
                                <br><small class="text-muted">Rp <?= number_format($report['sales_amt_period'], 0, ',', '.'); ?></small>
                            </td>
                            <td>
                                <?= number_format($report['orders_qty_period']); ?> <?= htmlspecialchars($report['unit']); ?>
                                <br><small class="text-muted">Rp <?= number_format($report['orders_amt_period'], 0, ',', '.'); ?></small>
                            </td>
                            <td><?= number_format($report['demand'], 0, ',', '.'); ?> <?= htmlspecialchars($report['unit']); ?></td>
                            <td class="fw-bold" style="color: #10b981;">
                                <?= number_format($report['eoq']); ?> <?= htmlspecialchars($report['unit']); ?>
                            </td>
                            <td><?= $report['lead_time']; ?> hari</td>
                            <td>
                                <?= $report['safety_stock'] > 0 ? number_format($report['safety_stock']) : '0'; ?>
                            </td>
                            <td>
                                <?= $report['rop'] > 0 ? number_format($report['rop']) : '0'; ?>
                            </td>
                            <td>
                                <?php if ($report['rop_status'] === 'reorder'): ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; padding: 0.3rem 0.6rem; border-radius: 4px;">Perlu Reorder</span>
                                <?php elseif ($report['rop_status'] === 'aman'): ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 0.3rem 0.6rem; border-radius: 4px;">Aman</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; padding: 0.3rem 0.6rem; border-radius: 4px;">Data Kurang</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Insight Panels -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            <!-- Produk Kritis -->
            <div class="card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2);">
                <div class="card-body">
                    <h3 class="fw-bold mb-3" style="color: #ef4444; font-size: 1rem;">⚠️ Produk Perlu Reorder Segera</h3>
                    <?php if (empty($data['produk_kritis'])): ?>
                        <p class="text-muted text-sm mb-0">Tidak ada produk yang memerlukan reorder saat ini.</p>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach($data['produk_kritis'] as $produk): ?>
                                <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(239, 68, 68, 0.1);">
                                    <div class="fw-medium"><?= htmlspecialchars($produk['name']); ?></div>
                                    <small class="text-muted">
                                        Stok: <?= number_format($produk['stock']); ?> | ROP: <?= number_format($produk['rop']); ?> | 
                                        Perlu: <?= number_format(max(0, $produk['rop'] - $produk['stock'])); ?> <?= htmlspecialchars($produk['unit']); ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Seller -->
            <div class="card" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
                <div class="card-body">
                    <h3 class="fw-bold mb-3" style="color: #10b981; font-size: 1rem;">🏆 Top Seller Periode Ini</h3>
                    <?php if (empty($data['top_sellers'])): ?>
                        <p class="text-muted text-sm mb-0">Tidak ada penjualan dalam periode ini.</p>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach($data['top_sellers'] as $idx => $seller): ?>
                                <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(16, 185, 129, 0.1);">
                                    <div class="fw-medium">#<?= $idx + 1; ?>. <?= htmlspecialchars($seller['name']); ?></div>
                                    <small class="text-muted">
                                        Terjual: <?= number_format($seller['total_sold']); ?> <?= htmlspecialchars($seller['unit']); ?> | 
                                        Total: Rp <?= number_format($seller['total_amount'], 0, ',', '.'); ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pesanan Terlambat -->
            <div class="card" style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2);">
                <div class="card-body">
                    <h3 class="fw-bold mb-3" style="color: #f59e0b; font-size: 1rem;">⏰ Pesanan Terlambat</h3>
                    <?php if (empty($data['pesanan_terlambat'])): ?>
                        <p class="text-muted text-sm mb-0">Tidak ada pesanan yang terlambat saat ini.</p>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0; margin: 0; max-height: 200px; overflow-y: auto;">
                            <?php foreach($data['pesanan_terlambat'] as $order): ?>
                                <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(245, 158, 11, 0.1);">
                                    <div class="fw-medium"><?= htmlspecialchars($order['product_name']); ?></div>
                                    <small class="text-muted">
                                        Supplier: <?= htmlspecialchars($order['supplier_name']); ?> | 
                                        Dipesan: <?= date('d M Y', strtotime($order['date'])); ?> | 
                                        <span style="color: #f59e0b;">Terlambat <?= $order['days_late']; ?> hari</span>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function toggleCustomDates() {
        const select = document.getElementById('periodeSelect');
        const customDates = document.getElementById('customDates');
        if (select.value === 'custom') {
            customDates.style.display = 'flex';
        } else {
            customDates.style.display = 'none';
        }
    }

    document.getElementById('btn-download').addEventListener('click', function () {
        this.style.display = 'none';
        
        var element = document.getElementById('print-area');
        
        // Toggle light mode untuk PDF
        element.classList.add('pdf-light-mode');
        
        var opt = {
            margin:       [0.3, 0.3, 0.3, 0.3],
            filename:     'Laporan_Inventaris_EOQ_' + new Date().toISOString().split('T')[0] + '.pdf',
            image:        { type: 'jpeg', quality: 0.95 },
            html2canvas:  { 
                scale: 1.5,
                useCORS: true,
                letterRendering: true,
                scrollY: 0,
                scrollX: 0,
                backgroundColor: '#ffffff'
            },
            jsPDF:        { 
                unit: 'in', 
                format: 'a4', 
                orientation: 'landscape',
                compress: true
            },
            pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            // Kembalikan ke dark mode
            element.classList.remove('pdf-light-mode');
            document.getElementById('btn-download').style.display = 'inline-block';
        });
    });
</script>

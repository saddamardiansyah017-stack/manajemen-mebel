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
        }
        .no-print {
            display: none !important;
        }
        .card {
            border: none;
            box-shadow: none;
        }
        .card-header {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
    }
</style>

<div class="card" id="print-area">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="text-xl fw-bold">Laporan Inventaris & EOQ</h2>
        <button id="btn-download" class="btn btn-primary btn-sm no-print">⬇️ Download PDF</button>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <p class="text-muted mb-1">Tanggal Cetak: <?= date('d M Y H:i:s') ?></p>
            <p class="text-muted mb-0">Dicetak oleh: <?= htmlspecialchars($_SESSION['username']); ?></p>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Stok Tersedia</th>
                        <th>Total Permintaan (12 Bln)</th>
                        <th>Biaya Pesan (S)</th>
                        <th>Biaya Simpan (H)</th>
                        <th>Rekomendasi EOQ</th>
                        <th>Lead Time</th>
                        <th>Safety Stock</th>
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
                        <tr>
                            <td class="fw-medium text-main"><?= htmlspecialchars($report['name']); ?></td>
                            <td><?= $report['stock']; ?> <?= htmlspecialchars($report['unit']); ?></td>
                            <td><?= number_format($report['demand'], 0, ',', '.'); ?> <?= htmlspecialchars($report['unit']); ?></td>
                            <td>Rp <?= number_format($report['ordering_cost'], 0, ',', '.'); ?></td>
                            <td>Rp <?= number_format($report['holding_cost'], 0, ',', '.'); ?></td>
                            <td class="fw-bold" style="color: #10b981;">
                                <?= number_format($report['eoq'], 0, ',', '.') . ' ' . htmlspecialchars($report['unit']); ?>
                            </td>
                            <td>
                                <?= $report['lead_time'] > 0 ? $report['lead_time'] . ' hari' : '<span class="text-muted">N/A</span>'; ?>
                            </td>
                            <td>
                                <?= $report['safety_stock'] > 0 ? number_format($report['safety_stock']) . ' ' . htmlspecialchars($report['unit']) : '0'; ?>
                            </td>
                            <td>
                                <?= $report['rop'] > 0 ? number_format($report['rop']) . ' ' . htmlspecialchars($report['unit']) : '0'; ?>
                            </td>
                            <td>
                                <?php if ($report['rop_status'] === 'reorder'): ?>
                                    <span class="badge bg-danger">Perlu Reorder</span>
                                <?php elseif ($report['rop_status'] === 'aman'): ?>
                                    <span class="badge bg-success">Aman</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Data belum cukup</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    document.getElementById('btn-download').addEventListener('click', function () {
        // Sembunyikan tombol saat dirender ke PDF
        this.style.display = 'none';
        
        var element = document.getElementById('print-area');
        var opt = {
            margin:       0.5,
            filename:     'Laporan_Inventaris_EOQ.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
        };

        // Mulai generate PDF
        html2pdf().set(opt).from(element).save().then(() => {
            // Tampilkan kembali tombol
            document.getElementById('btn-download').style.display = 'inline-block';
        });
    });
</script>

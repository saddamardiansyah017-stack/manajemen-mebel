<?php
class ReportController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
        // Laporan bisa diakses oleh admin dan owner
        if (!in_array($_SESSION['role'], ['admin', 'owner'])) {
            $this->redirect('/products');
        }
    }

    public function index() {
        $productModel = $this->model('Product');
        $saleModel    = $this->model('Sale');
        $orderModel   = $this->model('Order');

        // Terima parameter filter periode
        $periode = $_GET['periode'] ?? 'bulanan';
        $dariCustom = $_GET['dari'] ?? null;
        $sampaiCustom = $_GET['sampai'] ?? null;

        // Tentukan rentang tanggal berdasarkan periode
        $sampai = date('Y-m-d'); // Hari ini
        switch ($periode) {
            case 'harian':
                $dari = date('Y-m-d');
                break;
            case 'mingguan':
                $dari = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'bulanan':
                $dari = date('Y-m-d', strtotime('-30 days'));
                break;
            case 'tahunan':
                $dari = date('Y-m-d', strtotime('-365 days'));
                break;
            case 'custom':
                if ($dariCustom && $sampaiCustom) {
                    $dari = $dariCustom;
                    $sampai = $sampaiCustom;
                } else {
                    // Default ke bulanan jika custom tidak lengkap
                    $dari = date('Y-m-d', strtotime('-30 days'));
                }
                break;
            default:
                $dari = date('Y-m-d', strtotime('-30 days'));
        }

        $productsRaw = $productModel->getAllProducts();
        $reportData  = [];

        foreach ($productsRaw as $product) {
            // Data berdasarkan periode
            $salesPeriod = $saleModel->getTotalSalesByPeriod($product['id'], $dari, $sampai);
            $ordersPeriod = $orderModel->getTotalOrdersByPeriod($product['id'], $dari, $sampai);
            
            $demandData    = $saleModel->getAnnualizedDemandByPeriod($product['id'], $dari, $sampai);
            $demand        = $demandData['annualized_demand'];
            $ordering_cost = (float) $product['ordering_cost'];
            $holding_cost  = (float) $product['holding_cost'];
            $leadTime      = $orderModel->getAverageLeadTime($product['id']);
            $demandStats   = $saleModel->getDailyDemandStatsByPeriod($product['id'], $dari, $sampai);

            $metrics = InventoryCalculator::calculateAll([
                'demand'        => $demand,
                'ordering_cost' => $ordering_cost,
                'holding_cost'  => $holding_cost,
                'max_daily'     => $demandStats['max_daily'],
                'avg_daily'     => $demandStats['avg_daily'],
                'lead_time'     => $leadTime,
                'stock'         => (int) $product['stock'],
            ]);

            $product['sales_qty_period']  = $salesPeriod['quantity'];
            $product['sales_amt_period']  = $salesPeriod['amount'];
            $product['orders_qty_period'] = $ordersPeriod['quantity'];
            $product['orders_amt_period'] = $ordersPeriod['amount'];
            $product['demand']            = $demand;
            $product['data_months']       = $demandData['data_months'];
            $product['eoq']               = $metrics['eoq'];
            $product['lead_time']         = $leadTime;
            $product['safety_stock']      = $metrics['safety_stock'];
            $product['rop']               = $metrics['rop'];
            $product['rop_status']        = $metrics['rop_status'];

            $reportData[] = $product;
        }

        // Summary cards
        $salesSummary = $saleModel->getAllSalesSummaryByPeriod($dari, $sampai);
        $ordersSummary = $orderModel->getAllOrdersSummaryByPeriod($dari, $sampai);
        
        $totalProdukAktif = count(array_filter($reportData, fn($r) => $r['eoq'] > 0));
        $perluReorder = count(array_filter($reportData, fn($r) => $r['rop_status'] === 'reorder'));

        // Insight data
        $topSellers = $saleModel->getTopSellingProductsByPeriod(5, $dari, $sampai);
        $pesananTerlambat = $orderModel->getLateOrders();
        
        // Produk kritis (reorder + stock paling rendah)
        $produkKritis = array_filter($reportData, fn($r) => $r['rop_status'] === 'reorder');
        usort($produkKritis, fn($a, $b) => ($a['stock'] - $a['rop']) <=> ($b['stock'] - $b['rop']));
        $produkKritis = array_slice($produkKritis, 0, 5);

        $data = [
            'reports'            => $reportData,
            'periode'            => $periode,
            'dari'               => $dari,
            'sampai'             => $sampai,
            'total_produk_aktif' => $totalProdukAktif,
            'perlu_reorder'      => $perluReorder,
            'total_penjualan'    => $salesSummary['total_amount'],
            'total_pembelian'    => $ordersSummary['total_amount'],
            'top_sellers'        => $topSellers,
            'pesanan_terlambat'  => $pesananTerlambat,
            'produk_kritis'      => $produkKritis,
        ];

        $this->view('reports/index', $data);
    }
}

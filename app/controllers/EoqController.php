<?php
class EoqController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
        // Hanya admin dan owner yang boleh akses
        if (!in_array($_SESSION['role'], ['admin', 'owner'])) {
            $this->redirect('/products');
        }
    }

    public function index() {
        $productModel = $this->model('Product');
        $saleModel    = $this->model('Sale');
        $orderModel   = $this->model('Order');

        // 1. Data EOQ Semua Produk
        $productsRaw = $productModel->getAllProducts();
        $eoqProducts = [];
        foreach ($productsRaw as $product) {
            $demandData    = $saleModel->getAnnualizedDemand($product['id']);
            $demand        = $demandData['annualized_demand'];
            $ordering_cost = (float) $product['ordering_cost'];
            $holding_cost  = (float) $product['holding_cost'];
            $leadTime      = $orderModel->getAverageLeadTime($product['id']);
            $demandStats   = $saleModel->getDailyDemandStats($product['id']);

            $metrics = InventoryCalculator::calculateAll([
                'demand'        => $demand,
                'ordering_cost' => $ordering_cost,
                'holding_cost'  => $holding_cost,
                'max_daily'     => $demandStats['max_daily'],
                'avg_daily'     => $demandStats['avg_daily'],
                'lead_time'     => $leadTime,
                'stock'         => (int) $product['stock'],
            ]);

            $product['demand']       = $demand;
            $product['data_months']  = $demandData['data_months'];
            $product['eoq']          = $metrics['eoq'];
            $product['lead_time']    = $leadTime;
            $product['safety_stock'] = $metrics['safety_stock'];
            $product['rop']          = $metrics['rop'];
            $product['rop_status']   = $metrics['rop_status'];

            if ($metrics['eoq'] > 0 || $demand > 0) {
                $eoqProducts[] = $product;
            }
        }

        // 2. Data Riwayat Stok Global
        $allOrders = $orderModel->getAllOrders();
        $allSales  = $saleModel->getAllSales();
        $stockHistory = [];

        foreach ($allOrders as $o) {
            $stockHistory[] = [
                'type'         => 'order',
                'id'           => $o['id'],
                'product_id'   => $o['product_id'],
                'product_name' => $o['product_name'],
                'unit'         => $o['product_unit'],
                'date'         => $o['date'],
                'quantity'     => $o['order_quantity'],
                'amount'       => $o['amount'],
                'keterangan'   => 'Pemasok: ' . htmlspecialchars($o['supplier_name']) . ' &nbsp;|&nbsp; Oleh: ' . htmlspecialchars($o['ordered_by_name']),
            ];
        }

        foreach ($allSales as $s) {
            $stockHistory[] = [
                'type'         => 'sale',
                'id'           => $s['id'],
                'product_id'   => $s['product_id'],
                'product_name' => $s['product_name'],
                'unit'         => $s['product_unit'],
                'date'         => $s['date'],
                'quantity'     => $s['quantity'],
                'amount'       => $s['amount'],
                'keterangan'   => 'Oleh: ' . htmlspecialchars($s['created_by_name']),
            ];
        }

        usort($stockHistory, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

        // Batasi maksimal 50 data terbaru agar tidak terlalu berat
        $stockHistory = array_slice($stockHistory, 0, 50);

        $data = [
            'eoq_products'  => $eoqProducts,
            'stock_history' => $stockHistory,
            'suppliers'     => $this->model('Supplier')->getAllSuppliers(),
            'success'       => isset($_GET['success']) ? 'Pesanan massal berhasil diproses.' : '',
            'error'         => isset($_GET['error']) ? 'Gagal memproses pesanan massal.' : ''
        ];
        
        $this->view('eoq/index', $data);
    }

    public function bulkOrder() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $supplier_id = (int) $_POST['supplier_id'];
            if ($supplier_id <= 0) {
                 $this->redirect('/eoq?error=1');
            }

            $productModel = $this->model('Product');
            $saleModel    = $this->model('Sale');
            $orderModel   = $this->model('Order');
            
            $productsRaw = $productModel->getAllProducts();
            $successCount = 0;

            foreach ($productsRaw as $product) {
                $demandData    = $saleModel->getAnnualizedDemand($product['id']);
                $demand        = $demandData['annualized_demand'];
                $ordering_cost = (float) $product['ordering_cost'];
                $holding_cost  = (float) $product['holding_cost'];
                
                $eoq = InventoryCalculator::calculateEOQ($demand, $ordering_cost, $holding_cost);
                if ($eoq > 0) {
                    $amount = $eoq * $product['price'];
                    $orderData = [
                        'date'           => date('Y-m-d H:i:s'),
                        'order_quantity' => $eoq,
                        'amount'         => $amount,
                        'ordered_by'     => $_SESSION['user_id'],
                        'supplier_id'    => $supplier_id,
                        'product_id'     => $product['id'],
                    ];
                    if ($orderModel->addOrder($orderData) > 0) {
                        $orderModel->updateProductStock($product['id'], $eoq);
                        $successCount++;
                    }
                }
            }
            if ($successCount > 0) {
                $this->redirect('/eoq?success=1');
            } else {
                $this->redirect('/eoq?error=1');
            }
        } else {
            $this->redirect('/eoq');
        }
    }
}

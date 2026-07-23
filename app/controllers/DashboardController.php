<?php
class DashboardController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    public function index() {
        $productModel  = $this->model('Product');
        $supplierModel = $this->model('Supplier');
        $saleModel     = $this->model('Sale');
        $orderModel    = $this->model('Order');

        // 1. Summary Statistics
        $totalProducts  = $productModel->countProducts();
        $totalSuppliers = $supplierModel->countSuppliers();
        
        // Total penjualan bulan ini
        $salesThisMonth = $saleModel->getTotalSalesThisMonth();

        // 2. Produk yang perlu reorder (stok <= ROP)
        $reorderProducts = [];
        $allProducts = $productModel->getAllProducts();
        
        foreach ($allProducts as $product) {
            $demandData    = $saleModel->getAnnualizedDemand($product['id']);
            $demand        = $demandData['annualized_demand'];
            $leadTime      = $orderModel->getAverageLeadTime($product['id']);
            $demandStats   = $saleModel->getDailyDemandStats($product['id']);

            if ($leadTime > 0 && $demandStats['avg_daily'] > 0) {
                $metrics = InventoryCalculator::calculateAll([
                    'demand'        => $demand,
                    'ordering_cost' => (float) $product['ordering_cost'],
                    'holding_cost'  => (float) $product['holding_cost'],
                    'max_daily'     => $demandStats['max_daily'],
                    'avg_daily'     => $demandStats['avg_daily'],
                    'lead_time'     => $leadTime,
                    'stock'         => (int) $product['stock'],
                ]);

                if ($metrics['rop_status'] === 'reorder') {
                    $reorderProducts[] = [
                        'id'           => $product['id'],
                        'name'         => $product['name'],
                        'unit'         => $product['unit'],
                        'stock'        => $product['stock'],
                        'rop'          => $metrics['rop'],
                        'safety_stock' => $metrics['safety_stock'],
                    ];
                }
            }
        }

        // 3. Top 5 produk terlaris (30 hari)
        $topProducts = $saleModel->getTopSellingProducts(5, 30);

        // 4. Pesanan belum diterima
        $pendingOrders = $orderModel->getPendingOrders();

        $data = [
            'username'         => $_SESSION['username'],
            'role'             => $_SESSION['role'],
            'total_products'   => $totalProducts,
            'total_suppliers'  => $totalSuppliers,
            'reorder_count'    => count($reorderProducts),
            'sales_this_month' => $salesThisMonth,
            'reorder_products' => $reorderProducts,
            'top_products'     => $topProducts,
            'pending_orders'   => $pendingOrders,
        ];
        
        $this->view('dashboard/index', $data);
    }
}

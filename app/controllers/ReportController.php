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

        $productsRaw = $productModel->getAllProducts();
        $reportData  = [];

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

            $reportData[] = $product;
        }

        $data = [
            'reports' => $reportData,
        ];

        $this->view('reports/index', $data);
    }
}

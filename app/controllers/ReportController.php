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

        $productsRaw = $productModel->getAllProducts();
        $reportData  = [];

        foreach ($productsRaw as $product) {
            $demand        = $saleModel->getTotalDemand($product['id'], 12);
            $ordering_cost = (float) $product['ordering_cost'];
            $holding_cost  = (float) $product['holding_cost'];
            
            $eoq = 0;
            if ($holding_cost > 0 && $ordering_cost > 0 && $demand > 0) {
                $eoq = ceil(sqrt((2 * $demand * $ordering_cost) / $holding_cost));
            }

            $product['demand'] = $demand;
            $product['eoq']    = $eoq;

            $reportData[] = $product;
        }

        $data = [
            'reports' => $reportData,
        ];

        $this->view('reports/index', $data);
    }
}

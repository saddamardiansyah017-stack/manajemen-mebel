<?php
class ProductController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    public function index() {
        $productModel = $this->model('Product');
        $data['products'] = $productModel->getAllProducts();
        $this->view('products/index', $data);
    }

    public function create() {
        $data = ['error' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $productModel = $this->model('Product');
            if ($productModel->addProduct($_POST) > 0) {
                $this->redirect('/products');
            } else {
                $data['error'] = 'Gagal menambahkan produk';
            }
        }

        $this->view('products/create', $data);
    }

    public function edit($id) {
        $productModel = $this->model('Product');
        $data['product'] = $productModel->getProductById($id);
        $data['error'] = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST['id'] = $id; 
            if ($productModel->updateProduct($_POST) >= 0) {
                $this->redirect('/products');
            } else {
                $data['error'] = 'Gagal memperbarui produk';
            }
        }

        $this->view('products/edit', $data);
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $productModel = $this->model('Product');
            $productModel->deleteProduct($id);
        }
        $this->redirect('/products');
    }
}

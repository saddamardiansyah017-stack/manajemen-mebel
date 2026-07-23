<?php
class SupplierController extends Controller {
    public function __construct() {
        // Protect routes - both admin and owner can access
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    public function index() {
        $supplierModel = $this->model('Supplier');
        $data['suppliers'] = $supplierModel->getAllSuppliers();
        $this->view('suppliers/index', $data);
    }

    public function create() {
        $data = ['error' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $supplierModel = $this->model('Supplier');
            if ($supplierModel->addSupplier($_POST) > 0) {
                $this->redirect('/suppliers');
            } else {
                $data['error'] = 'Gagal menambahkan pemasok';
            }
        }

        $this->view('suppliers/create', $data);
    }

    public function edit($id) {
        $supplierModel = $this->model('Supplier');
        $data['supplier'] = $supplierModel->getSupplierById($id);
        $data['error'] = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST['id'] = $id; // Ensure ID is passed correctly
            if ($supplierModel->updateSupplier($_POST) >= 0) {
                $this->redirect('/suppliers');
            } else {
                $data['error'] = 'Gagal memperbarui pemasok';
            }
        }

        $this->view('suppliers/edit', $data);
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $supplierModel = $this->model('Supplier');
            $supplierModel->deleteSupplier($id);
        }
        $this->redirect('/suppliers');
    }
}

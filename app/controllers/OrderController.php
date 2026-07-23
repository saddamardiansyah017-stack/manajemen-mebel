<?php
class OrderController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
        // Hanya admin dan owner yang boleh akses
        if (!in_array($_SESSION['role'], ['admin', 'owner'])) {
            $this->redirect('/products');
        }
    }

    /**
     * GET  /products/{id}/orders  → tampilkan halaman gabungan stok (orders + sales)
     * POST /products/{id}/orders  → action=order  : tambah stok
     *                             → action=sale   : kurangi stok
     */
    public function index($product_id) {
        $productModel  = $this->model('Product');
        $supplierModel = $this->model('Supplier');
        $orderModel    = $this->model('Order');
        $saleModel     = $this->model('Sale');

        $product = $productModel->getProductById($product_id);
        if (!$product) {
            $this->redirect('/products');
        }

        $demand        = $saleModel->getTotalDemand($product_id, 12);
        $ordering_cost = (float) $product['ordering_cost'];
        $holding_cost  = (float) $product['holding_cost'];
        
        $eoq = 0;
        if ($holding_cost > 0 && $ordering_cost > 0 && $demand > 0) {
            $eoq = ceil(sqrt((2 * $demand * $ordering_cost) / $holding_cost));
        }

        $data = [
            'product'       => $product,
            'orders'        => $orderModel->getOrdersByProduct($product_id),
            'sales'         => $saleModel->getSalesByProduct($product_id),
            'suppliers'     => $supplierModel->getAllSuppliers(),
            'demand'        => $demand,
            'ordering_cost' => $ordering_cost,
            'holding_cost'  => $holding_cost,
            'eoq'           => $eoq,
            'error'         => '',
            'success'       => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'order') {
                // --- Tambah Stok ---
                $order_quantity = (int) $_POST['order_quantity'];
                $supplier_id    = (int) $_POST['supplier_id'];

                if ($order_quantity <= 0) {
                    $data['error'] = 'Kuantitas order harus lebih dari 0.';
                } elseif ($supplier_id <= 0) {
                    $data['error'] = 'Pilih supplier terlebih dahulu.';
                } else {
                    $amount    = $order_quantity * $product['price'];
                    $orderData = [
                        'date'           => date('Y-m-d H:i:s'),
                        'order_quantity' => $order_quantity,
                        'amount'         => $amount,
                        'ordered_by'     => $_SESSION['user_id'],
                        'supplier_id'    => $supplier_id,
                        'product_id'     => $product_id,
                    ];

                    if ($orderModel->addOrder($orderData) > 0) {
                        $orderModel->updateProductStock($product_id, $order_quantity);
                        $this->redirect('/products/' . $product_id . '/orders?success=order');
                    } else {
                        $data['error'] = 'Gagal menambahkan order. Coba lagi.';
                    }
                }

            } elseif ($action === 'sale') {
                // --- Kurangi Stok ---
                $quantity = (int) $_POST['quantity'];

                if ($quantity <= 0) {
                    $data['error'] = 'Kuantitas penjualan harus lebih dari 0.';
                } elseif ($quantity > $product['stock']) {
                    $data['error'] = 'Stok tidak mencukupi. Stok saat ini: ' . $product['stock'] . ' ' . $product['unit'] . '.';
                } else {
                    $amount   = $quantity * $product['price'];
                    $saleData = [
                        'date'       => date('Y-m-d H:i:s'),
                        'quantity'   => $quantity,
                        'amount'     => $amount,
                        'created_by' => $_SESSION['user_id'],
                        'product_id' => $product_id,
                    ];

                    if ($saleModel->addSale($saleData) > 0) {
                        $saleModel->decrementProductStock($product_id, $quantity);
                        $this->redirect('/products/' . $product_id . '/orders?success=sale');
                    } else {
                        $data['error'] = 'Gagal menyimpan penjualan. Coba lagi.';
                    }
                }
            } elseif ($action === 'delete_order') {
                $order_id = (int) $_POST['order_id'];
                $order = $orderModel->getOrderById($order_id);
                if ($order && $order['product_id'] == $product_id) {
                    if ($orderModel->deleteOrder($order_id)) {
                        $orderModel->updateProductStock($product_id, -$order['order_quantity']);
                        $this->redirect('/products/' . $product_id . '/orders?success=delete');
                    } else {
                        $data['error'] = 'Gagal menghapus pesanan.';
                    }
                } else {
                    $data['error'] = 'Pesanan tidak ditemukan.';
                }
            }

            // Refresh data setelah POST gagal
            $data['product'] = $productModel->getProductById($product_id);
            $data['orders']  = $orderModel->getOrdersByProduct($product_id);
            $data['sales']   = $saleModel->getSalesByProduct($product_id);
        }

        // Pesan sukses via query string
        if (isset($_GET['success'])) {
            if ($_GET['success'] === 'order') $data['success'] = 'Stok berhasil ditambahkan.';
            if ($_GET['success'] === 'sale')  $data['success'] = 'Penjualan berhasil dicatat, stok dikurangi.';
            if ($_GET['success'] === 'delete') $data['success'] = 'Pesanan berhasil dihapus, stok dikembalikan.';
        }

        $this->view('orders/index', $data);
    }
}

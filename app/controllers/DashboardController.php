<?php
class DashboardController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    public function index() {
        $data = [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ];
        $this->view('dashboard/index', $data);
    }
}

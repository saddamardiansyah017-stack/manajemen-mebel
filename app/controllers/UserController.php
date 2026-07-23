<?php
class UserController extends Controller {
    public function __construct() {
        // Protect routes
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    public function index() {
        $userModel = $this->model('User');
        $data['users'] = $userModel->getAllUsers();
        $this->view('users/index', $data);
    }

    public function create() {
        if ($_SESSION['role'] !== 'owner') {
            $this->redirect('/users');
        }

        $data = ['error' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = $this->model('User');
            $_POST['role'] = isset($_POST['role']) ? $_POST['role'] : 'admin';
            if ($userModel->addUser($_POST) > 0) {
                $this->redirect('/users');
            } else {
                $data['error'] = 'Gagal menambahkan pengguna';
            }
        }

        $this->view('users/create', $data);
    }

    public function edit($id) {
        if ($_SESSION['role'] !== 'owner') {
            $this->redirect('/users');
        }

        $userModel = $this->model('User');
        $data['user'] = $userModel->getUserById($id);
        $data['error'] = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST['id'] = $id; // Ensure ID is passed correctly
            if ($userModel->updateUser($_POST) >= 0) {
                $this->redirect('/users');
            } else {
                $data['error'] = 'Gagal memperbarui pengguna';
            }
        }

        $this->view('users/edit', $data);
    }

    public function delete($id) {
        if ($_SESSION['role'] !== 'owner') {
            $this->redirect('/users');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = $this->model('User');
            $userModel->deleteUser($id);
        }
        $this->redirect('/users');
    }
}

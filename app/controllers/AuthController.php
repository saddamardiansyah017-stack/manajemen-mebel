<?php
class AuthController extends Controller {
    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $data = ['error' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = $this->model('User');
            $user = $userModel->authenticate($_POST['username'], $_POST['password']);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $this->redirect('/dashboard');
            } else {
                $data['error'] = 'Nama pengguna atau kata sandi tidak valid';
            }
        }

        $this->view('auth/login', $data);
    }

    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }
}

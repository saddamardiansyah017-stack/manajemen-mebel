<?php
class Controller {
    public function view($view, $data = []) {
        require_once '../app/views/layouts/main.php';
    }

    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model;
    }
    
    public function redirect($url) {
        header('Location: ' . BASEURL . $url);
        exit;
    }
}

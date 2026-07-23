<?php
session_start();
require_once '../config/config.php';
require_once '../app/core/Database.php';
require_once '../app/core/Controller.php';

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$controllerName = 'DashboardController'; // default
$methodName = 'index';
$params = [];

// Determine controller
if ($url[0] == 'login') {
    $controllerName = 'AuthController';
    $methodName = 'login';
    unset($url[0]);
} elseif ($url[0] == 'logout') {
    $controllerName = 'AuthController';
    $methodName = 'logout';
    unset($url[0]);
} elseif ($url[0] == 'users') {
    $controllerName = 'UserController';
    unset($url[0]);
    if (isset($url[1])) {
        if ($url[1] == 'create') {
            $methodName = 'create';
            unset($url[1]);
        } elseif ($url[1] == 'edit') {
            $methodName = 'edit';
            unset($url[1]);
        } elseif ($url[1] == 'delete') {
            $methodName = 'delete';
            unset($url[1]);
        }
    }
} elseif ($url[0] == 'dashboard') {
    $controllerName = 'DashboardController';
    unset($url[0]);
} elseif ($url[0] == 'suppliers') {
    $controllerName = 'SupplierController';
    unset($url[0]);
    if (isset($url[1])) {
        if ($url[1] == 'create') {
            $methodName = 'create';
            unset($url[1]);
        } elseif ($url[1] == 'edit') {
            $methodName = 'edit';
            unset($url[1]);
        } elseif ($url[1] == 'delete') {
            $methodName = 'delete';
            unset($url[1]);
        }
    }
} elseif ($url[0] == 'products') {
    // /products/{id}/orders → OrderController
    if (isset($url[1]) && is_numeric($url[1]) && isset($url[2]) && $url[2] == 'orders') {
        $controllerName = 'OrderController';
        $methodName     = 'index';
        $params         = [$url[1]];
        $url            = [];
    } else {
        $controllerName = 'ProductController';
        unset($url[0]);
        if (isset($url[1])) {
            if ($url[1] == 'create') {
                $methodName = 'create';
                unset($url[1]);
            } elseif ($url[1] == 'edit') {
                $methodName = 'edit';
                unset($url[1]);
            } elseif ($url[1] == 'delete') {
                $methodName = 'delete';
                unset($url[1]);
            }
        }
    }
} elseif ($url[0] == 'reports') {
    $controllerName = 'ReportController';
    unset($url[0]);
} elseif ($url[0] == 'eoq') {
    $controllerName = 'EoqController';
    unset($url[0]);
    if (isset($url[1])) {
        if ($url[1] == 'bulkOrder') {
            $methodName = 'bulkOrder';
            unset($url[1]);
        }
    }
}

// Load controller
$controllerFile = '../app/controllers/' . $controllerName . '.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName;
} else {
    die("Controller not found");
}

// Load method
if (!empty($url)) {
    $params = array_values($url);
}

if (method_exists($controller, $methodName)) {
    call_user_func_array([$controller, $methodName], $params);
} else {
    die("Method not found");
}

<?php
namespace App\Core;

class Router
{
    public function dispatch(): void
    {
        try {
            $route = $_GET['q'] ?? 'home/index';
            [$controllerName, $action] = array_pad(explode('/', $route, 2), 2, 'index');

            $class = 'App\\Controller\\' . ucfirst($controllerName) . 'Controller';
            if (!class_exists($class)) {
                http_response_code(404);
                echo 'Controller not found';
                return;
            }
            $controller = new $class();
            if (!method_exists($controller, $action)) {
                http_response_code(404);
                echo 'Action not found';
                return;
            }
            $controller->$action();
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Internal Server Error';
            error_log($e->getMessage());
            echo '<pre>';
            echo $e->getMessage();
            echo $e->getTraceAsString();
            echo '</pre>';
        }
    }
}

<?php
namespace App\Core;

use App\Controller\ErrorController;
use App\Controller\NotFoundController;

class Router
{
    public function dispatch(): void
    {
        try {
            $route = $_GET['q'] ?? 'home/index';
            $parts = explode('/', $route);
            $action = array_pop($parts) ?: 'index';
            $controllerName = implode('\\', array_map('ucfirst', $parts)) ?: 'Home';

            $class = 'App\\Controller\\' . ucfirst($controllerName) . 'Controller';
            if (!class_exists($class)) {
                http_response_code(404);
                echo 'Controller not found';
                return;
            }
            $controller = new $class();
            if (!method_exists($controller, $action) || !is_callable([$controller, $action])) {
                http_response_code(404);
                $controller = new NotFoundController();
                $controller->index();
                die();
            }
                
            $controller->$action();

        } catch (\Throwable $e) {
            try {
                http_response_code(500);
                $controller = new ErrorController();
                $controller->index($e);
            } catch (\Throwable $e) {
                // Fallback to a simple error message
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
}

<?php
namespace App\Core;

use App\Controller\AuthController;

abstract class Controller
{
    private Request $request;


    public function __construct()
    {
        $this->request = Request::getInstance();
        $this->checkAuth();
    }

    public function getRequest(?string $key = null, mixed $defailt = null): mixed
    {
        if ($key === null) {
            return $this->request;
        }

        return $this->request->request($key, $defailt);
    }

    public function getSession(?string $key = null, mixed $default = null): mixed
    {
        return $this->getRequest()->session($key, $default);
    }  

    /**
     * Check if user is authenticated
     */
    protected function checkAuth(): void
    {
        $uid = $this->getRequest()->session('uid');

        if (null === $uid && !$this instanceof  AuthController) {
            header('Location: /?q=auth/login');
            exit;
        }
    }

    protected function getView(string $template, array $params = []): View
    {
        return new View($template, $params);
    }

    protected function render(string $view, array $params = []): void
    {
        $viewObj = $this->getView($view, $params);
        $viewObj->render($params);
    }

    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public function redirectReferer(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $referer");
        exit;
    }

}

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

    public function getRequest(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->request;
        }

        return $this->request->request($key, $default);
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

        if (
            null === $uid 
            && !$this instanceof  AuthController
            //&& !$this instanceof \App\Controller\ErrorController
            && !$this instanceof \App\Controller\NotFoundController
            ) {
            $this->redirect('/?q=auth/login');
            exit;
        }
    }

    protected function getView(string $template, array $params = []): View
    {
        return new View($this, $template, $params);
    }

    protected function render(string $view, array $params = [], bool $standalone = false): void
    {
        $viewObj = $this->getView($view, $params);
        $viewObj->render($params, $standalone);
    }

    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public function redirectReferer(): void
    {
        $referer = $this->getRequest()->getReferer();
        header("Location: $referer");
        exit;
    }

}

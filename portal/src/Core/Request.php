<?php
namespace App\Core;

class Request
{
    private static ?Request $instance = null;

    private array $session = [];
    private array $cookies = [];
    private array $server = [];
    private array $params = [];
    private array $queryParams = [];
    private array $postParams = [];
    private array $files = [];

    private function __construct()
    {
        session_start();
        $this->params = $_REQUEST;
        $this->session = $_SESSION;
        $this->cookies = $_COOKIE;
        $this->server = $_SERVER;
        $this->queryParams = $_GET;
        $this->postParams = $_POST;
        $this->files = $_FILES;
    }

    public static function getInstance(): Request
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function request(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->params;
        }

        return $this->params[$key] ?? $default;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->queryParams;
        }

        return $this->queryParams[$key] ?? $default;
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->postParams;
        }

        return $this->postParams[$key] ?? $default;
    }

    public function session(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->session;
        }

        return $this->session[$key] ?? $default;
    }

    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->cookies;
        }

        return $this->cookies[$key] ?? $default;
    }

    public function server(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->server;
        }

        return $this->server[$key] ?? $default;
    }

    public function file(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->files;
        }

        return $this->files[$key] ?? $default;
    }
    
}
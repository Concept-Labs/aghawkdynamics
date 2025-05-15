<?php
namespace App\Core;

class View 
{
    const DEFAULT_TEMPLATE = 'index';
    const TEMPLATES_DIR = __DIR__ . '/../../views/';
    const LAYOUTS_DIR = __DIR__ . '/../../views/layout/';
    const HEADER_FILE = self::LAYOUTS_DIR . 'header.phtml';
    const FOOTER_FILE = self::LAYOUTS_DIR . 'footer.phtml';

    private string $template = self::DEFAULT_TEMPLATE;
    private array $params = [];

    public function __construct(?string $template = null, array $params = [])
    {
        $this->template = $template ?? self::DEFAULT_TEMPLATE;
        $this->params = $params;
    }

    public function render(array $params = []): void
    {
        try {
            $this->params = array_merge($this->params, $params);
            $this->params['request'] = Request::getInstance();

            extract($this->params);
            $viewFile = self::TEMPLATES_DIR . $this->template . '.phtml';
            if (!file_exists($viewFile)) {
                throw new \RuntimeException("View not found: $viewFile");
            }
            include self::HEADER_FILE;
            include $viewFile;
            include self::FOOTER_FILE;
        } catch (\Throwable $e) {
            include self::HEADER_FILE;
            echo '<h1>Error</h1>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            include self::FOOTER_FILE;
        }
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getParam(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function setParam(string $key, mixed $value): void
    {
        $this->params[$key] = $value;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }
}
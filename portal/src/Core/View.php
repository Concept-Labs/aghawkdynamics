<?php
namespace App\Core;

class View 
{
    const DEFAULT_TEMPLATE = 'index';
    const TEMPLATES_DIR = __DIR__ . '/../../views/';
    const LAYOUTS_DIR = __DIR__ . '/../../views/layout/';
    const HEADER_FILE = self::LAYOUTS_DIR . 'header.phtml';
    const CONTENT_FILE = self::LAYOUTS_DIR . 'content.phtml';
    const FOOTER_FILE = self::LAYOUTS_DIR . 'footer.phtml';

    private string $template = self::DEFAULT_TEMPLATE;
    private array $params = [];

    public function __construct(
        private Controller $controller, 
        ?string $template = null, 
        array $params = []
    ) {
        $this->template = $template ?? self::DEFAULT_TEMPLATE;
        $this->params = $params;
    }

    public function render(array $params = []): void
    {
        

            $this->getRequest()->addMessage('Rendering view: ' . $this->template);

            extract($this->params);
            $this->params = array_merge($this->params, $params);
            $this->params['request'] = Request::getInstance();
            $this->params['_content'] = $viewFile = self::TEMPLATES_DIR . $this->template . '.phtml';

            if (!file_exists($viewFile)) {
                throw new \RuntimeException("View not found: $viewFile");
            }
            include self::HEADER_FILE;
            include self::CONTENT_FILE;
            include self::FOOTER_FILE;
        
    }

    public function getController(): Controller
    {
        return $this->controller;
    }

    public function getRequest(): Request
    {
        return $this->getController()->getRequest();
    }

    public function getTemplate(): string
    {
        return self::TEMPLATES_DIR . $this->template . '.phtml';
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function var(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function setVar(string $key, mixed $value): void
    {
        $this->params[$key] = $value;
    }

    public function vars(): array
    {
        return $this->params;
    }

    public function setVars(array $params): void
    {
        $this->params = $params;
    }
}
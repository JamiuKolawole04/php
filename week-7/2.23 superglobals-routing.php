<?php

declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function register(string $path, callable $action): void
    {
        $this->routes[$path] = $action;
    }

    public function dispatch(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (isset($this->routes[$path])) {
            $action = $this->routes[$path];
            $action();
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}

$router = new Router();

$router->register('/', function (): void {
    echo 'Home';
});

$router->register('/invoice', function (): void {
    echo 'Invoices';
});

$router->dispatch();

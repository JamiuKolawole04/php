<?php

class Request
{
    public function path()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
}

class Response
{
    public function status($code)
    {
        http_response_code($code);
    }

    public function send($body)
    {
        echo $body;
    }
}

class Controller
{
    public $request;
    public $response;

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}

class Router
{
    public $routes = [];

    public function add($path, $controller, $method)
    {
        $this->routes[$path] = [$controller, $method];
    }

    public function run($request, $response)
    {
        $path = $request->path();

        if (!isset($this->routes[$path])) {
            $response->status(404);
            $response->send('404 Not Found');
            return;
        }

        list($controller, $method) = $this->routes[$path];
        $page = new $controller($request, $response);

        $response->send($page->$method());
    }
}

class HomeController extends Controller
{
    public function index()
    {
        $name = $this->request->get('name', 'Guest');

        return '<h1>Home</h1><p>Hello, ' . $name . '</p>';
    }
}

class InvoiceController extends Controller
{
    public function index()
    {
        return '<h1>Invoices</h1>';
    }
}

class Routes
{
    public function run()
    {
        $router = new Router();
        $router->add('/', 'HomeController', 'index');
        $router->add('/invoice', 'InvoiceController', 'index');

        $router->run(new Request(), new Response());
    }
}

$routes = new Routes();
$routes->run();

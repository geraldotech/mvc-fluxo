<?php

declare(strict_types=1);

class App
{
    private array $publicControllers = [
        'AuthController',
        'ErrorController',
    ];

    public function run(): void
    {
        $route = trim((string) ($_GET['url'] ?? ''), '/');

        if ($route === '') {
            $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
            $basePath = BASE_URL === '' ? '' : BASE_URL;

            if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
                $requestPath = substr($requestPath, strlen($basePath));
            }

            $route = trim($requestPath, '/');
        }

        $segments = $route === '' ? [] : explode('/', $route);

        $controllerName = ucfirst($segments[0] ?? 'home') . 'Controller';
        $method = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        if (!class_exists($controllerName)) {
            $this->dispatchNotFound();
            return;
        }

        if (!in_array($controllerName, $this->publicControllers, true) && !Auth::check()) {
            header('Location: ' . (BASE_URL ?: '') . '/auth');
            exit;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            $this->dispatchNotFound();
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }

    private function dispatchNotFound(): void
    {
        (new ErrorController())->notFound();
    }
}

<?php

declare(strict_types=1);

abstract class Controller
{
    protected function model(string $model): object
    {
        return new $model();
    }

    protected function view(string $view, array $data = []): void
    {
        $viewFile = APP_PATH . '/views/' . $view . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            exit('View not found.');
        }

        extract($data, EXTR_SKIP);
        require APP_PATH . '/views/layouts/header.php';
        require $viewFile;
        require APP_PATH . '/views/layouts/footer.php';
    }

    protected function redirect(string $path = ''): void
    {
        $target = BASE_URL ?: '';

        if ($path !== '') {
            $target .= '/' . ltrim($path, '/');
        }

        header('Location: ' . ($target !== '' ? $target : '/'));
        exit;
    }
}

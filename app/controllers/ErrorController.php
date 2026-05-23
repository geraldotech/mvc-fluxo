<?php

declare(strict_types=1);

class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);

        $this->view('pages/404', [
            'title' => '404',
        ]);
    }
}

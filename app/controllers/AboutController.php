<?php

declare(strict_types=1);

class AboutController extends Controller
{
    private AboutModel $aboutModel;

    public function __construct()
    {
        $this->aboutModel = $this->model('AboutModel');
    }

    public function index(): void
    {
        $data = [
            'title' => 'About',
            'description' => 'Projeto base com controllers separados, model compartilhado, views e assets em CSS/JS puro.',
            'users' => $this->aboutModel->getUsers(),
            'dbError' => $this->aboutModel->getLastError(),
        ];

        $this->view('pages/about', $data);
    }
}

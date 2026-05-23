<?php

declare(strict_types=1);

class HomeController extends Controller
{
    private PageModel $pageModel;

    public function __construct()
    {
        $this->pageModel = $this->model('PageModel');
    }

    public function index(): void
    {
        $user = Auth::user();

        $this->view('pages/home', [
            'title' => 'Painel',
            'description' => 'Fluxo de compras protegido por login, com permissoes por perfil e aprovacao.',
            'status' => $this->pageModel->getDatabaseStatus(),
            'user' => $user,
        ]);
    }
}

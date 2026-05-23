<?php

declare(strict_types=1);

class DownloadsController extends Controller
{
    private PageModel $pageModel;

    public function __construct()
    {
        $this->pageModel = $this->model('PageModel');
    }

    public function index(): void
    {
        $this->view('pages/downloads', [
            'title' => 'Downloads',
            'files' => $this->pageModel->getDownloads(),
        ]);
    }
}

<?php

declare(strict_types=1);

class ItemsController extends Controller
{
    private ItemModel $itemModel;

    public function __construct()
    {
        $this->itemModel = $this->model('ItemModel');
        Auth::requireAdmin();
    }

    public function index(): void
    {
        $editItemId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editingItem = $editItemId > 0 ? $this->itemModel->findById($editItemId) : null;
        $formOld = $_SESSION['item_form_old'] ?? [];

        $this->view('pages/items', [
            'title' => 'Itens',
            'items' => $this->itemModel->getAll(),
            'editingItem' => $editingItem,
            'formOld' => $formOld,
            'formError' => $_SESSION['item_form_error'] ?? null,
            'formSuccess' => $_SESSION['item_form_success'] ?? null,
        ]);

        unset($_SESSION['item_form_error'], $_SESSION['item_form_success'], $_SESSION['item_form_old']);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('items');
        }

        $data = $this->validatePayload();

        if ($data === null) {
            $this->redirect('items');
        }

        if (!$this->itemModel->create($data)) {
            $_SESSION['item_form_old'] = $_POST;
            $_SESSION['item_form_error'] = 'Nao foi possivel cadastrar o item.';
            $this->redirect('items');
        }

        $_SESSION['item_form_success'] = 'Item cadastrado com sucesso.';
        $this->redirect('items');
    }

    public function update($id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('items');
        }

        $itemId = (int) $id;
        $item = $this->itemModel->findById($itemId);

        if ($itemId <= 0 || $item === null) {
            $_SESSION['item_form_error'] = 'Item nao encontrado.';
            $this->redirect('items');
        }

        $data = $this->validatePayload();

        if ($data === null) {
            $this->redirect('items?edit=' . $itemId);
        }

        if (!$this->itemModel->update($itemId, $data)) {
            $_SESSION['item_form_old'] = $_POST;
            $_SESSION['item_form_error'] = 'Nao foi possivel atualizar o item.';
            $this->redirect('items?edit=' . $itemId);
        }

        $_SESSION['item_form_success'] = 'Item atualizado com sucesso.';
        $this->redirect('items');
    }

    public function destroy($id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('items');
        }

        $itemId = (int) $id;
        $item = $this->itemModel->findById($itemId);

        if ($itemId <= 0 || $item === null) {
            $_SESSION['item_form_error'] = 'Item nao encontrado.';
            $this->redirect('items');
        }

        if (!$this->itemModel->delete($itemId)) {
            $_SESSION['item_form_error'] = 'Nao foi possivel apagar o item.';
            $this->redirect('items');
        }

        $_SESSION['item_form_success'] = 'Item apagado com sucesso.';
        $this->redirect('items');
    }

    private function validatePayload(): ?array
    {
        $itemName = trim((string) ($_POST['item_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $priceRaw = str_replace(',', '.', trim((string) ($_POST['price'] ?? '')));

        if ($itemName === '' || $description === '' || $category === '' || $priceRaw === '') {
            $_SESSION['item_form_old'] = $_POST;
            $_SESSION['item_form_error'] = 'Preencha nome, descricao, preco e categoria.';
            return null;
        }

        if (!is_numeric($priceRaw) || (float) $priceRaw < 0) {
            $_SESSION['item_form_old'] = $_POST;
            $_SESSION['item_form_error'] = 'Informe um preco valido.';
            return null;
        }

        return [
            'item_name' => $itemName,
            'description' => $description,
            'price' => number_format((float) $priceRaw, 2, '.', ''),
            'category' => $category,
        ];
    }
}

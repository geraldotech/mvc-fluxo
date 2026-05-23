<?php

declare(strict_types=1);

class UsersController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('UserModel');
        Auth::requireAdmin();
    }

    public function index(): void
    {
        $editUserId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editingUser = $editUserId > 0 ? $this->userModel->findById($editUserId) : null;
        $formOld = $_SESSION['user_form_old'] ?? [];

        $this->view('pages/users', [
            'title' => 'Usuarios',
            'users' => $this->userModel->getAll(),
            'editingUser' => $editingUser,
            'formOld' => $formOld,
            'formError' => $_SESSION['user_form_error'] ?? null,
            'formSuccess' => $_SESSION['user_form_success'] ?? null,
        ]);

        unset($_SESSION['user_form_error'], $_SESSION['user_form_success'], $_SESSION['user_form_old']);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users');
        }

        $login = trim((string) ($_POST['login'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($login === '' || $name === '' || $password === '') {
            $_SESSION['user_form_old'] = $_POST;
            $_SESSION['user_form_error'] = 'Preencha login, nome e senha.';
            $this->redirect('users');
        }

        if ($this->userModel->findByLogin($login) !== null) {
            $_SESSION['user_form_old'] = $_POST;
            $_SESSION['user_form_error'] = 'Ja existe um usuario com esse login.';
            $this->redirect('users');
        }

        $created = $this->userModel->create([
            'login' => $login,
            'name' => $name,
            'password' => $password,
            'is_admin' => isset($_POST['is_admin']),
            'solicitante_approval' => isset($_POST['solicitante_approval']),
            'admin_approval' => isset($_POST['admin_approval']),
            'financial_approval' => isset($_POST['financial_approval']),
            'purchasing_approval' => isset($_POST['purchasing_approval']),
        ]);

        if (!$created) {
            $_SESSION['user_form_old'] = $_POST;
            $_SESSION['user_form_error'] = 'Nao foi possivel cadastrar o usuario.';
            $this->redirect('users');
        }

        $_SESSION['user_form_success'] = 'Usuario cadastrado com sucesso.';
        $this->redirect('users');
    }

    public function update($id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users');
        }

        $userId = (int) $id;
        $user = $this->userModel->findById($userId);

        if ($userId <= 0 || $user === null) {
            $_SESSION['user_form_error'] = 'Usuario nao encontrado.';
            $this->redirect('users');
        }

        $login = trim((string) ($_POST['login'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $isAdmin = isset($_POST['is_admin']);

        if ($login === '' || $name === '') {
            $_SESSION['user_form_old'] = $_POST;
            $_SESSION['user_form_error'] = 'Preencha login e nome.';
            $this->redirect('users?edit=' . $userId);
        }

        if ($this->userModel->loginExistsForOtherUser($login, $userId)) {
            $_SESSION['user_form_old'] = $_POST;
            $_SESSION['user_form_error'] = 'Ja existe outro usuario com esse login.';
            $this->redirect('users?edit=' . $userId);
        }

        if ((int) $user['is_admin'] === 1 && !$isAdmin && $this->userModel->countAdmins() <= 1) {
            $_SESSION['user_form_old'] = $_POST;
            $_SESSION['user_form_error'] = 'Nao e permitido remover o ultimo administrador do sistema.';
            $this->redirect('users?edit=' . $userId);
        }

        $updated = $this->userModel->update($userId, [
            'login' => $login,
            'name' => $name,
            'password' => $password,
            'is_admin' => $isAdmin,
            'solicitante_approval' => isset($_POST['solicitante_approval']),
            'admin_approval' => isset($_POST['admin_approval']),
            'financial_approval' => isset($_POST['financial_approval']),
            'purchasing_approval' => isset($_POST['purchasing_approval']),
        ]);

        if (!$updated) {
            $_SESSION['user_form_old'] = $_POST;
            $_SESSION['user_form_error'] = 'Nao foi possivel atualizar o usuario.';
            $this->redirect('users?edit=' . $userId);
        }

        $authUser = Auth::user();

        if ($authUser !== null && (int) $authUser['id'] === $userId) {
            $freshUser = $this->userModel->findById($userId);

            if ($freshUser !== null) {
                Auth::login($freshUser);
            }
        }

        $_SESSION['user_form_success'] = 'Usuario atualizado com sucesso.';
        $this->redirect('users');
    }
}

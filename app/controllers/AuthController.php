<?php

declare(strict_types=1);

class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('UserModel');
    }

    public function index(): void
    {
        if (Auth::check()) {
            $this->redirect();
        }

        $this->view('pages/login', [
            'title' => 'Login',
            'error' => $_SESSION['auth_error'] ?? null,
        ]);

        unset($_SESSION['auth_error']);
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth');
        }

        $login = trim((string) ($_POST['login'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = $this->userModel->findByLogin($login);

        if ($user === null || !password_verify($password, $user['password'])) {
            $_SESSION['auth_error'] = 'Login ou senha invalidos.';
            $this->redirect('auth');
        }

        Auth::login($user);
        $this->redirect();
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('auth');
    }
}

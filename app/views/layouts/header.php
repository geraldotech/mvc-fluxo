<?php

declare(strict_types=1);

$pageTitle = isset($title) ? $title . ' | ' . APP_NAME : APP_NAME;
$authUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars((BASE_URL ?: '') . '/public/css/style.css', ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a class="brand" href="<?= htmlspecialchars(BASE_URL ?: '/', ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></a>
            <button class="menu-toggle" type="button" aria-label="Abrir menu" data-menu-toggle>
                <span></span>
                <span></span>
            </button>
            <nav class="nav" data-menu>
                <?php if ($authUser !== null): ?>
                    <a href="<?= htmlspecialchars(BASE_URL ?: '/', ENT_QUOTES, 'UTF-8'); ?>">Painel</a>
                    <a href="<?= htmlspecialchars((BASE_URL ?: '') . '/downloads', ENT_QUOTES, 'UTF-8'); ?>">Arquivos</a>
                    <?php if (($authUser['is_admin'] ?? false) === true): ?>
                        <a href="<?= htmlspecialchars((BASE_URL ?: '') . '/users', ENT_QUOTES, 'UTF-8'); ?>">Usuarios</a>
                        <a href="<?= htmlspecialchars((BASE_URL ?: '') . '/items', ENT_QUOTES, 'UTF-8'); ?>">Itens</a>
                    <?php endif; ?>
                    <span class="nav-user">Ola <?= htmlspecialchars($authUser['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="<?= htmlspecialchars((BASE_URL ?: '') . '/auth/logout', ENT_QUOTES, 'UTF-8'); ?>">Sair</a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars((BASE_URL ?: '') . '/auth', ENT_QUOTES, 'UTF-8'); ?>">Login</a>
                <?php endif; ?>
            </nav>
        </header>
        <main class="content">

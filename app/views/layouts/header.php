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
<body class="app-body">
    <div class="container py-4">
        <nav class="navbar navbar-expand-lg app-surface rounded-4 shadow-sm px-3 px-lg-4 mb-4">
            <div class="container-fluid p-0">
                <a class="navbar-brand fw-semibold text-uppercase tracking-wide" href="<?= htmlspecialchars(BASE_URL ?: '/', ENT_QUOTES, 'UTF-8'); ?>">
                    <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Abrir menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php if ($authUser !== null): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars(BASE_URL ?: '/', ENT_QUOTES, 'UTF-8'); ?>">Painel</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars((BASE_URL ?: '') . '/requests', ENT_QUOTES, 'UTF-8'); ?>">Solicitacoes</a></li>
                            <?php if (($authUser['is_admin'] ?? false) === true): ?>
                                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars((BASE_URL ?: '') . '/users', ENT_QUOTES, 'UTF-8'); ?>">Usuarios</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars((BASE_URL ?: '') . '/items', ENT_QUOTES, 'UTF-8'); ?>">Itens</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>

                    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
                        <?php if ($authUser !== null): ?>
                            <span class="badge text-bg-light border fw-normal px-3 py-2">Ola <?= htmlspecialchars($authUser['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars((BASE_URL ?: '') . '/auth/logout', ENT_QUOTES, 'UTF-8'); ?>">Sair</a>
                        <?php else: ?>
                            <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars((BASE_URL ?: '') . '/auth', ENT_QUOTES, 'UTF-8'); ?>">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
        <main class="d-grid gap-4">

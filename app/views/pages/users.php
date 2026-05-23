<?php
$isEditing = !empty($editingUser);
$shouldOpenModal = $isEditing || !empty($formError);
$formAction = $isEditing
    ? (BASE_URL ?: '') . '/users/update/' . (int) $editingUser['id']
    : (BASE_URL ?: '') . '/users/store';
$loginValue = $formOld['login'] ?? ($editingUser['login'] ?? '');
$nameValue = $formOld['name'] ?? ($editingUser['name'] ?? '');
?>
<section class="card app-surface border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <p class="app-eyebrow">Administracao</p>
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 mb-4">
            <div>
                <h1 class="h2 mb-2"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="text-secondary mb-0">Somente administradores podem cadastrar usuarios e definir permissoes de aprovacao.</p>
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#userModal">Novo usuario</button>
        </div>

        <?php if (!empty($formError)): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($formSuccess)): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($formSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Login</th>
                        <th>Nome</th>
                        <th>Permissoes</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($user['login'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars(implode(', ', array_filter([
                                (int) $user['is_admin'] === 1 ? 'isAdmin' : null,
                                (int) $user['solicitante_approval'] === 1 ? 'SOLICITANTE_APPROVAL' : null,
                                (int) $user['admin_approval'] === 1 ? 'ADMIN_APPROVAL' : null,
                                (int) $user['financial_approval'] === 1 ? 'FINANCIAL_APPROVAL' : null,
                                (int) $user['purchasing_approval'] === 1 ? 'PURCHASING_APPROVAL' : null,
                            ])), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-end">
                                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars((BASE_URL ?: '') . '/users?edit=' . (int) $user['id'], ENT_QUOTES, 'UTF-8'); ?>">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true" data-auto-open="<?= $shouldOpenModal ? 'true' : 'false'; ?>">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title h4 mb-0"><?= $isEditing ? 'Editar usuario' : 'Novo usuario'; ?></h2>
                    <?php if ($isEditing): ?>
                        <small class="text-secondary">Deixe a senha em branco para manter a atual.</small>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="user-login">Login</label>
                        <input class="form-control" id="user-login" type="text" name="login" value="<?= htmlspecialchars((string) $loginValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="user-name">Nome</label>
                        <input class="form-control" id="user-name" type="text" name="name" value="<?= htmlspecialchars((string) $nameValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="user-password"><?= $isEditing ? 'Nova senha' : 'Senha'; ?></label>
                        <input class="form-control" id="user-password" type="password" name="password" <?= $isEditing ? '' : 'required'; ?>>
                    </div>

                    <div class="col-12">
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label class="permission-option" for="is_admin">
                                    <input class="form-check-input mt-0" type="checkbox" id="is_admin" name="is_admin" <?= !empty($formOld['is_admin']) || (!isset($formOld['is_admin']) && !empty($editingUser['is_admin'])) ? 'checked' : ''; ?>>
                                    <span>IsAdmin</span>
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="permission-option" for="solicitante_approval">
                                    <input class="form-check-input mt-0" type="checkbox" id="solicitante_approval" name="solicitante_approval" <?= !empty($formOld['solicitante_approval']) || (!isset($formOld['solicitante_approval']) && !empty($editingUser['solicitante_approval'])) ? 'checked' : ''; ?>>
                                    <span>SOLICITANTE_APPROVAL</span>
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="permission-option" for="admin_approval">
                                    <input class="form-check-input mt-0" type="checkbox" id="admin_approval" name="admin_approval" <?= !empty($formOld['admin_approval']) || (!isset($formOld['admin_approval']) && !empty($editingUser['admin_approval'])) ? 'checked' : ''; ?>>
                                    <span>ADMIN_APPROVAL</span>
                                </label>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="permission-option" for="financial_approval">
                                    <input class="form-check-input mt-0" type="checkbox" id="financial_approval" name="financial_approval" <?= !empty($formOld['financial_approval']) || (!isset($formOld['financial_approval']) && !empty($editingUser['financial_approval'])) ? 'checked' : ''; ?>>
                                    <span>FINANCIAL_APPROVAL</span>
                                </label>
                            </div>
                            <div class="col-12">
                                <label class="permission-option" for="purchasing_approval">
                                    <input class="form-check-input mt-0" type="checkbox" id="purchasing_approval" name="purchasing_approval" <?= !empty($formOld['purchasing_approval']) || (!isset($formOld['purchasing_approval']) && !empty($editingUser['purchasing_approval'])) ? 'checked' : ''; ?>>
                                    <span>PURCHASING_APPROVAL</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit"><?= $isEditing ? 'Salvar alteracoes' : 'Cadastrar usuario'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<section class="panel">
    <?php
    $isEditing = !empty($editingUser);
    $shouldOpenModal = $isEditing || !empty($formError);
    $formAction = $isEditing
        ? (BASE_URL ?: '') . '/users/update/' . (int) $editingUser['id']
        : (BASE_URL ?: '') . '/users/store';
    $loginValue = $formOld['login'] ?? ($editingUser['login'] ?? '');
    $nameValue = $formOld['name'] ?? ($editingUser['name'] ?? '');
    ?>
    <p class="eyebrow">Administracao</p>
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="lead">Somente administradores podem cadastrar usuarios e definir permissoes de aprovacao.</p>

    <?php if (!empty($formError)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!empty($formSuccess)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($formSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <article class="card card-wide">
        <div class="section-head">
            <div>
                <h2>Usuarios cadastrados</h2>
                <p class="section-copy">A listagem ocupa toda a largura, e o cadastro/edicao acontece em modal.</p>
            </div>
            <button class="button button-primary" type="button" data-bs-toggle="modal" data-bs-target="#userModal">Novo usuario</button>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Login</th>
                        <th>Nome</th>
                        <th>Permissoes</th>
                        <th>Acoes</th>
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
                            <td>
                                <a class="button button-inline" href="<?= htmlspecialchars((BASE_URL ?: '') . '/users?edit=' . (int) $user['id'], ENT_QUOTES, 'UTF-8'); ?>">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

</section>

<div
    class="modal fade app-modal"
    id="userModal"
    tabindex="-1"
    aria-hidden="true"
    data-auto-open="<?= $shouldOpenModal ? 'true' : 'false'; ?>"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content app-modal-content">
            <div class="modal-header app-modal-header">
                <div>
                    <h2 class="modal-title app-modal-title"><?= $isEditing ? 'Editar usuario' : 'Novo usuario'; ?></h2>
                    <?php if ($isEditing): ?>
                        <p class="form-help">Deixe a senha em branco para manter a atual.</p>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form class="form-grid" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>">
                    <label class="field">
                        <span>Login</span>
                        <input type="text" name="login" value="<?= htmlspecialchars((string) $loginValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>

                    <label class="field">
                        <span>Nome</span>
                        <input type="text" name="name" value="<?= htmlspecialchars((string) $nameValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>

                    <label class="field">
                        <span><?= $isEditing ? 'Nova senha' : 'Senha'; ?></span>
                        <input type="password" name="password" <?= $isEditing ? '' : 'required'; ?>>
                    </label>

                    <div class="checkbox-grid">
                        <label><input type="checkbox" name="is_admin" <?= !empty($formOld['is_admin']) || (!isset($formOld['is_admin']) && !empty($editingUser['is_admin'])) ? 'checked' : ''; ?>> IsAdmin</label>
                        <label><input type="checkbox" name="solicitante_approval" <?= !empty($formOld['solicitante_approval']) || (!isset($formOld['solicitante_approval']) && !empty($editingUser['solicitante_approval'])) ? 'checked' : ''; ?>> SOLICITANTE_APPROVAL</label>
                        <label><input type="checkbox" name="admin_approval" <?= !empty($formOld['admin_approval']) || (!isset($formOld['admin_approval']) && !empty($editingUser['admin_approval'])) ? 'checked' : ''; ?>> ADMIN_APPROVAL</label>
                        <label><input type="checkbox" name="financial_approval" <?= !empty($formOld['financial_approval']) || (!isset($formOld['financial_approval']) && !empty($editingUser['financial_approval'])) ? 'checked' : ''; ?>> FINANCIAL_APPROVAL</label>
                        <label><input type="checkbox" name="purchasing_approval" <?= !empty($formOld['purchasing_approval']) || (!isset($formOld['purchasing_approval']) && !empty($editingUser['purchasing_approval'])) ? 'checked' : ''; ?>> PURCHASING_APPROVAL</label>
                    </div>

                    <button class="button button-primary" type="submit"><?= $isEditing ? 'Salvar alteracoes' : 'Cadastrar usuario'; ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

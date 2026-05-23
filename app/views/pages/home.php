<section class="card app-surface border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-lg-5">
        <p class="app-eyebrow">Fluxo de compra</p>
        <h1 class="display-5 fw-semibold mb-3"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="lead text-secondary mb-4"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></p>

        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-4">
                <div class="card h-100 border-0 bg-body-tertiary">
                    <div class="card-body">
                        <h2 class="h5">Usuario logado</h2>
                        <p class="mb-0 text-secondary"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($user['login'] ?? '', ENT_QUOTES, 'UTF-8'); ?>)</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card h-100 border-0 bg-body-tertiary">
                    <div class="card-body">
                        <h2 class="h5">Perfil</h2>
                        <p class="mb-0 text-secondary"><?= !empty($user['is_admin']) ? 'Administrador do sistema' : 'Usuario autenticado'; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card h-100 border-0 bg-body-tertiary">
                    <div class="card-body">
                        <h2 class="h5">Banco</h2>
                        <p class="mb-0 text-secondary">Usuarios e permissoes persistidos em MySQL via PDO.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <p class="text-uppercase small fw-semibold text-secondary mb-2">Permissoes do usuario</p>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ([
                    'is_admin' => 'isAdmin',
                    'solicitante_approval' => 'SOLICITANTE_APPROVAL',
                    'admin_approval' => 'ADMIN_APPROVAL',
                    'financial_approval' => 'FINANCIAL_APPROVAL',
                    'purchasing_approval' => 'PURCHASING_APPROVAL',
                ] as $key => $label): ?>
                    <span class="badge rounded-pill <?= !empty($user[$key]) ? 'text-bg-success' : 'text-bg-light border text-secondary'; ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="alert alert-light border mb-0">
            <span class="text-uppercase small fw-semibold text-secondary d-block mb-1">Status do banco</span>
            <strong><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
    </div>
</section>

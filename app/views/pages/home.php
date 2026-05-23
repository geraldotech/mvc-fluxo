<section class="hero">
    <p class="eyebrow">Fluxo de compra</p>
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="lead"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></p>

    <div class="card-grid">
        <article class="card">
            <h2>Usuario logado</h2>
            <p><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($user['login'] ?? '', ENT_QUOTES, 'UTF-8'); ?>)</p>
        </article>
        <article class="card">
            <h2>Perfil</h2>
            <p><?= !empty($user['is_admin']) ? 'Administrador do sistema' : 'Usuario autenticado' ?></p>
        </article>
        <article class="card">
            <h2>Banco</h2>
            <p>Usuarios e permissoes persistidos em MySQL via PDO.</p>
        </article>
    </div>

    <div class="permission-list">
        <span class="status-label">Permissoes do usuario</span>
        <div class="chips">
            <?php foreach ([
                'is_admin' => 'isAdmin',
                'solicitante_approval' => 'SOLICITANTE_APPROVAL',
                'admin_approval' => 'ADMIN_APPROVAL',
                'financial_approval' => 'FINANCIAL_APPROVAL',
                'purchasing_approval' => 'PURCHASING_APPROVAL',
            ] as $key => $label): ?>
                <span class="chip <?= !empty($user[$key]) ? 'chip-active' : ''; ?>">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="status-box">
        <span class="status-label">Status do banco</span>
        <strong><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></strong>
    </div>
</section>

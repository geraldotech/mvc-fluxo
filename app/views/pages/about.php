<section class="card app-surface border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <p class="app-eyebrow">Sobre o projeto</p>
        <h1 class="h2 mb-3"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="text-secondary mb-4"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></p>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card h-100 border-0 bg-body-tertiary">
                    <div class="card-body">
                        <h2 class="h5">Estrutura</h2>
                        <p class="mb-0 text-secondary"><code>app/core</code>, <code>controllers</code>, <code>models</code>, <code>views</code> e <code>public</code> para assets.</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card h-100 border-0 bg-body-tertiary">
                    <div class="card-body">
                        <h2 class="h5">Design</h2>
                        <p class="mb-0 text-secondary">Interface baseada em Bootstrap 5 com poucos overrides visuais.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 bg-body-tertiary">
            <div class="card-body">
                <h2 class="h5 mb-3">Usuarios da tabela <code>users</code></h2>

                <?php if (!empty($dbError)): ?>
                    <div class="alert alert-warning mb-0"><?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php elseif (empty($users)): ?>
                    <p class="mb-0 text-secondary">Nenhum usuario encontrado na tabela <code>users</code>.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <?php foreach (array_keys($users[0]) as $column): ?>
                                        <th><?= htmlspecialchars((string) $column, ENT_QUOTES, 'UTF-8'); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <?php foreach ($user as $value): ?>
                                            <td><?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

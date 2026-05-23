<section class="panel">
    <p class="eyebrow">Sobre o projeto</p>
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="lead"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></p>

    <div class="stack">
        <div class="card">
            <h2>Estrutura</h2>
            <p>`app/core`, `controllers`, `models`, `views` e `public` para assets.</p>
        </div>
        <div class="card">
            <h2>Design</h2>
            <p>Interface minimalista, responsiva e sem bibliotecas externas.</p>
        </div>
    </div>

    <div class="stack">
        <div class="card">
            <h2>Usuarios da tabela `users`</h2>

            <?php if (!empty($dbError)): ?>
                <p><?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php elseif (empty($users)): ?>
                <p>Nenhum usuario encontrado na tabela `users`.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
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
</section>

<section class="auth-shell">
    <div class="auth-panel">
        <p class="eyebrow">Fluxo de compra</p>
        <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="lead">Acesse o sistema com seu usuario. Todas as paginas exigem autenticacao.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="hint-box">
            <strong>Primeiro acesso</strong>
            <p>Usuario padrao: <code>admin</code> | Senha: <code>admin123</code></p>
        </div>

        <form class="form-grid auth-form" method="post" action="<?= htmlspecialchars((BASE_URL ?: '') . '/auth/authenticate', ENT_QUOTES, 'UTF-8'); ?>">
            <label class="field auth-field">
                <span>Login</span>
                <input type="text" name="login" placeholder="Seu login" autocomplete="username" required>
            </label>

            <label class="field auth-field">
                <span>Senha</span>
                <input type="password" name="password" placeholder="Sua senha" autocomplete="current-password" required>
            </label>

            <button class="button button-primary auth-submit" type="submit">Entrar</button>
        </form>
    </div>
</section>

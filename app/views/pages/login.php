<section class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card app-surface border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-lg-5">
                <p class="app-eyebrow">Fluxo de compra</p>
                <h1 class="display-6 fw-semibold mb-3"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="text-secondary mb-4">Acesse o sistema com seu usuario. Todas as paginas exigem autenticacao.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <div class="alert alert-light border" role="alert">
                    <strong>Primeiro acesso</strong><br>
                    Usuario padrao: <code>admin</code> | Senha: <code>admin123</code>
                </div>

                <form class="vstack gap-3" method="post" action="<?= htmlspecialchars((BASE_URL ?: '') . '/auth/authenticate', ENT_QUOTES, 'UTF-8'); ?>">
                    <div>
                        <label class="form-label fw-semibold" for="login">Login</label>
                        <input class="form-control form-control-lg" id="login" type="text" name="login" placeholder="Seu login" autocomplete="username" required>
                    </div>

                    <div>
                        <label class="form-label fw-semibold" for="password">Senha</label>
                        <input class="form-control form-control-lg" id="password" type="password" name="password" placeholder="Sua senha" autocomplete="current-password" required>
                    </div>

                    <button class="btn btn-primary btn-lg w-100 mt-2" type="submit">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <p class="eyebrow">Arquivos</p>
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="lead">Lista simples carregada pelo model para demonstrar separacao de responsabilidades.</p>

    <div class="download-list">
        <?php foreach ($files as $file): ?>
            <article class="download-item">
                <div>
                    <h2><?= htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?= htmlspecialchars($file['format'], ENT_QUOTES, 'UTF-8'); ?> . <?= htmlspecialchars($file['size'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <a href="#" class="button">Baixar</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

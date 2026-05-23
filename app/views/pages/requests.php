<?php
$oldTitle = $formOld['title'] ?? '';
$oldItemIds = array_map('intval', $formOld['item_ids'] ?? []);
?>
<section class="card app-surface border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <p class="app-eyebrow">Solicitacoes</p>
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 mb-4">
            <div>
                <h1 class="h2 mb-2"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="text-secondary mb-0">Crie solicitacoes com varios itens e acompanhe o fluxo por etapa e por item.</p>
            </div>
            <?php if ($canCreate): ?>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#requestModal">Nova solicitacao</button>
            <?php endif; ?>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 bg-body-tertiary h-100">
                    <div class="card-body">
                        <p class="text-uppercase small fw-semibold text-secondary mb-2">Pendentes admin</p>
                        <div class="display-6 mb-0"><?= (int) ($pendingCounts['admin'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 bg-body-tertiary h-100">
                    <div class="card-body">
                        <p class="text-uppercase small fw-semibold text-secondary mb-2">Pendentes financeiro</p>
                        <div class="display-6 mb-0"><?= (int) ($pendingCounts['financial'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 bg-body-tertiary h-100">
                    <div class="card-body">
                        <p class="text-uppercase small fw-semibold text-secondary mb-2">Pendentes compra</p>
                        <div class="display-6 mb-0"><?= (int) ($pendingCounts['purchasing'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
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
                        <th>Titulo</th>
                        <th>Solicitante</th>
                        <th>Etapa</th>
                        <th>Itens</th>
                        <th>Criada em</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td>#<?= htmlspecialchars((string) $request['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($request['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($request['creator_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge text-bg-light border">
                                    <?= htmlspecialchars($stageLabels[$request['current_stage']] ?? $request['current_stage'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars((string) $request['total_items'], ENT_QUOTES, 'UTF-8'); ?>
                                <small class="text-secondary d-block">
                                    <?= htmlspecialchars((string) $request['completed_items'], ENT_QUOTES, 'UTF-8'); ?> concluidos /
                                    <?= htmlspecialchars((string) $request['rejected_items'], ENT_QUOTES, 'UTF-8'); ?> rejeitados
                                </small>
                            </td>
                            <td><?= htmlspecialchars((string) $request['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars((BASE_URL ?: '') . '/requests/show/' . (int) $request['id'], ENT_QUOTES, 'UTF-8'); ?>">Abrir fluxo</a>
                                    <form method="post" action="<?= htmlspecialchars((BASE_URL ?: '') . '/requests/destroy/' . (int) $request['id'], ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Apagar esta solicitacao e todo o fluxo?');">
                                        <button class="btn btn-outline-danger btn-sm" type="submit">Apagar fluxo</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php if ($canCreate): ?>
    <div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true" data-auto-open="<?= $shouldOpenModal ? 'true' : 'false'; ?>">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h2 class="modal-title h4 mb-0">Nova solicitacao</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" method="post" action="<?= htmlspecialchars((BASE_URL ?: '') . '/requests/store', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="request-title">Titulo da solicitacao</label>
                            <input class="form-control" id="request-title" type="text" name="title" value="<?= htmlspecialchars((string) $oldTitle, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Selecionar itens</label>
                            <div class="border rounded-3 p-3 request-item-list">
                                <div class="row g-2">
                                    <?php foreach ($items as $item): ?>
                                        <div class="col-12 col-md-6">
                                            <label class="permission-option" for="request-item-<?= (int) $item['id']; ?>">
                                                <input class="form-check-input mt-0" id="request-item-<?= (int) $item['id']; ?>" type="checkbox" name="item_ids[]" value="<?= (int) $item['id']; ?>" <?= in_array((int) $item['id'], $oldItemIds, true) ? 'checked' : ''; ?>>
                                                <span>
                                                    <strong class="d-block"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <small class="text-secondary"><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?> . R$ <?= htmlspecialchars(number_format((float) $item['price'], 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></small>
                                                </span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">Criar solicitacao</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

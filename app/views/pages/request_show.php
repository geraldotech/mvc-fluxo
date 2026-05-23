<section class="card app-surface border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 mb-4">
            <div>
                <p class="app-eyebrow mb-2">Fluxo de solicitacao</p>
                <h1 class="h2 mb-2"><?= htmlspecialchars($request['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="text-secondary mb-0">Criada por <?= htmlspecialchars($request['creator_name'], ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($request['creator_login'], ENT_QUOTES, 'UTF-8'); ?>)</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge text-bg-light border px-3 py-2"><?= htmlspecialchars($stageLabels[$request['current_stage']] ?? $request['current_stage'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="badge text-bg-secondary px-3 py-2"><?= htmlspecialchars($statusLabels[$request['status']] ?? $request['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars((BASE_URL ?: '') . '/requests', ENT_QUOTES, 'UTF-8'); ?>">Voltar</a>
            </div>
        </div>

        <?php if (!empty($actionError)): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($actionError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($actionSuccess)): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($actionSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($bulkActionItems)): ?>
            <div class="card border mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">Decisao em lote da etapa atual</h2>
                            <p class="text-secondary mb-0">Marque os itens que devem ser aprovados. Itens nao marcados serao reprovados ao enviar.</p>
                        </div>
                        <span class="badge text-bg-light border"><?= count($bulkActionItems); ?> item(ns) aguardando</span>
                    </div>

                    <form method="post" action="<?= htmlspecialchars((BASE_URL ?: '') . '/requests/decideStage/' . (int) $request['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-2 mb-3">
                            <?php foreach ($bulkActionItems as $bulkItem): ?>
                                <div class="col-12 col-lg-6">
                                    <label class="permission-option" for="approve-item-<?= (int) $bulkItem['id']; ?>">
                                        <input type="hidden" name="actionable_item_ids[]" value="<?= (int) $bulkItem['id']; ?>">
                                        <input class="form-check-input mt-0" id="approve-item-<?= (int) $bulkItem['id']; ?>" type="checkbox" name="approved_item_ids[]" value="<?= (int) $bulkItem['id']; ?>">
                                        <span>
                                            <strong class="d-block"><?= htmlspecialchars($bulkItem['item_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <small class="text-secondary"><?= htmlspecialchars($bulkItem['category'], ENT_QUOTES, 'UTF-8'); ?> . R$ <?= htmlspecialchars(number_format((float) $bulkItem['price'], 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></small>
                                        </span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">Enviar decisoes da etapa</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border bg-body-tertiary mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <strong class="d-block mb-1">Onde esta agora</strong>
                        <span><?= htmlspecialchars($stageLabels[$request['current_stage']] ?? $request['current_stage'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="col-12 col-lg-4">
                        <strong class="d-block mb-1">Quem falta nesta etapa</strong>
                        <?php if (empty($requestMissingNames) || !in_array($request['current_stage'], ['ADMIN_APPROVAL', 'FINANCIAL_APPROVAL', 'PURCHASING'], true)): ?>
                            <span class="text-secondary">Nenhum pendente nesta etapa.</span>
                        <?php else: ?>
                            <span><?= htmlspecialchars(implode(', ', $requestMissingNames), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-12 col-lg-4">
                        <strong class="d-block mb-1">Ja aprovaram nesta etapa</strong>
                        <?php if (
                            in_array($request['current_stage'], ['COMPLETED', 'REJECTED'], true)
                            || empty($requestApprovedNames)
                            || !in_array($request['current_stage'], ['ADMIN_APPROVAL', 'FINANCIAL_APPROVAL'], true)
                        ): ?>
                            <span class="text-secondary">Sem aprovacoes registradas nesta etapa.</span>
                        <?php else: ?>
                            <span><?= htmlspecialchars(implode(', ', $requestApprovedNames), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <strong class="d-block mb-2">Etapas do fluxo</strong>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($requestFlowStages as $stage): ?>
                                <?php
                                $stageClass = 'text-bg-light border';

                                if ($request['status'] === 'REJECTED') {
                                    $stageClass = $stage === $request['current_stage'] ? 'text-bg-danger' : 'text-bg-light border';
                                } elseif ($stage === $request['current_stage']) {
                                    $stageClass = 'text-bg-primary';
                                } elseif (array_search($stage, $requestFlowStages, true) < array_search($request['current_stage'], $requestFlowStages, true)) {
                                    $stageClass = 'text-bg-success';
                                }
                                ?>
                                <span class="badge <?= $stageClass; ?>">
                                    <?= htmlspecialchars($stageLabels[$stage] ?? $stage, ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if ($stage === $request['current_stage']): ?>
                                        . atual
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="vstack gap-3">
            <?php foreach ($items as $item): ?>
                <div class="card border">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-lg-between gap-3 mb-3">
                            <div>
                                <h2 class="h5 mb-1"><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="text-secondary mb-0"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="text-lg-end">
                                <div class="fw-semibold">R$ <?= htmlspecialchars(number_format((float) $item['price'], 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-secondary"><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge text-bg-light border"><?= htmlspecialchars($stageLabels[$item['current_stage']] ?? $item['current_stage'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="badge <?= $item['item_status'] === 'REJECTED' ? 'text-bg-danger' : ($item['item_status'] === 'COMPLETED' ? 'text-bg-success' : 'text-bg-secondary'); ?>">
                                <?= htmlspecialchars($statusLabels[$item['item_status']] ?? $item['item_status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php if ($item['required_approvals'] > 0 && in_array($item['current_stage'], ['ADMIN_APPROVAL', 'FINANCIAL_APPROVAL'], true)): ?>
                                <span class="badge text-bg-light border"><?= htmlspecialchars((string) $item['approved_count'], ENT_QUOTES, 'UTF-8'); ?>/<?= htmlspecialchars((string) $item['required_approvals'], ENT_QUOTES, 'UTF-8'); ?> aprovacoes</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($item['can_purchase']): ?>
                            <form class="row g-2 mb-3" method="post" action="<?= htmlspecialchars((BASE_URL ?: '') . '/requests/purchase/' . (int) $item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="request_id" value="<?= (int) $request['id']; ?>">
                                <div class="col-12 col-lg-8">
                                    <input class="form-control form-control-sm" type="text" name="receipt_note" placeholder="Comprovante ou observacao da compra">
                                </div>
                                <div class="col-12 col-lg-4 d-grid">
                                    <button class="btn btn-primary btn-sm" type="submit">Finalizar compra do item</button>
                                </div>
                            </form>
                        <?php elseif ($item['user_decision'] !== null): ?>
                            <div class="alert alert-light border mb-3 py-2">Voce ja registrou sua decisao nesta etapa: <strong><?= htmlspecialchars($item['user_decision'], ENT_QUOTES, 'UTF-8'); ?></strong>.</div>
                        <?php elseif ($item['can_approve']): ?>
                            <div class="alert alert-light border mb-3 py-2">Este item aguarda sua decisao no envio em lote acima.</div>
                        <?php endif; ?>

                        <?php if (!empty($item['receipt_note'])): ?>
                            <div class="alert alert-light border mb-3">
                                <strong>Comprovante / observacao da compra:</strong><br>
                                <?= nl2br(htmlspecialchars($item['receipt_note'], ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                        <?php endif; ?>

                        <div>
                            <p class="text-uppercase small fw-semibold text-secondary mb-2">Historico de aprovacoes</p>
                            <?php if (empty($item['history'])): ?>
                                <p class="text-secondary mb-0">Nenhuma acao registrada ate o momento.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($item['history'] as $approval): ?>
                                        <li class="list-group-item px-0">
                                            <strong><?= htmlspecialchars($approval['approver_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <span class="text-secondary"> . <?= htmlspecialchars($stageLabels[$approval['stage_code']] ?? $approval['stage_code'], ENT_QUOTES, 'UTF-8'); ?> . <?= htmlspecialchars($approval['decision'], ENT_QUOTES, 'UTF-8'); ?> . <?= htmlspecialchars((string) $approval['decided_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

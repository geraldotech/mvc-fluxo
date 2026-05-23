<?php
$isEditing = !empty($editingItem);
$shouldOpenModal = $isEditing || !empty($formError);
$formAction = $isEditing
    ? (BASE_URL ?: '') . '/items/update/' . (int) $editingItem['id']
    : (BASE_URL ?: '') . '/items/store';
$itemNameValue = $formOld['item_name'] ?? ($editingItem['item_name'] ?? '');
$descriptionValue = $formOld['description'] ?? ($editingItem['description'] ?? '');
$priceValue = $formOld['price'] ?? ($editingItem['price'] ?? '');
$categoryValue = $formOld['category'] ?? ($editingItem['category'] ?? '');
?>
<section class="card app-surface border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <p class="app-eyebrow">Catalogo</p>
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 mb-4">
            <div>
                <h1 class="h2 mb-2"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="text-secondary mb-0">Somente administradores podem cadastrar, editar e apagar itens do fluxo de compra.</p>
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#itemModal">Novo item</button>
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
                        <th>Item</th>
                        <th>Descricao</th>
                        <th>Preco</th>
                        <th>Categoria</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $item['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-break"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>R$ <?= htmlspecialchars(number_format((float) $item['price'], 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars((BASE_URL ?: '') . '/items?edit=' . (int) $item['id'], ENT_QUOTES, 'UTF-8'); ?>">Editar</a>
                                    <form method="post" action="<?= htmlspecialchars((BASE_URL ?: '') . '/items/destroy/' . (int) $item['id'], ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Apagar este item?');">
                                        <button class="btn btn-outline-danger btn-sm" type="submit">Apagar</button>
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

<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true" data-auto-open="<?= $shouldOpenModal ? 'true' : 'false'; ?>">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h2 class="modal-title h4 mb-0"><?= $isEditing ? 'Editar item' : 'Novo item'; ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="item-name">Item name</label>
                        <input class="form-control" id="item-name" type="text" name="item_name" value="<?= htmlspecialchars((string) $itemNameValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="item-category">Category</label>
                        <input class="form-control" id="item-category" type="text" name="category" value="<?= htmlspecialchars((string) $categoryValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="item-description">Description</label>
                        <textarea class="form-control" id="item-description" name="description" rows="5" required><?= htmlspecialchars((string) $descriptionValue, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold" for="item-price">Price</label>
                        <input class="form-control" id="item-price" type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars((string) $priceValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit"><?= $isEditing ? 'Salvar alteracoes' : 'Cadastrar item'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

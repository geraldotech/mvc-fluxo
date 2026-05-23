<section class="panel">
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
    <p class="eyebrow">Catalogo</p>
    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p class="lead">Somente administradores podem cadastrar, editar e apagar itens do fluxo de compra.</p>

    <?php if (!empty($formError)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!empty($formSuccess)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($formSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <article class="card card-wide">
        <div class="section-head">
            <div>
                <h2>Itens cadastrados</h2>
                <p class="section-copy">A listagem ocupa toda a largura, e o cadastro/edicao acontece em modal.</p>
            </div>
            <button class="button button-primary" type="button" data-bs-toggle="modal" data-bs-target="#itemModal">Novo item</button>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item</th>
                        <th>Descricao</th>
                        <th>Preco</th>
                        <th>Categoria</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $item['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item['item_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="table-description"><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>R$ <?= htmlspecialchars(number_format((float) $item['price'], 2, ',', '.'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <div class="action-row">
                                    <a class="button button-inline" href="<?= htmlspecialchars((BASE_URL ?: '') . '/items?edit=' . (int) $item['id'], ENT_QUOTES, 'UTF-8'); ?>">Editar</a>
                                    <form method="post" action="<?= htmlspecialchars((BASE_URL ?: '') . '/items/destroy/' . (int) $item['id'], ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Apagar este item?');">
                                        <button class="button button-inline button-danger" type="submit">Apagar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

</section>

<div
    class="modal fade app-modal"
    id="itemModal"
    tabindex="-1"
    aria-hidden="true"
    data-auto-open="<?= $shouldOpenModal ? 'true' : 'false'; ?>"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content app-modal-content">
            <div class="modal-header app-modal-header">
                <h2 class="modal-title app-modal-title"><?= $isEditing ? 'Editar item' : 'Novo item'; ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form class="form-grid" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>">
                    <label class="field">
                        <span>Item name</span>
                        <input type="text" name="item_name" value="<?= htmlspecialchars((string) $itemNameValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>

                    <label class="field">
                        <span>Description</span>
                        <textarea name="description" rows="5" required><?= htmlspecialchars((string) $descriptionValue, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>

                    <label class="field">
                        <span>Price</span>
                        <input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars((string) $priceValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>

                    <label class="field">
                        <span>Category</span>
                        <input type="text" name="category" value="<?= htmlspecialchars((string) $categoryValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>

                    <button class="button button-primary" type="submit"><?= $isEditing ? 'Salvar alteracoes' : 'Cadastrar item'; ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

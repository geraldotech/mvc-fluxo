<?php

declare(strict_types=1);

class RequestsController extends Controller
{
    private RequestModel $requestModel;
    private ItemModel $itemModel;

    public function __construct()
    {
        $this->requestModel = $this->model('RequestModel');
        $this->itemModel = $this->model('ItemModel');
        Auth::requireLogin();
    }

    public function index(): void
    {
        $user = Auth::user();

        $this->view('pages/requests', [
            'title' => 'Solicitacoes',
            'requests' => $this->requestModel->getAllRequests(),
            'items' => $this->itemModel->getAll(), 
            'pendingCounts' => $user === null ? [] : $this->requestModel->getPendingCountsForUser((int) $user['id']),
            'canCreate' => Auth::hasPermission('solicitante_approval'),
            'formError' => $_SESSION['request_form_error'] ?? null,
            'formSuccess' => $_SESSION['request_form_success'] ?? null,
            'formOld' => $_SESSION['request_form_old'] ?? [],
            'shouldOpenModal' => !empty($_SESSION['request_form_error']),
            'stageLabels' => $this->getStageLabels(),
        ]);

        unset(
            $_SESSION['request_form_error'],
            $_SESSION['request_form_success'],
            $_SESSION['request_form_old']
        );
    }

    public function store(): void
    {
        Auth::requirePermission('solicitante_approval');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('requests');
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $itemIds = $_POST['item_ids'] ?? [];
        $_SESSION['request_form_old'] = [
            'title' => $title,
            'item_ids' => is_array($itemIds) ? $itemIds : [],
        ];

        if ($title === '' || !is_array($itemIds) || empty($itemIds)) {
            $_SESSION['request_form_error'] = 'Informe o titulo e selecione pelo menos um item.';
            $this->redirect('requests');
        }

        $user = Auth::user();
        $requestId = $this->requestModel->createRequest($title, $itemIds, (int) ($user['id'] ?? 0));

        if ($requestId === null) {
            $_SESSION['request_form_error'] = 'Nao foi possivel criar a solicitacao.';
            $this->redirect('requests');
        }

        unset($_SESSION['request_form_old']);
        $_SESSION['request_action_success'] = 'Solicitacao criada com sucesso.';
        $this->redirect('requests/show/' . $requestId);
    }

    public function show($id = null): void
    {
        $requestId = (int) $id;
        $request = $this->requestModel->findRequestById($requestId);

        if ($requestId <= 0 || $request === null) {
            (new ErrorController())->notFound();
            return;
        }

        $user = Auth::user();
        $items = $this->requestModel->getRequestItems($requestId);
        $approvals = $this->requestModel->getApprovalHistoryByRequest($requestId);
        $userDecisions = $user === null ? [] : $this->requestModel->getUserDecisionsForRequest($requestId, (int) $user['id']);
        $approverCounts = $this->requestModel->getApproverCounts();
        $stageApproversMap = $this->requestModel->getStageApproversMap();
        $requestMissingNames = [];
        $requestApprovedNames = [];
        $bulkActionItems = [];
        $requestStageHistory = [];
        $financialResponsibleNames = array_values(array_map(
            fn (array $approver): string => $approver['name'],
            $stageApproversMap[RequestModel::STAGE_FINANCIAL] ?? []
        ));
        $financialReadyItems = [];
        $purchasingResponsibleNames = array_values(array_map(
            fn (array $approver): string => $approver['name'],
            $stageApproversMap[RequestModel::STAGE_PURCHASING] ?? []
        ));
        $purchasingReadyItems = [];

        foreach ($items as &$item) {
            $decisionKey = (int) $item['id'] . ':' . $item['current_stage'];
            $item['user_decision'] = $userDecisions[$decisionKey] ?? null;
            $item['history'] = $approvals[(int) $item['id']] ?? [];
            $item['previous_stage_approved_names'] = array_values(array_unique(array_map(
                fn (array $approval): string => (string) $approval['approver_name'],
                array_filter(
                    $item['history'],
                    fn (array $approval): bool => $approval['stage_code'] === RequestModel::STAGE_FINANCIAL
                        && $approval['decision'] === RequestModel::DECISION_APPROVED
                )
            )));
            $item['required_approvals'] = $approverCounts[$item['current_stage']] ?? 0;
            $item['approved_count'] = count(array_filter(
                $item['history'],
                fn (array $approval): bool => $approval['stage_code'] === $item['current_stage'] && $approval['decision'] === RequestModel::DECISION_APPROVED
            ));
            $item['current_stage_approvers'] = $stageApproversMap[$item['current_stage']] ?? [];
            $item['approved_names'] = array_values(array_map(
                fn (array $approval): string => $approval['approver_name'],
                array_filter(
                    $item['history'],
                    fn (array $approval): bool => $approval['stage_code'] === $item['current_stage'] && $approval['decision'] === RequestModel::DECISION_APPROVED
                )
            ));
            $item['missing_names'] = array_values(array_map(
                fn (array $approver): string => $approver['name'],
                array_filter(
                    $item['current_stage_approvers'],
                    fn (array $approver): bool => !in_array($approver['name'], $item['approved_names'], true)
                )
            ));
            $item['remaining_stages'] = $this->getRemainingStages($item['current_stage'], $item['item_status']);

            if ($item['item_status'] === RequestModel::ITEM_STATUS_OPEN && $item['current_stage'] === $request['current_stage']) {
                $requestMissingNames = array_merge($requestMissingNames, $item['missing_names']);
                $requestApprovedNames = array_merge($requestApprovedNames, $item['approved_names']);
            }

            $item['can_approve'] = in_array($item['current_stage'], [RequestModel::STAGE_ADMIN, RequestModel::STAGE_FINANCIAL], true)
                && $item['item_status'] === RequestModel::ITEM_STATUS_OPEN
                && $item['user_decision'] === null
                && $this->userCanActOnStage($item['current_stage']);
            $item['can_purchase'] = $item['current_stage'] === RequestModel::STAGE_PURCHASING
                && $item['item_status'] === RequestModel::ITEM_STATUS_OPEN
                && $this->userCanActOnStage(RequestModel::STAGE_PURCHASING)
                && $user !== null
                && in_array((string) ($user['name'] ?? ''), $item['previous_stage_approved_names'], true);

            if ($item['can_approve']) {
                $bulkActionItems[] = [
                    'id' => (int) $item['id'],
                    'item_name' => $item['item_name'],
                    'category' => $item['category'],
                    'price' => $item['price'],
                ];
            }

            if ($item['current_stage'] === RequestModel::STAGE_FINANCIAL && $item['item_status'] === RequestModel::ITEM_STATUS_OPEN) {
                if (!empty($financialResponsibleNames)) {
                    $financialReadyItems[] = [
                        'item_name' => $item['item_name'],
                        'decisions' => array_map(
                            fn (string $approverName): array => [
                                'approver_name' => $approverName,
                                'decision' => 'AGUARDANDO_APROVACAO',
                                'comment' => '',
                                'decided_at' => '',
                            ],
                            $financialResponsibleNames
                        ),
                    ];
                }
            }

            if ($item['current_stage'] === RequestModel::STAGE_PURCHASING && $item['item_status'] === RequestModel::ITEM_STATUS_OPEN) {
                if (!empty($item['previous_stage_approved_names'])) {
                    $purchasingReadyItems[] = [
                        'item_name' => $item['item_name'],
                        'decisions' => array_map(
                            fn (string $approverName): array => [
                                'approver_name' => $approverName,
                                'decision' => 'AGUARDANDO_COMPRA',
                                'comment' => '',
                                'decided_at' => '',
                            ],
                            $item['previous_stage_approved_names']
                        ),
                    ];
                }
            }
        }
        unset($item);

        $requestMissingNames = array_values(array_unique($requestMissingNames));
        $requestApprovedNames = array_values(array_unique($requestApprovedNames));
        $purchasingResponsibleNames = array_values(array_unique(array_merge(
            [],
            ...array_map(
                static fn (array $item): array => $item['current_stage'] === RequestModel::STAGE_PURCHASING
                    && $item['item_status'] === RequestModel::ITEM_STATUS_OPEN
                    ? ($item['previous_stage_approved_names'] ?? [])
                    : [],
                $items
            )
        )));
        $requestStageHistory = $this->buildRequestStageHistory($items);
        $requestFlowStages = [
            RequestModel::STAGE_ADMIN,
            RequestModel::STAGE_FINANCIAL,
            RequestModel::STAGE_PURCHASING,
            RequestModel::STAGE_COMPLETED,
        ];

        $this->view('pages/request_show', [
            'title' => 'Solicitacao #' . $requestId,
            'request' => $request,
            'items' => $items,
            'requestMissingNames' => $requestMissingNames,
            'requestApprovedNames' => $requestApprovedNames,
            'requestFlowStages' => $requestFlowStages,
            'requestStageHistory' => $requestStageHistory,
            'bulkActionItems' => $bulkActionItems,
            'financialResponsibleNames' => $financialResponsibleNames,
            'financialReadyItems' => $financialReadyItems,
            'purchasingResponsibleNames' => $purchasingResponsibleNames,
            'purchasingReadyItems' => $purchasingReadyItems,
            'stageLabels' => $this->getStageLabels(),
            'statusLabels' => $this->getStatusLabels(),
            'actionError' => $_SESSION['request_action_error'] ?? null,
            'actionSuccess' => $_SESSION['request_action_success'] ?? null,
        ]);

        unset($_SESSION['request_action_error'], $_SESSION['request_action_success']);
    }

    public function decide($requestItemId = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('requests');
        }

        $itemId = (int) $requestItemId;
        $requestId = (int) ($_POST['request_id'] ?? 0);
        $decision = strtoupper(trim((string) ($_POST['decision'] ?? '')));
        $user = Auth::user();

        $ok = $this->requestModel->approveOrRejectItem($itemId, (int) ($user['id'] ?? 0), $decision);

        $_SESSION[$ok ? 'request_action_success' : 'request_action_error'] = $ok
            ? 'Decisao registrada com sucesso.'
            : 'Nao foi possivel registrar a decisao.';

        $this->redirect('requests/show/' . $requestId);
    }

    public function decideStage($id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('requests');
        }

        $requestId = (int) $id;
        $request = $this->requestModel->findRequestById($requestId);

        if ($requestId <= 0 || $request === null) {
            $_SESSION['request_action_error'] = 'Solicitacao nao encontrada.';
            $this->redirect('requests');
        }

        $user = Auth::user();
        $actionableItemIds = array_values(array_unique(array_map('intval', $_POST['actionable_item_ids'] ?? [])));
        $approvedItemIds = array_values(array_unique(array_map('intval', $_POST['approved_item_ids'] ?? [])));

        if (empty($actionableItemIds)) {
            $_SESSION['request_action_error'] = 'Nenhum item disponivel para decisao nesta etapa.';
            $this->redirect('requests/show/' . $requestId);
        }

        $successCount = 0;

        foreach ($actionableItemIds as $itemId) {
            $decision = in_array($itemId, $approvedItemIds, true)
                ? RequestModel::DECISION_APPROVED
                : RequestModel::DECISION_REJECTED;

            if ($this->requestModel->approveOrRejectItem($itemId, (int) ($user['id'] ?? 0), $decision)) {
                $successCount++;
            }
        }

        if ($successCount === 0) {
            $_SESSION['request_action_error'] = 'Nao foi possivel registrar as decisoes desta etapa.';
        } elseif ($successCount < count($actionableItemIds)) {
            $_SESSION['request_action_success'] = 'Parte das decisoes foi registrada. Alguns itens ja nao estavam mais disponiveis.';
        } else {
            $_SESSION['request_action_success'] = 'Decisoes da etapa registradas com sucesso.';
        }

        $this->redirect('requests/show/' . $requestId);
    }

    public function purchase($requestItemId = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('requests');
        }

        $itemId = (int) $requestItemId;
        $requestId = (int) ($_POST['request_id'] ?? 0);
        $receiptNote = trim((string) ($_POST['receipt_note'] ?? ''));
        $user = Auth::user();

        $ok = $this->requestModel->completePurchasing($itemId, (int) ($user['id'] ?? 0), $receiptNote);

        $_SESSION[$ok ? 'request_action_success' : 'request_action_error'] = $ok
            ? 'Item finalizado na etapa de compra.'
            : 'Nao foi possivel finalizar a compra do item.';

        $this->redirect('requests/show/' . $requestId);
    }

    public function destroy($id = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('requests');
        }

        $requestId = (int) $id;
        $request = $this->requestModel->findRequestById($requestId);

        if ($requestId <= 0 || $request === null) {
            $_SESSION['request_form_error'] = 'Solicitacao nao encontrada.';
            $this->redirect('requests');
        }

        $user = Auth::user();
        $canDelete = Auth::hasPermission('solicitante_approval')
            && (
                !empty($user['is_admin'])
                || (int) ($request['created_by'] ?? 0) === (int) ($user['id'] ?? 0)
            );

        if (!$canDelete) {
            $_SESSION['request_form_error'] = 'Voce nao pode apagar esta solicitacao.';
            $this->redirect('requests');
        }

        $deleted = $this->requestModel->deleteRequest($requestId);

        $_SESSION[$deleted ? 'request_form_success' : 'request_form_error'] = $deleted
            ? 'Solicitacao apagada com sucesso.'
            : 'Nao foi possivel apagar a solicitacao.';

        $this->redirect('requests');
    }

    private function getStageLabels(): array
    {
        return [
            RequestModel::STAGE_OPEN => 'Solicitacao Aberta',
            RequestModel::STAGE_ADMIN => 'Aprovacao Administrativa',
            RequestModel::STAGE_FINANCIAL => 'Aprovacao Financeira',
            RequestModel::STAGE_PURCHASING => 'Compra em Andamento',
            RequestModel::STAGE_COMPLETED => 'Finalizado',
            RequestModel::STAGE_REJECTED => 'Reprovado',
        ];
    }

    private function getStatusLabels(): array
    {
        return [
            RequestModel::REQUEST_STATUS_OPEN => 'Em andamento',
            RequestModel::REQUEST_STATUS_COMPLETED => 'Finalizada',
            RequestModel::REQUEST_STATUS_REJECTED => 'Rejeitada',
            RequestModel::ITEM_STATUS_OPEN => 'Pendente',
            RequestModel::ITEM_STATUS_COMPLETED => 'Concluido',
            RequestModel::ITEM_STATUS_REJECTED => 'Rejeitado',
        ];
    }

    private function userCanActOnStage(string $stage): bool
    {
        return match ($stage) {
            RequestModel::STAGE_ADMIN => Auth::hasPermission('admin_approval'),
            RequestModel::STAGE_FINANCIAL => Auth::hasPermission('financial_approval'),
            RequestModel::STAGE_PURCHASING => Auth::hasPermission('purchasing_approval'),
            default => false,
        };
    }

    private function getRemainingStages(string $currentStage, string $itemStatus): array
    {
        if ($itemStatus === RequestModel::ITEM_STATUS_REJECTED) {
            return [RequestModel::STAGE_REJECTED];
        }

        if ($itemStatus === RequestModel::ITEM_STATUS_COMPLETED) {
            return [RequestModel::STAGE_COMPLETED];
        }

        $flow = [
            RequestModel::STAGE_ADMIN,
            RequestModel::STAGE_FINANCIAL,
            RequestModel::STAGE_PURCHASING,
            RequestModel::STAGE_COMPLETED,
        ];

        $index = array_search($currentStage, $flow, true);

        if ($index === false) {
            return $flow;
        }

        return array_slice($flow, $index);
    }

    private function buildRequestStageHistory(array $items): array
    {
        $historyByStage = [];

        foreach ($items as $item) {
            foreach ($item['history'] as $entry) {
                $stage = $entry['stage_code'];
                $itemKey = $stage . ':' . $item['item_name'];

                if (!isset($historyByStage[$stage][$itemKey])) {
                    $historyByStage[$stage][$itemKey] = [
                        'item_name' => $item['item_name'],
                        'decisions' => [],
                    ];
                }

                $historyByStage[$stage][$itemKey]['decisions'][] = [
                    'approver_name' => $entry['approver_name'],
                    'decision' => $entry['decision'],
                    'comment' => $entry['comment'] ?? '',
                    'decided_at' => $entry['decided_at'],
                ];
            }

            if (
                $item['item_status'] === RequestModel::ITEM_STATUS_OPEN
                && in_array($item['current_stage'], [RequestModel::STAGE_ADMIN, RequestModel::STAGE_FINANCIAL, RequestModel::STAGE_PURCHASING], true)
            ) {
                $stage = $item['current_stage'];
                $itemKey = $stage . ':' . $item['item_name'];

                if (!isset($historyByStage[$stage][$itemKey])) {
                    $historyByStage[$stage][$itemKey] = [
                        'item_name' => $item['item_name'],
                        'decisions' => [],
                    ];
                }

                $existingApprovers = array_values(array_map(
                    fn (array $decision): string => $decision['approver_name'],
                    $historyByStage[$stage][$itemKey]['decisions']
                ));

                foreach (($item['current_stage_approvers'] ?? []) as $approver) {
                    $approverName = (string) ($approver['name'] ?? '');

                    if ($approverName === '' || in_array($approverName, $existingApprovers, true)) {
                        continue;
                    }

                    $historyByStage[$stage][$itemKey]['decisions'][] = [
                        'approver_name' => $approverName,
                        'decision' => $stage === RequestModel::STAGE_PURCHASING ? 'AGUARDANDO_COMPRA' : 'AGUARDANDO_APROVACAO',
                        'comment' => '',
                        'decided_at' => '',
                    ];
                }
            }

            if (
                !empty($item['completed_at'])
                && $item['current_stage'] === RequestModel::STAGE_COMPLETED
                && $item['item_status'] === RequestModel::ITEM_STATUS_COMPLETED
            ) {
                $stage = RequestModel::STAGE_PURCHASING;
                $itemKey = $stage . ':' . $item['item_name'];

                if (!isset($historyByStage[$stage][$itemKey])) {
                    $historyByStage[$stage][$itemKey] = [
                        'item_name' => $item['item_name'],
                        'decisions' => [],
                    ];
                }

                $historyByStage[$stage][$itemKey]['decisions'][] = [
                    'approver_name' => $item['purchased_by_name'] ?: 'Usuario responsavel',
                    'decision' => 'COMPRA_FINALIZADA',
                    'comment' => $item['receipt_note'] ?? '',
                    'decided_at' => $item['completed_at'],
                ];
            }
        }

        foreach ($historyByStage as $stage => $entries) {
            foreach ($entries as $itemKey => $itemEntry) {
                usort(
                    $itemEntry['decisions'],
                    function (array $left, array $right): int {
                        $leftDate = (string) ($left['decided_at'] ?? '');
                        $rightDate = (string) ($right['decided_at'] ?? '');

                        if ($leftDate === '' && $rightDate !== '') {
                            return 1;
                        }

                        if ($leftDate !== '' && $rightDate === '') {
                            return -1;
                        }

                        return strcmp($leftDate, $rightDate);
                    }
                );
                $entries[$itemKey] = $itemEntry;
            }

            $historyByStage[$stage] = array_values($entries);
        }

        return $historyByStage;
    }
}

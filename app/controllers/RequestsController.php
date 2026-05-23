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

        foreach ($items as &$item) {
            $decisionKey = (int) $item['id'] . ':' . $item['current_stage'];
            $item['user_decision'] = $userDecisions[$decisionKey] ?? null;
            $item['history'] = $approvals[(int) $item['id']] ?? [];
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
                && $this->userCanActOnStage(RequestModel::STAGE_PURCHASING);
        }
        unset($item);

        $requestMissingNames = array_values(array_unique($requestMissingNames));
        $requestApprovedNames = array_values(array_unique($requestApprovedNames));
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
}

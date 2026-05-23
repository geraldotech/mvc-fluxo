<?php

declare(strict_types=1);

class RequestModel extends Model
{
    public const REQUEST_STATUS_OPEN = 'OPEN';
    public const REQUEST_STATUS_COMPLETED = 'COMPLETED';
    public const REQUEST_STATUS_REJECTED = 'REJECTED';

    public const ITEM_STATUS_OPEN = 'OPEN';
    public const ITEM_STATUS_COMPLETED = 'COMPLETED';
    public const ITEM_STATUS_REJECTED = 'REJECTED';

    public const STAGE_OPEN = 'OPEN';
    public const STAGE_ADMIN = 'ADMIN_APPROVAL';
    public const STAGE_FINANCIAL = 'FINANCIAL_APPROVAL';
    public const STAGE_PURCHASING = 'PURCHASING';
    public const STAGE_COMPLETED = 'COMPLETED';
    public const STAGE_REJECTED = 'REJECTED';

    public const DECISION_APPROVED = 'APPROVED';
    public const DECISION_REJECTED = 'REJECTED';

    public function __construct()
    {
        parent::__construct();

        if ($this->db !== null) {
            $this->ensureTables();
        }
    }

    public function getAllRequests(): array
    {
        if ($this->db === null) {
            return [];
        }

        $statement = $this->db->query(
            'SELECT
                pr.id,
                pr.title,
                pr.current_stage,
                pr.status,
                pr.created_at,
                u.name AS creator_name,
                COUNT(ri.id) AS total_items,
                SUM(CASE WHEN ri.item_status = "COMPLETED" THEN 1 ELSE 0 END) AS completed_items,
                SUM(CASE WHEN ri.item_status = "REJECTED" THEN 1 ELSE 0 END) AS rejected_items
             FROM purchase_requests pr
             INNER JOIN users u ON u.id = pr.created_by
             LEFT JOIN purchase_request_items ri ON ri.request_id = pr.id
             GROUP BY pr.id, pr.title, pr.current_stage, pr.status, pr.created_at, u.name
             ORDER BY pr.created_at DESC'
        );

        return $statement->fetchAll();
    }

    public function getPendingCountsForUser(int $userId): array
    {
        if ($this->db === null) {
            return [
                'admin' => 0,
                'financial' => 0,
                'purchasing' => 0,
            ];
        }

        $user = $this->getUserById($userId);

        if ($user === null) {
            return [
                'admin' => 0,
                'financial' => 0,
                'purchasing' => 0,
            ];
        }

        return [
            'admin' => $this->countPendingStageForUser($user, self::STAGE_ADMIN),
            'financial' => $this->countPendingStageForUser($user, self::STAGE_FINANCIAL),
            'purchasing' => $this->countPendingStageForUser($user, self::STAGE_PURCHASING),
        ];
    }

    public function createRequest(string $title, array $itemIds, int $creatorId): ?int
    {
        if ($this->db === null) {
            return null;
        }

        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        $items = $this->getItemsByIds($itemIds);

        if ($title === '' || empty($items)) {
            return null;
        }

        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare(
                'INSERT INTO purchase_requests (title, created_by, current_stage, status)
                 VALUES (:title, :created_by, :current_stage, :status)'
            );
            $statement->execute([
                'title' => $title,
                'created_by' => $creatorId,
                'current_stage' => self::STAGE_OPEN,
                'status' => self::REQUEST_STATUS_OPEN,
            ]);

            $requestId = (int) $this->db->lastInsertId();

            $itemStatement = $this->db->prepare(
                'INSERT INTO purchase_request_items (
                    request_id,
                    item_id,
                    item_name,
                    description,
                    price,
                    category,
                    current_stage,
                    item_status
                 ) VALUES (
                    :request_id,
                    :item_id,
                    :item_name,
                    :description,
                    :price,
                    :category,
                    :current_stage,
                    :item_status
                 )'
            );

            foreach ($items as $item) {
                $itemStatement->execute([
                    'request_id' => $requestId,
                    'item_id' => $item['id'],
                    'item_name' => $item['item_name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'category' => $item['category'],
                    'current_stage' => self::STAGE_ADMIN,
                    'item_status' => self::ITEM_STATUS_OPEN,
                ]);

                $this->advanceThroughEmptyStages((int) $this->db->lastInsertId());
            }

            $this->refreshRequestStatus($requestId);
            $this->db->commit();

            return $requestId;
        } catch (Throwable $throwable) {
            $this->db->rollBack();
            return null;
        }
    }

    public function findRequestById(int $requestId): ?array
    {
        if ($this->db === null) {
            return null;
        }

        $statement = $this->db->prepare(
            'SELECT pr.*, u.name AS creator_name, u.login AS creator_login
             FROM purchase_requests pr
             INNER JOIN users u ON u.id = pr.created_by
             WHERE pr.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $requestId]);
        $request = $statement->fetch();

        return $request === false ? null : $request;
    }

    public function getRequestItems(int $requestId): array
    {
        if ($this->db === null) {
            return [];
        }

        $statement = $this->db->prepare(
            'SELECT *
             FROM purchase_request_items
             WHERE request_id = :request_id
             ORDER BY id ASC'
        );
        $statement->execute(['request_id' => $requestId]);

        return $statement->fetchAll();
    }

    public function getApprovalHistoryByRequest(int $requestId): array
    {
        if ($this->db === null) {
            return [];
        }

        $statement = $this->db->prepare(
            'SELECT
                pia.request_item_id,
                pia.stage_code,
                pia.decision,
                pia.comment,
                pia.decided_at,
                u.name AS approver_name
             FROM purchase_item_approvals pia
             INNER JOIN purchase_request_items pri ON pri.id = pia.request_item_id
             INNER JOIN users u ON u.id = pia.approver_id
             WHERE pri.request_id = :request_id
             ORDER BY pia.decided_at ASC, pia.id ASC'
        );
        $statement->execute(['request_id' => $requestId]);

        $history = [];

        foreach ($statement->fetchAll() as $approval) {
            $history[(int) $approval['request_item_id']][] = $approval;
        }

        return $history;
    }

    public function getUserDecisionsForRequest(int $requestId, int $userId): array
    {
        if ($this->db === null) {
            return [];
        }

        $statement = $this->db->prepare(
            'SELECT pia.request_item_id, pia.stage_code, pia.decision
             FROM purchase_item_approvals pia
             INNER JOIN purchase_request_items pri ON pri.id = pia.request_item_id
             WHERE pri.request_id = :request_id
               AND pia.approver_id = :approver_id'
        );
        $statement->execute([
            'request_id' => $requestId,
            'approver_id' => $userId,
        ]);

        $decisions = [];

        foreach ($statement->fetchAll() as $row) {
            $decisions[(int) $row['request_item_id'] . ':' . $row['stage_code']] = $row['decision'];
        }

        return $decisions;
    }

    public function approveOrRejectItem(int $requestItemId, int $userId, string $decision): bool
    {
        if ($this->db === null) {
            return false;
        }

        if (!in_array($decision, [self::DECISION_APPROVED, self::DECISION_REJECTED], true)) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            $item = $this->getRequestItemForUpdate($requestItemId);

            if ($item === null || $item['item_status'] !== self::ITEM_STATUS_OPEN) {
                throw new RuntimeException('Item invalido.');
            }

            if (!in_array($item['current_stage'], [self::STAGE_ADMIN, self::STAGE_FINANCIAL], true)) {
                throw new RuntimeException('Etapa invalida.');
            }

            $permission = $this->getPermissionColumnForStage($item['current_stage']);
            $user = $this->getUserById($userId);

            if ($permission === null || $user === null || !$this->userHasStagePermission($user, $permission)) {
                throw new RuntimeException('Sem permissao.');
            }

            if ($this->userAlreadyDecided($requestItemId, $item['current_stage'], $userId)) {
                throw new RuntimeException('Ja decidiu.');
            }

            $insert = $this->db->prepare(
                'INSERT INTO purchase_item_approvals (request_item_id, stage_code, approver_id, decision)
                 VALUES (:request_item_id, :stage_code, :approver_id, :decision)'
            );
            $insert->execute([
                'request_item_id' => $requestItemId,
                'stage_code' => $item['current_stage'],
                'approver_id' => $userId,
                'decision' => $decision,
            ]);

            if ($decision === self::DECISION_REJECTED) {
                $this->updateRequestItemStage($requestItemId, self::STAGE_REJECTED, self::ITEM_STATUS_REJECTED);
            } else {
                $requiredApprovers = $this->countApproversForStage($item['current_stage']);
                $approvedCount = $this->countStageDecisions($requestItemId, $item['current_stage'], self::DECISION_APPROVED);

                if ($requiredApprovers === 0 || $approvedCount >= $requiredApprovers) {
                    $this->advanceItemAfterApproval($requestItemId, $item['current_stage']);
                }
            }

            $this->refreshRequestStatus((int) $item['request_id']);
            $this->db->commit();

            return true;
        } catch (Throwable $throwable) {
            $this->db->rollBack();
            return false;
        }
    }

    public function completePurchasing(int $requestItemId, int $userId, string $receiptNote): bool
    {
        if ($this->db === null) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            $item = $this->getRequestItemForUpdate($requestItemId);
            $user = $this->getUserById($userId);

            if (
                $item === null
                || $user === null
                || $item['current_stage'] !== self::STAGE_PURCHASING
                || $item['item_status'] !== self::ITEM_STATUS_OPEN
                || !$this->userHasStagePermission($user, 'purchasing_approval')
            ) {
                throw new RuntimeException('Operacao invalida.');
            }

            $statement = $this->db->prepare(
                'UPDATE purchase_request_items
                 SET current_stage = :current_stage,
                     item_status = :item_status,
                     receipt_note = :receipt_note,
                     purchased_by = :purchased_by,
                     completed_at = NOW()
                 WHERE id = :id'
            );
            $statement->execute([
                'current_stage' => self::STAGE_COMPLETED,
                'item_status' => self::ITEM_STATUS_COMPLETED,
                'receipt_note' => $receiptNote,
                'purchased_by' => $userId,
                'id' => $requestItemId,
            ]);

            $this->refreshRequestStatus((int) $item['request_id']);
            $this->db->commit();

            return true;
        } catch (Throwable $throwable) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getApproverCounts(): array
    {
        return [
            self::STAGE_ADMIN => $this->countApproversForStage(self::STAGE_ADMIN),
            self::STAGE_FINANCIAL => $this->countApproversForStage(self::STAGE_FINANCIAL),
            self::STAGE_PURCHASING => $this->countApproversForStage(self::STAGE_PURCHASING),
        ];
    }

    public function deleteRequest(int $requestId): bool
    {
        if ($this->db === null) {
            return false;
        }

        $statement = $this->db->prepare('DELETE FROM purchase_requests WHERE id = :id');

        return $statement->execute(['id' => $requestId]);
    }

    public function getStageApproversMap(): array
    {
        return [
            self::STAGE_ADMIN => $this->getStageApprovers(self::STAGE_ADMIN),
            self::STAGE_FINANCIAL => $this->getStageApprovers(self::STAGE_FINANCIAL),
            self::STAGE_PURCHASING => $this->getStageApprovers(self::STAGE_PURCHASING),
        ];
    }

    private function ensureTables(): void
    {
        $this->db?->exec(
            <<<'SQL'
CREATE TABLE IF NOT EXISTS purchase_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    current_stage VARCHAR(40) NOT NULL DEFAULT 'OPEN',
    status VARCHAR(40) NOT NULL DEFAULT 'OPEN',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_purchase_requests_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );

        $this->db?->exec(
            <<<'SQL'
CREATE TABLE IF NOT EXISTS purchase_request_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    category VARCHAR(100) NOT NULL,
    current_stage VARCHAR(40) NOT NULL DEFAULT 'ADMIN_APPROVAL',
    item_status VARCHAR(40) NOT NULL DEFAULT 'OPEN',
    receipt_note TEXT NULL,
    purchased_by INT UNSIGNED NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_purchase_request_items_request FOREIGN KEY (request_id) REFERENCES purchase_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_purchase_request_items_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    CONSTRAINT fk_purchase_request_items_user FOREIGN KEY (purchased_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );

        $this->db?->exec(
            <<<'SQL'
CREATE TABLE IF NOT EXISTS purchase_item_approvals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_item_id INT UNSIGNED NOT NULL,
    stage_code VARCHAR(40) NOT NULL,
    approver_id INT UNSIGNED NOT NULL,
    decision VARCHAR(40) NOT NULL,
    comment TEXT NULL,
    decided_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_purchase_item_stage_user UNIQUE (request_item_id, stage_code, approver_id),
    CONSTRAINT fk_purchase_item_approvals_item FOREIGN KEY (request_item_id) REFERENCES purchase_request_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_purchase_item_approvals_user FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
    }

    private function getItemsByIds(array $itemIds): array
    {
        if ($this->db === null || empty($itemIds)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($itemIds), '?'));
        $statement = $this->db->prepare(
            "SELECT id, item_name, description, price, category
             FROM items
             WHERE id IN ($placeholders)"
        );
        $statement->execute($itemIds);

        return $statement->fetchAll();
    }

    private function getRequestItemForUpdate(int $requestItemId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT *
             FROM purchase_request_items
             WHERE id = :id
             LIMIT 1
             FOR UPDATE'
        );
        $statement->execute(['id' => $requestItemId]);
        $item = $statement->fetch();

        return $item === false ? null : $item;
    }

    private function getUserById(int $userId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $userId]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    private function getPermissionColumnForStage(string $stage): ?string
    {
        return match ($stage) {
            self::STAGE_ADMIN => 'admin_approval',
            self::STAGE_FINANCIAL => 'financial_approval',
            self::STAGE_PURCHASING => 'purchasing_approval',
            default => null,
        };
    }

    private function userHasStagePermission(array $user, string $permissionColumn): bool
    {
        return (int) ($user['is_admin'] ?? 0) === 1 || (int) ($user[$permissionColumn] ?? 0) === 1;
    }

    private function userAlreadyDecided(int $requestItemId, string $stage, int $userId): bool
    {
        $statement = $this->db->prepare(
            'SELECT id
             FROM purchase_item_approvals
             WHERE request_item_id = :request_item_id
               AND stage_code = :stage_code
               AND approver_id = :approver_id
             LIMIT 1'
        );
        $statement->execute([
            'request_item_id' => $requestItemId,
            'stage_code' => $stage,
            'approver_id' => $userId,
        ]);

        return $statement->fetch() !== false;
    }

    private function countApproversForStage(string $stage): int
    {
        return count($this->getStageApprovers($stage));
    }

    private function countStageDecisions(int $requestItemId, string $stage, string $decision): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*)
             FROM purchase_item_approvals
             WHERE request_item_id = :request_item_id
               AND stage_code = :stage_code
               AND decision = :decision'
        );
        $statement->execute([
            'request_item_id' => $requestItemId,
            'stage_code' => $stage,
            'decision' => $decision,
        ]);

        return (int) $statement->fetchColumn();
    }

    private function advanceItemAfterApproval(int $requestItemId, string $currentStage): void
    {
        $nextStage = match ($currentStage) {
            self::STAGE_ADMIN => self::STAGE_FINANCIAL,
            self::STAGE_FINANCIAL => self::STAGE_PURCHASING,
            default => self::STAGE_COMPLETED,
        };

        $this->updateRequestItemStage($requestItemId, $nextStage, self::ITEM_STATUS_OPEN);
        $this->advanceThroughEmptyStages($requestItemId);
    }

    private function advanceThroughEmptyStages(int $requestItemId): void
    {
        $item = $this->getRequestItemForUpdate($requestItemId);

        if ($item === null || $item['item_status'] !== self::ITEM_STATUS_OPEN) {
            return;
        }

        while (in_array($item['current_stage'], [self::STAGE_ADMIN, self::STAGE_FINANCIAL, self::STAGE_PURCHASING], true)) {
            $approverCount = $this->countApproversForStage($item['current_stage']);

            if ($item['current_stage'] === self::STAGE_PURCHASING) {
                if ($approverCount > 0) {
                    return;
                }

                $this->updateRequestItemStage($requestItemId, self::STAGE_COMPLETED, self::ITEM_STATUS_COMPLETED);
                return;
            }

            if ($approverCount > 0) {
                return;
            }

            $nextStage = $item['current_stage'] === self::STAGE_ADMIN
                ? self::STAGE_FINANCIAL
                : self::STAGE_PURCHASING;

            $this->updateRequestItemStage($requestItemId, $nextStage, self::ITEM_STATUS_OPEN);
            $item = $this->getRequestItemForUpdate($requestItemId);
        }
    }

    private function updateRequestItemStage(int $requestItemId, string $stage, string $itemStatus): void
    {
        $statement = $this->db->prepare(
            'UPDATE purchase_request_items
             SET current_stage = :current_stage,
                 item_status = :item_status
             WHERE id = :id'
        );
        $statement->execute([
            'current_stage' => $stage,
            'item_status' => $itemStatus,
            'id' => $requestItemId,
        ]);
    }

    private function refreshRequestStatus(int $requestId): void
    {
        $statement = $this->db->prepare(
            'SELECT current_stage, item_status
             FROM purchase_request_items
             WHERE request_id = :request_id'
        );
        $statement->execute(['request_id' => $requestId]);
        $items = $statement->fetchAll();

        if (empty($items)) {
            return;
        }

        $total = count($items);
        $rejected = 0;
        $completed = 0;
        $adminPending = 0;
        $financialPending = 0;
        $purchasingPending = 0;

        foreach ($items as $item) {
            if ($item['item_status'] === self::ITEM_STATUS_REJECTED) {
                $rejected++;
                continue;
            }

            if ($item['item_status'] === self::ITEM_STATUS_COMPLETED) {
                $completed++;
                continue;
            }

            if ($item['current_stage'] === self::STAGE_ADMIN) {
                $adminPending++;
            } elseif ($item['current_stage'] === self::STAGE_FINANCIAL) {
                $financialPending++;
            } elseif ($item['current_stage'] === self::STAGE_PURCHASING) {
                $purchasingPending++;
            }
        }

        $currentStage = self::STAGE_OPEN;
        $status = self::REQUEST_STATUS_OPEN;

        if ($adminPending > 0) {
            $currentStage = self::STAGE_ADMIN;
        } elseif ($financialPending > 0) {
            $currentStage = self::STAGE_FINANCIAL;
        } elseif ($purchasingPending > 0) {
            $currentStage = self::STAGE_PURCHASING;
        } elseif ($rejected === $total) {
            $currentStage = self::STAGE_COMPLETED;
            $status = self::REQUEST_STATUS_REJECTED;
        } elseif (($completed + $rejected) === $total) {
            $currentStage = self::STAGE_COMPLETED;
            $status = self::REQUEST_STATUS_COMPLETED;
        }

        $update = $this->db->prepare(
            'UPDATE purchase_requests
             SET current_stage = :current_stage,
                 status = :status
             WHERE id = :id'
        );
        $update->execute([
            'current_stage' => $currentStage,
            'status' => $status,
            'id' => $requestId,
        ]);
    }

    private function countPendingStageForUser(array $user, string $stage): int
    {
        if ($this->db === null) {
            return 0;
        }

        $permission = $this->getPermissionColumnForStage($stage);

        if ($permission === null || !$this->userHasStagePermission($user, $permission)) {
            return 0;
        }

        if ($stage === self::STAGE_PURCHASING) {
            $statement = $this->db->prepare(
                'SELECT COUNT(*)
                 FROM purchase_request_items
                 WHERE current_stage = :current_stage
                   AND item_status = :item_status'
            );
            $statement->execute([
                'current_stage' => $stage,
                'item_status' => self::ITEM_STATUS_OPEN,
            ]);

            return (int) $statement->fetchColumn();
        }

        $statement = $this->db->prepare(
            'SELECT COUNT(*)
             FROM purchase_request_items pri
             WHERE pri.current_stage = :current_stage
               AND pri.item_status = :item_status
               AND NOT EXISTS (
                   SELECT 1
                   FROM purchase_item_approvals pia
                   WHERE pia.request_item_id = pri.id
                     AND pia.stage_code = pri.current_stage
                     AND pia.approver_id = :approver_id
               )'
        );
        $statement->execute([
            'current_stage' => $stage,
            'item_status' => self::ITEM_STATUS_OPEN,
            'approver_id' => (int) $user['id'],
        ]);

        return (int) $statement->fetchColumn();
    }

    private function getStageApprovers(string $stage): array
    {
        if ($this->db === null) {
            return [];
        }

        $permissionColumn = $this->getPermissionColumnForStage($stage);

        if ($permissionColumn === null) {
            return [];
        }

        $statement = $this->db->query(
            "SELECT id, name
             FROM users
             WHERE $permissionColumn = 1
             ORDER BY name ASC"
        );
        $approvers = $statement->fetchAll();

        if (!empty($approvers)) {
            return $approvers;
        }

        $adminStatement = $this->db->query(
            'SELECT id, name
             FROM users
             WHERE is_admin = 1
             ORDER BY name ASC'
        );

        return $adminStatement->fetchAll();
    }
}

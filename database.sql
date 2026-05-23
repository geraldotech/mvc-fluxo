CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    solicitante_approval TINYINT(1) NOT NULL DEFAULT 0,
    admin_approval TINYINT(1) NOT NULL DEFAULT 0,
    financial_approval TINYINT(1) NOT NULL DEFAULT 0,
    purchasing_approval TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (
    login,
    name,
    password,
    is_admin,
    solicitante_approval,
    admin_approval,
    financial_approval,
    purchasing_approval
) VALUES (
    'admin',
    'Administrador Inicial',
    '$2y$12$rE1X6VIFjnvvFi8S6X.48OPiKD94jhvXUSkAObXRwwx1z9N2N93cG',
    1,
    1,
    1,
    1,
    1
)
ON DUPLICATE KEY UPDATE login = login;

CREATE TABLE IF NOT EXISTS items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    category VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    current_stage VARCHAR(40) NOT NULL DEFAULT 'OPEN',
    status VARCHAR(40) NOT NULL DEFAULT 'OPEN',
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_purchase_requests_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

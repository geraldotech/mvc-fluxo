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

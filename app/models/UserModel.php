<?php

declare(strict_types=1);

class UserModel extends Model
{
    public function __construct()
    {
        parent::__construct();

        if ($this->db !== null) {
            $this->ensureTable();
            $this->seedDefaultAdmin();
        }
    }

    public function findByLogin(string $login): ?array
    {
        if ($this->db === null) {
            return null;
        }

        $statement = $this->db->prepare('SELECT * FROM users WHERE login = :login LIMIT 1');
        $statement->execute(['login' => $login]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function findById(int $id): ?array
    {
        if ($this->db === null) {
            return null;
        }

        $statement = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function getAll(): array
    {
        if ($this->db === null) {
            return [];
        }

        $statement = $this->db->query(
            'SELECT id, login, name, is_admin, solicitante_approval, admin_approval, financial_approval, purchasing_approval, created_at
             FROM users
             ORDER BY name ASC'
        );

        return $statement->fetchAll();
    }

    public function create(array $data): bool
    {
        if ($this->db === null) {
            return false;
        }

        $statement = $this->db->prepare(
            'INSERT INTO users (
                login,
                name,
                password,
                is_admin,
                solicitante_approval,
                admin_approval,
                financial_approval,
                purchasing_approval
            ) VALUES (
                :login,
                :name,
                :password,
                :is_admin,
                :solicitante_approval,
                :admin_approval,
                :financial_approval,
                :purchasing_approval
            )'
        );

        return $statement->execute([
            'login' => $data['login'],
            'name' => $data['name'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'is_admin' => (int) ($data['is_admin'] ?? false),
            'solicitante_approval' => (int) ($data['solicitante_approval'] ?? false),
            'admin_approval' => (int) ($data['admin_approval'] ?? false),
            'financial_approval' => (int) ($data['financial_approval'] ?? false),
            'purchasing_approval' => (int) ($data['purchasing_approval'] ?? false),
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if ($this->db === null) {
            return false;
        }

        $fields = [
            'login = :login',
            'name = :name',
            'is_admin = :is_admin',
            'solicitante_approval = :solicitante_approval',
            'admin_approval = :admin_approval',
            'financial_approval = :financial_approval',
            'purchasing_approval = :purchasing_approval',
        ];

        $params = [
            'id' => $id,
            'login' => $data['login'],
            'name' => $data['name'],
            'is_admin' => (int) ($data['is_admin'] ?? false),
            'solicitante_approval' => (int) ($data['solicitante_approval'] ?? false),
            'admin_approval' => (int) ($data['admin_approval'] ?? false),
            'financial_approval' => (int) ($data['financial_approval'] ?? false),
            'purchasing_approval' => (int) ($data['purchasing_approval'] ?? false),
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $statement = $this->db->prepare($sql);

        return $statement->execute($params);
    }

    public function loginExistsForOtherUser(string $login, int $id): bool
    {
        if ($this->db === null) {
            return false;
        }

        $statement = $this->db->prepare('SELECT id FROM users WHERE login = :login AND id <> :id LIMIT 1');
        $statement->execute([
            'login' => $login,
            'id' => $id,
        ]);

        return $statement->fetch() !== false;
    }

    public function countAdmins(): int
    {
        if ($this->db === null) {
            return 0;
        }

        return (int) $this->db->query('SELECT COUNT(*) FROM users WHERE is_admin = 1')->fetchColumn();
    }

    private function ensureTable(): void
    {
        $sql = <<<'SQL'
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

        $this->db?->exec($sql);
    }

    private function seedDefaultAdmin(): void
    {
        if ($this->db === null) {
            return;
        }

        $count = (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();

        if ($count > 0) {
            return;
        }

        $this->create([
            'login' => 'admin',
            'name' => 'Administrador Inicial',
            'password' => 'admin123',
            'is_admin' => true,
            'solicitante_approval' => true,
            'admin_approval' => true,
            'financial_approval' => true,
            'purchasing_approval' => true,
        ]);
    }
}

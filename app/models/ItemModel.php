<?php

declare(strict_types=1);

class ItemModel extends Model
{
    public function __construct()
    {
        parent::__construct();

        if ($this->db !== null) {
            $this->ensureTable();
        }
    }

    public function getAll(): array
    {
        if ($this->db === null) {
            return [];
        }

        $statement = $this->db->query(
            'SELECT id, item_name, description, price, category, created_at
             FROM items
             ORDER BY item_name ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        if ($this->db === null) {
            return null;
        }

        $statement = $this->db->prepare('SELECT * FROM items WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $item = $statement->fetch();

        return $item === false ? null : $item;
    }

    public function create(array $data): bool
    {
        if ($this->db === null) {
            return false;
        }

        $statement = $this->db->prepare(
            'INSERT INTO items (item_name, description, price, category)
             VALUES (:item_name, :description, :price, :category)'
        );

        return $statement->execute([
            'item_name' => $data['item_name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'category' => $data['category'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if ($this->db === null) {
            return false;
        }

        $statement = $this->db->prepare(
            'UPDATE items
             SET item_name = :item_name,
                 description = :description,
                 price = :price,
                 category = :category
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'item_name' => $data['item_name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'category' => $data['category'],
        ]);
    }

    public function delete(int $id): bool
    {
        if ($this->db === null) {
            return false;
        }

        $statement = $this->db->prepare('DELETE FROM items WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }

    private function ensureTable(): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    category VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

        $this->db?->exec($sql);
    }
}

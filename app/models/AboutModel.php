<?php

declare(strict_types=1);

class AboutModel extends Model
{
    public function __construct()
    {
        parent::__construct();

        if ($this->db !== null) {
            (new UserModel());
        }
    }

    public function getUsers(): array
    {
        if ($this->db === null) {
            $this->lastError = 'Banco nao conectado. Revise as credenciais em config.php.';
            return [];
        }

        try {
            $statement = $this->db->query('SELECT * FROM users ORDER BY created_at DESC');
            $users = $statement->fetchAll();
            $this->lastError = null;

            return is_array($users) ? $users : [];
        } catch (PDOException $exception) {
            $this->lastError = 'Nao foi possivel consultar a tabela users: ' . $exception->getMessage();
            return [];
        }
    }
}

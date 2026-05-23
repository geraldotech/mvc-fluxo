<?php

declare(strict_types=1);

abstract class Model
{
    protected ?PDO $db = null;
    protected ?string $lastError = null;

    public function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        try {
            $this->db = new PDO(
                $dsn,
                DB_USERNAME,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $exception) {
            $this->db = null;
            $this->lastError = $exception->getMessage();
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}

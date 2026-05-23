<?php

declare(strict_types=1);

class PageModel extends Model
{
    public function getDatabaseStatus(): string
    {
        if ($this->db === null) {
            return 'Banco nao conectado. Ajuste as credenciais em config.php.';
        }

        return 'Conexao PDO pronta para consultas.';
    }

    public function getDownloads(): array
    {
        return [
            [
                'name' => 'Guia Inicial',
                'format' => 'PDF',
                'size' => '1.2 MB',
            ],
            [
                'name' => 'Template Base',
                'format' => 'ZIP',
                'size' => '860 KB',
            ],
            [
                'name' => 'Documentacao',
                'format' => 'TXT',
                'size' => '12 KB',
            ],
        ];
    }
}

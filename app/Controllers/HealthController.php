<?php

namespace App\Controllers;

use PDO;
use App\Core\Database;

class HealthController
{
    private PDO $db;

    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $stmt = $this->db->query("SELECT 1");
            $stmt->execute();
            echo json_encode([
                'status' => 'success',
                'app' => 'healthy',
                'database' => 'connected'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'app' => 'healthy',
                'database' => 'disconnected',
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
}

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
        $dbStatus = 'disconnected';
        $dbError = null;
        try {
            $stmt = $this->db->query("SELECT 1");
            $stmt->execute();
            $dbStatus = 'connected';
        } catch (\Exception $e) {
            $dbError = $e->getMessage();
        }

        $wantsJson = (isset($_GET['format']) && $_GET['format'] === 'json') ||
                     (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                     !isset($_SESSION['user_id']);

        if ($wantsJson) {
            header('Content-Type: application/json; charset=utf-8');
            if ($dbStatus === 'disconnected') {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'app' => 'healthy',
                    'database' => 'disconnected',
                    'message' => $dbError
                ]);
            } else {
                echo json_encode([
                    'status' => 'success',
                    'app' => 'healthy',
                    'database' => 'connected'
                ]);
            }
            exit;
        }

        render('health/index', [
            'title' => 'Trạng Thái Hệ Thống',
            'app_status' => 'healthy',
            'db_status' => $dbStatus,
            'db_error' => $dbError,
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown/Local',
            'server_time' => date('d/m/Y H:i:s'),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ]);
    }
}

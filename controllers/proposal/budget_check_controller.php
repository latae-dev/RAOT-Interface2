<?php

header('Content-Type: application/json; charset=utf-8');

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

require_once __DIR__ . '/../../configs/database.php';
require_once __DIR__ . '/../../services/proposal/budget_check_service.php';
require_once __DIR__ . '/../../services/proposal/sap_budget_client.php';
require_once __DIR__ . '/../../repositories/proposal/budget_parameter_repository.php';

class BudgetCheckController
{
    private BudgetCheckService $service;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect('raot_db_qas');

        if (!$db) {
            throw new RuntimeException('Database connection failed');
        }

        $config = require __DIR__ . '/../../configs/sap_budget_config.php';
        $parameterRepository = new BudgetParameterRepository($db);
        $sapClient = new SapBudgetClient($config);
        $this->service = new BudgetCheckService($parameterRepository, $sapClient, $config);
    }

    public function processRequest(): void
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_GET['action'] ?? '';

            if ($method !== 'POST' || $action !== 'check') {
                http_response_code(405);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Method not allowed',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $payload = json_decode(file_get_contents('php://input'), true);
            if (!is_array($payload)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid JSON payload',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $result = $this->service->checkBudget($payload);
            $statusCode = ($result['status'] ?? '') === 'error' ? 422 : 200;

            http_response_code($statusCode);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            error_log('BudgetCheckController: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Internal server error',
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}

$controller = new BudgetCheckController();
$controller->processRequest();

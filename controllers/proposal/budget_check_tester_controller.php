<?php

header('Content-Type: application/json; charset=utf-8');

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/' . date('Y-m-d') . '.log');

require_once __DIR__ . '/../../configs/sap_budget_config.php';
require_once __DIR__ . '/../../services/proposal/sap_budget_client.php';
require_once __DIR__ . '/../../services/proposal/budget_check_tester_service.php';

class BudgetCheckTesterController
{
    private BudgetCheckTesterService $service;
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../configs/sap_budget_config.php';

        if (empty($this->config['debug'])) {
            throw new RuntimeException('Budget check tester is disabled (set SAP_BUDGET_DEBUG=true)');
        }

        $this->service = new BudgetCheckTesterService(new SapBudgetClient($this->config), $this->config);
    }

    public function processRequest(): void
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_GET['action'] ?? '';

            if ($method !== 'POST' || $action !== 'run') {
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

            $sapParams = $payload['sap_params'] ?? [];
            if (!is_array($sapParams)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'sap_params must be an object',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $customAmounts = $payload['custom_amounts'] ?? [];
            if (!is_array($customAmounts)) {
                $customAmounts = [];
            }

            $result = $this->service->runScenarios($sapParams, $customAmounts);
            $statusCode = ($result['status'] ?? '') === 'success' ? 200 : 422;

            http_response_code($statusCode);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            error_log('BudgetCheckTesterController: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}

try {
    $controller = new BudgetCheckTesterController();
    $controller->processRequest();
} catch (Throwable $e) {
    error_log('BudgetCheckTesterController bootstrap: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

<?php

ini_set('display_errors', '0');
ob_start();

include_once '../../configs/authMiddleware.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../configs/database.php';
include_once '../../configs/constants.php';
include_once '../../services/dashboard/dashboard_user_stats_service.php';
include_once '../../services/dashboard/dashboard_overview_service.php';
include_once '../../services/dashboard/dashboard_annual_budget_service.php';
include_once '../../services/proposal/sap_budget_client.php';
include_once '../../repositories/user/user_dashboard_repository.php';
include_once '../../repositories/dashboard/dashboard_document_repository.php';

class DashboardController
{
    private DashboardUserStatsService $statsService;
    private DashboardOverviewService $overviewService;
    private DashboardAnnualBudgetService $annualBudgetService;

    public function __construct()
    {
        checkAuth();

        $database = new Database();
        $db = $database->connect('raot_db_qas');
        if (!$db) {
            $this->sendJson([
                'status' => 'error',
                'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้',
            ], 500);
            exit;
        }

        $config = require __DIR__ . '/../../configs/dashboard_config.php';
        $sapConfig = require __DIR__ . '/../../configs/sap_budget_config.php';
        $userRepository = new UserDashboardRepository($db);
        $documentRepository = new DashboardDocumentRepository($db);
        $this->statsService = new DashboardUserStatsService($userRepository, $config);
        $this->overviewService = new DashboardOverviewService($this->statsService, $documentRepository);
        $this->annualBudgetService = new DashboardAnnualBudgetService(new SapBudgetClient($sapConfig), $sapConfig);
    }

    public function processRequest(): void
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $action = $_GET['action'] ?? '';

            if ($action === 'user_stats' && $method === 'GET') {
                $this->getUserStats();
                return;
            }

            if ($action === 'overview' && $method === 'GET') {
                $this->getOverview();
                return;
            }

            if ($action === 'annual_budget' && ($method === 'POST' || $method === 'GET')) {
                $this->getAnnualBudget();
                return;
            }

            $this->sendJson(['status' => 'error', 'message' => 'Method not allowed'], 405);
        } catch (Throwable $e) {
            $this->sendJson([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดภายในระบบ',
            ], 500);
        }
    }

    private function sendJson(array $payload, int $statusCode = 200): void
    {
        if (ob_get_length()) {
            ob_clean();
        }

        http_response_code($statusCode);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function getUserStats(): void
    {
        $userId = getCurrentUserId();
        $stats = $this->statsService->getUserStats($userId ? (int) $userId : null);

        $this->sendJson([
            'status' => 'success',
            'data' => $stats,
        ]);
    }

    private function getOverview(): void
    {
        $userId = getCurrentUserId();
        $overview = $this->overviewService->getOverview($userId ? (int) $userId : null);

        $this->sendJson([
            'status' => 'success',
            'data' => $overview,
        ]);
    }

    private function getAnnualBudget(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        $year = null;
        if (isset($payload['year'])) {
            $year = (int) $payload['year'];
        } elseif (isset($_GET['year'])) {
            $year = (int) $_GET['year'];
        }

        $result = $this->annualBudgetService->getAnnualBudgetReport($year);

        if (($result['status'] ?? '') === 'error') {
            $this->sendJson($result, 422);
            return;
        }

        $this->sendJson($result);
    }
}

try {
    $controller = new DashboardController();
    $controller->processRequest();
} catch (Throwable $e) {
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'ระบบไม่สามารถเริ่มต้นการทำงานได้',
    ], JSON_UNESCAPED_UNICODE);
}
